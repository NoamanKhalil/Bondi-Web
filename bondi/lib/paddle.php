<?php
// Paddle Billing: checking that a notification really came from Paddle, and looking up a buyer's email.

/**
 * Paddle signs each notification: header "Paddle-Signature: ts=1671552777;h1=…", where h1 is the
 * HMAC-SHA256 of "ts:body" with the destination's secret key. Anything else, or older than 5 minutes
 * (a replay), is refused.
 */
function paddle_signature_valid(string $body, string $header, string $secret, int $tolerance = 300): bool
{
    $parts = [];
    foreach (explode(';', $header) as $piece) {
        [$name, $value] = array_pad(explode('=', trim($piece), 2), 2, '');
        $parts[$name][] = $value;
    }
    $timestamp = (int)($parts['ts'][0] ?? 0);
    if ($timestamp === 0 || abs(time() - $timestamp) > $tolerance || $secret === '') {
        return false;
    }
    $expected = hash_hmac('sha256', $timestamp . ':' . $body, $secret);
    foreach ($parts['h1'] ?? [] as $signature) {
        if (hash_equals($expected, $signature)) {
            return true;
        }
    }
    return false;
}

function paddle_api_base(): string
{
    return config('paddle.environment') === 'production' ? 'https://api.paddle.com' : 'https://sandbox-api.paddle.com';
}

/** GET from Paddle's API; the decoded "data", or null. */
function paddle_get(string $path): ?array
{
    $curl = curl_init(paddle_api_base() . $path);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . config('paddle.api_key'), 'Accept: application/json'],
    ]);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if (!is_string($response) || $status !== 200) {
        return null;
    }
    $decoded = json_decode($response, true);
    return $decoded['data'] ?? null;
}

/** The buyer's email: a completed transaction names the customer, not their email. */
function paddle_customer_email(string $customerId): ?string
{
    if (config('test_mode')) {
        return 'test+' . preg_replace('/[^a-z0-9_]/i', '', $customerId) . '@example.com';
    }
    $customer = paddle_get('/customers/' . rawurlencode($customerId));
    $email = $customer['email'] ?? null;
    return is_string($email) ? strtolower(trim($email)) : null;
}
