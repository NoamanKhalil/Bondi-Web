<?php
// Bondi license admin, at https://trybondi.app/admin/. One password (its hash is in config.php).
// Find a license by email, key ending or Paddle order; revoke or restore it; free a Mac; email the buyer a
// new key; give a free license; see launch licenses left and Paddle notifications that failed; see and
// download the website's update sign-ups (filtered, with where people are); see sign-ups, trials and sales over time;
// see the record of sign-ins and changes made here (Activity, sql/007).
// The private code lives beside public_html (best) or inside it as public_html/bondi, locked by its .htaccess.
require is_file(dirname(__DIR__, 2) . '/bondi/bootstrap.php') ? dirname(__DIR__, 2) . '/bondi/bootstrap.php' : dirname(__DIR__) . '/bondi/bootstrap.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS'])]);
session_name('bondi_admin');
session_start();
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

function h(mixed $text): string { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
function money(?int $cents, ?string $currency): string { return $cents === null ? '—' : number_format($cents / 100, 2) . ' ' . h($currency ?? ''); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function go(string $query = '', ?string $flash = null): never
{
    if ($flash !== null) { $_SESSION['flash'] = $flash; }
    header('Location: ./' . ($query === '' ? '' : '?' . $query));
    exit;
}

// MARK: Sign in and out

$loggedIn = ($_SESSION['admin'] ?? false) === true;
if (($_POST['do'] ?? '') === 'login') {
    if (!within_limit('admin-login:' . client_key(), 5, 900)) {
        if (within_limit('admin-login-noted:' . client_key(), 1, 900)) {
            audit('sign_in_blocked', 'Sign-in blocked after too many tries'); // once per 15 minutes, so retries can't flood the record
        }
        $loginError = 'Too many attempts. Wait 15 minutes.';
    } else {
        // Both are always checked, and a miss never says which one was wrong.
        $username = strtolower(trim((string)config('admin_username', '')));
        $userOk = $username !== '' && hash_equals($username, strtolower(trim((string)($_POST['username'] ?? ''))));
        // The password is written plainly in config.php (owner's choice), or as a hash if admin_password is empty.
        $given = (string)($_POST['password'] ?? '');
        $plain = (string)config('admin_password', '');
        $hash = (string)config('admin_password_hash', '');
        $passOk = $plain !== '' ? hash_equals($plain, $given) : ($hash !== '' && password_verify($given, $hash));
        if ($userOk && $passOk) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            audit('sign_in', 'Signed in');
            go();
        }
        audit('sign_in_failed', 'Wrong username or password');
        $loginError = $username === '' || ($plain === '' && $hash === '')
            ? 'Sign-in isn\'t set up: add admin_username and admin_password to config.php.'
            : 'Wrong username or password.';
    }
}
if (!$loggedIn) {
    page_start('Sign in');
    echo '<form method="post" class="card narrow"><h1>Bondi admin</h1>';
    if (isset($loginError)) { echo '<p class="error">' . h($loginError) . '</p>'; }
    echo '<input type="hidden" name="do" value="login">'
       . '<input name="username" placeholder="Username" autocomplete="username" autocapitalize="none" autofocus required>'
       . '<input type="password" name="password" placeholder="Password" autocomplete="current-password" required>'
       . '<button>Sign in</button></form>';
    page_end();
}

// MARK: Actions (every change is a POST with the page's token, and asks first in the browser)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
        go('', 'That form expired. Try again.');
    }
    $licenseId = (int)($_POST['license'] ?? 0);
    switch ($_POST['do'] ?? '') {
        case 'logout':
            audit('sign_out', 'Signed out');
            session_destroy();
            go();
        case 'revoke':
        case 'restore':
            // From the licenses list, go back to that list (and its search); otherwise to the license's page.
            $back = (string)($_POST['back'] ?? '');
            $back = preg_match('/^(list|q=[^&]*)$/', $back) ? ($back === 'list' ? '' : $back) : "license=$licenseId";
            if ($_POST['do'] === 'revoke') {
                if (run("UPDATE licenses SET status = 'revoked', status_changed_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'active'", [$licenseId]) > 0) {
                    audit('revoke', "Revoked license #$licenseId");
                }
                go($back, "License #$licenseId revoked. Bondi locks on that Mac at its next check (within a day).");
            }
            if (run("UPDATE licenses SET status = 'active', status_changed_at = CURRENT_TIMESTAMP WHERE id = ? AND status <> 'active'", [$licenseId]) > 0) {
                audit('restore', "Restored license #$licenseId");
            }
            go($back, "License #$licenseId restored.");
        case 'deactivate':
            if (run('UPDATE activations SET deactivated_at = CURRENT_TIMESTAMP WHERE id = ? AND deactivated_at IS NULL',
                    [(int)($_POST['activation'] ?? 0)]) > 0) {
                audit('free_mac', "Freed a Mac on license #$licenseId");
            }
            go("license=$licenseId", 'That Mac is deactivated; the license can be used on another Mac.');
        case 'new_key':
            $row = one('SELECT c.email FROM licenses l JOIN customers c ON c.id = l.customer_id WHERE l.id = ?', [$licenseId]);
            if ($row !== null) {
                send_key_email($row['email'], rotate_key($licenseId), 'recovery', $licenseId);
                audit('new_key', "Emailed a new key for license #$licenseId");
            }
            go("license=$licenseId", 'A new key was emailed to ' . ($row['email'] ?? '?') . '. The old key no longer works.');
        case 'comp':
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                go('', 'Enter a valid email.');
            }
            [$newId, $key] = create_license(customer_id($email), 'comp', null, 0, null);
            if (!empty($_POST['send'])) {
                send_key_email($email, $key, 'comp', $newId);
            }
            audit('free_license', "Gave free license #$newId" . (!empty($_POST['send']) ? ' and emailed the key' : ''));
            $_SESSION['new_key'] = $key; // shown once on the next page
            go("license=$newId", 'Free license created' . (!empty($_POST['send']) ? ' and emailed.' : '.'));
        case 'news_test':
        case 'news_send':
            $subject = trim((string)($_POST['subject'] ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));
            $filters = signup_filters($_POST);
            $_SESSION['news_draft'] = ['subject' => $subject, 'message' => $message]; // kept until it's all sent
            $back = 'signups' . (filters_query($filters) === '' ? '' : '&' . filters_query($filters));
            if ($subject === '' || $message === '') {
                go($back, 'Write a subject and a message first.');
            }
            if ($_POST['do'] === 'news_test') {
                $first = signups_reachable($filters)[0]['name'] ?? 'there';
                $mail = update_email($first, $subject, $message);
                $ok = send_mail((string)config('support_email'), '[Test] ' . $mail['subject'], $mail['text'], $mail['html'], email_images());
                if ($ok) { audit('email_test', 'Sent a test of "' . $subject . '" to the support inbox'); }
                go($back, $ok ? 'Test sent to ' . config('support_email') . " (with {name} as \"$first\")." : 'The test could not be sent.');
            }
            // Each message is logged as it goes, so pressing Send again with the same words skips everyone
            // who already has it and carries on with the rest.
            $campaign = 'update-' . substr(sha1($subject . "\n" . $message), 0, 12);
            $sentBefore = array_flip(array_column(all('SELECT email FROM email_log WHERE kind = ?', [$campaign]), 'email'));
            $todo = array_values(array_filter(signups_reachable($filters), fn($r) => !isset($sentBefore[$r['email']])));
            ignore_user_abort(true);
            set_time_limit(0);
            $started = microtime(true);
            $sent = $failed = 0;
            foreach ($todo as $row) {
                if (microtime(true) - $started > 40) {
                    break; // stays well inside the host's time limit; the rest go on the next press
                }
                $mail = update_email($row['name'], $subject, $message, privacy_ready() ? unsubscribe_url((int)$row['id']) : null);
                if (send_mail($row['email'], $mail['subject'], $mail['text'], $mail['html'], email_images(), unsubscribe_headers((int)$row['id']))) {
                    run('INSERT INTO email_log (email, kind, license_id) VALUES (?, ?, NULL)', [$row['email'], $campaign]);
                    $sent++;
                } else {
                    $failed++;
                }
            }
            $left = count($todo) - $sent;
            if ($sent > 0) { audit('email_send', 'Sent "' . $subject . '" to ' . $sent . ($sent === 1 ? ' person' : ' people') . (filtering($filters) ? ' (filtered list)' : '')); }
            if ($left === 0) {
                unset($_SESSION['news_draft']);
            }
            go($back, "Sent to $sent " . ($sent === 1 ? 'person' : 'people') . '.'
                . ($failed ? " $failed could not be sent." : '')
                . ($left > 0 ? " $left still to go: press Send again (same subject and message) to carry on." : ''));
        case 'welcome_rest':
            // The thank-you from Noaman to everyone on the list who hasn't had it (new sign-ups get it by themselves).
            $todo = all("SELECT s.email, s.name FROM signups s WHERE " . (privacy_ready() ? 's.unsubscribed_at IS NULL AND ' : '') . "NOT EXISTS
                         (SELECT 1 FROM email_log e WHERE e.email = s.email AND e.kind = 'welcome') ORDER BY s.id");
            ignore_user_abort(true);
            set_time_limit(0);
            $started = microtime(true);
            $sent = $failed = 0;
            foreach ($todo as $row) {
                if (microtime(true) - $started > 40) {
                    break;
                }
                sign_up_welcome($row['email'], $row['name']) ? $sent++ : $failed++;
            }
            $left = count($todo) - $sent - $failed;
            if ($sent > 0) { audit('welcome_send', 'Sent the thank-you to ' . $sent . ($sent === 1 ? ' person' : ' people')); }
            go('signups', "Thank-you sent to $sent " . ($sent === 1 ? 'person' : 'people') . '.'
                . ($failed ? " $failed could not be sent." : '') . ($left > 0 ? " $left still to go: press it again." : ''));
        case 'delete_signup':
            $signupId = (int)($_POST['signup'] ?? 0);
            $row = one('SELECT email FROM signups WHERE id = ?', [$signupId]);
            if ($row === null) {
                go('signups', 'That sign-up is already gone.');
            }
            if (!privacy_ready()) {
                run('DELETE FROM signups WHERE id = ?', [$signupId]);
                audit('delete_signup', "Removed sign-up #$signupId");
                go('signups', 'Removed ' . $row['email'] . ' from the list.');
            }
            [$code, $removed, $kept] = delete_person($row['email'], 'admin');
            audit('delete_signup', "Deleted sign-up #$signupId and everything held about them ($code)");
            go('signups', 'Deleted ' . $row['email'] . " ($removed" . ($kept ? "; kept: $kept" : '') . "). Confirmation number $code, emailed to them.");
    }
    go();
}

// MARK: Pages

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if (isset($_GET['license'])) {
    license_page((int)$_GET['license'], $flash);
} elseif (isset($_GET['requests'])) {
    requests_page((string)($_GET['q'] ?? ''), $flash);
} elseif (isset($_GET['webhooks'])) {
    webhooks_page($flash);
} elseif (isset($_GET['signups'])) {
    isset($_GET['csv']) ? signups_csv(signup_filters($_GET)) : signups_page(signup_filters($_GET), $flash);
} elseif (isset($_GET['activity'])) {
    activity_page($flash);
} else {
    dashboard((string)($_GET['q'] ?? ''), $flash);
}

function dashboard(string $query, ?string $flash): never
{
    page_start('Bondi admin');
    nav($flash);
    $offer = current_offer();
    $trials = one('SELECT COUNT(*) AS total, SUM(started_at > NOW() - INTERVAL 7 DAY) AS week FROM trials');
    $counts = [];
    foreach (all('SELECT status, COUNT(*) AS n FROM licenses GROUP BY status') as $row) { $counts[$row['status']] = (int)$row['n']; }
    $launchSold = one("SELECT COUNT(*) AS n FROM licenses WHERE price_tier = 'launch' AND status = 'active'")['n'];
    $revenue = all("SELECT currency, SUM(amount_cents) AS cents FROM licenses WHERE status = 'active' AND amount_cents > 0 GROUP BY currency");
    $failed = one('SELECT COUNT(*) AS n FROM webhook_events WHERE processed_at IS NULL')['n'];
    $signups = one('SELECT COUNT(*) AS total, SUM(created_at > NOW() - INTERVAL 7 DAY) AS week FROM signups');

    echo '<div class="stats">';
    stat_box('Now selling at', '$' . $offer['price'] . ($offer['launch_remaining'] !== null ? ' · ' . $offer['launch_remaining'] . ' launch left' : ''), 'price_tiers');
    stat_box('Launch licenses sold', (int)$launchSold . ' of 250', 'licenses');
    stat_box('Active licenses', $counts['active'] ?? 0, 'licenses');
    stat_box('Refunded / revoked', ($counts['refunded'] ?? 0) . ' / ' . ($counts['revoked'] ?? 0), 'licenses');
    stat_box('Trials started', (int)$trials['total'] . ' (' . (int)$trials['week'] . ' this week)', 'trials');
    $beta = interest_counts()['beta'];
    stat_box('Update sign-ups', (int)$signups['total'] . ' (' . (int)$signups['week'] . ' this week' . ($beta === null ? '' : ", $beta beta") . ')', 'signups');
    stat_box('Revenue (active, before Paddle fees)', $revenue ? implode(' + ', array_map(fn($r) => money((int)$r['cents'], $r['currency']), $revenue)) : '—', 'licenses');
    echo '</div>';
    if ($failed > 0) {
        echo '<p class="error">' . (int)$failed . ' Paddle notification(s) not processed. <a href="?webhooks">See them</a>.</p>';
    }
    if (audit_ready()) {
        $tries = (int)one("SELECT COUNT(*) AS n FROM audit_log WHERE event IN ('sign_in_failed', 'sign_in_blocked') AND created_at > NOW() - INTERVAL 1 DAY")['n'];
        if ($tries > 0) {
            echo '<p class="error">' . $tries . ' failed sign-in' . ($tries === 1 ? '' : 's') . ' to this page in the last day. <a href="?activity">See where from</a>.</p>';
        }
    }
    growth_section();

    echo '<form class="search"><input name="q" value="' . h($query) . '" placeholder="Email, last 4 of key, full key, or Paddle txn_…" autofocus><button>Search</button></form>';
    $rows = $query === '' ? recent_licenses() : search_licenses($query);
    echo '<h2>' . ($query === '' ? 'Latest licenses' : 'Results') . table_tag('licenses', 'customers') . '</h2>';
    licenses_table($rows);

    echo '<h2>Give a free license' . table_tag('licenses', 'price tier: comp') . '</h2><form method="post" class="inline">' . csrf_field()
       . '<input type="hidden" name="do" value="comp"><input type="email" name="email" placeholder="Their email" required>'
       . '<label><input type="checkbox" name="send" value="1" checked> Email them the key</label><button>Create</button></form>';
    page_end();
}

function recent_licenses(): array
{
    return all('SELECT l.*, c.email, (SELECT COUNT(*) FROM activations a WHERE a.license_id = l.id AND a.deactivated_at IS NULL) AS macs
                FROM licenses l JOIN customers c ON c.id = l.customer_id ORDER BY l.id DESC LIMIT 50');
}

function search_licenses(string $query): array
{
    $query = trim($query);
    $normalized = normalize_key($query);
    $conditions = ['c.email LIKE ?', 'l.paddle_transaction_id = ?', 'l.key_last4 = ?'];
    $args = ['%' . strtolower($query) . '%', $query, strtoupper($query)];
    if ($normalized !== null) {
        $conditions[] = 'l.key_hash = ?';
        $args[] = key_hash($normalized);
    }
    return all('SELECT l.*, c.email, (SELECT COUNT(*) FROM activations a WHERE a.license_id = l.id AND a.deactivated_at IS NULL) AS macs
                FROM licenses l JOIN customers c ON c.id = l.customer_id WHERE ' . implode(' OR ', $conditions) . ' ORDER BY l.id DESC LIMIT 100', $args);
}

function licenses_table(array $rows): void
{
    if (!$rows) { echo '<p class="muted">None.</p>'; return; }
    $back = isset($_GET['q']) && $_GET['q'] !== '' ? 'q=' . rawurlencode((string)$_GET['q']) : 'list';
    echo '<table><tr><th>License #</th><th>Email</th><th>Key</th><th>Price tier</th><th>Paid</th><th>Status</th><th>On a Mac</th><th>Created</th><th></th></tr>';
    foreach ($rows as $row) {
        echo '<tr><td><a href="?license=' . (int)$row['id'] . '">' . (int)$row['id'] . '</a></td><td>' . h($row['email']) . '</td>'
           . '<td class="mono">••••' . h($row['key_last4']) . '</td><td>' . h($row['price_tier']) . '</td>'
           . '<td>' . money($row['amount_cents'] === null ? null : (int)$row['amount_cents'], $row['currency']) . '</td>'
           . '<td><span class="status ' . h($row['status']) . '">' . h($row['status']) . '</span></td>'
           . '<td>' . ((int)$row['macs'] > 0 ? 'Yes' : '—') . '</td><td>' . h($row['created_at']) . ' UTC</td><td>';
        if ($row['status'] === 'active') {
            action((int)$row['id'], 'revoke', 'Revoke', 'Revoke license #' . $row['id'] . ' (' . $row['email'] . ')? Bondi stops working on its Mac at the next check.', 'small danger', $back);
        } elseif ($row['status'] === 'revoked') {
            action((int)$row['id'], 'restore', 'Restore', 'Make license #' . $row['id'] . ' (' . $row['email'] . ') active again?', 'small', $back);
        }
        echo '</td></tr>';
    }
    echo '</table>';
}

function license_page(int $id, ?string $flash): never
{
    page_start("License #$id");
    nav($flash);
    $license = one('SELECT l.*, c.email FROM licenses l JOIN customers c ON c.id = l.customer_id WHERE l.id = ?', [$id]);
    if ($license === null) { echo '<p>No such license.</p>'; page_end(); }
    if (isset($_SESSION['new_key'])) {
        echo '<p class="key">New key (shown once): <span class="mono">' . h($_SESSION['new_key']) . '</span></p>';
        unset($_SESSION['new_key']);
    }
    echo '<div class="card"><h1>License #' . $id . ' <span class="status ' . h($license['status']) . '">' . h($license['status']) . '</span>' . table_tag('licenses', 'customers') . '</h1>'
       . '<p>' . h($license['email']) . ' · key ••••' . h($license['key_last4']) . ' · price tier ' . h($license['price_tier'])
       . ' · paid ' . money($license['amount_cents'] === null ? null : (int)$license['amount_cents'], $license['currency'])
       . ' · Paddle transaction ' . h($license['paddle_transaction_id'] ?? '—') . ' · created ' . h($license['created_at']) . ' UTC</p>';

    echo '<div class="actions">';
    if ($license['status'] === 'active') {
        action($id, 'revoke', 'Revoke', 'Revoke this license? Bondi stops working on its Mac at the next check.', 'danger');
    } else {
        action($id, 'restore', 'Restore', 'Make this license active again?');
    }
    action($id, 'new_key', 'Email a new key', 'Email ' . $license['email'] . ' a new key? The old key stops working; the activated Mac keeps working.');
    echo '</div></div>';

    echo '<h2>Macs' . table_tag('activations') . '</h2>';
    $macs = all('SELECT * FROM activations WHERE license_id = ? ORDER BY activated_at DESC', [$id]);
    if (!$macs) { echo '<p class="muted">Not activated on any Mac yet.</p>'; }
    else {
        echo '<table><tr><th>Mac</th><th>Activated</th><th>Last check</th><th>State</th><th></th></tr>';
        foreach ($macs as $mac) {
            echo '<tr><td>' . h($mac['device_name'] ?? 'Unnamed Mac') . '</td><td>' . h($mac['activated_at']) . '</td><td>' . h($mac['last_check_at']) . '</td>'
               . '<td>' . ($mac['deactivated_at'] ? 'Deactivated ' . h($mac['deactivated_at']) : 'Active') . '</td><td>';
            if (!$mac['deactivated_at']) {
                echo '<form method="post" onsubmit="return confirm(\'Deactivate this Mac? Bondi locks on it at its next check, and the license can be used on another Mac.\')">'
                   . csrf_field() . '<input type="hidden" name="do" value="deactivate"><input type="hidden" name="license" value="' . $id . '">'
                   . '<input type="hidden" name="activation" value="' . (int)$mac['id'] . '"><button class="small">Deactivate</button></form>';
            }
            echo '</td></tr>';
        }
        echo '</table>';
    }

    echo '<h2>Emails sent' . table_tag('email_log') . '</h2>';
    $emails = all('SELECT kind, sent_at FROM email_log WHERE license_id = ? ORDER BY id DESC', [$id]);
    echo $emails ? '<p>' . implode(' · ', array_map(fn($e) => h($e['kind']) . ' ' . h($e['sent_at']), $emails)) . '</p>' : '<p class="muted">None.</p>';
    page_end();
}

function signups_page(array $filters, ?string $flash): never
{
    page_start('Update sign-ups');
    nav($flash);
    signups_fill_ip_countries();
    $total = (int)one('SELECT COUNT(*) AS n FROM signups')['n'];
    echo '<h2>Update sign-ups <span class="muted">(' . $total . ')</span>' . table_tag('signups') . '</h2>';
    if (signups_have_page()) {
        $pages = all('SELECT page, COUNT(*) AS n FROM signups GROUP BY page ORDER BY n DESC');
        echo '<p class="muted">By page: ' . implode(' · ', array_map(fn($p) => h(page_name($p['page'])) . ' ' . (int)$p['n'], $pages)) . '</p>';
    } else {
        echo '<p class="muted">To tell beta-page sign-ups apart, import sql/005_signup_page.sql in phpMyAdmin.</p>';
    }
    countries_table();
    filters_form($filters);
    $matching = signups_matching($filters);
    $rows = array_slice($matching, 0, 500);
    if (filtering($filters)) {
        echo '<p class="muted">' . count($matching) . ' of ' . $total . ' match.</p>';
    }
    $reachable = count(signups_reachable($filters));
    if ($reachable > 0) { write_to_signups($filters, $reachable); }
    welcome_box();
    if (!$rows) { echo '<p class="muted">None.</p>'; page_end(); }
    $hasPage = signups_have_page();
    echo '<table><tr><th>Name</th><th>Email</th>' . ($hasPage ? '<th>Page</th>' : '') . '<th>Country</th><th>IP</th><th>Signed up</th><th></th></tr>';
    foreach ($rows as $row) {
        $source = $row['country_source'] === 'timezone' ? ' <span class="muted" title="From the browser\'s time zone: ' . h($row['time_zone']) . '">(time zone)</span>' : '';
        $off = ($row['unsubscribed_at'] ?? null) !== null ? ' <span class="status revoked" title="Unsubscribed ' . h($row['unsubscribed_at']) . ' UTC">unsubscribed</span>' : '';
        echo '<tr><td>' . h($row['name']) . $off . '</td><td>' . h($row['email']) . '</td>'
           . ($hasPage ? '<td>' . ($row['page'] === 'beta' ? '<span class="status active">Beta</span>' : 'Homepage') . '</td>' : '')
           . '<td title="' . h($row['country'] ?? '') . '">' . h(country_name($row['country'])) . $source . '</td>'
           . '<td class="mono">' . h($row['ip']) . '</td><td>' . h($row['created_at']) . ' UTC</td><td>'
           . '<form method="post" onsubmit="return confirm(' . h(json_encode('Delete everything we hold about ' . $row['email'] . '? They get a confirmation number by email. Purchase records stay.')) . ')">' . csrf_field()
           . '<input type="hidden" name="do" value="delete_signup"><input type="hidden" name="signup" value="' . (int)$row['id'] . '">'
           . '<button class="small danger">Delete</button></form></td></tr>';
    }
    echo '</table>';
    page_end();
}

/** The sign-ups filters, from the address or a form: search words, page, country, and subscribed or not. */
function signup_filters(array $in): array
{
    $page = (string)($in['page'] ?? '');
    $country = strtoupper((string)($in['country'] ?? ''));
    $status = (string)($in['status'] ?? '');
    return [
        'q' => trim((string)($in['q'] ?? '')),
        'page' => in_array($page, ['home', 'beta'], true) ? $page : '',
        'country' => $country === 'NONE' ? 'none' : (preg_match('/^[A-Z]{2}$/', $country) ? $country : ''),
        'status' => in_array($status, ['subscribed', 'unsubscribed'], true) ? $status : '',
    ];
}

function filtering(array $filters): bool
{
    return implode('', $filters) !== '';
}

/** The filters as an address query, e.g. page=beta&country=DE (empty ones left out). */
function filters_query(array $filters): string
{
    return http_build_query(array_filter($filters, fn($value) => $value !== ''));
}

function filters_form(array $filters): void
{
    $option = fn(string $value, string $label, string $current) => '<option value="' . h($value) . '"' . ($value === $current ? ' selected' : '') . '>' . h($label) . '</option>';
    echo '<form class="filters"><input type="hidden" name="signups" value="1">'
       . '<input name="q" value="' . h($filters['q']) . '" placeholder="Name, email or country code" aria-label="Search">';
    if (signups_have_page()) {
        echo '<select name="page" aria-label="Page">' . $option('', 'Every page', $filters['page']) . $option('home', 'Homepage', $filters['page'])
           . $option('beta', 'Beta page', $filters['page']) . '</select>';
    }
    echo '<select name="country" aria-label="Country">' . $option('', 'Every country', $filters['country']);
    foreach (all('SELECT country, COUNT(*) AS n FROM signups GROUP BY country ORDER BY n DESC, country') as $row) {
        echo $row['country'] === null ? $option('none', 'Unknown (' . (int)$row['n'] . ')', $filters['country'])
                                      : $option($row['country'], country_name($row['country']) . ' (' . (int)$row['n'] . ')', $filters['country']);
    }
    echo '</select>';
    if (privacy_ready()) {
        echo '<select name="status" aria-label="Subscribed">' . $option('', 'Subscribed or not', $filters['status'])
           . $option('subscribed', 'Subscribed', $filters['status']) . $option('unsubscribed', 'Unsubscribed', $filters['status']) . '</select>';
    }
    echo '<button>Filter</button>' . (filtering($filters) ? ' <a href="?signups">Clear</a>' : '') . '</form>';
    $query = filters_query($filters);
    echo '<p><a href="?signups&csv' . ($query === '' ? '' : '&' . h($query)) . '">' . (filtering($filters) ? 'Download these as CSV' : 'Download all as CSV') . '</a></p>';
}

/** Where the people on the list are: each country with its share, and its beta-page sign-ups. Each links to its filter. */
function countries_table(): void
{
    $hasPage = signups_have_page();
    $rows = all('SELECT country, COUNT(*) AS n' . ($hasPage ? ", SUM(page = 'beta') AS beta" : '') . ' FROM signups GROUP BY country ORDER BY n DESC, country');
    if (!$rows) {
        return;
    }
    $total = array_sum(array_map(fn($row) => (int)$row['n'], $rows));
    $top = (int)$rows[0]['n'];
    echo '<h2>Where people are' . table_tag('signups') . '</h2><table class="where"><tr><th>Country</th><th class="num">Sign-ups</th><th class="share">Share</th>'
       . ($hasPage ? '<th class="num">Beta</th>' : '') . '</tr>';
    foreach (array_slice($rows, 0, 15) as $row) {
        $code = $row['country'];
        $name = $code === null ? '<span class="muted">Unknown</span>' : '<span class="flag" aria-hidden="true">' . flag($code) . '</span> ' . h(country_name($code));
        $share = round((int)$row['n'] / $total * 100);
        echo '<tr><td><a href="?signups&country=' . h($code ?? 'none') . '">' . $name . '</a></td><td class="num">' . (int)$row['n'] . '</td>'
           . '<td class="share"><span class="bar" style="width:' . max(2, round((int)$row['n'] / $top * 100)) . '%"></span><span class="muted">' . $share . '%</span></td>'
           . ($hasPage ? '<td class="num">' . (int)$row['beta'] . '</td>' : '') . '</tr>';
    }
    if (count($rows) > 15) {
        echo '<tr><td colspan="' . ($hasPage ? 4 : 3) . '" class="muted">and ' . (count($rows) - 15) . ' more countries (use the country filter below)</td></tr>';
    }
    echo '</table>';
}

/** A country's flag: "DE" → 🇩🇪 (two regional indicator letters). */
function flag(string $code): string
{
    return mb_chr(0x1F1E6 + ord($code[0]) - 65) . mb_chr(0x1F1E6 + ord($code[1]) - 65);
}

/** The sign-ups an email would reach: those matching the filters who haven't unsubscribed. */
function signups_reachable(array $filters): array
{
    return array_values(array_filter(signups_matching($filters), fn($row) => ($row['unsubscribed_at'] ?? null) === null));
}

/** Privacy requests: look one up by its confirmation number, or by the person's email (through its fingerprint). */
function requests_page(string $query, ?string $flash): never
{
    page_start('Privacy requests');
    nav($flash);
    echo '<h2>Privacy requests' . table_tag('data_requests') . '</h2>';
    if (!privacy_ready()) {
        echo '<p class="muted">Import sql/006_privacy.sql in phpMyAdmin to turn on unsubscribing, the Your data page and this record.</p>';
        page_end();
    }
    echo '<p class="muted">Every unsubscribe and deletion, kept 3 years. The email itself is never stored: searching by email '
       . 'matches its fingerprint.</p>'
       . '<form class="search"><input type="hidden" name="requests" value="1"><input name="q" value="' . h($query) . '" placeholder="Confirmation number (BR-…) or email" autofocus><button>Look up</button></form>';
    $rows = find_requests($query);
    if (!$rows) { echo '<p class="muted">None.</p>'; page_end(); }
    echo '<table><tr><th>Confirmation number</th><th>What</th><th>How</th><th>Done</th><th>Removed</th><th>Kept</th></tr>';
    foreach ($rows as $row) {
        echo '<tr><td class="mono">' . h($row['code']) . '</td><td>' . h($row['kind'] === 'delete' ? 'Deletion' : 'Unsubscribe') . '</td>'
           . '<td>' . h(['web' => 'Your data page', 'email_link' => 'Email link', 'admin' => 'Admin page'][$row['channel']] ?? $row['channel']) . '</td>'
           . '<td>' . h($row['completed_at']) . ' UTC</td><td>' . h($row['removed'] ?? '—') . '</td><td>' . h($row['kept'] ?? '—') . '</td></tr>';
    }
    echo '</table>';
    page_end();
}

/** The thank-you from Noaman: who has had it, and a button for those who haven't. */
function welcome_box(): void
{
    $waiting = (int)one("SELECT COUNT(*) AS n FROM signups s WHERE " . (privacy_ready() ? 's.unsubscribed_at IS NULL AND ' : '') . "NOT EXISTS
                         (SELECT 1 FROM email_log e WHERE e.email = s.email AND e.kind = 'welcome')")['n'];
    $had = (int)one("SELECT COUNT(DISTINCT email) AS n FROM email_log WHERE kind = 'welcome'")['n'];
    echo '<div class="card compose welcome"><p><b>Thank-you email from Noaman</b> <span class="muted">· sent by itself to every new sign-up · '
       . $had . ' sent so far</span></p>';
    if ($waiting > 0) {
        echo '<form method="post" class="inline" style="margin-top:10px">' . csrf_field() . '<input type="hidden" name="do" value="welcome_rest">'
           . '<button onclick="return confirm(' . h(json_encode("Send the thank-you email to the $waiting " . ($waiting === 1 ? 'person' : 'people') . ' who haven\'t had it?')) . ')">'
           . 'Send it to the ' . $waiting . ' who haven\'t had it</button></form>';
    } else {
        echo '<p class="muted" style="margin-top:6px">Everyone on the list has had it.</p>';
    }
    echo '</div>';
}

/** Everyone on the sign-up list who matches the filters, newest first. Searching "beta" still means the beta page. */
function signups_matching(array $filters): array
{
    $where = [];
    $args = [];
    $query = $filters['q'];
    if (signups_have_page() && strtolower($query) === 'beta' && $filters['page'] === '') {
        [$query, $filters['page']] = ['', 'beta'];
    }
    if ($query !== '') {
        $where[] = '(email LIKE ? OR name LIKE ? OR country = ?)';
        array_push($args, '%' . strtolower($query) . '%', '%' . $query . '%', strtoupper($query));
    }
    if ($filters['page'] !== '' && signups_have_page()) {
        $where[] = 'page = ?';
        $args[] = $filters['page'];
    }
    if ($filters['country'] === 'none') {
        $where[] = 'country IS NULL';
    } elseif ($filters['country'] !== '') {
        $where[] = 'country = ?';
        $args[] = $filters['country'];
    }
    if ($filters['status'] !== '' && privacy_ready()) {
        $where[] = $filters['status'] === 'subscribed' ? 'unsubscribed_at IS NULL' : 'unsubscribed_at IS NOT NULL';
    }
    return all('SELECT * FROM signups' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC', $args);
}

function write_to_signups(array $filters, int $count): void
{
    $draft = $_SESSION['news_draft'] ?? ['subject' => '', 'message' => ''];
    $who = filtering($filters) ? "the $count shown by this search" : "everyone on the list ($count)";
    $label = 'Send to ' . $count . ($count === 1 ? ' person' : ' people');
    $hidden = implode('', array_map(fn($key) => '<input type="hidden" name="' . $key . '" value="' . h($filters[$key]) . '">', array_keys($filters)));
    echo '<details class="card compose"' . ($draft['subject'] !== '' ? ' open' : '') . '><summary>Write to ' . h($who) . '</summary>'
       . '<form method="post">' . csrf_field() . $hidden
       . '<input name="subject" placeholder="Subject" value="' . h($draft['subject']) . '" required>'
       . '<textarea name="message" rows="9" placeholder="Hi {name},&#10;&#10;Your message. A blank line starts a new paragraph; links work as they are." required>' . h($draft['message']) . '</textarea>'
       . '<p class="muted">{name} becomes each person\'s name. Every email ends with why they got it and how to stop (reply "unsubscribe"; then remove them below). '
       . 'Sent from ' . h((string)config('support_email')) . '.</p>'
       . '<div class="actions"><button name="do" value="news_test" formnovalidate class="secondary">Send me a test</button>'
       . '<button name="do" value="news_send" onclick="return confirm(' . h(json_encode("Email $who now?")) . ')">' . h($label) . '</button></div>'
       . '</form>';
    $past = all("SELECT kind, COUNT(*) AS n, MIN(sent_at) AS first FROM email_log WHERE kind LIKE 'update-%' GROUP BY kind ORDER BY first DESC LIMIT 10");
    if ($past) {
        echo '<p class="muted past">Sent before: ' . implode(' · ', array_map(fn($p) => h($p['first']) . ' UTC to ' . (int)$p['n'], $past)) . '</p>';
    }
    echo '</details>';
}

function signups_csv(array $filters): never
{
    signups_fill_ip_countries();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="bondi-signups-' . (filtering($filters) ? 'filtered-' : '') . gmdate('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['name', 'email', 'country', 'country_name', 'country_source', 'time_zone', 'page', 'ip', 'created_at_utc', 'updated_at_utc', 'unsubscribed_at_utc'], ',', '"', '');
    foreach (array_reverse(signups_matching($filters)) as $row) { // oldest first
        // A leading = + - @ would run as a formula in Excel or Numbers, so it gets a ' in front.
        $safe = fn($value) => is_string($value) && preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
        fputcsv($out, array_map($safe, [$row['name'], $row['email'], $row['country'], $row['country'] === null ? null : country_name($row['country']), $row['country_source'], $row['time_zone'], $row['page'] ?? 'home',
                                          $row['ip'], $row['created_at'], $row['updated_at'], $row['unsubscribed_at'] ?? null]), ',', '"', '');
    }
    exit;
}

function webhooks_page(?string $flash): never
{
    page_start('Paddle notifications');
    nav($flash);
    echo '<h2>Paddle notifications (latest 100)' . table_tag('webhook_events') . '</h2><p class="muted">Paddle retries failed ones by itself for a few days.</p>';
    $rows = all('SELECT event_id, event_type, received_at, processed_at, error FROM webhook_events ORDER BY received_at DESC LIMIT 100');
    echo '<table><tr><th>Received</th><th>Type</th><th>Event</th><th>Result</th></tr>';
    foreach ($rows as $row) {
        echo '<tr><td>' . h($row['received_at']) . '</td><td>' . h($row['event_type']) . '</td><td class="mono">' . h($row['event_id']) . '</td><td>'
           . ($row['processed_at'] ? 'OK' : '<span class="error">' . h($row['error'] ?? 'waiting') . '</span>') . '</td></tr>';
    }
    echo '</table>';
    page_end();
}

/** Sign-ups, trials or licenses sold over time, by day, week, month or year: a bar chart drawn here, no scripts. */
function growth_section(): void
{
    $metrics = [
        'signups' => ['Sign-ups', 'signups', 'created_at', ''],
        'trials' => ['Trials started', 'trials', 'started_at', ''],
        'licenses' => ['Licenses sold', 'licenses', 'created_at', " AND price_tier <> 'comp'"],
    ];
    $periods = ['day' => 'Daily', 'week' => 'Weekly', 'month' => 'Monthly', 'year' => 'Yearly'];
    $metric = isset($metrics[$_GET['growth'] ?? '']) ? (string)$_GET['growth'] : 'signups';
    $period = isset($periods[$_GET['period'] ?? '']) ? (string)$_GET['period'] : 'day';
    [$title, $table, $column, $extra] = $metrics[$metric];

    // The buckets, oldest first: 30 days, 12 weeks (from Monday), 12 months or 5 years, ending now (UTC).
    $today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
    [$first, $step, $count, $words] = match ($period) {
        'day' => [$today->modify('-29 days'), '+1 day', 30, 'these 30 days'],
        'week' => [$today->modify('-' . ((int)$today->format('N') - 1) . ' days')->modify('-11 weeks'), '+1 week', 12, 'these 12 weeks'],
        'month' => [$today->modify('first day of this month')->modify('-11 months'), '+1 month', 12, 'these 12 months'],
        'year' => [$today->setDate((int)$today->format('Y') - 4, 1, 1), '+1 year', 5, 'these 5 years'],
    };
    $keys = [];
    for ($day = $first, $i = 0; $i < $count; $day = $day->modify($step), $i++) {
        $keys[] = $day->format('Y-m-d');
    }
    $bucket = match ($period) {
        'day' => "DATE($column)",
        'week' => "DATE(DATE_SUB($column, INTERVAL WEEKDAY($column) DAY))",
        'month' => "DATE_FORMAT($column, '%Y-%m-01')",
        'year' => "DATE_FORMAT($column, '%Y-01-01')",
    };
    $split = $metric === 'signups' && signups_have_page(); // beta-page sign-ups drawn on top of the homepage ones
    $values = $beta = array_fill_keys($keys, 0);
    foreach (all("SELECT $bucket AS b, COUNT(*) AS n" . ($split ? ", SUM(page = 'beta') AS beta" : '') . " FROM $table WHERE $column >= ?$extra GROUP BY b",
                 [$keys[0] . ' 00:00:00']) as $row) {
        if (isset($values[$row['b']])) {
            $values[$row['b']] = (int)$row['n'];
            $beta[$row['b']] = $split ? (int)$row['beta'] : 0;
        }
    }

    $link = fn(string $g, string $p, string $label, bool $on) => '<a href="?growth=' . $g . '&period=' . $p . '"' . ($on ? ' class="on" aria-current="true"' : '') . '>' . h($label) . '</a>';
    echo '<h2>' . h($title) . ' over time' . table_tag($table) . '</h2><div class="card growth"><div class="switch">';
    echo '<span class="seg">' . implode('', array_map(fn($g) => $link($g, $period, $metrics[$g][0], $g === $metric), array_keys($metrics))) . '</span>';
    echo '<span class="seg">' . implode('', array_map(fn($p) => $link($metric, $p, $periods[$p], $p === $period), array_keys($periods))) . '</span></div>';
    echo bar_chart($keys, $values, $beta, $period, strtolower($title));
    $sum = array_sum($values);
    $betaSum = array_sum($beta);
    echo '<p class="muted">In ' . $words . ': ' . $sum . ' ' . strtolower($title)
       . ($split ? ($betaSum > 0 ? " ($betaSum from the beta page)" : '') . ' · <span class="key a"></span> Homepage <span class="key b"></span> Beta page' : '') . ' · UTC</p></div>';
}

/** Bars for each bucket on one scale; the second series (beta) sits on top of the first. Colours come from the page's theme. */
function bar_chart(array $keys, array $values, array $second, string $period, string $unit): string
{
    [$width, $height, $left, $bottom, $top] = [960, 200, 34, 22, 10];
    $max = max(1, max($values));
    foreach ([1, 2, 4, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000, 2000, 2500, 5000, 10000] as $nice) {
        if ($nice >= $max) { $max = $nice; break; }
    }
    $plot = $height - $bottom - $top;
    $slot = ($width - $left) / count($keys);
    $y = fn(float $v) => round($top + $plot - $v / $max * $plot, 1);
    $svg = '<svg class="chart" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="' . h(ucfirst($unit)) . ' per ' . h($period) . '">';
    foreach (array_unique([0, $max % 2 === 0 ? $max / 2 : null, $max]) as $tick) {
        if ($tick === null) { continue; }
        $svg .= '<line class="grid" x1="' . $left . '" x2="' . $width . '" y1="' . $y($tick) . '" y2="' . $y($tick) . '"/>'
              . '<text x="' . ($left - 6) . '" y="' . ($y($tick) + 4) . '" text-anchor="end">' . $tick . '</text>';
    }
    $every = ['day' => 5, 'week' => 2, 'month' => 1, 'year' => 1][$period];
    foreach ($keys as $i => $key) {
        $date = new DateTimeImmutable($key, new DateTimeZone('UTC'));
        $label = match ($period) { 'day', 'week' => $date->format('M j'), 'month' => $date->format('M'), 'year' => $date->format('Y') };
        $x = round($left + $i * $slot + $slot * 0.15, 1);
        $w = round($slot * 0.7, 1);
        $n = $values[$key];
        $b = min($second[$key] ?? 0, $n);
        $tip = ($period === 'week' ? 'Week of ' : '') . $date->format($period === 'year' ? 'Y' : ($period === 'month' ? 'F Y' : 'D M j')) . ': ' . $n . ' ' . $unit . ($b > 0 ? " ($b beta)" : '');
        $svg .= '<g><title>' . h($tip) . '</title><rect class="hit" x="' . round($left + $i * $slot, 1) . '" y="' . $top . '" width="' . round($slot, 1) . '" height="' . $plot . '"/>';
        if ($n - $b > 0) { $svg .= '<rect class="a" x="' . $x . '" y="' . $y($n - $b) . '" width="' . $w . '" height="' . round($plot * ($n - $b) / $max, 1) . '" rx="2"/>'; }
        if ($b > 0) { $svg .= '<rect class="b" x="' . $x . '" y="' . $y($n) . '" width="' . $w . '" height="' . round($plot * $b / $max, 1) . '" rx="2"/>'; }
        $svg .= '</g>';
        if ($i % $every === 0 || $i === count($keys) - 1) {
            $svg .= '<text x="' . round($left + ($i + 0.5) * $slot, 1) . '" y="' . ($height - 6) . '" text-anchor="middle">' . h($label) . '</text>';
        }
    }
    return $svg . '</svg>';
}

/** Sign-ins (and failed ones) and every change made on this page, newest first. */
function activity_page(?string $flash): never
{
    page_start('Activity');
    nav($flash);
    echo '<h2>Activity' . table_tag('audit_log') . '</h2>';
    if (!audit_ready()) {
        echo '<p class="muted">Import sql/007_audit_log.sql in phpMyAdmin to start recording sign-ins and changes made here.</p>';
        page_end();
    }
    $week = one("SELECT SUM(event = 'sign_in') AS ins, SUM(event IN ('sign_in_failed', 'sign_in_blocked')) AS fails FROM audit_log WHERE created_at > NOW() - INTERVAL 7 DAY");
    echo '<p class="muted">Sign-ins, failed sign-ins and every change made on this page, with where they came from. Kept a year; never an email address or a password. '
       . 'Last 7 days: ' . (int)$week['ins'] . ' sign-in' . ((int)$week['ins'] === 1 ? '' : 's') . ', ' . (int)$week['fails'] . ' failed.</p>';
    $rows = audit_recent(100);
    if (!$rows) { echo '<p class="muted">Nothing yet.</p>'; page_end(); }
    echo '<table><tr><th>When (UTC)</th><th>What</th><th>From</th><th>IP address</th></tr>';
    foreach ($rows as $row) {
        $bad = in_array($row['event'], ['sign_in_failed', 'sign_in_blocked'], true);
        $from = $row['country'] === null ? '<span class="muted">—</span>' : '<span class="flag" aria-hidden="true">' . flag($row['country']) . '</span> ' . h(country_name($row['country']));
        echo '<tr><td>' . h($row['created_at']) . '</td><td' . ($bad ? ' class="error"' : '') . '>' . h($row['detail'] ?? $row['event']) . '</td>'
           . '<td>' . $from . '</td><td class="mono">' . h($row['ip'] ?? '—') . '</td></tr>';
    }
    echo '</table>';
    page_end();
}

/**
 * The update the site is running: the latest commit in the .git folder Hostinger deployed (date and first line of
 * its message). A commit that git has packed away shows when it arrived instead. Null when there's no .git.
 */
function site_version(): ?array
{
    $git = null;
    for ($dir = __DIR__, $i = 0; $i < 4 && $git === null; $dir = dirname($dir), $i++) {
        if (is_file("$dir/.git/HEAD")) { $git = "$dir/.git"; }
    }
    if ($git === null) {
        return null;
    }
    $hash = trim((string)@file_get_contents("$git/HEAD"));
    if (str_starts_with($hash, 'ref: ')) {
        $ref = substr($hash, 5);
        $hash = trim((string)@file_get_contents("$git/$ref"));
        if ($hash === '' && is_file("$git/packed-refs")) {
            foreach ((array)@file("$git/packed-refs", FILE_IGNORE_NEW_LINES) as $line) {
                if (str_ends_with($line, " $ref")) { $hash = strtok($line, ' '); break; }
            }
        }
    }
    if (!preg_match('/^[0-9a-f]{40}$/', $hash)) {
        return null;
    }
    $version = ['hash' => substr($hash, 0, 7), 'message' => null, 'time' => null];
    $object = "$git/objects/" . substr($hash, 0, 2) . '/' . substr($hash, 2);
    $raw = is_file($object) ? @gzuncompress((string)file_get_contents($object)) : false;
    if (is_string($raw) && preg_match('/\ncommitter [^\n]* (\d+) [+-]\d{4}\n/', $raw, $time)) {
        $version['time'] = (int)$time[1];
        $body = substr($raw, (int)strpos($raw, "\n\n") + 2);
        $version['message'] = strtok($body, "\n") ?: null;
    } elseif (is_file("$git/logs/HEAD")) {
        $lines = (array)@file("$git/logs/HEAD", FILE_IGNORE_NEW_LINES);
        if ($lines && preg_match('/ (\d+) [+-]\d{4}\t/', (string)end($lines), $time)) { $version['time'] = (int)$time[1]; }
    }
    return $version;
}

// MARK: Pieces

function action(int $licenseId, string $do, string $label, string $question, string $style = '', string $back = ''): void
{
    echo '<form method="post" onsubmit="return confirm(' . h(json_encode($question)) . ')">' . csrf_field()
       . '<input type="hidden" name="do" value="' . h($do) . '"><input type="hidden" name="license" value="' . $licenseId . '">'
       . ($back === '' ? '' : '<input type="hidden" name="back" value="' . h($back) . '">')
       . '<button class="' . h($style) . '">' . h($label) . '</button></form>';
}

function stat_box(string $label, mixed $value, string $table): void
{
    echo '<div class="stat"><div class="muted">' . h($label) . '</div><div class="value">' . h($value) . '</div>' . table_tag($table) . '</div>';
}

/** Where someone signed up: the beta page or the homepage. */
function page_name(?string $page): string
{
    return $page === 'beta' ? 'Beta page' : 'Homepage';
}

/** The database tables a section reads, so it can be matched to phpMyAdmin. */
function table_tag(string ...$tables): string
{
    return ' <span class="tbl" title="Database table in phpMyAdmin">' . h(implode(' + ', $tables)) . '</span>';
}

function nav(?string $flash): void
{
    echo '<nav><a href="./">Licenses</a> <a href="?signups">Update sign-ups</a> <a href="?requests">Privacy requests</a> <a href="?webhooks">Paddle notifications</a> <a href="?activity">Activity</a>'
       . '<form method="post">' . csrf_field() . '<input type="hidden" name="do" value="logout"><button class="link">Sign out</button></form></nav>';
    $version = site_version();
    if ($version !== null) {
        echo '<p class="version muted">Live version: ' . ($version['message'] !== null ? '“' . h($version['message']) . '” · ' : '')
           . ($version['time'] !== null ? h(gmdate('M j, Y, H:i', $version['time'])) . ' UTC · ' : '') . '<span class="mono">' . h($version['hash']) . '</span></p>';
    }
    if ($flash !== null) { echo '<p class="flash">' . h($flash) . '</p>'; }
}

function page_start(string $title): void
{
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<meta name="robots" content="noindex"><title>' . h($title) . '</title><style>
:root { color-scheme: light dark; --bg:#f5f5f7; --card:#fff; --text:#1d1d1f; --muted:#6e6e73; --line:#d2d2d7; --accent:#0071e3; --danger:#d70015; --ok:#248a3d; --warn:#b25000; }
@media (prefers-color-scheme: dark) { :root { --bg:#1c1c1e; --card:#2c2c2e; --text:#f5f5f7; --muted:#a1a1a6; --line:#3a3a3c; --accent:#0a84ff; --danger:#ff453a; --ok:#30d158; --warn:#ff9f0a; } }
body { margin:0; padding:24px; font:14px/1.5 -apple-system,BlinkMacSystemFont,"Helvetica Neue",sans-serif; background:var(--bg); color:var(--text); }
main { max-width:1100px; margin:0 auto; }
h1 { font-size:22px; margin:0 0 8px; } h2 { font-size:17px; margin:28px 0 8px; }
nav { display:flex; gap:16px; align-items:center; margin-bottom:16px; } nav form { margin-left:auto; }
a { color:var(--accent); text-decoration:none; }
.card { background:var(--card); border-radius:12px; padding:20px; } .narrow { max-width:320px; margin:80px auto; display:grid; gap:12px; }
.stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:10px; }
.stat { background:var(--card); border-radius:12px; padding:14px; } .stat .value { font-size:18px; font-weight:600; }
table { width:100%; border-collapse:collapse; background:var(--card); border-radius:12px; overflow:hidden; }
th, td { text-align:left; padding:8px 10px; border-bottom:1px solid var(--line); } th { color:var(--muted); font-weight:500; }
input { font:inherit; padding:8px 10px; border:1px solid var(--line); border-radius:8px; background:var(--card); color:var(--text); }
button { font:inherit; padding:8px 14px; border:0; border-radius:8px; background:var(--accent); color:#fff; cursor:pointer; }
button.danger { background:var(--danger); } button.small { padding:4px 10px; } button.link { background:none; color:var(--accent); padding:0; }
.search { display:flex; gap:8px; margin:20px 0 0; } .search input { flex:1; }
.inline { display:flex; gap:10px; align-items:center; flex-wrap:wrap; } .actions { display:flex; gap:10px; margin-top:12px; }
.muted { color:var(--muted); } .error { color:var(--danger); } .flash { background:var(--card); border-left:3px solid var(--accent); padding:10px 14px; border-radius:8px; }
.key { background:var(--card); padding:12px 14px; border-radius:8px; border-left:3px solid var(--ok); }
.mono { font-family:ui-monospace,Menlo,monospace; }
textarea { font:inherit; padding:8px 10px; border:1px solid var(--line); border-radius:8px; background:var(--card); color:var(--text); resize:vertical; }
.compose { margin:16px 0; } .compose summary { cursor:pointer; font-weight:600; color:var(--accent); }
.compose form { display:grid; gap:10px; margin-top:14px; } .compose p { margin:0; } .compose .past { margin-top:12px; }
button.secondary { background:none; color:var(--accent); border:1px solid var(--accent); }
td form { margin:0; }
.tbl { font:400 11px/1 ui-monospace,Menlo,monospace; color:var(--muted); border:1px solid var(--line); border-radius:6px; padding:2px 6px; margin-left:8px; vertical-align:middle; white-space:nowrap; }
.stat .tbl { display:inline-block; margin:6px 0 0; }
.status { font-size:12px; padding:2px 8px; border-radius:10px; border:1px solid currentColor; }
.status.active { color:var(--ok); } .status.refunded { color:var(--warn); } .status.revoked { color:var(--danger); }
.version { margin:-8px 0 16px; font-size:12px; }
.growth { display:grid; gap:12px; padding:16px; } .growth .switch { display:flex; flex-wrap:wrap; gap:8px 16px; justify-content:space-between; }
.seg { display:inline-flex; gap:2px; padding:3px; border-radius:9px; background:var(--bg); } .seg a { padding:4px 10px; border-radius:7px; color:var(--muted); }
.seg a.on { background:var(--card); color:var(--text); box-shadow:0 1px 3px rgba(0,0,0,.15); }
.chart { width:100%; height:auto; display:block; } .chart text { fill:var(--muted); font-size:11px; } .chart .grid { stroke:var(--line); stroke-width:1; }
.chart .a { fill:var(--accent); } .chart .b { fill:var(--ok); } .chart .hit { fill:transparent; } .chart g:hover .hit { fill:var(--line); opacity:.35; }
.key { display:inline-block; width:10px; height:10px; border-radius:3px; margin:0 3px 0 8px; vertical-align:-1px; } .key.a { background:var(--accent); } .key.b { background:var(--ok); }
.where { max-width:640px; } .where .num, th.num { text-align:right; width:80px; } .where .share { width:45%; }
.where .bar { display:inline-block; height:8px; border-radius:4px; background:var(--accent); margin-right:8px; vertical-align:middle; max-width:calc(100% - 44px); }
.flag { font-size:15px; }
.filters { display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin:20px 0 0; } .filters input { flex:1 1 220px; }
select { font:inherit; padding:8px 10px; border:1px solid var(--line); border-radius:8px; background:var(--card); color:var(--text); }
@media (max-width:700px) { body { padding:14px; } nav { flex-wrap:wrap; gap:6px 14px; } nav form { margin-left:0; }
  table { display:block; overflow-x:auto; white-space:nowrap; } .filters select { flex:1 1 140px; } .where .share { min-width:140px; } }
</style></head><body><main>';
}

function page_end(): never
{
    echo '</main></body></html>';
    exit;
}
