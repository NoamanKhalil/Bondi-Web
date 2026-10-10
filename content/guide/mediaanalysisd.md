---
title: What is mediaanalysisd on Mac?
name: mediaanalysisd
group: processes
description: mediaanalysisd analyzes photos and videos on your Mac for search, Live Text and Visual Look Up. Why it uses CPU, memory and storage, and what to leave alone.
answer: mediaanalysisd analyzes photos and videos on your Mac, for example to find text and objects for search and Live Text. It's part of macOS and safe; it's busy after you add many photos or videos, and it works on the Mac, not in the cloud.
status: published
reviewed: 2026-10-10
related: photoanalysisd, mdworker, cloudd
searches: mediaanalysisd mac mediaanalysisd macos mediaanalysisd macbook mediaanalysisd mac reddit mediaanalysisd mac storage mediaanalysisd macos 27 mediaanalysisd mac activity monitor mediaanalysisd macos 26 mediaanalysisd mac delete macos mediaanalysisd high cpu what is mediaanalysisd what is mediaanalysisd mac mediaanalysisd-access what is mediaanalysisd process what is mediaanalysisd access on mac what is mediaanalysisd on macbook what does mediaanalysisd do what does mediaanalysisd do on mac what is apple mediaanalysisd what is com apple mediaanalysisd cache
---
mediaanalysisd analyzes **photos and videos** on your Mac, for example to find text and objects for search, **Live Text** and **Visual Look Up**. It's part of macOS and safe.

## Key points

- mediaanalysisd finds text, objects and scenes in your photos and videos.
- It gets busy after you add many photos or videos.
- Quitting it doesn't help for long: macOS starts it again.

## What mediaanalysisd does

Searching Photos for "beach" or "receipt", copying text out of a picture, or identifying a plant with Visual Look Up all use what mediaanalysisd found. The analysis happens on your Mac. It works alongside [photoanalysisd](/guide/photoanalysisd/), which finds people, places and Memories.

## Why is mediaanalysisd using so much CPU?

After adding many photos or videos. Like photoanalysisd, it does most of its work while the Mac is idle, and a big library can take days on a laptop that's often on battery.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, mediaanalysisd was using **0.0% of the CPU** and **100.8 MB of memory**.

## Does mediaanalysisd use storage?

It keeps the results of its analysis in a cache on your Mac. Don't delete its folders by hand: leave the Mac plugged in and idle so it can finish, and restart if it seems stuck.

## Can I quit mediaanalysisd?

Stopping it doesn't help for long: macOS starts it again when it's needed.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **mediaanalysisd** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is mediaanalysisd a virus?

No. mediaanalysisd is part of macOS: it analyzes photos and videos for search and Live Text.

### Why is mediaanalysisd using so much CPU?

You've added many photos or videos, or just updated macOS. It settles when the analysis is done.

### Can I delete mediaanalysisd's files?

Don't delete them by hand. Let it finish its analysis; restarting the Mac helps if it seems stuck.
