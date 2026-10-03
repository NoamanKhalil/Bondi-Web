<?php
// "Your data" (GDPR access, portability and erasure; CCPA know and delete). Someone enters their email; if we
// hold anything for it, we email a one-time link (24 hours) to prove the request comes from that inbox. The
// link shows everything we hold, downloads it as JSON, lets them unsubscribe, and deletes it. Purchase records
// stay (tax law). Every unsubscribe or deletion gets a confirmation number (see bondi/lib/privacy.php).
require is_file(dirname(__DIR__, 2) . '/bondi/bootstrap.php') ? dirname(__DIR__, 2) . '/bondi/bootstrap.php' : dirname(__DIR__) . '/bondi/bootstrap.php';

$h = fn(mixed $s): string => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$support = (string)config('support_email');
$post = $_SERVER['REQUEST_METHOD'] === 'POST';
$do = $post ? (string)($_POST['do'] ?? '') : '';
$token = (string)($_POST['t'] ?? $_GET['t'] ?? '');

if (!privacy_ready()) {
    privacy_page_start('Your data');
    echo '<h1>Your data</h1><p>This page isn\'t ready yet. To see, download or delete what we hold about you, write to '
       . '<a href="mailto:' . $h($support) . '">' . $h($support) . '</a> and we\'ll do it within 30 days.</p>';
    privacy_page_end();
}

// The download: everything we hold, as JSON.
if (isset($_GET['download']) && ($email = data_link_email($token)) !== null) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="bondi-my-data-' . gmdate('Y-m-d') . '.json"');
    header('Cache-Control: no-store');
    echo json_encode(person_data($email), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

privacy_page_start('Your data');

// Step 1: ask for the link.
if ($do === 'request') {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<h1>Your data</h1><p class="error">Enter a valid email address.</p>';
        request_form($h, $email);
        privacy_page_end();
    }
    // Limits per caller and per address, so this page can't be used to flood someone's inbox.
    if (within_limit('your-data:' . client_key(), 10, 3600) && within_limit('your-data:' . email_fingerprint($email), 3, 3600)
        && holds_data_for($email)) {
        $mail = data_link_message(config('base_url') . '/your-data/?t=' . create_data_link($email));
        send_mail($email, $mail['subject'], $mail['text'], $mail['html'], email_images());
    }
    // The same answer either way, so no one can test whether an email is on our list.
    echo '<h1>Check your inbox</h1><p>If we hold anything for ' . $h($email) . ', a link is on its way. It works once, for 24 hours.</p>'
       . '<p class="note">Nothing arrived? Check your spam folder, or write to <a href="mailto:' . $h($support) . '">' . $h($support) . '</a>.</p>';
    privacy_page_end();
}

$email = data_link_email($token);
if ($token !== '' && $email === null) {
    echo '<h1>This link has expired</h1><p>Links work once, for 24 hours. Ask for a new one:</p>';
    request_form($h);
    privacy_page_end();
}
if ($email === null) {
    echo '<h1>Your data</h1><p>See everything we hold about you, download it, or delete it. Enter the email you used on '
       . 'trybondi.app or to buy Bondi, and we\'ll send you a link that works for 24 hours.</p>';
    request_form($h);
    echo '<p class="note">Bondi keeps your Mac\'s history only on your Mac; delete it in Bondi, Settings, Data. '
       . 'We don\'t sell or share your personal information. More in our <a href="/privacy/">privacy policy</a>.</p>';
    privacy_page_end();
}

$tokenField = '<input type="hidden" name="t" value="' . $h($token) . '">';
$signup = one('SELECT * FROM signups WHERE email = ?', [$email]);

// Step 3: what they chose.
if ($do === 'delete') {
    if (empty($_POST['sure'])) {
        echo '<p class="error">Tick the box to confirm, then press Delete my data again.</p>';
    } else {
        [$code, $removed, $kept] = delete_person($email, 'web');
        use_data_link($token);
        echo '<h1>Your data is deleted</h1><p>We\'ve deleted your ' . $h($removed) . '.</p>'
           . ($kept !== null ? '<p>We kept your ' . $h($kept) . '.</p>' : '')
           . '<p>Your confirmation number:</p><div class="code">' . $h($code) . '</div>'
           . '<p class="note">We\'ve emailed it to you too. We keep a record that we did this (the date and a code that can\'t be turned back into your email) for 3 years.</p>'
           . '<div class="panel"><h2>Thank you for being part of Bondi\'s early days</h2><p>You\'re always welcome back: <a href="/beta/">trybondi.app/beta</a>.</p></div>';
        privacy_page_end();
    }
} elseif ($do === 'unsubscribe' && $signup !== null) {
    $code = $signup['unsubscribed_at'] === null ? unsubscribe_signup($signup, 'web') : null;
    echo '<p class="flash">You\'re unsubscribed' . ($code ? '. Your confirmation number is <strong>' . $h($code) . '</strong> (also emailed).' : '.') . '</p>';
    $signup = one('SELECT * FROM signups WHERE email = ?', [$email]);
} elseif ($do === 'resubscribe' && $signup !== null) {
    resubscribe_signup($signup);
    echo '<p class="flash">Welcome back: you\'re on the list again.</p>';
    $signup = one('SELECT * FROM signups WHERE email = ?', [$email]);
}

// Step 2: everything we hold.
$data = person_data($email);
echo '<h1>Your data</h1><p>Everything we hold about <strong>' . $h($email) . '</strong>, as of now.</p>';

echo '<div class="panel"><h2>Your sign-up</h2>';
if ($data['sign_up'] === null) {
    echo '<p>You\'re not on our sign-up list.</p>';
} else {
    $s = $data['sign_up'];
    echo '<dl><dt>Name</dt><dd>' . $h($s['name']) . '</dd><dt>Email</dt><dd>' . $h($s['email']) . '</dd>'
       . '<dt>Country</dt><dd>' . $h(country_name($s['country'])) . ($s['country_source'] === 'timezone' ? ' (from your time zone)' : ($s['country'] ? ' (from your IP address)' : '')) . '</dd>'
       . '<dt>IP address</dt><dd>' . $h($s['ip']) . '</dd><dt>Signed up</dt><dd>' . $h($s['created_at']) . ' UTC</dd>'
       . '<dt>Emails</dt><dd>' . ($s['unsubscribed_at'] === null ? 'Subscribed' : 'Unsubscribed on ' . $h($s['unsubscribed_at']) . ' UTC') . '</dd></dl>';
    echo '<form method="post" class="row">' . $tokenField . ($s['unsubscribed_at'] === null
        ? '<input type="hidden" name="do" value="unsubscribe"><button class="btn quiet">Unsubscribe from emails</button>'
        : '<input type="hidden" name="do" value="resubscribe"><button class="btn quiet">Get emails again</button>') . '</form>';
}
echo '</div>';

echo '<div class="panel"><h2>Emails we\'ve sent you</h2>';
echo $data['emails_sent'] ? '<p>' . implode(' · ', array_map(fn($m) => $h(email_kind_name($m['kind'])) . ', ' . $h(substr($m['sent_at'], 0, 10)), $data['emails_sent'])) . '</p>'
                          : '<p>None.</p>';
echo '</div>';

if ($data['licenses']) {
    echo '<div class="panel"><h2>Your purchases</h2>';
    foreach ($data['licenses'] as $l) {
        $macs = array_filter($l['macs'], fn($m) => $m['deactivated_at'] === null);
        echo '<dl><dt>License</dt><dd>key ending ' . $h($l['key_last4']) . ', ' . $h($l['status']) . ', bought ' . $h(substr($l['created_at'], 0, 10)) . '</dd>'
           . '<dt>Paid</dt><dd>' . ($l['amount_cents'] === null ? '—' : $h(number_format($l['amount_cents'] / 100, 2) . ' ' . $l['currency'])) . '</dd>'
           . '<dt>On a Mac</dt><dd>' . ($macs ? $h(implode(', ', array_map(fn($m) => $m['device_name'] ?? 'a Mac', $macs))) : 'No') . '</dd></dl>';
    }
    echo '<p class="note">Purchase records are kept even if you delete your data: tax law requires it, and it keeps your license working.</p></div>';
}

echo '<div class="panel"><h2>Download it</h2><p>Everything above, in a file you can keep or take elsewhere (JSON).</p>'
   . '<p><a class="btn quiet" href="/your-data/?t=' . $h($token) . '&amp;download=1">Download my data</a></p></div>';

echo '<div class="panel"><h2>Delete it</h2><p>We delete your sign-up and our record of emails sent, straight away. '
   . 'You\'ll get a confirmation number. ' . ($data['licenses'] ? 'Your purchase record and license stay, as above.' : '') . '</p>'
   . '<form method="post">' . $tokenField . '<input type="hidden" name="do" value="delete">'
   . '<label class="check"><input type="checkbox" name="sure" value="1"> I understand this can\'t be undone.</label>'
   . '<div class="row"><button class="btn danger">Delete my data</button></div></form></div>';

echo '<p class="note">' . $h($data['note']) . ' Questions: <a href="mailto:' . $h($support) . '">' . $h($support) . '</a>.</p>';
privacy_page_end();

function request_form(callable $h, string $email = ''): void
{
    echo '<form method="post" class="row"><input type="hidden" name="do" value="request">'
       . '<label class="vh" for="email">Email</label><input id="email" type="email" name="email" value="' . $h($email) . '" placeholder="you@example.com" required autocomplete="email">'
       . '<button class="btn">Send me the link</button></form>';
}

function email_kind_name(string $kind): string
{
    return match (true) {
        $kind === 'welcome' => 'Thank-you for signing up',
        $kind === 'license' => 'License key',
        $kind === 'recovery' => 'New license key',
        $kind === 'comp' => 'Free license',
        str_starts_with($kind, 'update-') => 'News about Bondi',
        default => $kind,
    };
}
