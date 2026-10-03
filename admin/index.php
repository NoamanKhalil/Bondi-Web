<?php
// Bondi license admin, at https://trybondi.app/admin/. One password (its hash is in config.php).
// Find a license by email, key ending or Paddle order; revoke or restore it; free a Mac; email the buyer a
// new key; give a free license; see launch licenses left and Paddle notifications that failed; see and
// download the website's update sign-ups.
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
            go();
        }
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
            session_destroy();
            go();
        case 'revoke':
        case 'restore':
            // From the licenses list, go back to that list (and its search); otherwise to the license's page.
            $back = (string)($_POST['back'] ?? '');
            $back = preg_match('/^(list|q=[^&]*)$/', $back) ? ($back === 'list' ? '' : $back) : "license=$licenseId";
            if ($_POST['do'] === 'revoke') {
                run("UPDATE licenses SET status = 'revoked', status_changed_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'active'", [$licenseId]);
                go($back, "License #$licenseId revoked. Bondi locks on that Mac at its next check (within a day).");
            }
            run("UPDATE licenses SET status = 'active', status_changed_at = CURRENT_TIMESTAMP WHERE id = ? AND status <> 'active'", [$licenseId]);
            go($back, "License #$licenseId restored.");
        case 'deactivate':
            run('UPDATE activations SET deactivated_at = CURRENT_TIMESTAMP WHERE id = ? AND deactivated_at IS NULL',
                [(int)($_POST['activation'] ?? 0)]);
            go("license=$licenseId", 'That Mac is deactivated; the license can be used on another Mac.');
        case 'new_key':
            $row = one('SELECT c.email FROM licenses l JOIN customers c ON c.id = l.customer_id WHERE l.id = ?', [$licenseId]);
            if ($row !== null) {
                send_key_email($row['email'], rotate_key($licenseId), 'recovery', $licenseId);
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
            $_SESSION['new_key'] = $key; // shown once on the next page
            go("license=$newId", 'Free license created' . (!empty($_POST['send']) ? ' and emailed.' : '.'));
        case 'news_test':
        case 'news_send':
            $subject = trim((string)($_POST['subject'] ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));
            $query = trim((string)($_POST['q'] ?? ''));
            $_SESSION['news_draft'] = ['subject' => $subject, 'message' => $message]; // kept until it's all sent
            $back = 'signups' . ($query === '' ? '' : '&q=' . rawurlencode($query));
            if ($subject === '' || $message === '') {
                go($back, 'Write a subject and a message first.');
            }
            if ($_POST['do'] === 'news_test') {
                $first = signups_matching($query)[0]['name'] ?? 'there';
                $mail = update_email($first, $subject, $message);
                $ok = send_mail((string)config('support_email'), '[Test] ' . $mail['subject'], $mail['text'], $mail['html'], email_images());
                go($back, $ok ? 'Test sent to ' . config('support_email') . " (with {name} as \"$first\")." : 'The test could not be sent.');
            }
            // Each message is logged as it goes, so pressing Send again with the same words skips everyone
            // who already has it and carries on with the rest.
            $campaign = 'update-' . substr(sha1($subject . "\n" . $message), 0, 12);
            $sentBefore = array_flip(array_column(all('SELECT email FROM email_log WHERE kind = ?', [$campaign]), 'email'));
            $todo = array_values(array_filter(signups_matching($query), fn($r) => !isset($sentBefore[$r['email']])));
            ignore_user_abort(true);
            set_time_limit(0);
            $started = microtime(true);
            $sent = $failed = 0;
            foreach ($todo as $row) {
                if (microtime(true) - $started > 40) {
                    break; // stays well inside the host's time limit; the rest go on the next press
                }
                $mail = update_email($row['name'], $subject, $message);
                $unsubscribe = 'List-Unsubscribe: <mailto:' . config('support_email') . '?subject=unsubscribe>';
                if (send_mail($row['email'], $mail['subject'], $mail['text'], $mail['html'], email_images(), [$unsubscribe])) {
                    run('INSERT INTO email_log (email, kind, license_id) VALUES (?, ?, NULL)', [$row['email'], $campaign]);
                    $sent++;
                } else {
                    $failed++;
                }
            }
            $left = count($todo) - $sent;
            if ($left === 0) {
                unset($_SESSION['news_draft']);
            }
            go($back, "Sent to $sent " . ($sent === 1 ? 'person' : 'people') . '.'
                . ($failed ? " $failed could not be sent." : '')
                . ($left > 0 ? " $left still to go: press Send again (same subject and message) to carry on." : ''));
        case 'welcome_rest':
            // The thank-you from Noaman to everyone on the list who hasn't had it (new sign-ups get it by themselves).
            $todo = all("SELECT s.email, s.name FROM signups s WHERE NOT EXISTS
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
            go('signups', "Thank-you sent to $sent " . ($sent === 1 ? 'person' : 'people') . '.'
                . ($failed ? " $failed could not be sent." : '') . ($left > 0 ? " $left still to go: press it again." : ''));
        case 'delete_signup':
            $row = one('SELECT email FROM signups WHERE id = ?', [(int)($_POST['signup'] ?? 0)]);
            run('DELETE FROM signups WHERE id = ?', [(int)($_POST['signup'] ?? 0)]);
            go('signups', 'Removed ' . ($row['email'] ?? 'that sign-up') . ' from the list.');
    }
    go();
}

// MARK: Pages

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if (isset($_GET['license'])) {
    license_page((int)$_GET['license'], $flash);
} elseif (isset($_GET['webhooks'])) {
    webhooks_page($flash);
} elseif (isset($_GET['signups'])) {
    isset($_GET['csv']) ? signups_csv() : signups_page((string)($_GET['q'] ?? ''), $flash);
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

function signups_page(string $query, ?string $flash): never
{
    page_start('Update sign-ups');
    nav($flash);
    signups_fill_ip_countries();
    $total = (int)one('SELECT COUNT(*) AS n FROM signups')['n'];
    echo '<h2>Update sign-ups <span class="muted">(' . $total . ')</span>' . table_tag('signups') . '</h2>';
    $countries = all('SELECT COALESCE(country, \'?\') AS country, COUNT(*) AS n FROM signups GROUP BY country ORDER BY n DESC LIMIT 20');
    if (signups_have_page()) {
        $pages = all('SELECT page, COUNT(*) AS n FROM signups GROUP BY page ORDER BY n DESC');
        echo '<p class="muted">By page: ' . implode(' · ', array_map(fn($p) => h(page_name($p['page'])) . ' ' . (int)$p['n'], $pages))
           . ' <span title="Search for beta to see only them">(search “beta” for the beta list)</span></p>';
    } else {
        echo '<p class="muted">To tell beta-page sign-ups apart, import sql/005_signup_page.sql in phpMyAdmin.</p>';
    }
    if ($countries) {
        echo '<p class="muted">By country: ' . implode(' · ', array_map(fn($c) => h($c['country'] === '?' ? 'Unknown' : country_name($c['country'])) . ' ' . (int)$c['n'], $countries)) . '</p>';
    }
    echo '<form class="search"><input type="hidden" name="signups" value="1"><input name="q" value="' . h($query) . '" placeholder="Name, email, country code, or beta" autofocus>'
       . '<button>Search</button></form><p><a href="?signups&csv">Download all as CSV</a></p>';
    $matching = signups_matching($query);
    $rows = array_slice($matching, 0, 500);
    if ($matching) { write_to_signups($query, count($matching)); }
    welcome_box();
    if (!$rows) { echo '<p class="muted">None.</p>'; page_end(); }
    $hasPage = signups_have_page();
    echo '<table><tr><th>Name</th><th>Email</th>' . ($hasPage ? '<th>Page</th>' : '') . '<th>Country</th><th>IP</th><th>Signed up</th><th></th></tr>';
    foreach ($rows as $row) {
        $source = $row['country_source'] === 'timezone' ? ' <span class="muted" title="From the browser\'s time zone: ' . h($row['time_zone']) . '">(time zone)</span>' : '';
        echo '<tr><td>' . h($row['name']) . '</td><td>' . h($row['email']) . '</td>'
           . ($hasPage ? '<td>' . ($row['page'] === 'beta' ? '<span class="status active">Beta</span>' : 'Homepage') . '</td>' : '')
           . '<td title="' . h($row['country'] ?? '') . '">' . h(country_name($row['country'])) . $source . '</td>'
           . '<td class="mono">' . h($row['ip']) . '</td><td>' . h($row['created_at']) . ' UTC</td><td>'
           . '<form method="post" onsubmit="return confirm(' . h(json_encode('Remove ' . $row['email'] . ' from the list?')) . ')">' . csrf_field()
           . '<input type="hidden" name="do" value="delete_signup"><input type="hidden" name="signup" value="' . (int)$row['id'] . '">'
           . '<button class="small danger">Remove</button></form></td></tr>';
    }
    echo '</table>';
    page_end();
}

/** The thank-you from Noaman: who has had it, and a button for those who haven't. */
function welcome_box(): void
{
    $waiting = (int)one("SELECT COUNT(*) AS n FROM signups s WHERE NOT EXISTS
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

/** Everyone on the sign-up list, or those whose name, email or country matches the search; newest first. */
function signups_matching(string $query): array
{
    $query = trim($query);
    if ($query === '') {
        return all('SELECT * FROM signups ORDER BY id DESC');
    }
    if (signups_have_page() && strtolower($query) === 'beta') {
        return all("SELECT * FROM signups WHERE page = 'beta' ORDER BY id DESC");
    }
    return all('SELECT * FROM signups WHERE email LIKE ? OR name LIKE ? OR country = ? ORDER BY id DESC',
               ['%' . strtolower($query) . '%', '%' . $query . '%', strtoupper($query)]);
}

function write_to_signups(string $query, int $count): void
{
    $draft = $_SESSION['news_draft'] ?? ['subject' => '', 'message' => ''];
    $who = $query === '' ? "everyone on the list ($count)" : "the $count shown by this search";
    $label = 'Send to ' . $count . ($count === 1 ? ' person' : ' people');
    echo '<details class="card compose"' . ($draft['subject'] !== '' ? ' open' : '') . '><summary>Write to ' . h($who) . '</summary>'
       . '<form method="post">' . csrf_field() . '<input type="hidden" name="q" value="' . h($query) . '">'
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

function signups_csv(): never
{
    signups_fill_ip_countries();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="bondi-signups-' . gmdate('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['name', 'email', 'country', 'country_name', 'country_source', 'time_zone', 'page', 'ip', 'created_at_utc', 'updated_at_utc'], ',', '"', '');
    foreach (all('SELECT * FROM signups ORDER BY id') as $row) {
        // A leading = + - @ would run as a formula in Excel or Numbers, so it gets a ' in front.
        $safe = fn($value) => is_string($value) && preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
        fputcsv($out, array_map($safe, [$row['name'], $row['email'], $row['country'], $row['country'] === null ? null : country_name($row['country']), $row['country_source'], $row['time_zone'], $row['page'] ?? 'home',
                                          $row['ip'], $row['created_at'], $row['updated_at']]), ',', '"', '');
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
    echo '<nav><a href="./">Licenses</a> <a href="?signups">Update sign-ups</a> <a href="?webhooks">Paddle notifications</a>'
       . '<form method="post">' . csrf_field() . '<input type="hidden" name="do" value="logout"><button class="link">Sign out</button></form></nav>';
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
</style></head><body><main>';
}

function page_end(): never
{
    echo '</main></body></html>';
    exit;
}
