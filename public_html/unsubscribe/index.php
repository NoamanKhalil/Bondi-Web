<?php
// Opened by the "Unsubscribe" link in Bondi's list emails (?id=<sign-up>&s=<signature>). Opening the link
// changes nothing, because mail scanners open every link: the person presses Unsubscribe here. Afterwards they
// see their confirmation number and can undo it. (Mail apps' own one-click button posts to /api/unsubscribe.)
require is_file(dirname(__DIR__, 2) . '/bondi/bootstrap.php') ? dirname(__DIR__, 2) . '/bondi/bootstrap.php' : dirname(__DIR__) . '/bondi/bootstrap.php';

$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
privacy_page_start('Unsubscribe');

if (!privacy_ready()) {
    echo '<h1>Unsubscribe</h1><p>This page isn\'t ready yet. To unsubscribe, reply to any of our emails with "unsubscribe", or write to '
       . '<a href="mailto:' . $h((string)config('support_email')) . '">' . $h((string)config('support_email')) . '</a>.</p>';
    privacy_page_end();
}

$id = $_POST['id'] ?? $_GET['id'] ?? null;
$signature = $_POST['s'] ?? $_GET['s'] ?? null;
$signup = signup_for_unsubscribe_link($id, $signature);
if ($signup === null) {
    echo '<h1>This link doesn\'t work</h1><p>It may be incomplete, or you\'ve already deleted your data. '
       . 'You can manage everything on the <a href="/your-data/">Your data</a> page.</p>';
    privacy_page_end();
}
$hidden = '<input type="hidden" name="id" value="' . (int)$signup['id'] . '"><input type="hidden" name="s" value="' . $h((string)$signature) . '">';
$first = explode(' ', trim($signup['name']))[0];
$do = ($_SERVER['REQUEST_METHOD'] === 'POST') ? (string)($_POST['do'] ?? '') : '';

if ($do === 'unsubscribe') {
    if (!within_limit('unsubscribe-page:' . client_key(), 30, 3600)) {
        echo '<h1>Too many tries</h1><p>Wait a few minutes and try again.</p>';
        privacy_page_end();
    }
    $code = $signup['unsubscribed_at'] === null ? unsubscribe_signup($signup, 'email_link') : null;
    echo '<h1>You\'re unsubscribed</h1><p>We won\'t email you about Bondi again, ' . $h($first) . '. Thank you for being here early. It truly meant a lot.</p>';
    if ($code !== null) {
        echo '<p>Your confirmation number:</p><div class="code">' . $h($code) . '</div><p class="note">We\'ve emailed it to you too.</p>';
    }
    echo '<div class="panel"><h2>We\'ll miss you</h2><p>Changed your mind? One click puts you back on the list.</p>'
       . '<form method="post" class="row">' . $hidden . '<input type="hidden" name="do" value="resubscribe"><button class="btn">Stay on the list</button></form></div>'
       . '<p class="note">Your sign-up stays with us, without emails. To delete it, use the <a href="/your-data/">Your data</a> page.</p>';
    privacy_page_end();
}

if ($do === 'resubscribe') {
    resubscribe_signup($signup);
    echo '<h1>Welcome back, ' . $h($first) . '</h1><p>You\'re on the list again. We\'ll email you when the beta opens.</p>'
       . '<p><a class="btn" href="/beta/">Back to Bondi</a></p>';
    privacy_page_end();
}

if ($signup['unsubscribed_at'] !== null) {
    echo '<h1>You\'re already unsubscribed</h1><p>We don\'t email you about Bondi.</p>'
       . '<div class="panel"><p>Changed your mind?</p><form method="post" class="row">' . $hidden
       . '<input type="hidden" name="do" value="resubscribe"><button class="btn">Stay on the list</button></form></div>';
    privacy_page_end();
}

echo '<h1>Unsubscribe from Bondi emails?</h1><p>You\'ll stop getting emails about Bondi\'s beta and launch at ' . $h($signup['email']) . '. '
   . 'Emails about a license you\'ve bought, like a new key you ask for, still arrive.</p>'
   . '<form method="post" class="row">' . $hidden . '<input type="hidden" name="do" value="unsubscribe"><button class="btn">Unsubscribe</button>'
   . '<a class="btn quiet" href="/beta/">Keep me on the list</a></form>';
privacy_page_end();
