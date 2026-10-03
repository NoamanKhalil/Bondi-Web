<?php
// Bondi license API, at https://trybondi.app/api/<action>. The app sends only an anonymous device ID
// (a hash made on the Mac), the Mac's name when activating, and the license key or token. Nothing
// about apps or usage.
//
//   GET  offer        the current price and launch licenses left
//   POST trial        start or read this Mac's 7-day trial
//   POST checkout     a one-time claim and the buy page's address, before opening the browser
//   POST claim        after checkout: the license for that claim, activated on this Mac
//   POST activate     a pasted key, activated on this Mac
//   POST check        is this Mac's license still good (the app asks about once a day)
//   POST deactivate   free this Mac's license, to move it to another Mac
//   POST recover      email a new key to the buyer's address
//   POST paddle       Paddle's payment and refund notifications
//   POST signup       the website's "Sign up for updates": name and email (plus the visitor's IP and country),
//                     and page: "beta" from the beta page
//   GET  interest     how many people have signed up, and how many on the beta page (shown on that page)
//   POST unsubscribe  mail apps' own one-click Unsubscribe button (RFC 8058): ?id=<sign-up>&s=<signature>

// The private code lives beside public_html (best) or inside it as public_html/bondi, locked by its .htaccess.
require is_file(dirname(__DIR__, 2) . '/bondi/bootstrap.php') ? dirname(__DIR__, 2) . '/bondi/bootstrap.php' : dirname(__DIR__) . '/bondi/bootstrap.php';

$path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$action = $_GET['action'] ?? (preg_match('~(?:^|/)api/([a-z]+)$~', $path, $match) ? $match[1] : '');

try {
    match ($action) {
        'offer' => offer(),
        'trial' => trial(),
        'checkout' => checkout(),
        'claim' => claim(),
        'activate' => activate(),
        'check' => check(),
        'deactivate' => deactivate(),
        'recover' => recover(),
        'paddle' => paddle(),
        'signup' => signup(),
        'interest' => interest(),
        'unsubscribe' => unsubscribe_one_click(),
        default => fail('not_found', 'Unknown API call.', 404),
    };
} catch (Throwable $error) {
    error_log('Bondi API ' . $action . ': ' . $error->getMessage());
    fail('server', 'Something went wrong on the license server. Try again later.', 500);
}

function offer(): never
{
    $offer = current_offer();
    unset($offer['paddle_price_id']);
    json_out($offer + ['server_time' => iso(now())]);
}

function trial(): never
{
    require_post();
    $device = device_hash_input();
    limit_or_fail('trial:' . client_key(), 30, 3600);
    $version = mb_substr(trim((string)(input()['app_version'] ?? '')), 0, 20) ?: null;
    run('INSERT INTO trials (device_hash, app_version) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE last_seen_at = CURRENT_TIMESTAMP, app_version = VALUES(app_version)', [$device, $version]);
    $trial = one('SELECT started_at FROM trials WHERE device_hash = ?', [$device]);
    $ends = gmdate('Y-m-d H:i:s', strtotime($trial['started_at'] . ' UTC') + (int)config('trial_days', 7) * 86400);
    json_out(['started_at' => iso($trial['started_at']), 'ends_at' => iso($ends), 'server_time' => iso(now())]);
}

function checkout(): never
{
    require_post();
    $device = device_hash_input();
    limit_or_fail('checkout:' . client_key(), 20, 3600);
    [$secret, $hash] = new_secret();
    run('INSERT INTO checkout_claims (claim_hash, device_hash, device_name) VALUES (?, ?, ?)',
        [$hash, $device, device_name_input()]);
    $offer = current_offer();
    unset($offer['paddle_price_id']);
    json_out(['claim' => $secret, 'url' => config('base_url') . '/buy/?claim=' . $secret] + $offer);
}

function claim(): never
{
    require_post();
    $secret = (string)(input()['claim'] ?? '');
    limit_or_fail('claim:' . client_key(), 120, 3600);
    $claim = one('SELECT * FROM checkout_claims WHERE claim_hash = ?', [hash('sha256', $secret)]);
    if ($claim === null || strtotime($claim['created_at'] . ' UTC') < time() - 2 * 86400) {
        fail('not_found', 'This checkout has expired. If you paid, your key is in your email.', 404);
    }
    if ($claim['license_id'] === null) {
        json_out(['status' => 'pending']);
    }
    if ($claim['claimed_at'] !== null) {
        json_out(['status' => 'claimed', 'message' => 'Already activated. If this Mac lost it, enter the key from your email.']);
    }
    $license = one('SELECT * FROM licenses WHERE id = ?', [$claim['license_id']]);
    $result = activate_device($license, $claim['device_hash'], $claim['device_name']);
    run('UPDATE checkout_claims SET claimed_at = CURRENT_TIMESTAMP, key_plain = NULL WHERE id = ?', [$claim['id']]);
    if (isset($result['error'])) {
        fail($result['error'], 'The license was bought but couldn\'t be activated here. Enter the key from your email.', 409, $result);
    }
    json_out(license_summary($license) + ['token' => $result['token'], 'key' => $claim['key_plain']]);
}

function activate(): never
{
    require_post();
    limit_or_fail('activate:' . client_key(), 10, 600);
    $device = device_hash_input();
    $normalized = normalize_key((string)(input()['key'] ?? ''));
    $license = $normalized === null ? null : one('SELECT * FROM licenses WHERE key_hash = ?', [key_hash($normalized)]);
    if ($license === null) {
        fail('invalid_key', 'That key isn\'t valid. Check it against your email, or use Find My Key.', 404);
    }
    $result = activate_device($license, $device, device_name_input());
    if (($result['error'] ?? null) === 'in_use') {
        fail('in_use', 'This license is in use on ' . $result['device_name'] . '. Choose Deactivate This Mac there first.', 409, $result);
    }
    if (isset($result['error'])) {
        fail($result['error'], 'This license was ' . $result['error'] . ' and no longer works.', 403);
    }
    json_out(license_summary($license) + ['token' => $result['token']]);
}

/** The activation for a token from the app, with its license. */
function activation_for_token(): ?array
{
    $token = (string)(input()['token'] ?? '');
    return one('SELECT a.id AS activation_id, a.deactivated_at, a.device_hash AS activation_device, l.*
                FROM activations a JOIN licenses l ON l.id = a.license_id WHERE a.token_hash = ?', [hash('sha256', $token)]);
}

function check(): never
{
    require_post();
    limit_or_fail('check:' . client_key(), 120, 3600);
    $device = device_hash_input();
    $row = activation_for_token();
    if ($row === null || $row['deactivated_at'] !== null || $row['activation_device'] !== $device) {
        json_out(['status' => 'deactivated', 'server_time' => iso(now())]);
    }
    run('UPDATE activations SET last_check_at = CURRENT_TIMESTAMP WHERE id = ?', [$row['activation_id']]);
    json_out(license_summary($row) + ['server_time' => iso(now())]);
}

function deactivate(): never
{
    require_post();
    limit_or_fail('deactivate:' . client_key(), 30, 3600);
    $row = activation_for_token();
    if ($row !== null) {
        run('UPDATE activations SET deactivated_at = CURRENT_TIMESTAMP WHERE id = ? AND deactivated_at IS NULL', [$row['activation_id']]);
    }
    json_out(['status' => 'deactivated']);
}

function recover(): never
{
    require_post();
    $email = strtolower(trim((string)(input()['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail('email', 'Enter the email you bought Bondi with.');
    }
    limit_or_fail('recover:' . client_key(), 10, 3600);
    // The same answer whether or not the email has a license, so no one can test addresses.
    $answer = ['status' => 'sent', 'message' => 'If that email bought Bondi, a new key is on its way.'];
    if (!within_limit('recover:' . hash('sha256', $email), 3, 3600)) {
        json_out($answer);
    }
    $licenses = all("SELECT l.id FROM licenses l JOIN customers c ON c.id = l.customer_id
                     WHERE c.email = ? AND l.status = 'active'", [$email]);
    foreach ($licenses as $license) {
        send_key_email($email, rotate_key((int)$license['id']), 'recovery', (int)$license['id']);
    }
    json_out($answer);
}

function paddle(): never
{
    require_post();
    $body = file_get_contents('php://input') ?: '';
    if (!paddle_signature_valid($body, $_SERVER['HTTP_PADDLE_SIGNATURE'] ?? '', (string)config('paddle.webhook_secret'))) {
        fail('signature', 'Bad signature.', 401);
    }
    $event = json_decode($body, true);
    $eventId = (string)($event['event_id'] ?? '');
    if ($eventId === '') {
        fail('event', 'No event ID.');
    }
    run('INSERT IGNORE INTO webhook_events (event_id, event_type, occurred_at, payload) VALUES (?, ?, ?, ?)',
        [$eventId, (string)($event['event_type'] ?? ''),
         isset($event['occurred_at']) ? gmdate('Y-m-d H:i:s', strtotime($event['occurred_at'])) : null, $body]);
    $stored = one('SELECT processed_at FROM webhook_events WHERE event_id = ?', [$eventId]);
    if ($stored['processed_at'] !== null) {
        json_out(['status' => 'already_processed']);
    }
    try {
        handle_paddle_event($event);
        run('UPDATE webhook_events SET processed_at = CURRENT_TIMESTAMP, error = NULL WHERE event_id = ?', [$eventId]);
        json_out(['status' => 'ok']);
    } catch (Throwable $error) {
        run('UPDATE webhook_events SET error = ? WHERE event_id = ?', [mb_substr($error->getMessage(), 0, 500), $eventId]);
        error_log('Bondi Paddle event ' . $eventId . ': ' . $error->getMessage());
        fail('processing', 'Will retry.', 500); // Paddle retries; the event is processed again then
    }
}

/** Gmail's and Apple Mail's Unsubscribe button posts here. Quiet: no email follows a one-click unsubscribe. */
function unsubscribe_one_click(): never
{
    require_post();
    limit_or_fail('unsubscribe:' . client_key(), 60, 3600);
    if (!privacy_ready()) {
        fail('unavailable', 'Unsubscribing isn\'t set up yet. Reply to the email with "unsubscribe".', 503);
    }
    $signup = signup_for_unsubscribe_link($_GET['id'] ?? null, $_GET['s'] ?? null);
    if ($signup === null) {
        fail('not_found', 'This unsubscribe link isn\'t valid.', 404);
    }
    $code = $signup['unsubscribed_at'] === null ? unsubscribe_signup($signup, 'email_link', true) : null;
    json_out(['status' => 'unsubscribed', 'code' => $code]);
}

function interest(): never
{
    json_out(interest_counts());
}

function signup(): never
{
    require_post();
    $done = ['status' => 'ok', 'message' => 'Thanks. We\'ll email you when the beta opens.'];
    // A field people can't see: bots fill it in. They get the same answer and nothing is saved.
    if (trim((string)(input()['website'] ?? '')) !== '') {
        json_out($done);
    }
    $name = trim(preg_replace('/\s+/u', ' ', (string)(input()['name'] ?? '')) ?? '');
    $email = strtolower(trim((string)(input()['email'] ?? '')));
    if ($name === '' || mb_strlen($name) > 100) {
        fail('name', 'Enter your name.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        fail('email', 'Enter a valid email address.');
    }
    limit_or_fail('signup:' . client_key(), 10, 3600);
    $timeZone = mb_substr(trim((string)(input()['time_zone'] ?? '')), 0, 64) ?: null;
    [$country, $source] = signup_country($timeZone);
    $values = [$email, $name, signup_ip(), $country, $source, $timeZone];
    if (signups_have_page()) {
        // Someone who ever signed up on the beta page stays marked as beta.
        run("INSERT INTO signups (email, name, ip, country, country_source, time_zone, page) VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE name = VALUES(name), ip = VALUES(ip), country = VALUES(country),
                 country_source = VALUES(country_source), time_zone = VALUES(time_zone),
                 page = IF(VALUES(page) = 'beta', 'beta', page), updated_at = CURRENT_TIMESTAMP"
                 . (privacy_ready() ? ', unsubscribed_at = NULL' : ''), // signing up again is a fresh yes
            [...$values, (input()['page'] ?? '') === 'beta' ? 'beta' : 'home']);
    } else {
        run('INSERT INTO signups (email, name, ip, country, country_source, time_zone) VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE name = VALUES(name), ip = VALUES(ip), country = VALUES(country),
                 country_source = VALUES(country_source), time_zone = VALUES(time_zone), updated_at = CURRENT_TIMESTAMP',
            $values);
    }
    try {
        sign_up_welcome($email, $name); // the thank-you from Noaman, once per email
    } catch (Throwable $error) {
        error_log('Bondi sign-up welcome to ' . $email . ': ' . $error->getMessage()); // the sign-up still counts
    }
    json_out($done);
}
