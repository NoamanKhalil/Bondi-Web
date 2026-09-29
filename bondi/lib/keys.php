<?php
// License keys and activation tokens. Keys look like BONDI-7K3QX-M2PZ8-R4WNT-9HCFD: 20 random characters
// from Crockford's alphabet (no I, L, O or U, so they're hard to misread). Only their SHA-256 is stored.

const KEY_ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

function new_license_key(): string
{
    $characters = '';
    foreach (str_split(random_bytes(20)) as $byte) {
        $characters .= KEY_ALPHABET[ord($byte) % 32];   // 256 is a multiple of 32: no bias
    }
    return 'BONDI-' . implode('-', str_split($characters, 5));
}

/**
 * A key as typed or pasted, reduced to its 20 characters: case, spaces, line breaks and dashes don't
 * matter, and the letters people mistake for digits (O, I, L) are read as 0 and 1. Null if it can't be a key.
 */
function normalize_key(string $typed): ?string
{
    $key = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $typed) ?? '');
    if (str_starts_with($key, 'BONDI')) {
        $key = substr($key, 5);
    }
    $key = strtr($key, ['O' => '0', 'I' => '1', 'L' => '1']);
    return preg_match('/^[0-9A-HJKMNP-TV-Z]{20}$/', $key) ? $key : null;
}

function key_hash(string $normalized): string
{
    return hash('sha256', 'BONDI-' . $normalized);
}

function key_last4(string $normalized): string
{
    return substr($normalized, -4);
}

/** A secret for the Mac to keep (activation token or checkout claim), and its hash for the database. */
function new_secret(): array
{
    $secret = bin2hex(random_bytes(32));
    return [$secret, hash('sha256', $secret)];
}
