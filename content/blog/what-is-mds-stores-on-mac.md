---
title: What is mds_stores on Mac?
description: mds_stores is Spotlight's indexer on your Mac. Here's what it does, why it uses CPU after updates or new files, and why you should let it finish.
answer: mds_stores is Spotlight's indexer. It's part of macOS and safe; when it's busy, it's building or rebuilding the search index, which settles on its own.
date: 2026-10-04
topic: processes
status: draft
order: 11
related: spotlight-indexing-slow-mac, mac-slow-after-update, mac-fans-loud
---
mds_stores is Spotlight's indexer: it keeps the index of your files that makes Spotlight searches instant. It's part of macOS, it's safe, and when it's busy it's because it's (re)building that index, which settles on its own.

## Key points

- mds_stores is part of macOS: Spotlight's indexer, together with mds.
- It gets busy after a macOS update, when you add many files, or when you connect a new disk.
- Leave it running: it finishes on its own. You can keep folders out in System Settings → Spotlight.

## What mds_stores does

Every time you search with Spotlight (Command-Space), the results come from an index rather than a search of your whole disk. mds and mds_stores keep that index up to date, with help from **mdworker** processes that read the contents of your files.

On an ordinary day it's almost idle: on the Mac I measure on, mds_stores was using **0.0% of the CPU and 105.3 MB of memory**.

## Why mds_stores is using CPU

Bondi's note: *After a macOS update, when you add many files, or when a new disk is connected, it re-indexes. That can take hours, then it settles.*

If it stays busy for a long time, look at what else is happening: a big download, an import, an update or a sync is usually the reason, and it settles once that finishes. If your whole Mac feels slow, [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual causes.

## Can I quit mds_stores?

Leave it running: macOS needs it. Leave it running: it finishes on its own.

## How to check it

Op

Bondi shows the same in plain words: its process guide has notes on 125 common macOS processes, including mds_stores, with what each one is using right now.

## Questions

### Is mds_stores a virus?

No. mds_stores is part of macOS: Spotlight's indexer, which keeps the index that makes searches instant.

### Why is mds_stores using so much CPU?

It's rebuilding Spotlight's index, usually after a macOS update, after adding many files, or when a new disk is connected. It settles on its own, often within a few hours.

### Can I stop mds_stores?

Leave it running: macOS needs it, and it finishes on its own. To stop it indexing a folder, add the folder to Spotlight's privacy list in System Settings → Spotlight.
