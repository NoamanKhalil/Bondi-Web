---
title: What is mdworker (mdworker_shared) on Mac?
name: mdworker
group: processes
description: mdworker and mdworker_shared are Spotlight's helpers that read your files. Why there are many copies, why they use high CPU, and when they settle.
answer: mdworker and mdworker_shared are Spotlight's helpers: they read the contents of new and changed files so Spotlight can find them. They're part of macOS and safe; many copies and high CPU after an update or a big download are normal.
status: published
reviewed: 2026-10-10
related: mds-stores, fseventsd, mediaanalysisd
article: spotlight-indexing-slow-mac
searches: mdworker mac mdworker mac high cpu mdworker macos mdworker macbook mdworker mac cpu mac mdworker_shared macos mdworker_shared macos mdworker_shared high cpu mac mdworker high cpu usage macbook mdworker_shared what is mdworker_shared what is mdworker on mac what is mdworker_shared on mac what is mdworker what is mdworker shared on mac what is mdworker_shared in activity monitor what is mdworker on mac activity monitor what is mdworker activity monitor what is mdworker_shared process on mac what is mdworker_shared on mac activity monitor
---
mdworker, and **mdworker_shared** in recent versions of macOS, are **Spotlight's helpers**: they read the contents of your files so Spotlight can find them. They're safe. You may also see **mdbulkimport**.

## Key points

- mdworker processes read new and changed files for Spotlight.
- They get busy with many new or changed files, often after an update or a big download.
- Many copies at once are normal; quitting them doesn't help for long.

## What mdworker does

When a file is added or changed, macOS hands it to an mdworker, which reads its text, dates, authors and other details. [mds_stores](/guide/mds-stores/) then adds what it found to Spotlight's index. Each mdworker handles one kind of file at a time, which is why there are often several.

## Why is mdworker using so much CPU?

Many new or changed files, often after an update or a big download. Unpacking an archive, copying a folder of documents, or a build that writes thousands of files all give them work.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, mdworker was using **0.0% of the CPU** and **10.4 MB of memory** across 2 copies.

It settles on its own. More in [Is Spotlight indexing slowing down your Mac?](/blog/spotlight-indexing-slow-mac/)

## Can I quit mdworker?

Stopping it doesn't help for long: macOS starts it again when it's needed.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **mdworker** in the search field at the top right.
3. Look at **% CPU** and **Memory**. Many copies of mdworker_shared are normal.

## Questions

### Is mdworker_shared a virus?

No. mdworker_shared is part of macOS: it's one of Spotlight's helpers that read your files.

### Why are there so many mdworker_shared processes?

Each handles one kind of file at a time, so macOS runs several at once. They close when there's nothing left to read.

### Why is mdworker using so much CPU?

Many files are new or changed, often after an update or a big download. It settles once Spotlight has caught up.
