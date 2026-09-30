<?php
// The MySQL connection and three small helpers. Every query uses placeholders, never pasted values.

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', config('db.host'), config('db.name'));
        $pdo = new PDO($dsn, config('db.user'), config('db.pass'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET time_zone = '+00:00'");
    }
    return $pdo;
}

/** The first row, or null. */
function one(string $sql, array $args = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($args);
    $row = $statement->fetch();
    return $row === false ? null : $row;
}

/** Every row. */
function all(string $sql, array $args = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($args);
    return $statement->fetchAll();
}

/** Runs a change; returns the number of rows it touched. */
function run(string $sql, array $args = []): int
{
    $statement = db()->prepare($sql);
    $statement->execute($args);
    return $statement->rowCount();
}

function now(): string
{
    return gmdate('Y-m-d H:i:s');
}

/** A database time as ISO 8601 for the app: "2026-09-29T14:03:00Z". */
function iso(?string $time): ?string
{
    return $time === null ? null : str_replace(' ', 'T', $time) . 'Z';
}
