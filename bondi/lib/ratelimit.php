<?php
// Counts requests per caller per time window, to stop guessing keys or flooding recovery emails.

/** True when the caller is still within `max` requests per `seconds`; counts this request. */
function within_limit(string $bucket, int $max, int $seconds): bool
{
    $window = gmdate('Y-m-d H:i:s', intdiv(time(), $seconds) * $seconds);
    run('INSERT INTO rate_limits (bucket, window_start, hits) VALUES (?, ?, 1)
         ON DUPLICATE KEY UPDATE hits = hits + 1', [$bucket, $window]);
    $row = one('SELECT hits FROM rate_limits WHERE bucket = ? AND window_start = ?', [$bucket, $window]);
    if (random_int(1, 200) === 1) {
        run('DELETE FROM rate_limits WHERE window_start < ?', [gmdate('Y-m-d H:i:s', time() - 86400)]);
    }
    return (int)($row['hits'] ?? 0) <= $max;
}

function limit_or_fail(string $bucket, int $max, int $seconds): void
{
    if (!within_limit($bucket, $max, $seconds)) {
        fail('rate_limited', 'Too many attempts. Wait a few minutes and try again.', 429);
    }
}
