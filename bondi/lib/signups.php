<?php
// The website's "Sign up for updates" list: name, email, IP address and country.

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
 * The visitor's country as [code, source]: from the IP when the host or CDN adds a country header
 * (Cloudflare's CF-IPCountry, Apache's GeoIP module), otherwise from the browser's time zone.
 * [null, null] when neither says. No outside service is asked.
 */
function signup_country(?string $timeZone): array
{
    $headers = (array)config('country_headers', ['HTTP_CF_IPCOUNTRY', 'GEOIP_COUNTRY_CODE', 'HTTP_X_COUNTRY_CODE']);
    foreach ($headers as $header) {
        $code = strtoupper(trim((string)($_SERVER[$header] ?? '')));
        if (preg_match('/^[A-Z]{2}$/', $code) && $code !== 'XX' && $code !== 'T1') { // XX unknown, T1 Tor
            return [$code, 'ip'];
        }
    }
    $code = time_zone_country($timeZone);
    return $code === null ? [null, null] : [$code, 'timezone'];
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
