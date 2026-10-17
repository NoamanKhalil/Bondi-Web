<?php
if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true)) { http_response_code(404); exit; } // a tool for Terminal (and the local test server), never a web page on the host
// Local testing only: `php -S 127.0.0.1:8099 -t public_html dev/router.php` stands in for
// Hostinger's .htaccess rewrite, sending /api/<action> to api/index.php.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// The same invite-link rule as public_html/.htaccess: /w/name opens the beta page with that name.
if (preg_match('~^/w/([a-z0-9](?:[a-z0-9-]{0,38}[a-z0-9])?)/?$~i', $path, $invite)) {
    header('Location: /beta/?via=' . strtolower($invite[1]), true, 302);
    return true;
}
if (preg_match('~^/w/~i', $path)) {
    http_response_code(404); // a name with spaces, underscores or anything else is not a link
    return true;
}
if (preg_match('~^/api/[a-z]+$~', $path)) {
    require __DIR__ . '/../public_html/api/index.php';
    return true;
}
return false;
