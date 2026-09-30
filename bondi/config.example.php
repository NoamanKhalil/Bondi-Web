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

    // Send emails through that mailbox on Titan, so mail apps don't show "via" a Hostinger server.
    // pass: the support@ mailbox password (hPanel → Emails). Empty sends from the web server instead.
    'smtp' => [
        'host' => 'smtp.titan.email',
        'port' => 465,
        'user' => 'support@trybondi.app',
        'pass' => '',
    ],

    // Admin page sign-in: a username you choose (not case-sensitive) and a password used nowhere else.
    'admin_username' => 'CHANGE-ME',
    'admin_password' => '',

    // Optional instead of admin_password (leave that one empty): the password as a hash, so it can't be read
    // from this file. Make one in Terminal with php -r 'echo password_hash(trim(fgets(STDIN)), PASSWORD_DEFAULT), PHP_EOL;'
    'admin_password_hash' => '',

    // Trial length in days (owner decision: 7).
    'trial_days' => 7,

    // Update sign-ups: which request header holds the visitor's real IP when the site is behind a CDN
    // (Cloudflare: 'HTTP_CF_CONNECTING_IP'). null uses the connecting address, right on plain Hostinger.
    'client_ip_header' => null,

    // Testing only: emails go to mail.log instead of being sent, and customer emails aren't looked up
    // at Paddle. Must be false on the live server.
    'test_mode' => false,
];
