---
title: What is fileproviderd on Mac?
name: fileproviderd
group: processes
description: fileproviderd connects iCloud Drive, OneDrive, Dropbox and other cloud storage to Finder. Why it uses high CPU or memory, especially while OneDrive syncs.
answer: fileproviderd connects cloud storage (iCloud Drive, OneDrive, Dropbox and others) to Finder. It's part of macOS and safe; high CPU or memory means a big sync, often the first one after signing in to a cloud app like OneDrive.
status: published
reviewed: 2026-10-10
related: cloudd, fseventsd, mdworker
searches: fileproviderd mac fileproviderd mac high memory fileproviderd mac high cpu onedrive fileproviderd mac os fileproviderd macbook fileproviderd process mac macos fileproviderd high cpu usage macos fileproviderd cpu fileproviderd onedrive mac mac fileproviderd と は what is fileproviderd on macos what is fileproviderd on my mac what is fileproviderd process what is fileproviderd on macbook what does fileproviderd do
---
fileproviderd connects **cloud storage** to Finder: iCloud Drive, and apps such as OneDrive, Dropbox and Google Drive that use macOS's built-in way of showing cloud files. It's part of macOS and safe.

## Key points

- fileproviderd shows cloud files in Finder and downloads them when you open them.
- It gets busy syncing many files, or during the first sync after signing in.
- Quitting it doesn't help for long: macOS starts it again.

## What fileproviderd does

Cloud apps can keep files online until you need them. fileproviderd is what lists those files in Finder, downloads one when you open it, and uploads your changes, working with the cloud app's own sync process.

## Why is fileproviderd using so much CPU or memory?

Syncing many files, or the first sync after signing in. With **OneDrive**, high CPU from fileproviderd is common right after setting it up or adding a big folder: every file passes through it.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, fileproviderd was using **0.0% of the CPU** and **16.1 MB of memory**.

To quiet it, pause syncing in the cloud app (OneDrive and Dropbox have a Pause option in their menu bar icon) and let it finish later, for example overnight.

## Can I quit fileproviderd?

Stopping it doesn't help for long: macOS starts it again when it's needed.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **fileproviderd** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is fileproviderd a virus?

No. fileproviderd is part of macOS: it connects iCloud Drive and other cloud storage to Finder.

### Why is fileproviderd using high CPU with OneDrive?

OneDrive syncs through it, so a first sync or a big folder keeps it busy. Pausing OneDrive's sync quiets it until you resume.

### Why is fileproviderd using so much memory?

It's handling many files at once during a sync. It settles when the sync finishes.
