<?php
// Loaded first by every page and API call. Lives outside public_html, so it can't be opened from the web.
declare(strict_types=1);

date_default_timezone_set('UTC');

// Local tests point BONDI_CONFIG at their own throwaway settings; the live site always uses config.php.
$configFile = getenv('BONDI_CONFIG') ?: __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "The Bondi server isn't set up yet: copy bondi/config.example.php to bondi/config.php and fill it in.\n";
    exit;
}
$GLOBALS['bondi_config'] = require $configFile;

require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/http.php';
require __DIR__ . '/lib/keys.php';
require __DIR__ . '/lib/ratelimit.php';
require __DIR__ . '/lib/paddle.php';
require __DIR__ . '/lib/mail.php';
require __DIR__ . '/lib/licenses.php';
require __DIR__ . '/lib/signups.php';

/** A setting from config.php, e.g. config('paddle.api_key'). */
function config(string $path, mixed $default = null): mixed
{
    $value = $GLOBALS['bondi_config'];
    foreach (explode('.', $path) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}
