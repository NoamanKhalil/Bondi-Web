---
title: What is fseventsd on Mac?
name: fseventsd
group: processes
description: fseventsd keeps a record of file changes for Time Machine, Spotlight and other apps. Why fseventsd uses CPU or memory during builds, downloads and unpacking.
answer: fseventsd keeps a record of which files changed, so Time Machine, Spotlight and other apps know what to look at. It's part of macOS and safe; it's busy when many files change at once, like builds, downloads or unpacking archives.
status: draft
reviewed: 2026-10-10
related: mds-stores, mdworker, backupd
searches: fseventsd mac fseventsd macos fseventsd mac cpu fseventsd macbook fseventsd mac process fseventsd mac activity monitor macos fseventsd high cpu macos fseventsd high memory fseventsd uuid mac mac fseventsd と は what is fseventsd on mac what is fseventsd what is fseventsd-uuid what is .fseventsd folder on usb what is .fseventsd folder what is fseventsd process on mac what is .fseventsd file what is fseventsd on my usb what is fseventsd on mac activity monitor what is .fseventsd and .spotlight-v100
---
fseventsd keeps a **record of file changes** on each disk, so apps like Time Machine and Spotlight know what changed without checking every file. It's part of macOS and safe.

## Key points

- fseventsd records which folders changed, and when.
- It gets busy with many file changes at once, for example builds, downloads or unpacking archives.
- Leave it running: Time Machine, Spotlight and many apps rely on it.

## What fseventsd does

Instead of every app scanning your disk for changes, macOS keeps one log of them. [backupd](/guide/backupd/) uses it to back up only what changed, [mds_stores](/guide/mds-stores/) to index only what's new, and sync apps and developer tools to react when files change.

## Why is fseventsd using so much CPU?

Many file changes at once, for example builds, downloads or unpacking archives. Developer tools that write thousands of small files, such as installing packages into a project, keep it especially busy.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, fseventsd was running, but as a system process it can be measured only with administrator permission, which that reading didn't have.

## Can I quit fseventsd?

Leave it running: macOS needs it.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **fseventsd** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is fseventsd a virus?

No. fseventsd is part of macOS: it keeps a record of file changes.

### Why is fseventsd using high CPU?

Many files are changing at once: a build, a download, unpacking an archive or installing packages. It settles when the changes stop.

### Can I disable fseventsd?

No. Time Machine, Spotlight and many apps depend on its record of changes.
