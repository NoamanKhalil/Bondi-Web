<?php
// Emails: license keys (styled, with the same words as plain text) and, from the admin page, notes to
// update sign-ups. They go through the support@ mailbox on Titan (smtp in config.php), so they're signed for
// trybondi.app and mail apps show no "via" server. Without an SMTP password, or if Titan fails, PHP's mail()
// sends them from the web server instead. In test mode they're written to bondi/mail.log.
//
// The Bondi icon travels inside the license email, so opening it loads nothing from any server (no tracking,
// nothing for a mail app to block).

function send_key_email(string $email, string $key, string $kind, ?int $licenseId): void
{
    $mail = key_email($key, $kind);
    send_mail($email, $mail['subject'], $mail['text'], $mail['html'], email_images());
    run('INSERT INTO email_log (email, kind, license_id) VALUES (?, ?, ?)', [$email, $kind, $licenseId]);
}

/**
 * Sends one email: plain text, plus HTML when given, plus PNG images the HTML shows as cid:<id>, plus any
 * extra headers (such as List-Unsubscribe; a From: there replaces the usual sender).
 * Returns false only when nothing could send it.
 */
function send_mail(string $to, string $subject, string $text, ?string $html = null, array $images = [], array $extraHeaders = []): bool
{
    if (config('test_mode')) {
        file_put_contents(dirname(__DIR__) . '/mail.log', "To: $to\nSubject: $subject\n\n$text\n----\n", FILE_APPEND);
        return true;
    }
    $part = static fn(string $content): string => chunk_split(base64_encode($content), 76, "\r\n");
    $textPart = "Content-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . $part($text);
    if ($html === null) {
        [$type, $body] = ['text/plain; charset=utf-8', $part($text)];
        $headers = ['Content-Transfer-Encoding: base64'];
    } else {
        $alternative = 'alt-' . bin2hex(random_bytes(12));
        $body = "--$alternative\r\n$textPart"
              . "--$alternative\r\nContent-Type: text/html; charset=utf-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . $part($html)
              . "--$alternative--\r\n";
        $type = "multipart/alternative; boundary=\"$alternative\"";
        if ($images) {
            $related = 'rel-' . bin2hex(random_bytes(12));
            $body = "--$related\r\nContent-Type: $type\r\n\r\n$body";
            foreach ($images as $id => $png) {
                $body .= "--$related\r\nContent-Type: image/png\r\nContent-Transfer-Encoding: base64\r\n"
                       . "Content-ID: <$id>\r\nContent-Disposition: inline; filename=\"bondi.png\"\r\n\r\n" . $part($png);
            }
            $body .= "--$related--\r\n";
            $type = "multipart/related; boundary=\"$related\"; type=\"multipart/alternative\"";
        }
        $headers = [];
    }
    $customFrom = (bool)array_filter($extraHeaders, fn($line) => stripos($line, 'From:') === 0);
    $headers = array_merge([
        ...($customFrom ? [] : ['From: ' . config('mail_from')]),
        'Reply-To: ' . config('support_email'),
        'MIME-Version: 1.0',
        "Content-Type: $type",
    ], $headers, $extraHeaders);

    if ((string)config('smtp.pass', '') !== '') {
        try {
            smtp_send($to, $subject, $headers, $body);
            return true;
        } catch (Throwable $error) {
            error_log('Bondi mail: SMTP failed, sending with mail() instead: ' . $error->getMessage());
        }
    }
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/** Hands one message to the SMTP server in config.php (Titan: smtp.titan.email, port 465). Throws on any refusal. */
function smtp_send(string $to, string $subject, array $headers, string $body): void
{
    $host = (string)config('smtp.host', 'smtp.titan.email');
    $port = (int)config('smtp.port', 465);
    $user = (string)config('smtp.user', config('support_email'));
    $server = @stream_socket_client(($port === 465 ? 'ssl://' : 'tcp://') . "$host:$port", $errno, $errstr, 15);
    if ($server === false) {
        throw new RuntimeException("can't reach $host:$port ($errstr)");
    }
    stream_set_timeout($server, 15);
    $say = static function (?string $line, int $expect) use ($server): void {
        if ($line !== null) {
            fwrite($server, $line . "\r\n");
        }
        $reply = '';
        while (($got = fgets($server, 1024)) !== false) {
            $reply .= $got;
            if (strlen($got) < 4 || $got[3] !== '-') {
                break;
            }
        }
        if ((int)substr($reply, 0, 3) !== $expect) {
            $sent = $line === null ? 'connect' : strtok($line, ' ');
            throw new RuntimeException("$sent: " . trim($reply));
        }
    };
    try {
        $say(null, 220);
        $say('EHLO trybondi.app', 250);
        if ($port === 587) {
            $say('STARTTLS', 220);
            stream_socket_enable_crypto($server, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $say('EHLO trybondi.app', 250);
        }
        $say('AUTH LOGIN', 334);
        $say(base64_encode($user), 334);
        $say(base64_encode((string)config('smtp.pass')), 235);
        $say("MAIL FROM:<$user>", 250);
        $say('RCPT TO:<' . str_replace(['<', '>', "\r", "\n"], '', $to) . '>', 250);
        $say('DATA', 354);
        $message = implode("\r\n", array_merge([
            'Date: ' . date(DATE_RFC2822),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@trybondi.app>',
            "To: $to",
            'Subject: ' . (preg_match('/[^\x20-\x7e]/', $subject) ? '=?UTF-8?B?' . base64_encode($subject) . '?=' : $subject),
        ], $headers)) . "\r\n\r\n" . $body;
        // A line that starts with a dot gets a second one, so it can't end the message early.
        $say(preg_replace('/^\./m', '..', rtrim($message, "\r\n")) . "\r\n.", 250);
        try {
            $say('QUIT', 221); // The message is already accepted; a rude goodbye doesn't matter.
        } catch (Throwable) {
        }
    } finally {
        fclose($server);
    }
}

/** The words of a license email: ['subject', 'text', 'html']. */
function key_email(string $key, string $kind): array
{
    $activate = 'bondi://activate?key=' . rawurlencode($key);
    $support = (string)config('support_email');
    if ($kind === 'recovery') {
        $subject = 'Your new Bondi license key';
        $heading = 'Your new license key';
        $intro = "Here's a new license key for Bondi, as you asked. Your old key no longer works, but any Mac "
               . "that's already activated keeps working.";
    } elseif ($kind === 'comp') {
        $subject = 'A Bondi license for you';
        $heading = 'A Bondi license for you';
        $intro = "Here's a Bondi license, with our compliments.";
    } else {
        $subject = 'Your Bondi license key';
        $heading = 'Thanks for buying Bondi';
        $intro = "If you bought from inside Bondi, it has probably activated itself already.";
    }
    $textIntro = $kind === 'recovery' || $kind === 'comp' ? $intro : "Thanks for buying Bondi. $intro";
    $oneMac = 'One license covers one Mac. To move it to another Mac, choose Deactivate This Mac in Bondi, '
            . 'Settings, License on the old one first.';
    $keep = "Keep this email: it's your proof of purchase. Paddle, who handles the payment, sends the receipt.";

    $text = "$textIntro\n\n"
          . "Your license key:\n\n    $key\n\n"
          . "To activate Bondi on a Mac, open this link on that Mac:\n    $activate\n"
          . "or paste the key in Bondi, Settings, License.\n\n"
          . "$oneMac\n\n"
          . "$keep\n"
          . "Questions: $support\n";

    $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $font = EMAIL_FONT;
    $mono = "ui-monospace,'SF Mono',Menlo,Consolas,monospace";
    $inner = <<<HTML
    <h1 class="ink" style="margin:24px 0 10px;font-size:26px;line-height:1.15;font-weight:700;letter-spacing:-.02em;color:#1d1d1f;">{$e($heading)}</h1>
    <p class="soft" style="margin:0 0 28px;font-size:16px;line-height:1.5;color:#6e6e73;">{$e($intro)}</p>

    <p class="soft" style="margin:0 0 8px;font-size:12px;line-height:1.3;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#6e6e73;">Your license key</p>
    <div class="key" style="margin:0 0 24px;padding:16px 18px;background:rgba(0,0,0,.04);border:1px solid rgba(0,0,0,.08);border-radius:12px;font-family:$mono;font-size:19px;line-height:1.3;font-weight:600;letter-spacing:.04em;color:#0e86a6;text-align:center;-webkit-user-select:all;user-select:all;overflow-wrap:anywhere;">{$e($key)}</div>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
      <td style="border-radius:10px;background:#0e86a6;">
        <a href="{$e($activate)}" style="display:inline-block;padding:13px 24px;font-family:$font;font-size:16px;line-height:1;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;">Activate on this Mac</a>
      </td>
    </tr></table>
    <p class="soft" style="margin:12px 0 0;font-size:14px;line-height:1.5;color:#6e6e73;">Open this email on the Mac you want to use, or paste the key in Bondi, Settings, License.</p>

    <hr class="rule" style="margin:32px 0 24px;border:0;border-top:1px solid rgba(0,0,0,.08);">
    <p class="soft" style="margin:0 0 12px;font-size:14px;line-height:1.5;color:#6e6e73;">{$e($oneMac)}</p>
    <p class="soft" style="margin:0 0 12px;font-size:14px;line-height:1.5;color:#6e6e73;">{$e($keep)}</p>
    <p class="soft" style="margin:0;font-size:14px;line-height:1.5;color:#6e6e73;">Questions? <a class="link" href="mailto:{$e($support)}" style="color:#0e86a6;text-decoration:none;">{$e($support)}</a></p>
HTML;

    return ['subject' => $subject, 'text' => $text, 'html' => email_html($subject, "Your Bondi license key: $key", $inner)];
}

/**
 * A note to one update sign-up, written on the admin page. {name} in the subject or message becomes their name.
 * Blank lines start new paragraphs; web addresses become links. Returns ['subject', 'text', 'html'].
 */
function update_email(string $name, string $subject, string $message): array
{
    $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $subject = str_replace('{name}', $name, $subject);
    $message = trim(str_replace(["\r\n", '{name}'], ["\n", $name], $message));
    $support = (string)config('support_email');
    $why = "You're getting this because you signed up for Bondi updates at trybondi.app. "
         . "To stop them, reply with \"unsubscribe\".";

    $paragraphs = '';
    foreach (preg_split('/\n\s*\n/', $message) as $paragraph) {
        $html = nl2br($e(trim($paragraph)), false);
        $html = preg_replace('~https?://[^\s<>"\']+[^\s<>"\'.,;:!?)]~', '<a class="link" href="$0" style="color:#0e86a6;text-decoration:none;">$0</a>', $html);
        $paragraphs .= '<p class="ink" style="margin:0 0 16px;font-size:16px;line-height:1.55;color:#1d1d1f;">' . $html . '</p>';
    }
    $inner = <<<HTML
    <h1 class="ink" style="margin:24px 0 18px;font-size:24px;line-height:1.2;font-weight:700;letter-spacing:-.02em;color:#1d1d1f;">{$e($subject)}</h1>
    $paragraphs
    <hr class="rule" style="margin:28px 0 20px;border:0;border-top:1px solid rgba(0,0,0,.08);">
    <p class="soft" style="margin:0;font-size:13px;line-height:1.5;color:#6e6e73;">{$e($why)} Questions? <a class="link" href="mailto:{$e($support)}" style="color:#0e86a6;text-decoration:none;">{$e($support)}</a></p>
HTML;
    $text = "$message\n\n--\n$why\nQuestions: $support\n";
    $preview = mb_substr(preg_replace('/\s+/', ' ', $message), 0, 120);

    return ['subject' => $subject, 'text' => $text, 'html' => email_html($subject, $preview, $inner)];
}

/** Noaman, Bondi's maker, on X. */
const MAKER_X = 'khalilnoaman';

/**
 * The thank-you for joining the beta list, from Noaman, sent once per sign-up (sign_up_welcome()).
 * Returns ['subject', 'text', 'html'].
 */
function welcome_email(string $name): array
{
    $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $first = explode(' ', trim($name))[0] ?: 'friend';
    $support = (string)config('support_email');
    $x = 'https://x.com/' . MAKER_X;
    $post = 'https://x.com/intent/post?text=' . rawurlencode("I just joined the beta for Bondi, a Mac app that tells you why your Mac is slow in one plain sentence. Come join the tribe: https://trybondi.app/beta/ (made by @" . MAKER_X . ")");
    $mail = 'mailto:?subject=' . rawurlencode('Join me on the Bondi beta') . '&body='
          . rawurlencode("I just joined the beta for Bondi, a Mac app that tells you why your Mac is slow in one plain sentence, right on your Mac. I think you'd love it:\n\nhttps://trybondi.app/beta/");
    $subject = "You're in, $first. Thank you.";
    $paras = [
        "Thank you for signing up for the Bondi beta. I mean that with my whole heart.",
        "I made Bondi because everyone with a Mac deserves a straight answer to a simple question: why is my Mac slow? Bondi answers in one plain sentence, right on your Mac, and nothing ever leaves it. I've poured so much love into every detail, and you're one of the very first people to believe in it. That means more to me than I can say.",
        "I'll email you the moment the beta opens. Until then, two small things would mean the world to me:",
    ];
    $why = "You're getting this because you signed up for the Bondi beta at trybondi.app. To stop hearing from us, reply with \"unsubscribe\".";

    $text = "Hi $first,\n\n" . implode("\n\n", $paras) . "\n\n"
          . "1. Follow along on X. I'm @" . MAKER_X . ", and I share Bondi's journey there: $x\n\n"
          . "2. Invite a friend. Know someone whose Mac drives them up the wall? Bring them into the tribe: https://trybondi.app/beta/\n\n"
          . "With love and gratitude,\nNoaman\nMaker of Bondi\n\n--\n$why\nQuestions: $support\n";

    $p = fn(string $t): string => '<p class="ink" style="margin:0 0 16px;font-size:16px;line-height:1.55;color:#1d1d1f;">' . $e($t) . '</p>';
    $body = implode('', array_map($p, $paras));
    $font = EMAIL_FONT;
    $inner = <<<HTML
    <h1 class="ink" style="margin:24px 0 18px;font-size:28px;line-height:1.15;font-weight:700;letter-spacing:-.02em;color:#1d1d1f;">You're in, {$e($first)}.</h1>
    $body
    <div class="key" style="margin:8px 0 14px;padding:18px 20px;background:rgba(0,0,0,.04);border:1px solid rgba(0,0,0,.08);border-radius:14px;">
      <p class="ink" style="margin:0 0 4px;font-size:16px;font-weight:600;color:#1d1d1f;">Follow along on X</p>
      <p class="soft" style="margin:0 0 14px;font-size:15px;line-height:1.5;color:#6e6e73;">I'm @{$e(MAKER_X)}, and I share Bondi's journey there.</p>
      <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="border-radius:10px;background:#0e86a6;">
        <a href="{$e($x)}" style="display:inline-block;padding:12px 22px;font-family:$font;font-size:15px;line-height:1;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;">Follow @{$e(MAKER_X)}</a>
      </td></tr></table>
    </div>
    <div class="key" style="margin:0 0 24px;padding:18px 20px;background:rgba(0,0,0,.04);border:1px solid rgba(0,0,0,.08);border-radius:14px;">
      <p class="ink" style="margin:0 0 4px;font-size:16px;font-weight:600;color:#1d1d1f;">Invite a friend</p>
      <p class="soft" style="margin:0 0 12px;font-size:15px;line-height:1.5;color:#6e6e73;">Know someone whose Mac drives them up the wall? Bring them into the tribe.</p>
      <p style="margin:0;font-size:15px;font-weight:600;"><a class="link" href="{$e($post)}" style="color:#0e86a6;text-decoration:none;">Share on X</a> <span class="soft" style="color:#6e6e73;">&nbsp;·&nbsp;</span> <a class="link" href="{$e($mail)}" style="color:#0e86a6;text-decoration:none;">Email a friend</a></p>
    </div>
    <p class="ink" style="margin:0;font-size:16px;line-height:1.55;color:#1d1d1f;">With love and gratitude,<br><strong>Noaman</strong><br><span class="soft" style="color:#6e6e73;">Maker of Bondi</span></p>
    <hr class="rule" style="margin:28px 0 20px;border:0;border-top:1px solid rgba(0,0,0,.08);">
    <p class="soft" style="margin:0;font-size:13px;line-height:1.5;color:#6e6e73;">{$e($why)} Questions? <a class="link" href="mailto:{$e($support)}" style="color:#0e86a6;text-decoration:none;">{$e($support)}</a></p>
HTML;
    return ['subject' => $subject, 'text' => $text, 'html' => email_html($subject, "Thank you for signing up for the Bondi beta. A note from Noaman.", $inner)];
}

/**
 * Sends one sign-up the thank-you from Noaman, unless they've had it. Logged in email_log as 'welcome'.
 * Returns true if it was sent now.
 */
function sign_up_welcome(string $email, string $name): bool
{
    if (one("SELECT id FROM email_log WHERE email = ? AND kind = 'welcome' LIMIT 1", [$email]) !== null) {
        return false;
    }
    $mail = welcome_email($name);
    $support = (string)config('support_email');
    $sent = send_mail($email, $mail['subject'], $mail['text'], $mail['html'], email_images(), [
        "From: Noaman Khalil <$support>",
        "List-Unsubscribe: <mailto:$support?subject=unsubscribe>",
    ]);
    if ($sent) {
        run("INSERT INTO email_log (email, kind, license_id) VALUES (?, 'welcome', NULL)", [$email]);
    }
    return $sent;
}

const EMAIL_FONT = "-apple-system,BlinkMacSystemFont,'SF Pro Text','Helvetica Neue',Helvetica,Arial,sans-serif";

/** Bondi's email page around $inner: icon, card, footer; light and dark, phone width. */
function email_html(string $title, string $preview, string $inner): string
{
    $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $font = EMAIL_FONT;
    return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<title>{$e($title)}</title>
<style>
  @media (prefers-color-scheme: dark) {
    .page { background:#131315 !important; }
    .card { background:#1e1e1e !important; border-color:rgba(255,255,255,.08) !important; }
    .ink { color:#f2f2f2 !important; }
    .soft { color:#a1a1a6 !important; }
    .key { background:rgba(255,255,255,.05) !important; border-color:rgba(255,255,255,.1) !important; color:#5fd4ea !important; }
    .rule { border-color:rgba(255,255,255,.08) !important; }
    .link { color:#5fd4ea !important; }
  }
  @media (max-width:520px) { .pad { padding:28px 22px !important; } .key { font-size:15px !important; letter-spacing:0 !important; padding:14px 10px !important; } }
</style>
</head>
<body class="page" style="margin:0;padding:0;background:#f5f5f7;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{$e($preview)}</div>
<table role="presentation" class="page" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f5f7;">
<tr><td align="center" style="padding:40px 12px;">
  <table role="presentation" class="card" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px;background:#ffffff;border:1px solid rgba(0,0,0,.06);border-radius:18px;">
  <tr><td class="pad" style="padding:40px 40px 32px;font-family:$font;">
    <img src="cid:bondi-icon@trybondi.app" width="56" height="56" alt="Bondi" style="display:block;border:0;width:56px;height:56px;border-radius:13px;">
$inner
  </td></tr>
  </table>
  <p class="soft" style="margin:20px 0 0;font-family:$font;font-size:12px;line-height:1.5;color:#6e6e73;">Bondi for Apple Mac · Jabble Super Intelligence Inc. · <a class="link" href="https://trybondi.app" style="color:#0e86a6;text-decoration:none;">trybondi.app</a></p>
</td></tr>
</table>
</body>
</html>
HTML;
}

/** The Bondi icon that email_html() shows, to pass to send_mail(). */
function email_images(): array
{
    return ['bondi-icon@trybondi.app' => (string)file_get_contents(dirname(__DIR__) . '/mail-icon.png')];
}
