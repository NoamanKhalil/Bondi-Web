---
title: What is photoanalysisd on Mac?
name: photoanalysisd
group: processes
description: photoanalysisd analyzes your Photos library for people, places and Memories. Why it uses CPU after imports, and whether you can disable or stop it.
answer: photoanalysisd analyzes your Photos library for people, places and Memories. It's part of macOS and safe; it's busy after you add many photos or turn on iCloud Photos, and it works mostly while your Mac is idle and plugged in.
status: published
reviewed: 2026-10-10
related: mediaanalysisd, cloudd, mds-stores
searches: photoanalysisd mac photoanalysisd mac disable photoanalysisd macos photoanalysisd mac process photoanalysisd macbook mac photoanalysisd cpu photoanalysisd mac activity monitor macos photoanalysisd high cpu mac photoanalysisd stop macos photoanalysisd process what is photoanalysisd what is photoanalysisd on mac what is photoanalysisd in activity monitor what is photoanalysisd on mac activity monitor what is photoanalysisd on macbook what does photoanalysisd do what is the photoanalysisd process on mac macos what is photoanalysisd what is com apple photoanalysisd
---
photoanalysisd is the part of **Photos** that analyzes your library to find people, places and Memories. It's safe, and when it's busy it's because you've added many photos or turned on iCloud Photos.

## Key points

- photoanalysisd finds people, places and Memories in your photo library.
- It gets busy after importing many photos or turning on iCloud Photos.
- It prefers to work while the Mac is idle and plugged in; quitting it doesn't help for long.

## What photoanalysisd does

Faces in the People album, places on the map, and the Memories Photos makes for you all come from photoanalysisd looking through your library on your Mac. Nothing is sent anywhere for this. A related process, [mediaanalysisd](/guide/mediaanalysisd/), analyzes photos and videos for search and Live Text.

## Why is photoanalysisd using so much CPU?

After importing many photos or turning on iCloud Photos. It prefers to work while the Mac is idle and plugged in, so on a laptop it can take days to get through a big library on battery.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, photoanalysisd was using **0.0% of the CPU** and **46.0 MB of memory**.

To help it finish, leave the Mac plugged in, awake and idle for a while, for example overnight.

## Can I quit or disable photoanalysisd?

Stopping it doesn't help for long: macOS starts it again when it's needed. There's no switch to turn it off; it stops on its own when the library is done.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **photoanalysisd** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is photoanalysisd a virus?

No. photoanalysisd is part of macOS: it analyzes your photo library for Photos.

### Can I disable photoanalysisd?

No. There's no setting to turn it off, and quitting it only pauses it. Leaving the Mac plugged in and idle helps it finish sooner.

### Why is photoanalysisd using so much CPU?

You've imported many photos, turned on iCloud Photos, or just updated macOS. It settles when the analysis is done.
