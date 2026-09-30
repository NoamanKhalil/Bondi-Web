<?php
// Flow: BY-05 / BY-06 after checkout. Bondi activates itself when it was bought from inside the app;
// otherwise the key is in the buyer's email.
$fromApp = ($_GET['from'] ?? '') === 'app';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Thank you · Bondi</title>
<meta name="robots" content="noindex">
<link rel="icon" type="image/png" href="/assets/favicon.png">
<style>
  :root { color-scheme: light dark; --bg: #f5f5f7; --card: #fff; --text: #1d1d1f; --muted: #6e6e73; --accent: #0071e3; }
  @media (prefers-color-scheme: dark) { :root { --bg: #1c1c1e; --card: #2c2c2e; --text: #f5f5f7; --muted: #a1a1a6; --accent: #0a84ff; } }
  body { margin: 0; font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Helvetica Neue", sans-serif; background: var(--bg); color: var(--text); }
  main { max-width: 440px; margin: 64px auto; padding: 32px; background: var(--card); border-radius: 16px; text-align: center; }
  a.button { display: block; margin-top: 20px; padding: 14px; font-weight: 600; color: #fff; background: var(--accent); border-radius: 10px; text-decoration: none; }
  .note { color: var(--muted); font-size: 14px; }
</style>
</head>
<body>
<main>
  <h1>Thank you!</h1>
  <?php if ($fromApp): ?>
    <p>Bondi is activating itself now. Switch back to it; it takes a few seconds.</p>
    <a class="button" href="bondi://open">Open Bondi</a>
  <?php else: ?>
    <p>Your license key is on its way to your email. Open Bondi, go to Settings, License, and paste it, or click the link in the email on the Mac you want to use.</p>
  <?php endif; ?>
  <p class="note">Nothing arrived after a few minutes? Check your spam folder, or use Find My Key in Bondi.</p>
</main>
</body>
</html>
