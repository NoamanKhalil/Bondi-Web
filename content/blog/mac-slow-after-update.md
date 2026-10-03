---
title: Why is my Mac slow after a macOS update?
description: Mac slow after a macOS Sequoia or Tahoe update? It's re-indexing Spotlight, re-scanning photos and syncing iCloud. Here's what's running and how long it lasts.
date: 2026-10-03
status: published
order: 7
image: /assets/features/guide.png
related: spotlight-indexing-slow-mac, mac-fans-loud, why-is-my-mac-slow
---
A Mac is often slow for a day or so after a macOS update (to macOS Sequoia, Tahoe or any other version) because macOS is catching up in the background: rebuilding the Spotlight search index, re-analyzing your photos, re-checking apps and syncing with iCloud. It settles on its own, usually within hours, and faster if you leave the Mac plugged in and awake.

## What's running after a macOS update

Open Activity Monitor (Command-Space, then type "Activity Monitor"), choose the **CPU** tab and sort by **% CPU**. After an update you'll often see these near the top:

| Process | What it's doing | Can you stop it? |
| --- | --- | --- |
| **mds_stores**, **mds** | Spotlight's indexer, rebuilding the index of your files so searches are instant. | Leave it running: it finishes on its own. |
| **mdworker**, **mdworker_shared** | Spotlight helpers reading the contents of your files. | Stopping them doesn't help for long: macOS starts them again. |
| **photoanalysisd** | Photos analyzing your library for people, places and Memories. It prefers to work while the Mac is idle and plugged in. | It restarts when needed. |
| **mediaanalysisd** | Analyzing photos and videos for search and Live Text. | It restarts when needed. |
| **cloudd**, **bird** | iCloud syncing, especially after signing in again or turning on a feature. | They restart when needed. |
| **syspolicyd**, **XProtect** | Gatekeeper and Apple's malware protection checking apps and applying new rules. | Leave them running: macOS needs them. |
| **softwareupdated** | Finishing or preparing an update. | Leave it running. |

*These descriptions are Bondi's own process notes, which cover 125 common macOS processes.*

## How long it takes

It depends on how many files and photos you have. A Mac with a large photo library and a full disk can take a day; a lightly used Mac, an hour or two. The signs it's done: fans quiet, the processes above back near zero, and Spotlight searches answering instantly.

## How to help it finish

1. **Plug in and leave it awake.** Photos analysis in particular waits for the Mac to be idle and on power.
2. **Don't fight it.** Force-quitting these processes just makes macOS start over.
3. **Give it room.** If your disk is nearly full, free some space; indexing and updates need it.
4. **Restart once** after the update has fully installed, if things still feel stuck the next day.

## When it's not the update

If it's still slow after a day or two, the update probably isn't the cause. Check memory pressure and which app is busiest: [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual suspects.

## How Bondi helps

Bondi's process guide explains each of these processes in plain words and shows what it's using right now, so you can see it's Spotlight or Photos catching up rather than an app gone wrong.
