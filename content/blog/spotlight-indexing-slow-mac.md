---
title: Is Spotlight indexing slowing down your Mac?
description: When mds_stores and mdworker are busy, Spotlight is rebuilding its index. Here's how to tell, how long it lasts, and how to keep folders out of it.
date: 2026-10-03
status: draft
order: 8
image: /assets/features/guide.png
related: mac-slow-after-update, mac-fans-loud, why-is-my-mac-slow
---
If **mds_stores**, **mds** or **mdworker** are near the top of Activity Monitor, Spotlight is indexing: reading your files so searches can be instant. It's busiest after a macOS update, after adding lots of files, or when you connect a new disk, and it settles on its own, usually within a few hours.

## What each process does

- **mds** and **mds_stores**: Spotlight's indexer. They keep the index of your files.
- **mdworker** and **mdworker_shared**: helpers that open your files and read their contents for the index.
- **corespotlightd**: keeps the index for content inside apps, such as messages, mail and notes.

Normally they're quiet. On the Mac I measure on, mds_stores was using **0.0% of the CPU and 105.3 MB of memory** on an ordinary day. It's only when there's a lot to (re)index that they show up.

## How to tell Spotlight is indexing (mds_stores high CPU)

- **mds_stores** or several **mdworker** processes high in Activity Monitor's **CPU** tab.
- Fans louder than usual, with nothing else busy.
- Spotlight searches that miss files you know exist, or show a note that indexing is in progress.

## How long it lasts

From minutes to a few hours, depending on how many files you have and how fast the disk is. A first index of a large external drive can take longer. It's worth letting it finish: a complete index makes every search instant afterwards.

## How to help

1. **Leave it running.** Force-quitting just restarts the work later.
2. **Keep the Mac plugged in and awake** until it's done.
3. **Keep folders out of the index** if they don't need searching: big build folders, caches, virtual machines, or backups. You can leave folders out in **System Settings → Spotlight** (look for the privacy option there; its exact name varies by macOS version).

## When Spotlight indexing is stuck

If Spotlight is still busy after a day, something keeps giving it new files: a folder that changes constantly (logs, builds, a sync folder mid-sync) is the usual cause. Adding that folder to Spotlight's privacy list stops the churn.

## How Bondi helps

Bondi's process guide explains each of these processes and what it's using right now, so when Spotlight is the reason your Mac is busy, you can see it and know to wait rather than worry.
