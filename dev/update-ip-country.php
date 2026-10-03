<?php
if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true)) { http_response_code(404); exit; } // a tool for Terminal (and the local test server), never a web page on the host
// Refreshes the IP address → country lists the sign-up form uses (bondi/ip-country-v4.bin, -v6.bin).
// Usage: php dev/update-ip-country.php   (then commit the two files; every few months is plenty)
//
// Source: geo-whois-asn-country from github.com/sapics/ip-location-db, public domain (CC0), built from the
// regional internet registries' own records. It's downloaded here, on a developer's Mac; the live site
// only ever reads the two files, so no visitor's address is sent anywhere.
//
// File format, sorted by first address: first address, last address (4 bytes each for IPv4, 16 for IPv6),
// then the country's two letters. Neighbouring ranges in one country are merged.

ini_set('memory_limit', '1G');
$base = 'https://cdn.jsdelivr.net/npm/@ip-location-db/geo-whois-asn-country/geo-whois-asn-country-';
$out = dirname(__DIR__) . '/bondi';

foreach (['v4', 'v6'] as $family) {
    $csv = file_get_contents($base . 'ip' . $family . '.csv');
    if ($csv === false || strlen($csv) < 100000) {
        fwrite(STDERR, "Couldn't download the IP$family list.\n");
        exit(1);
    }
    $records = [];
    $skipped = 0;
    foreach (explode("\n", trim($csv)) as $line) {
        [$first, $last, $country] = array_pad(explode(',', trim($line)), 3, '');
        $a = @inet_pton($first);
        $b = @inet_pton($last);
        if ($a === false || $b === false || !preg_match('/^[A-Z]{2}$/', $country)) {
            $skipped++;
            continue;
        }
        $previous = count($records) - 1;
        if ($previous >= 0 && $records[$previous][2] === $country && next_address($records[$previous][1]) === $a) {
            $records[$previous][1] = $b; // continues the range before it, in the same country
        } else {
            $records[] = [$a, $b, $country];
        }
    }
    usort($records, fn($x, $y) => strcmp($x[0], $y[0]));
    $binary = '';
    foreach ($records as [$a, $b, $country]) {
        $binary .= $a . $b . $country;
    }
    file_put_contents("$out/ip-country-$family.bin", $binary);
    printf("IP%s: %d ranges, %.1f MB (%d lines skipped)\n", $family, count($records), strlen($binary) / 1e6, $skipped);
}

/** The address right after $address (big-endian bytes, any length). */
function next_address(string $address): string
{
    for ($i = strlen($address) - 1; $i >= 0; $i--) {
        $byte = ord($address[$i]);
        if ($byte < 255) {
            return substr($address, 0, $i) . chr($byte + 1) . str_repeat("\0", strlen($address) - $i - 1);
        }
    }
    return $address; // the very last address; nothing comes after it
}
