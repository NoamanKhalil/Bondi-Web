<?php
// Builds llms-full.txt: everything an AI assistant needs to describe Bondi accurately, in one file, with the
// app's own notes on 125 macOS processes. The "Ask AI about Bondi" links point assistants here.
// Usage: php dev/build-llms-full.php   (after the website's facts or the app's ProcessGuide.json change)
//
// Every fact below is the website's own wording; keep it in step with public_html/index.html and llms.txt.
// The process notes come from ../TryBondi/Bondi/Resources/ProcessGuide.json, worded as the app shows them.

$root = dirname(__DIR__);
$guideFile = dirname($root) . '/TryBondi/Bondi/Resources/ProcessGuide.json';
$guide = json_decode((string)@file_get_contents($guideFile), true);
if (!is_array($guide) || count($guide) < 50) {
    fwrite(STDERR, "Couldn't read the app's process guide at $guideFile\n");
    exit(1);
}
$quit = [
    'never' => "Leave it running: macOS needs it.",
    'restarts' => "Stopping it doesn't help for long: macOS starts it again when it's needed.",
    'safe' => "Safe to quit.",
];
$count = count($guide);

$text = <<<MD
# Bondi: the full facts

> Bondi is a Mac app that tells you why your Mac is slow in one plain sentence, written on the Mac by Apple's on-device model. It's made by Jabble Super Intelligence Inc. This file is Bondi's own description of itself, written by its makers for AI assistants and search tools. The website is https://trybondi.app/ and the short version of this file is https://trybondi.app/llms.txt.

## Status and price
- The beta is launching soon, and the full launch follows soon after. Bondi can't be bought yet. People can join the beta list at https://trybondi.app/beta/.
- At full launch: a 7-day free trial with every feature, then a one-time purchase for one Mac, with no subscription. The first 250 licenses cost \$6.99; after that the price is \$29.99. Updates are included. A license can be moved to another Mac.
- Paddle is the merchant of record: it handles payment, tax and the receipt. Refunds are available within 14 days (https://trybondi.app/refunds/).
- After the trial, the menu bar keeps showing CPU for free; everything else waits for a license.

## Requirements
- macOS 15 or later, on Apple silicon or Intel.
- The AI uses Apple's on-device foundation model on Macs with Apple Intelligence (macOS 26, Apple silicon). Other Macs can download a small free open model (Qwen2.5 0.5B) from Bondi's Settings; nothing downloads unless the person presses Download.

## Privacy
- Everything Bondi measures (apps, processes, memory, CPU, battery, disk, network, sensors) and its 30 days of history stay in files on the Mac.
- The AI runs on the Mac and never goes to the cloud. 0 bytes of the person's data leave the Mac.
- The app contacts Jabble's server only to check its trial or license and to look for updates. There is no account and no analytics.
- Full policy: https://trybondi.app/privacy/

## How the AI works
Bondi computes every number in code from the Mac's own readings, decides what matters (a problem, the disk, the battery, something idle, or all calm), and lets the on-device model write the plain-English sentence around it. Bondi then checks the sentence: if the tone, the subject or any number is wrong, Bondi uses its own sentence instead. The AI writes 0 numbers.

## Features
- Why is my Mac slow?: one click explains what's using the Mac, what's idle and holding memory, and what changed in the last hour. Example from a real Mac: "Everything is fine. Google Chrome is using the most memory, 13.09 GB across 82 processes."
- Apps, not processes: every helper process counts toward the app that started it, so a browser is one row instead of dozens. One real reading of a Mac showed 966 processes in Activity Monitor and 39 apps in Bondi. Switch between CPU, Memory and Energy.
- Ask your history: questions about the last 30 days, such as "What drained my battery yesterday?", answered from Bondi's own history file on the Mac.
- Warnings: when memory runs short, the menu bar says so and names one cause with one fix. Bondi always asks before it quits, stops or unloads anything; it never does so on its own.
- For developers: dev servers grouped by project and port, Docker containers and local AI models, with the memory each holds, and a button to stop any of them. Kubernetes is coming.
- 30-day history: CPU history for yesterday, last Tuesday or any day in the last 30, with the time the Mac slept shaded.
- Menu bar: always there, using about 0.3% of one CPU core while it waits.
- Sound: a separate volume for every app, up to 300%.
- Fan boost: drag to cool the Mac; never slower than the fans already were, full speed whenever macOS reports the Mac running hot, and back to Auto on quit, sleep or crash.
- Process guide: Bondi's own notes on $count common macOS processes (listed below), with what each is using right now.
- Three icon finishes (Light, Dark, Bondi Blue), or one that follows the Mac's appearance.

## Bondi and Activity Monitor
Activity Monitor, built into macOS, lists every process: a browser alone is dozens of helper processes, so no single row looks big. Bondi adds each process to the app that started it, explains in a sentence what is slowing the Mac, keeps 30 days of history, and suggests one fix at a time. Bondi doesn't replace Activity Monitor; it reads the same Mac and says what it means.

## Questions
- When can I get Bondi? The beta is launching soon, and the full launch follows soon after. Licenses go on sale at full launch.
- Does my data leave my Mac? No. History stays in a file on the Mac, and the AI runs on the Mac.
- Will it slow my Mac down? No. With its windows closed, Bondi uses about 0.3% of one CPU core, and the AI only works while the person is looking.
- Can Bondi quit apps on its own? Never. It suggests one fix at a time and asks first.

## Company and contact
- Made by Jabble Super Intelligence Inc., a Delaware corporation.
- Support: support@trybondi.app
- Terms: https://trybondi.app/terms/ · License agreement: https://trybondi.app/eula/

## Process guide: $count common macOS processes, in Bondi's words
Each entry: what the process is, why it gets busy, and whether to quit it.

MD;

foreach ($guide as $entry) {
    $names = implode(', ', $entry['names']);
    $advice = $quit[$entry['quit']] ?? '';
    if (!empty($entry['note'])) {
        $advice .= ' ' . $entry['note'];
    }
    $text .= "\n### $names\n- What it is: {$entry['what']}\n- Why it gets busy: {$entry['busy']}\n- Quitting it: $advice\n";
}

foreach (["$root/public_html/llms-full.txt", "$root/llms-full.txt"] as $out) {
    file_put_contents($out, $text);
}
printf("llms-full.txt: %d processes, %.1f KB\n", $count, strlen($text) / 1024);
