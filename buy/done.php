<?php
// Flow: BY-05 / BY-06 after checkout. Bondi activates itself when it was bought from inside the app;
// otherwise the key is in the buyer's email.
require __DIR__ . '/frame.php';
$fromApp = ($_GET['from'] ?? '') === 'app';

buy_page_start('Thank you · Bondi');
?>
<div class="buy-hero">
  <img src="/assets/icon-blue.png" alt="" width="88" height="88">
  <p class="eyebrow">Bondi is yours</p>
  <h1>Thank you!</h1>
  <?php if ($fromApp): ?>
    <p class="sub">Bondi is activating itself now. Switch back to it; it takes a few seconds.</p>
  <?php else: ?>
    <p class="sub">Your license key is on its way to your email. Open Bondi, go to Settings, License, and paste it, or click the link in the email on the Mac you want to use.</p>
  <?php endif; ?>
</div>
<?php if ($fromApp): ?><div class="actions"><a class="pillbtn primary" href="bondi://open">Open Bondi</a></div><?php endif; ?>
<p class="note">Nothing arrived after a few minutes? Check your spam folder, or use Find My Key in Bondi. Still stuck? Write to <a href="mailto:support@trybondi.app">support@trybondi.app</a>.</p>
<?php buy_page_end();
