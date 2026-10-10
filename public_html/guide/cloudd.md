# What is cloudd on Mac?

> cloudd syncs your data with iCloud for Apple's apps and iCloud Drive. Why cloudd uses high CPU on a Mac, and what to do when a sync seems stuck.

**Quick answer:** cloudd syncs your data with iCloud for Apple's apps and iCloud Drive. It's part of macOS and safe; high CPU means a large sync, such as after signing in to iCloud, and it settles when the sync finishes.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/cloudd/

cloudd is the part of macOS that keeps your data in step with **iCloud**: for Apple's apps (like Notes, Reminders and Safari) and for iCloud Drive. It's safe, and when it's busy it's syncing.

## Key points

- cloudd syncs app data and iCloud Drive with iCloud.
- High CPU means a large sync: after signing in, turning on a new iCloud feature, or adding many files.
- Quitting it doesn't help: macOS starts it again.

## What cloudd does

When you change a note on your iPhone and it appears on your Mac, cloudd fetched it. It works with [fileproviderd](https://trybondi.app/guide/fileproviderd/), which shows iCloud Drive's files in Finder, and with Apple's push service, [apsd](https://trybondi.app/guide/apsd/), which tells your Mac when something new is waiting.

## Why is cloudd using so much CPU?

Large syncs, such as after signing in or turning on a new iCloud feature. Turning on iCloud Drive's **Desktop & Documents Folders**, or moving many files into iCloud Drive, keeps it busy until everything has uploaded.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, cloudd was using **0.0% of the CPU** and **24.2 MB of memory** (the 1 of its 2 copies that could be measured).

If it stays busy for a day, check iCloud Drive in Finder's sidebar: a progress circle shows a sync still running. A restart often unsticks a sync that has stopped moving.

## Can I quit cloudd?

Stopping it doesn't help for long: macOS starts it again when it's needed.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **cloudd** in the search field at the top right.
3. Look at **% CPU** and **Memory**. Two copies can be normal.

## Questions

### Is cloudd a virus?

No. cloudd is part of macOS: it syncs your data with iCloud.

### Why is cloudd using so much CPU on my Mac?

It's syncing a lot: after signing in to iCloud, turning on a new iCloud feature, or adding many files to iCloud Drive. It settles when the sync finishes.

### Can I stop cloudd?

Not for long: macOS starts it again. There's no pause button for iCloud syncing; letting it finish, or restarting if it stops moving, is the usual answer.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
