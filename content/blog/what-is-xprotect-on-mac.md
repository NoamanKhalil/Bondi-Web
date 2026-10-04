---
title: What is XProtect on Mac?
description: XProtect is Apple's built-in malware protection on your Mac. Here's what XprotectService does, why it sometimes uses CPU, and why you should leave it running.
date: 2026-10-04
status: draft
order: 13
related: what-is-syspolicyd-on-mac, mac-slow-after-update, why-is-my-mac-slow
---
XProtect is Apple's built-in malware protection on every Mac. It checks apps and files against known malware and keeps its rules up to date, quietly, in processes such as XprotectService. When it's busy, it's scanning, usually after a rules update or a new download.

## Key points

- XProtect is Apple's built-in malware protection; its processes include XprotectService.
- It gets busy scanning after a rules update, or checking newly downloaded files.
- Leave it running: macOS needs it, and it's protecting your Mac.

## What XProtect does

XProtect works in the background without a window or settings of its own. It checks apps and files for known malware and updates its rules from Apple automatically, so it stays current without you doing anything. Its processes appear in Activity Monitor under names like **XprotectService**, **XProtectBridgeService** and **XProtectUpdateService**.

## Why XProtect is using CPU

Bondi's note: *Scans after a rules update, or checking newly downloaded files.*

If it stays busy for a long time, look at what else is happening: a big download, an import, an update or a sync is usually the reason, and it settles once that finishes. If your whole Mac feels slow, [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual causes.

## Can I quit XProtect?

Leave it running: macOS needs it.

## How to check it

Op

Bondi shows the same in plain words: its process guide has notes on 125 common macOS processes, including XProtect, with what each one is using right now.

## Questions

### Is XprotectService a virus?

No. It's part of XProtect, Apple's built-in malware protection on every Mac.

### Why is XProtect using CPU?

It's scanning: usually after its rules are updated, or while checking files you've just downloaded. It settles when the scan finishes.

### Can I turn off XProtect?

Leave it running: macOS needs it, and it's what protects your Mac from known malware.
