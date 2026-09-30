<?php
// Bondi license server settings. Copy this file to config.php (same folder) and fill it in.
// config.php holds passwords and keys: it's never committed to git and never goes in public_html.
return [
    // The site's address, without a trailing slash.
    'base_url' => 'https://trybondi.app',

    // MySQL database from Hostinger: hPanel → Databases → Management.
    'db' => [
        'host' => 'localhost',
        'name' => 'u000000000_bondi',
        'user' => 'u000000000_bondi',
        'pass' => 'CHANGE-ME',
    ],

    // Paddle: 'sandbox' while testing, 'production' when selling.
    'paddle' => [
        'environment'    => 'sandbox',
        // Developer tools → Authentication → API keys (server side, keep secret).
        'api_key'        => 'CHANGE-ME',
        // Developer tools → Authentication → Client-side tokens (safe to show on the buy page).
        'client_token'   => 'CHANGE-ME',
        // Developer tools → Notifications → your destination → Secret key.
        'webhook_secret' => 'CHANGE-ME',
    ],

    // Emails with license keys come from this address (create it in hPanel → Emails).
    'mail_from' => 'Bondi <support@trybondi.app>',
    'support_email' => 'support@trybondi.app',

    // Admin page password, stored as a hash. Make one on your Mac in Terminal:
    //   php -r 'echo password_hash("your password here", PASSWORD_DEFAULT), PHP_EOL;'
    // and paste the result (starts with $2y$ or $argon2) here.
    'admin_password_hash' => 'CHANGE-ME',

    // Trial length in days (owner decision: 7).
    'trial_days' => 7,

    // Update sign-ups: which request header holds the visitor's real IP when the site is behind a CDN
    // (Cloudflare: 'HTTP_CF_CONNECTING_IP'). null uses the connecting address, right on plain Hostinger.
    'client_ip_header' => null,

    // Testing only: emails go to mail.log instead of being sent, and customer emails aren't looked up
    // at Paddle. Must be false on the live server.
    'test_mode' => false,
];
