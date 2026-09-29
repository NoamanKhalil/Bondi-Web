<?php
// Local testing only: `php -S 127.0.0.1:8099 -t server/public_html server/dev/router.php` stands in for
// Hostinger's .htaccess rewrite, sending /api/<action> to api/index.php.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('~^/api/[a-z]+$~', $path)) {
    require __DIR__ . '/../public_html/api/index.php';
    return true;
}
return false;
