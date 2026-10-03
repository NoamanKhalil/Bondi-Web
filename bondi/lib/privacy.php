<?php
// Unsubscribing, "Your data" (see it, download it, delete it) and the record of each request (GDPR, CCPA).
//
// - Unsubscribe links carry the sign-up's number and a signature only this server can make, so no one can
//   unsubscribe someone else by guessing. The web page asks for one more click, because mail scanners open links.
// - "Your data" emails a one-time link (only its hash is stored) to prove the request comes from that inbox.
// - Each request gets a confirmation number (BR-XXXXX-XXXX). The record keeps the number, the dates, what was
//   removed and what was kept, and a keyed fingerprint of the email (HMAC), never the email itself. Kept 3 years.

/** True once sql/006_privacy.sql has been imported. */
function privacy_ready(): bool
{
    static $ready = null;
    return $ready ??= one("SHOW TABLES LIKE 'data_requests'") !== null
        && one("SHOW COLUMNS FROM signups LIKE 'unsubscribed_at'") !== null;
}

/** The server's secret for signatures and fingerprints: app_secret in config.php, or one made from its settings. */
function app_secret(): string
{
    $secret = (string)config('app_secret', '');
    return $secret !== '' ? $secret : hash('sha256', 'bondi|' . config('db.pass') . '|' . config('db.name'));
}

/** The keyed fingerprint of an email: the same email always gives the same code, but it can't be reversed. */
function email_fingerprint(string $email): string
{
    return hash_hmac('sha256', strtolower(trim($email)), app_secret());
}

/** A confirmation number like BR-7K3QX-M2PZ (Crockford's alphabet, as license keys use). */
function new_request_code(): string
{
    do {
        $characters = '';
        foreach (str_split(random_bytes(9)) as $byte) {
            $characters .= KEY_ALPHABET[ord($byte) % 32];
        }
        $code = 'BR-' . substr($characters, 0, 5) . '-' . substr($characters, 5, 4);
    } while (one('SELECT id FROM data_requests WHERE code = ?', [$code]) !== null);
    return $code;
}

/** Records a request that's been done; returns its confirmation number. */
function record_request(string $kind, string $channel, string $email, ?string $removed = null, ?string $kept = null): string
{
    $code = new_request_code();
    run('INSERT INTO data_requests (code, kind, channel, email_hmac, completed_at, removed, kept) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?)',
        [$code, $kind, $channel, email_fingerprint($email), $removed, $kept]);
    if (random_int(1, 50) === 1) {
        run('DELETE FROM data_requests WHERE requested_at < NOW() - INTERVAL 3 YEAR'); // the record itself goes after 3 years
    }
    return $code;
}

// MARK: Unsubscribing

function unsubscribe_signature(int $signupId): string
{
    return substr(hash_hmac('sha256', "unsubscribe|$signupId", app_secret()), 0, 32);
}

/** The page an email's "Unsubscribe" link opens. */
function unsubscribe_url(int $signupId): string
{
    return config('base_url') . "/unsubscribe/?id=$signupId&s=" . unsubscribe_signature($signupId);
}

/** The address mail apps call for their own one-click Unsubscribe button (RFC 8058). */
function unsubscribe_one_click_url(int $signupId): string
{
    return config('base_url') . "/api/unsubscribe?id=$signupId&s=" . unsubscribe_signature($signupId);
}

/** Headers that make Gmail and Apple Mail show their Unsubscribe button. */
function unsubscribe_headers(?int $signupId): array
{
    $mailto = '<mailto:' . config('support_email') . '?subject=unsubscribe>';
    if ($signupId === null || !privacy_ready()) {
        return ["List-Unsubscribe: $mailto"];
    }
    return ['List-Unsubscribe: <' . unsubscribe_one_click_url($signupId) . ">, $mailto", 'List-Unsubscribe-Post: List-Unsubscribe=One-Click'];
}

/** The sign-up an unsubscribe link points to, if its signature is right. */
function signup_for_unsubscribe_link(mixed $id, mixed $signature): ?array
{
    $id = (int)$id;
    if ($id < 1 || !is_string($signature) || !hash_equals(unsubscribe_signature($id), $signature)) {
        return null;
    }
    return one('SELECT * FROM signups WHERE id = ?', [$id]);
}

/** Unsubscribes a sign-up; returns the confirmation number. A thank-you email follows unless $quiet. */
function unsubscribe_signup(array $signup, string $channel, bool $quiet = false): string
{
    run('UPDATE signups SET unsubscribed_at = COALESCE(unsubscribed_at, CURRENT_TIMESTAMP) WHERE id = ?', [$signup['id']]);
    $code = record_request('unsubscribe', $channel, $signup['email'], 'email updates', 'your sign-up, until you ask to delete it');
    if (!$quiet) {
        $mail = unsubscribed_email($signup['name'], $code);
        send_mail($signup['email'], $mail['subject'], $mail['text'], $mail['html'], email_images());
    }
    return $code;
}

function resubscribe_signup(array $signup): void
{
    run('UPDATE signups SET unsubscribed_at = NULL WHERE id = ?', [$signup['id']]);
}

// MARK: Your data

/** Makes a one-time link for the Your data page (24 hours); returns its token. */
function create_data_link(string $email): string
{
    [$token, $hash] = new_secret();
    run('INSERT INTO data_links (token_hash, email, expires_at) VALUES (?, ?, NOW() + INTERVAL 1 DAY)', [$hash, strtolower(trim($email))]);
    run('DELETE FROM data_links WHERE expires_at < NOW() - INTERVAL 1 DAY OR used_at < NOW() - INTERVAL 1 DAY');
    return $token;
}

/** The email a Your data link belongs to, while it's unused and in date. */
function data_link_email(mixed $token): ?string
{
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    $row = one('SELECT email FROM data_links WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()', [hash('sha256', $token)]);
    return $row['email'] ?? null;
}

function use_data_link(string $token): void
{
    run('UPDATE data_links SET used_at = CURRENT_TIMESTAMP WHERE token_hash = ?', [hash('sha256', $token)]);
}

/** True if we hold anything about this email (a sign-up or a purchase). */
function holds_data_for(string $email): bool
{
    return one('SELECT id FROM signups WHERE email = ?', [$email]) !== null
        || one('SELECT id FROM customers WHERE email = ?', [$email]) !== null;
}

/** Everything we hold about an email, for the Your data page and its download. */
function person_data(string $email): array
{
    $signup = one('SELECT name, email, country, country_source, time_zone, ip, created_at, updated_at, '
                . (signups_have_page() ? 'page, ' : '') . 'unsubscribed_at FROM signups WHERE email = ?', [$email]);
    $customer = one('SELECT id, email, created_at FROM customers WHERE email = ?', [$email]);
    $licenses = [];
    if ($customer !== null) {
        foreach (all('SELECT id, key_last4, price_tier, amount_cents, currency, status, paddle_transaction_id, created_at
                      FROM licenses WHERE customer_id = ? ORDER BY id', [$customer['id']]) as $license) {
            $license['macs'] = all('SELECT device_name, activated_at, last_check_at, deactivated_at FROM activations WHERE license_id = ?', [$license['id']]);
            unset($license['id']);
            $licenses[] = $license;
        }
        unset($customer['id']);
    }
    return [
        'email' => $email,
        'sign_up' => $signup,
        'emails_sent' => all('SELECT kind, sent_at FROM email_log WHERE email = ? ORDER BY sent_at', [$email]),
        'customer' => $customer,
        'licenses' => $licenses,
        'requests' => all('SELECT code, kind, completed_at FROM data_requests WHERE email_hmac = ? ORDER BY id', [email_fingerprint($email)]),
        'note' => 'Bondi keeps your usage history only on your Mac; delete it in Bondi, Settings, Data. Trials are recorded by an anonymous device ID, not your email.',
        'as_of_utc' => now(),
    ];
}

/**
 * Deletes what we hold about an email, except purchase records (kept for tax law, and so the license keeps
 * working). Records the request; returns [code, removed, kept]. A confirmation email follows unless $quiet.
 */
function delete_person(string $email, string $channel, bool $quiet = false): array
{
    $email = strtolower(trim($email));
    $removed = [];
    $kept = null;
    db()->beginTransaction();
    try {
        if (run('DELETE FROM signups WHERE email = ?', [$email]) > 0) {
            $removed[] = 'sign-up (name, email, IP address, country)';
        }
        if (run('DELETE FROM email_log WHERE email = ?', [$email]) > 0) {
            $removed[] = 'our record of emails sent';
        }
        run('DELETE FROM data_links WHERE email = ?', [$email]);
        $customer = one('SELECT id FROM customers WHERE email = ?', [$email]);
        if ($customer !== null) {
            if (one('SELECT id FROM licenses WHERE customer_id = ? LIMIT 1', [$customer['id']]) !== null) {
                $kept = 'purchase record and license (tax law requires it, and it keeps your license working)';
            } else {
                run('DELETE FROM customers WHERE id = ?', [$customer['id']]);
                $removed[] = 'customer record';
            }
        }
        $removedText = match (count($removed)) {
            0 => 'nothing (we held no other data for this email)',
            1 => $removed[0],
            default => implode(', ', array_slice($removed, 0, -1)) . ' and ' . end($removed),
        };
        $code = record_request('delete', $channel, $email, $removedText, $kept);
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
    if (!$quiet) {
        $mail = deleted_email($code, $removedText, $kept);
        send_mail($email, $mail['subject'], $mail['text'], $mail['html'], email_images());
    }
    return [$code, $removedText, $kept];
}

/** Requests matching a confirmation number or an email (through its fingerprint), for the admin page. */
function find_requests(string $query): array
{
    $query = trim($query);
    if ($query === '') {
        return all('SELECT * FROM data_requests ORDER BY id DESC LIMIT 50');
    }
    return all('SELECT * FROM data_requests WHERE code = ? OR email_hmac = ? ORDER BY id DESC',
               [strtoupper($query), email_fingerprint($query)]);
}

// MARK: The pages' frame (unsubscribe/ and your-data/), in the privacy policy's style

function privacy_page_start(string $title): void
{
    header('Cache-Control: no-store');
    header('Referrer-Policy: no-referrer'); // the one-time link never travels on to another site
    $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $toggle = '<button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode"><svg class="moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5a8.5 8.5 0 1 0 10.7 10.7z"/></svg><svg class="sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M5.3 18.7l1.6-1.6M17.1 6.9l1.6-1.6"/></svg></button>';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . $h($title) . ' · Bondi</title><meta name="robots" content="noindex"><meta name="referrer" content="no-referrer">'
       . '<link rel="icon" type="image/png" href="/assets/favicon.png"><link rel="stylesheet" href="/assets/legal.css"><script src="/assets/theme.js"></script>'
       . '<style>
.panel { background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 22px 24px; margin: 18px 0; }
.panel h2 { margin-top: 0; }
.btn { display: inline-block; font: 600 1rem/1 "IBM Plex Sans", -apple-system, sans-serif; padding: 13px 22px; border-radius: 980px; border: 1px solid var(--bondi); background: var(--bondi); color: var(--ground); cursor: pointer; text-decoration: none; }
.btn.quiet { background: none; color: var(--bondi); }
.btn.danger { background: #c4314b; border-color: #c4314b; color: #fff; }
.row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-top: 14px; }
input[type=email] { font: inherit; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--line); background: var(--surface); color: var(--ink); min-width: 0; flex: 1 1 240px; }
.code { font: 600 1.25rem/1 "IBM Plex Mono", ui-monospace, monospace; letter-spacing: .04em; color: var(--bondi); background: var(--surface); border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; display: inline-block; margin: 6px 0 4px; }
dl { display: grid; grid-template-columns: max-content 1fr; gap: 6px 18px; margin: 0; } dt { color: var(--ink); font-weight: 600; } dd { margin: 0; color: var(--soft); overflow-wrap: anywhere; }
.note { font-size: .9rem; }
label.check { display: flex; gap: 10px; align-items: flex-start; margin-top: 12px; color: var(--soft); }
.flash { background: var(--surface); border-left: 3px solid var(--bondi); padding: 12px 14px; border-radius: 8px; color: var(--ink); }
.error { color: #c4314b; font-weight: 600; }
</style></head><body>'
       . '<header><a href="/">Bondi</a><nav><a href="/privacy/">Privacy</a><a href="/your-data/">Your data</a>' . $toggle . '</nav></header><main>';
}

function privacy_page_end(): never
{
    echo '</main><footer><span>© 2026 Jabble Super Intelligence Inc.</span><a href="/privacy/">Privacy policy</a>'
       . '<a href="mailto:' . htmlspecialchars((string)config('support_email')) . '">' . htmlspecialchars((string)config('support_email')) . '</a><a href="/">trybondi.app</a></footer></body></html>';
    exit;
}
