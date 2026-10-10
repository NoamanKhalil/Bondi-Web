---
title: What is backupd on Mac?
description: backupd runs Time Machine backups on your Mac. Here's why it gets busy, how long a backup takes, and how to stop one safely.
answer: backupd runs Time Machine backups. It's part of macOS and safe to leave alone; when it's busy, a backup is running, and the first backup takes the longest.
date: 2026-10-04
topic: processes
status: draft
order: 17
related: mac-fans-loud, why-is-my-mac-slow, what-is-mds-stores-on-mac
---
backupd runs Time Machine backups on your Mac. It's part of macOS and safe; when it's busy, a backup is running, and the first backup, or one after many files changed, takes the longest.

## Key points

- backupd runs Time Machine backups (with backupd-helper).
- It's busiest during the first backup or after many files changed.
- Don't quit it: to stop a backup, use Skip This Backup in the Time Machine menu.

## What backupd does

Time Machine keeps hourly, daily and weekly copies of your files on a backup disk. backupd does the copying, with **backupd-helper**. After the first full backup, later ones only copy what changed, so they're usually quick.

## Why backupd is using CPU

Bondi's note: *During a backup, especially the first one or after many files changed.*

If it stays busy for a long time, look at what else is happening: a big download, an import, an update or a sync is usually the reason, and it settles once that finishes. If your whole Mac feels slow, [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual causes.

## Can I quit backupd?

Leave it running: macOS needs it. To stop a backup, use Skip This Backup in the Time Machine menu instead.

## How to check it

Op

Bondi shows the same in plain words: its process guide has notes on 125 common macOS processes, including backupd, with what each one is using right now.

## Questions

### Is backupd a virus?

No. backupd is part of macOS: it runs Time Machine backups.

### Why is backupd using so much CPU?

A backup is running. The first backup, or one after many files changed, takes longest; later backups only copy what changed.

### How do I stop backupd?

Don't quit it. To stop a backup, use Skip This Backup in the Time Machine menu.
