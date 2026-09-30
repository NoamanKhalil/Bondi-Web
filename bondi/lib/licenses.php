<?php
// The license rules: the current price, making licenses, activating a Mac (one Mac per license), and
// Paddle's payment and refund notifications.

/** The price to offer now, from the current_offer view. */
function current_offer(): array
{
    $offer = one('SELECT tier, paddle_price_id, amount_cents, currency, remaining FROM current_offer');
    if ($offer === null) {
        fail('no_price', 'No price is set up on the server.', 503);
    }
    return [
        'tier' => $offer['tier'],
        'price' => number_format($offer['amount_cents'] / 100, 2, '.', ''),
        'currency' => $offer['currency'],
        'launch_remaining' => $offer['remaining'] === null ? null : (int)$offer['remaining'],
        'paddle_price_id' => $offer['paddle_price_id'],
    ];
}

/** The customer with this email, made if new. */
function customer_id(string $email, ?string $paddleCustomerId = null): int
{
    $email = strtolower(trim($email));
    $existing = one('SELECT id, paddle_customer_id FROM customers WHERE email = ?', [$email]);
    if ($existing !== null) {
        if ($paddleCustomerId !== null && $existing['paddle_customer_id'] === null) {
            run('UPDATE customers SET paddle_customer_id = ? WHERE id = ?', [$paddleCustomerId, $existing['id']]);
        }
        return (int)$existing['id'];
    }
    run('INSERT INTO customers (email, paddle_customer_id) VALUES (?, ?)', [$email, $paddleCustomerId]);
    return (int)db()->lastInsertId();
}

/** Makes a license; returns [id, key]. The key is shown or emailed once and only its hash is kept. */
function create_license(int $customerId, string $tier, ?string $transactionId, ?int $amountCents, ?string $currency): array
{
    $key = new_license_key();
    $normalized = normalize_key($key);
    run('INSERT INTO licenses (customer_id, key_hash, key_last4, price_tier, paddle_transaction_id, amount_cents, currency)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$customerId, key_hash($normalized), key_last4($normalized), $tier, $transactionId, $amountCents, $currency]);
    return [(int)db()->lastInsertId(), $key];
}

/** Gives a license a new key (lost key, or on request); the old key stops working. Returns the key. */
function rotate_key(int $licenseId): string
{
    $key = new_license_key();
    $normalized = normalize_key($key);
    run('UPDATE licenses SET key_hash = ?, key_last4 = ? WHERE id = ?', [key_hash($normalized), key_last4($normalized), $licenseId]);
    return $key;
}

/**
 * Activates this Mac on a license. One Mac per license: if another Mac holds it, the answer says which,
 * so the user can deactivate it there. The same Mac activating again gets a fresh token.
 */
function activate_device(array $license, string $deviceHash, ?string $deviceName): array
{
    if ($license['status'] !== 'active') {
        return ['error' => $license['status']];
    }
    $others = all('SELECT device_name FROM activations
                   WHERE license_id = ? AND deactivated_at IS NULL AND device_hash <> ?', [$license['id'], $deviceHash]);
    if (count($others) >= (int)$license['max_activations']) {
        return ['error' => 'in_use', 'device_name' => $others[0]['device_name'] ?? 'another Mac'];
    }
    [$token, $tokenHash] = new_secret();
    run('INSERT INTO activations (license_id, device_hash, device_name, token_hash)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE device_name = VALUES(device_name), token_hash = VALUES(token_hash),
                                 activated_at = CURRENT_TIMESTAMP, last_check_at = CURRENT_TIMESTAMP, deactivated_at = NULL',
        [$license['id'], $deviceHash, $deviceName, $tokenHash]);
    return ['token' => $token];
}

/** What the app shows about its license. */
function license_summary(array $license): array
{
    $email = one('SELECT email FROM customers WHERE id = ?', [$license['customer_id']])['email'] ?? '';
    return ['status' => $license['status'], 'key_last4' => $license['key_last4'], 'email' => $email];
}

// MARK: Paddle notifications

/** Handles one verified notification. Throws on a problem, so the event stays unprocessed and Paddle retries. */
function handle_paddle_event(array $event): void
{
    $type = (string)($event['event_type'] ?? '');
    $data = $event['data'] ?? [];
    if ($type === 'transaction.completed') {
        handle_transaction_completed($data);
    } elseif ($type === 'adjustment.created' || $type === 'adjustment.updated') {
        handle_adjustment($data);
    }
    // Other events are recorded but need nothing.
}

function handle_transaction_completed(array $transaction): void
{
    $transactionId = (string)($transaction['id'] ?? '');
    if ($transactionId === '' || one('SELECT id FROM licenses WHERE paddle_transaction_id = ?', [$transactionId]) !== null) {
        return; // already issued
    }
    $priceId = (string)($transaction['items'][0]['price']['id'] ?? $transaction['items'][0]['price_id'] ?? '');
    $tier = one('SELECT tier FROM price_tiers WHERE paddle_price_id = ?', [$priceId])['tier'] ?? null;
    if ($tier === null) {
        throw new RuntimeException("Unknown Paddle price $priceId: add it to price_tiers.");
    }
    $customerId = (string)($transaction['customer_id'] ?? '');
    $email = $customerId === '' ? null : paddle_customer_email($customerId);
    if ($email === null) {
        throw new RuntimeException("Couldn't read the buyer's email for customer $customerId from Paddle.");
    }
    $total = $transaction['details']['totals']['grand_total'] ?? null;
    $currency = $transaction['currency_code'] ?? null;
    $claimHash = $transaction['custom_data']['claim'] ?? null;

    db()->beginTransaction();
    try {
        [$licenseId, $key] = create_license(customer_id($email, $customerId), $tier, $transactionId,
                                            $total === null ? null : (int)$total, $currency);
        if (is_string($claimHash) && preg_match('/^[a-f0-9]{64}$/', $claimHash)) {
            run('UPDATE checkout_claims SET license_id = ?, paddle_transaction_id = ?, key_plain = ?
                 WHERE claim_hash = ? AND license_id IS NULL', [$licenseId, $transactionId, $key, $claimHash]);
        }
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
    send_key_email($email, $key, 'license', $licenseId);
}

/** A full refund or a chargeback ends the license; the app locks at its next check. */
function handle_adjustment(array $adjustment): void
{
    $action = $adjustment['action'] ?? '';
    $approved = ($adjustment['status'] ?? '') === 'approved';
    $full = ($adjustment['type'] ?? 'full') === 'full';
    $transactionId = (string)($adjustment['transaction_id'] ?? '');
    if ($transactionId === '' || !$approved) {
        return;
    }
    if ($action === 'refund' && $full) {
        run("UPDATE licenses SET status = 'refunded', status_changed_at = CURRENT_TIMESTAMP
             WHERE paddle_transaction_id = ? AND status = 'active'", [$transactionId]);
    } elseif ($action === 'chargeback') {
        run("UPDATE licenses SET status = 'revoked', status_changed_at = CURRENT_TIMESTAMP
             WHERE paddle_transaction_id = ? AND status = 'active'", [$transactionId]);
    }
}
