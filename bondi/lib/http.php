<?php
// Reading requests and writing JSON answers for the app.

function json_out(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(string $error, string $message, int $status = 400, array $extra = []): never
{
    json_out(['error' => $error, 'message' => $message] + $extra, $status);
}

/** The request's JSON body (the app sends JSON), or form fields. */
function input(): array
{
    static $body = null;
    if ($body === null) {
        $raw = file_get_contents('php://input') ?: '';
        $decoded = json_decode($raw, true);
        $body = is_array($decoded) ? $decoded : $_POST;
    }
    return $body;
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        fail('method', 'Use POST.', 405);
    }
}

/** A device ID from the app: a 64-character SHA-256 made on the Mac. */
function device_hash_input(): string
{
    $hash = strtolower(trim((string)(input()['device_hash'] ?? '')));
    if (!preg_match('/^[a-f0-9]{64}$/', $hash)) {
        fail('device', 'Missing or malformed device ID.');
    }
    return $hash;
}

function device_name_input(): ?string
{
    $name = trim((string)(input()['device_name'] ?? ''));
    return $name === '' ? null : mb_substr($name, 0, 100);
}

/** The caller's IP address, hashed, for rate limits (the address itself isn't kept). */
function client_key(): string
{
    return substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|bondi'), 0, 32);
}
