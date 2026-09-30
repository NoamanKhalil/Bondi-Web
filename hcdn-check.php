<?php
// Temporary: which request headers carry the visitor's address and country behind Hostinger's CDN. Remove after use.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo 'REMOTE_ADDR: ' . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";
foreach ($_SERVER as $name => $value) {
    if (str_starts_with($name, 'HTTP_') && preg_match('/COUNTRY|GEO|IP|FORWARD|REAL|CLIENT|HCDN|CITY|REGION|CONTINENT|VIA/', $name) && !str_contains($name, 'COOKIE')) {
        echo "$name: $value\n";
    }
}
echo 'all header names: ' . implode(', ', array_filter(array_keys($_SERVER), fn($n) => str_starts_with($n, 'HTTP_'))) . "\n";
