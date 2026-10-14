<?php
// Flow: BY-04 Checkout. Opened by Bondi (with ?claim=… so it can activate itself afterwards) or from
// the website. Paddle's checkout opens over the page; Paddle handles payment, tax and the receipt.
// The private code lives beside public_html (best) or inside it as public_html/bondi, locked by its .htaccess.
require is_file(dirname(__DIR__, 2) . '/bondi/bootstrap.php') ? dirname(__DIR__, 2) . '/bondi/bootstrap.php' : dirname(__DIR__) . '/bondi/bootstrap.php';
require __DIR__ . '/frame.php';

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
// The price after the launch offer, shown under the launch price
$regular = $offer['tier'] === 'launch' ? one("SELECT amount_cents FROM price_tiers WHERE tier = 'regular'") : null;

buy_page_start('Buy Bondi');
?>
<div class="buy-hero">
  <img src="/assets/icon-blue.png" alt="" width="88" height="88">
  <p class="eyebrow">Bondi for Apple Mac</p>
  <h1>Buy it once. Keep it.</h1>
  <p class="sub">Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence.</p>
</div>
<section class="price-card" aria-label="Price">
  <?php if ($left !== null): ?><span class="badge">Launch price · <?= (int)$left ?> left</span><?php endif; ?>
  <p class="price"><?= htmlspecialchars($price) ?></p>
  <?php if ($regular): ?><p class="was">$<?= number_format($regular['amount_cents'] / 100, 2) ?> after the launch offer</p><?php endif; ?>
  <ul>
    <li>Everything in Bondi, with updates</li>
    <li>On-device AI: nothing leaves your Mac</li>
    <li>One Mac; move it to another any time</li>
    <li>No subscription</li>
  </ul>
  <button id="buy" class="pillbtn primary" type="button" <?= $settings['priceId'] ? '' : 'disabled' ?>>Buy Bondi for <?= htmlspecialchars($price) ?></button>
  <?php if ($claim): ?><p class="from-app">Bondi activates itself as soon as you've paid.</p><?php endif; ?>
  <p class="note" id="status"><?= $settings['priceId'] ? 'Payment, tax and receipt are handled by Paddle.' : 'Checkout isn\'t set up yet.' ?></p>
</section>
<p class="note">Full refund within 14 days. By buying you agree to the <a href="/terms/">terms</a> and <a href="/eula/">license agreement</a>; see the <a href="/refunds/">refund policy</a>.</p>
<script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
<script>
  const settings = <?= json_encode($settings, JSON_UNESCAPED_SLASHES) ?>;
  if (settings.sandbox) Paddle.Environment.set('sandbox');
  Paddle.Initialize({ token: settings.token });
  document.getElementById('buy').addEventListener('click', () => {
    Paddle.Checkout.open({
      items: [{ priceId: settings.priceId, quantity: 1 }],
      customData: settings.claim ? { claim: settings.claim } : undefined,
      // Paddle's window in the page's own light or dark
      settings: { displayMode: 'overlay', successUrl: settings.successUrl, allowLogout: false,
                  theme: document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light' },
    });
  });
</script>
<?php buy_page_end();
