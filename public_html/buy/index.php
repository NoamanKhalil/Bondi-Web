<?php
// Flow: BY-04 Checkout. Opened by Bondi (with ?claim=… so it can activate itself afterwards) or from
// the website. Paddle's checkout opens over the page; Paddle handles payment, tax and the receipt.
// The private code lives beside public_html (best) or inside it as public_html/bondi, locked by its .htaccess.
require is_file(dirname(__DIR__, 2) . '/bondi/bootstrap.php') ? dirname(__DIR__, 2) . '/bondi/bootstrap.php' : dirname(__DIR__) . '/bondi/bootstrap.php';

$offer = current_offer();
$claim = preg_match('/^[a-f0-9]{64}$/', (string)($_GET['claim'] ?? '')) ? hash('sha256', $_GET['claim']) : null;
$sandbox = config('paddle.environment') !== 'production';
$settings = [
    'priceId' => $offer['paddle_price_id'],
    'token' => config('paddle.client_token'),
    'sandbox' => $sandbox,
    'claim' => $claim,                       // only the hash goes to Paddle
    'successUrl' => config('base_url') . '/buy/done.php' . ($claim ? '?from=app' : ''),
];
$price = '$' . $offer['price'];
$left = $offer['launch_remaining'];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Buy Bondi</title>
<meta name="robots" content="noindex">
<link rel="icon" type="image/png" href="/assets/favicon.png">
<style>
  :root { color-scheme: light dark; --bg: #f5f5f7; --card: #fff; --text: #1d1d1f; --muted: #6e6e73; --accent: #0071e3; }
  @media (prefers-color-scheme: dark) { :root { --bg: #1c1c1e; --card: #2c2c2e; --text: #f5f5f7; --muted: #a1a1a6; --accent: #0a84ff; } }
  body { margin: 0; font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Helvetica Neue", sans-serif; background: var(--bg); color: var(--text); }
  main { max-width: 440px; margin: 64px auto; padding: 32px; background: var(--card); border-radius: 16px; text-align: center; }
  h1 { margin: 0 0 8px; font-size: 28px; }
  .price { font-size: 44px; font-weight: 700; margin: 16px 0 0; }
  .note { color: var(--muted); font-size: 14px; }
  a { color: var(--accent); }
  ul { text-align: left; color: var(--muted); font-size: 15px; padding-left: 20px; }
  button { margin-top: 20px; width: 100%; padding: 14px; font-size: 17px; font-weight: 600; color: #fff; background: var(--accent); border: 0; border-radius: 10px; cursor: pointer; }
  button:disabled { opacity: .5; cursor: default; }
</style>
</head>
<body>
<main>
  <h1>Bondi</h1>
  <p class="note">See what's slowing your Mac, in plain English.</p>
  <p class="price"><?= htmlspecialchars($price) ?></p>
  <p class="note">One-time purchase for one Mac<?= $left !== null ? ' · launch price, ' . (int)$left . ' left' : '' ?></p>
  <ul>
    <li>Everything in Bondi, with updates</li>
    <li>On-device AI included; your data never leaves your Mac</li>
    <li>Move it to another Mac any time</li>
  </ul>
  <button id="buy" <?= $settings['priceId'] ? '' : 'disabled' ?>>Buy Bondi</button>
  <p class="note" id="status"><?= $settings['priceId'] ? 'Payment, tax and receipt are handled by Paddle.' : 'Checkout isn\'t set up yet.' ?></p>
  <p class="note">No subscription. Full refund within 14 days. By buying you agree to the <a href="/terms/">terms</a> and <a href="/eula/">license agreement</a>; see the <a href="/refunds/">refund policy</a>.</p>
</main>
<script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
<script>
  const settings = <?= json_encode($settings, JSON_UNESCAPED_SLASHES) ?>;
  if (settings.sandbox) Paddle.Environment.set('sandbox');
  Paddle.Initialize({ token: settings.token });
  document.getElementById('buy').addEventListener('click', () => {
    Paddle.Checkout.open({
      items: [{ priceId: settings.priceId, quantity: 1 }],
      customData: settings.claim ? { claim: settings.claim } : undefined,
      settings: { displayMode: 'overlay', successUrl: settings.successUrl, allowLogout: false },
    });
  });
</script>
</body>
</html>
