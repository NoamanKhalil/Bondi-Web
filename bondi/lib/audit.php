<?php
// The admin's activity record (sql/007_audit_log.sql): sign-ins, failed sign-ins and every change made there.
// Never an email address or a password; license and sign-up numbers only. Kept a year.

/** True once sql/007_audit_log.sql has been imported. */
function audit_ready(): bool
{
    static $ready = null;
    return $ready ??= one("SHOW TABLES LIKE 'audit_log'") !== null;
}

/** Records one admin event with the IP address and country it came from. Does nothing before sql/007. */
function audit(string $event, string $detail = ''): void
{
    if (!audit_ready()) {
        return;
    }
    $ip = signup_ip();
    run('INSERT INTO audit_log (event, detail, ip, country) VALUES (?, ?, ?, ?)',
        [$event, $detail === '' ? null : mb_substr($detail, 0, 300), $ip === '' ? null : $ip, $ip === '' ? null : ip_country($ip)]);
    run('DELETE FROM audit_log WHERE created_at < NOW() - INTERVAL 1 YEAR');
}

/** The latest events, newest first. */
function audit_recent(int $limit = 100): array
{
    return audit_ready() ? all('SELECT * FROM audit_log ORDER BY id DESC LIMIT ' . max(1, min($limit, 500))) : [];
}
