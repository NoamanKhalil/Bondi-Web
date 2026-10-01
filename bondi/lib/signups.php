<?php
// The website's "Sign up for updates" list: name, email, IP address and country.

/** True once sql/005_signup_page.sql has been imported (signups.page exists). */
function signups_have_page(): bool
{
    static $has = null;
    return $has ??= one("SHOW COLUMNS FROM signups LIKE 'page'") !== null;
}

/**
 * The real numbers for the beta page: everyone on the update list, and those who signed up on the beta page
 * (null until sql/005 is imported).
 */
function interest_counts(): array
{
    $total = (int)one('SELECT COUNT(*) AS n FROM signups')['n'];
    $beta = signups_have_page() ? (int)one("SELECT COUNT(*) AS n FROM signups WHERE page = 'beta'")['n'] : null;
    return ['signups' => $total, 'beta' => $beta];
}

/** The visitor's IP address. Behind a CDN, set `client_ip_header` in config.php (e.g. HTTP_CF_CONNECTING_IP). */
function signup_ip(): string
{
    $header = config('client_ip_header');
    $candidate = is_string($header) ? trim(explode(',', (string)($_SERVER[$header] ?? ''))[0]) : '';
    if (filter_var($candidate, FILTER_VALIDATE_IP)) {
        return $candidate;
    }
    return (string)($_SERVER['REMOTE_ADDR'] ?? '');
}

/**
 * The visitor's country as [code, source]: from their IP address ('ip'), otherwise from the browser's time
 * zone ('timezone'); [null, null] when neither says. The IP's country comes from a header when the host or
 * a CDN adds one (Cloudflare's CF-IPCountry), or else from the lists in bondi/ (ip_country()). No outside
 * service is asked.
 */
function signup_country(?string $timeZone, ?string $ip = null): array
{
    $headers = (array)config('country_headers', ['HTTP_CF_IPCOUNTRY', 'GEOIP_COUNTRY_CODE', 'HTTP_X_COUNTRY_CODE']);
    foreach ($headers as $header) {
        $code = strtoupper(trim((string)($_SERVER[$header] ?? '')));
        if (preg_match('/^[A-Z]{2}$/', $code) && $code !== 'XX' && $code !== 'T1') { // XX unknown, T1 Tor
            return [$code, 'ip'];
        }
    }
    $code = ip_country($ip ?? signup_ip());
    if ($code !== null) {
        return [$code, 'ip'];
    }
    $code = time_zone_country($timeZone);
    return $code === null ? [null, null] : [$code, 'timezone'];
}

/**
 * "8.8.8.8" → "US", from bondi/ip-country-v4.bin and -v6.bin (made by dev/update-ip-country.php from the
 * internet registries' public records). null for private or unknown addresses. Reads about 20 records.
 */
function ip_country(string $ip): ?string
{
    $packed = @inet_pton($ip);
    if ($packed === false) {
        return null;
    }
    if (strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10) . "\xff\xff")) {
        $packed = substr($packed, 12); // ::ffff:1.2.3.4 is an IPv4 address
    }
    $width = strlen($packed);
    $file = @fopen(dirname(__DIR__) . '/ip-country-' . ($width === 4 ? 'v4' : 'v6') . '.bin', 'rb');
    if ($file === false) {
        return null;
    }
    $size = 2 * $width + 2;
    [$low, $high, $found] = [0, intdiv(fstat($file)['size'], $size) - 1, null];
    while ($low <= $high) { // the last range that starts at or before the address
        $middle = intdiv($low + $high, 2);
        fseek($file, $middle * $size);
        $record = fread($file, $size);
        if (strcmp(substr($record, 0, $width), $packed) <= 0) {
            [$found, $low] = [$record, $middle + 1];
        } else {
            $high = $middle - 1;
        }
    }
    fclose($file);
    if ($found === null || strcmp($packed, substr($found, $width, $width)) > 0) {
        return null; // falls in a gap between ranges
    }
    return substr($found, 2 * $width, 2);
}

/** "CA" → "Canada" (in English), when the server has PHP's intl extension; otherwise the code itself. */
function country_name(?string $code): string
{
    if ($code === null || $code === '') {
        return '—';
    }
    $name = class_exists('Locale') ? Locale::getDisplayRegion('-' . $code, 'en') : '';
    return $name !== '' && $name !== $code ? $name : $code;
}

/** Sign-ups saved before the IP lists existed get their country from their saved IP address. */
function signups_fill_ip_countries(): void
{
    foreach (all("SELECT id, ip FROM signups WHERE country_source IS NULL OR country_source <> 'ip' LIMIT 1000") as $row) {
        $code = ip_country((string)$row['ip']);
        if ($code !== null) {
            run("UPDATE signups SET country = ?, country_source = 'ip' WHERE id = ?", [$code, $row['id']]);
        }
    }
}

/** "America/Toronto" → "CA", from PHP's own time zone database; null for UTC or an unknown zone. */
function time_zone_country(?string $timeZone): ?string
{
    // Older names some browsers still report (Chrome says Asia/Calcutta), which PHP knows only as links.
    $older = [
        'Asia/Calcutta' => 'IN', 'Asia/Katmandu' => 'NP', 'Asia/Rangoon' => 'MM', 'Asia/Saigon' => 'VN',
        'Asia/Dacca' => 'BD', 'Asia/Thimbu' => 'BT', 'Asia/Ulan_Bator' => 'MN', 'Europe/Kiev' => 'UA',
        'Europe/Uzhgorod' => 'UA', 'Europe/Zaporozhye' => 'UA', 'Atlantic/Faeroe' => 'FO', 'America/Godthab' => 'GL',
        'America/Buenos_Aires' => 'AR', 'America/Catamarca' => 'AR', 'America/Cordoba' => 'AR',
        'America/Jujuy' => 'AR', 'America/Mendoza' => 'AR', 'America/Indianapolis' => 'US',
        'America/Louisville' => 'US', 'America/Coral_Harbour' => 'CA', 'Pacific/Ponape' => 'FM',
        'Pacific/Truk' => 'FM', 'Pacific/Enderbury' => 'KI', 'Asia/Istanbul' => 'TR',
    ];
    if ($timeZone === null || $timeZone === '') {
        return null;
    }
    if (isset($older[$timeZone])) {
        return $older[$timeZone];
    }
    try {
        $code = (new DateTimeZone($timeZone))->getLocation()['country_code'] ?? '??';
    } catch (Throwable) {
        return null;
    }
    return preg_match('/^[A-Z]{2}$/', $code) ? $code : null;
}
