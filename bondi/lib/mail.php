<?php
// License emails, sent with PHP's mail() through Hostinger's mail server. In test mode they're written
// to bondi/mail.log instead.

function send_key_email(string $email, string $key, string $kind, ?int $licenseId): void
{
    $activate = 'bondi://activate?key=' . rawurlencode($key);
    $support = config('support_email');
    if ($kind === 'recovery') {
        $subject = 'Your new Bondi license key';
        $intro = "Here's a new license key for Bondi, as you asked. Your old key no longer works, but any Mac "
               . "that's already activated keeps working.";
    } elseif ($kind === 'comp') {
        $subject = 'A Bondi license for you';
        $intro = "Here's a Bondi license, with our compliments.";
    } else {
        $subject = 'Your Bondi license key';
        $intro = "Thanks for buying Bondi. If you bought from inside Bondi, it has probably activated itself already.";
    }
    $body = "$intro\n\n"
          . "Your license key:\n\n    $key\n\n"
          . "To activate Bondi on a Mac, open this link on that Mac:\n    $activate\n"
          . "or paste the key in Bondi, Settings, License.\n\n"
          . "One license covers one Mac. To move it to another Mac, choose Deactivate This Mac in Bondi, "
          . "Settings, License on the old one first.\n\n"
          . "Keep this email: it's your proof of purchase. Paddle, who handles the payment, sends the receipt.\n"
          . "Questions: $support\n";

    if (config('test_mode')) {
        file_put_contents(dirname(__DIR__) . '/mail.log', "To: $email\nSubject: $subject\n\n$body\n----\n", FILE_APPEND);
    } else {
        $headers = [
            'From: ' . config('mail_from'),
            'Reply-To: ' . $support,
            'Content-Type: text/plain; charset=utf-8',
        ];
        mail($email, $subject, $body, implode("\r\n", $headers));
    }
    run('INSERT INTO email_log (email, kind, license_id) VALUES (?, ?, ?)', [$email, $kind, $licenseId]);
}
