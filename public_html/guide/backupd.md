# What is backupd on Mac?

> backupd runs Time Machine backups on your Mac. Why it uses CPU during a backup, what backupd-helper is, and how to stop a backup safely.

**Quick answer:** backupd runs Time Machine backups. It's part of macOS and safe; it's busy while a backup runs, longest the first time. To stop a backup, use Skip This Backup in the Time Machine menu.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/backupd/

backupd is the part of macOS that runs **Time Machine** backups. It's safe, and when it's busy a backup is running.

## Key points

- backupd copies your files to your Time Machine disk.
- It's busiest during the first backup, or after many files changed.
- Don't quit it: to stop a backup, choose **Skip This Backup** in the Time Machine menu.

## What backupd does

Every hour or so, Time Machine backs up what changed since the last backup. backupd does the copying; **backupd-helper** is its partner, which starts backups on schedule and prepares the disk. It knows what changed from [fseventsd](https://trybondi.app/guide/fseventsd/), macOS's record of file changes, so it doesn't have to read every file each time.

## Why is backupd using so much CPU?

The first backup copies everything, and that can take hours. After that, backups are small, unless lots of files changed: a big download, a photo import, or a project folder that rebuilds constantly.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, backupd was running (2 copies), but as a system process it can be measured only with administrator permission, which that reading didn't have.

To make backups lighter, leave out folders that don't need a backup (downloads you can fetch again, build folders, virtual machines) in **System Settings → General → Time Machine → Options**.

## Can I quit backupd?

Leave it running: macOS needs it. To stop a backup in progress, open the Time Machine menu in the menu bar and choose **Skip This Backup**.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **backupd** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is backupd a virus?

No. backupd is part of macOS: it runs Time Machine backups.

### What is backupd-helper on Mac?

Time Machine's partner process: it starts backups on schedule and gets the backup disk ready. It's part of macOS too.

### How do I stop backupd?

Don't quit it. Choose Skip This Backup in the Time Machine menu, and the backup stops safely.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
