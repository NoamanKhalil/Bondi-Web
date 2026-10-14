<?php
// The buy pages' frame: the site's header (logo and light/dark switch) and footer, on the site's own styles
// (assets/blog.css). Opening this file on its own shows nothing.

function buy_page_start(string $title): void
{
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">'
       . '<title>' . htmlspecialchars($title) . '</title><meta name="robots" content="noindex">'
       . '<link rel="icon" type="image/png" href="/assets/favicon.png"><link rel="apple-touch-icon" href="/apple-touch-icon.png">'
       . '<script src="/assets/theme.js"></script><link rel="stylesheet" href="/assets/blog.css"><style>' . BUY_CSS . '</style></head><body>'
       . '<header class="nav"><div class="nav-inner"><a class="brand" href="/"><img src="/assets/icon-blue.png" alt="" width="26" height="26">Bondi</a>'
       . '<div class="nav-right"><button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode">'
       . '<svg class="moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5a8.5 8.5 0 1 0 10.7 10.7z"/></svg>'
       . '<svg class="sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M5.3 18.7l1.6-1.6M17.1 6.9l1.6-1.6"/></svg>'
       . '</button></div></div></header><main class="buy">';
}

function buy_page_end(): void
{
    echo '</main><footer><div class="foot-line"><span>© 2026 Jabble Super Intelligence Inc.</span><a href="/">trybondi.app</a>'
       . '<a href="/privacy/">Privacy</a><a href="/terms/">Terms</a><a href="/eula/">License agreement</a><a href="/refunds/">Refunds</a>'
       . '<a href="mailto:support@trybondi.app">support@trybondi.app</a></div></footer></body></html>';
}

// The hero and price card, in the homepage's pricing style
const BUY_CSS = <<<'CSS'
.buy { max-width: 560px; margin: 0 auto; padding: 48px 20px 64px; display: grid; gap: 28px; text-align: center; }
.buy-hero { display: grid; gap: 12px; justify-items: center; }
.buy-hero img { width: 88px; height: 88px; filter: drop-shadow(0 16px 26px rgba(0, 149, 182, 0.35)); }
.buy-hero .eyebrow { margin: 4px 0 0; color: var(--bondi-ink); font-weight: 600; font-size: 1.05rem; }
.buy-hero h1 { margin: 0; font: 700 clamp(2.4rem, 7vw, 3.6rem)/1.05 var(--display); letter-spacing: -0.04em; text-wrap: balance; }
.buy-hero p.sub { margin: 0; color: var(--soft); font-size: 1.1rem; max-width: 30rem; }
.price-card { background: var(--tile); border-radius: 28px; padding: 36px 32px; display: grid; gap: 16px; justify-items: center; box-shadow: var(--shadow); }
.price-card .badge { font-size: .85rem; font-weight: 600; padding: 6px 14px; border-radius: 980px; background: var(--bondi-soft); color: var(--bondi-ink); }
.price-card .price { font: 700 clamp(3.4rem, 12vw, 4.5rem)/1 var(--display); letter-spacing: -0.04em; margin: 0; font-variant-numeric: tabular-nums; }
.price-card .was { margin: -6px 0 0; color: var(--faint); }
.price-card ul { list-style: none; margin: 4px 0 0; padding: 0; display: grid; gap: 8px; text-align: left; color: var(--soft); }
.price-card li { position: relative; padding-left: 26px; } .price-card li::before { content: "✓"; position: absolute; left: 0; color: var(--bondi-ink); font-weight: 700; }
.price-card .pillbtn { width: 100%; margin-top: 8px; padding: 15px 22px; font-size: 1.1rem; font-weight: 600; border: 0; cursor: pointer; }
.price-card .pillbtn:disabled { opacity: .5; cursor: default; }
.buy .note { margin: 0; color: var(--faint); font-size: .88rem; }
.buy .note a { color: var(--soft); }
.from-app { background: var(--bondi-soft); color: var(--ink); border-radius: 14px; padding: 12px 16px; font-size: .95rem; margin: 0; }
.buy .actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
footer .foot-line { justify-content: center; }
CSS;
