<?php
// Drafts new pages for Bondi's Mac guide (content/guide/<slug>.md), one per process:
//   php dev/guide-new.php <reading.txt> <process> [<process>…]
// <reading.txt> is a reading of this Mac from Bondi's engine: the Debug build's `Bondi --dump-engine` (see
// video/scripts/refresh-reading.sh for where it lives). Each page starts from the app's own note on the process
// (../TryBondi/Bondi/Resources/ProcessGuide.json), the process's real figures in that reading, and what people search
// about it (Google's suggestions). It's a draft: add what's specific to the process (the searches listed in its header
// say what people want to know), check every fact, then set "status: published". Existing pages are never overwritten.

if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true)) {
    http_response_code(404);
    exit;
}
[$self, $readingFile] = $argv + [null, null];
$names = array_slice($argv, 2);
if (!$readingFile || !is_file($readingFile) || !$names) {
    fwrite(STDERR, "Usage: php dev/guide-new.php <reading.txt> <process> [<process>…]\n");
    exit(1);
}
$root = dirname(__DIR__);
$guide = json_decode((string)file_get_contents("$root/../TryBondi/Bondi/Resources/ProcessGuide.json"), true);
$reading = (string)file_get_contents($readingFile);
$chip = preg_match('/^SYSTEM chip=(.+?) load=/m', $reading, $m) ? $m[1] : 'Apple silicon';
$gb = preg_match('/^MEMORY physical=(\d+)/m', $reading, $m) ? round((int)$m[1] / 1073741824) : null;
$model = trim((string)shell_exec('sysctl -n hw.model 2>/dev/null'));
$kind = str_starts_with($model, 'MacBookPro') ? 'MacBook Pro' : (str_starts_with($model, 'MacBookAir') ? 'MacBook Air' : 'Mac');
$when = date('F j, Y', filemtime($readingFile));
$today = date('Y-m-d');
$quitWords = [
    'never' => 'Leave it running: macOS needs it.',
    'restarts' => "Stopping it doesn't help for long: macOS starts it again when it's needed.",
    'safe' => 'Safe to quit.',
];

foreach ($names as $name) {
    $entry = null;
    foreach ($guide as $e) {
        if (in_array($name, $e['names'], true)) { $entry = $e; break; }
    }
    if ($entry === null) {
        fwrite(STDERR, "No note on \"$name\" in the app's process guide; skipped.\n");
        continue;
    }
    $slug = strtolower(str_replace('_', '-', preg_replace('/[^A-Za-z0-9_.-]/', '', $name)));
    $file = "$root/content/guide/$slug.md";
    if (is_file($file)) {
        echo "content/guide/$slug.md already exists; left as it is.\n";
        continue;
    }

    // Its figures in the reading: every copy running under any of its names
    $copies = $measured = 0;
    $cpu = $mem = 0.0;
    foreach (explode("\n", $reading) as $line) {
        $f = explode("\t", trim($line));
        if (($f[0] ?? '') === 'PROC' && in_array($f[2] ?? '', $entry['names'], true)) {
            $copies++;
            if (preg_match('/cpuCore=([\d.]+)/', $line, $c) && preg_match('/mem=(\d+)/', $line, $b)) {
                $measured++;
                $cpu += (float)$c[1];
                $mem += (int)$b[1];
            }
        }
    }
    $size = fn(float $bytes) => $bytes >= 1073741824 ? number_format($bytes / 1073741824, 2) . ' GB' : number_format($bytes / 1048576, 1) . ' MB';
    $on = "In one reading of a $kind ($chip" . ($gb ? ", $gb GB" : '') . ") on $when, ";
    $readingLine = match (true) {
        $copies === 0 => $on . "$name wasn't running: it starts only when it's needed.",
        $measured === 0 => $on . "$name was running" . ($copies > 1 ? " ($copies copies)" : '') . ', but as a system process it can be measured only with administrator permission, which that reading didn\'t have.',
        default => $on . "$name was using **" . number_format($cpu, 1) . '% of the CPU** and **' . $size($mem) . ' of memory**'
            . ($copies > 1 ? ($measured < $copies ? " (the $measured of its $copies copies that could be measured)" : " across $copies copies") : '') . '.',
    };

    // What people search about it
    $searches = [];
    foreach (["$name mac", "what is $name"] as $q) {
        $out = (string)shell_exec('curl -s --max-time 8 ' . escapeshellarg('https://suggestqueries.google.com/complete/search?client=firefox&hl=en&gl=us&q=' . rawurlencode($q)));
        foreach ((json_decode($out, true)[1] ?? []) as $s) {
            if (stripos($s, $name) !== false && !in_array($s, $searches, true)) { $searches[] = $s; }
        }
        usleep(200000);
    }
    $wants = implode(' ', $searches);
    $busyHeading = stripos($wants, 'memory') !== false ? "Why is $name using so much memory?" : "Why is $name using so much CPU?";

    $what = rtrim($entry['what'], '.') . '.';
    $busy = rtrim($entry['busy'], '.') . '.';
    $quit = trim($quitWords[$entry['quit']] . ' ' . ($entry['note'] ?? ''));
    $also = count($entry['names']) > 1 ? 'Also seen as: ' . implode(', ', array_diff($entry['names'], [$name])) . '.' : '';
    $lowerWhat = lcfirst($what);

    $page = <<<MD
---
title: What is $name on Mac?
name: $name
group: processes
description: $name on Mac: $lowerWhat What it does, why it gets busy, and whether you can quit it.
answer: $name is part of macOS: $lowerWhat $quit
status: draft
reviewed: $today
related:
searches: {$wants}
---
$name is part of macOS. $what $also

## Key points

- $what
- $busy
- $quit

## What $name does

$what

## $busyHeading

$busy

$readingLine

## Can I quit $name?

$quit

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **$name** in the search field at the top right.
3. Look at **% CPU** and **Memory**. Several copies can be normal.

## Questions

### Is $name a virus?

No. $name is part of macOS: $lowerWhat

### $busyHeading

$busy

### Can I quit $name?

$quit

MD;
    file_put_contents($file, $page);
    echo "Drafted content/guide/$slug.md (" . count($searches) . " searches; reading: " . ($measured ? 'measured' : ($copies ? 'running, not measured' : 'not running')) . ")\n";
}
