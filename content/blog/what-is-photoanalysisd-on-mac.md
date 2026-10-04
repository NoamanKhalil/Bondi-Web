---
title: What is photoanalysisd on Mac?
description: photoanalysisd analyzes your Photos library for people, places and Memories. Here's why it uses CPU after imports or updates, and how to help it finish.
date: 2026-10-04
topic: processes
status: draft
order: 14
related: mac-slow-after-update, mac-fans-loud, what-is-cloudd-on-mac
---
photoanalysisd is the part of Photos that analyzes your library to find people, places and Memories. It's safe, and when it's busy it's because you've added many photos or turned on iCloud Photos. It prefers to work while your Mac is idle and plugged in.

## Key points

- photoanalysisd analyzes your photo library for Photos: people, places and Memories.
- It gets busy after importing many photos or turning on iCloud Photos.
- It prefers to work while the Mac is idle and plugged in; quitting it doesn't help for long.

## What photoanalysisd does

Faces in the People album, places on the map, and the Memories Photos makes for you all come from photoanalysisd looking through your library on your Mac. A related process, **mediaanalysisd**, analyzes photos and videos for search and Live Text.

Most of the time it's quiet: on the Mac I measure on, photoanalysisd was using **0.0% of the CPU and 52.5 MB of memory**.

## Why photoanalysisd is using CPU

Bondi's note: *After importing many photos or turning on iCloud Photos. It prefers to work while the Mac is idle and plugged in.*

If it stays busy for a long time, look at what else is happening: a big download, an import, an update or a sync is usually the reason, and it settles once that finishes. If your whole Mac feels slow, [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual causes.

## Can I quit photoanalysisd?

Stopping it doesn't help for long: macOS starts it again when it's needed.

## How to check it

Op

Bondi shows the same in plain words: its process guide has notes on 125 common macOS processes, including photoanalysisd, with what each one is using right now.

## Questions

### Is photoanalysisd a virus?

No. photoanalysisd is part of macOS: it analyzes your photo library for Photos (people, places and Memories).

### Why is photoanalysisd using so much CPU?

You've imported many photos, turned on iCloud Photos, or just updated macOS. It prefers to work while the Mac is idle and plugged in, and settles when the analysis is done.

### Can I stop photoanalysisd?

Stopping it doesn't help for long: macOS starts it again when it's needed. Leaving the Mac plugged in and idle helps it finish.
