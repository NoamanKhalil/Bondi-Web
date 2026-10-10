# What is mds_stores on Mac?

> mds_stores is Spotlight's indexer. Why it uses high CPU, memory or disk on your Mac, how long indexing takes, and how to keep folders out of it.

**Quick answer:** mds_stores is Spotlight's indexer: it keeps the index that makes searches instant. It's part of macOS and safe; high CPU, memory or disk use means it's rebuilding the index, which settles on its own, usually within hours.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/mds-stores/

mds_stores is **Spotlight's indexer**: it keeps the index of your files that makes Spotlight searches instant. It's part of macOS and safe. You'll often see it with **mds**, its manager.

## Key points

- mds_stores writes and stores Spotlight's index.
- It's busiest after a macOS update, after adding many files, or when you connect a new disk.
- Leave it running: it finishes on its own. You can keep folders out of the index.

## What mds_stores does

When files change, Spotlight's helpers, [mdworker](https://trybondi.app/guide/mdworker/), read them, and mds_stores adds what they found to the index. It learns which files changed from [fseventsd](https://trybondi.app/guide/fseventsd/).

## Why is mds_stores using so much CPU, memory or disk?

After a macOS update, when you add many files, or when a new disk is connected, it re-indexes. That can take hours, then it settles. While it works, high disk activity is normal: it's reading files and writing the index.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, mds_stores was running (2 copies), but as a system process it can be measured only with administrator permission, which that reading didn't have.

If it never settles, something keeps feeding it new files: a folder that changes constantly, such as logs, build output or a sync folder mid-sync. More in [Is Spotlight indexing slowing down your Mac?](https://trybondi.app/blog/spotlight-indexing-slow-mac/)

## Can I quit mds_stores?

Leave it running: it finishes on its own. To keep folders out of the index, add them to Spotlight's privacy list in **System Settings → Spotlight**.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **mds_stores** in the search field at the top right.
3. Look at **% CPU** and **Memory**. Two copies can be normal.

## Questions

### Is mds_stores a virus?

No. mds_stores is part of macOS: it's Spotlight's indexer.

### Why is mds_stores using so much CPU on my Mac?

Spotlight is rebuilding its index: after an update, many new files or a new disk. It settles on its own, usually within hours.

### How do I stop mds_stores from using so much CPU?

Let it finish. If a folder that changes constantly keeps it busy, add that folder to Spotlight's privacy list in System Settings → Spotlight.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
