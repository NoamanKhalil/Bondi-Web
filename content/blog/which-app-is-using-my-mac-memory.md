---
title: Which app is using my Mac's memory?
description: Apps split themselves into dozens of helper processes, so Activity Monitor rarely shows the real total. Here's how to see what each app is really using.
date: 2026-10-03
status: draft
order: 2
image: /assets/features/busiest.png
related: memory-pressure-explained, bondi-vs-activity-monitor, why-is-my-mac-slow
---
To see which app is really using your Mac's memory, you need to add up all of its processes, not look at the biggest row. Browsers and code editors split themselves into dozens of helper processes, so the real total is usually hidden.

## Why the biggest row isn't the answer

On the Mac I measure on, Activity Monitor's Memory tab listed **966 processes**. The top rows looked like this: a handful of "Google Chrome Helper (Renderer)" processes at around 370 to 640 MB each, Finder at 495 MB, Notes at 436 MB, and some "Code Helper" processes.

Nothing over a gigabyte. Nothing alarming.

Added up by app, the picture was completely different:

| App | Processes | Memory |
| --- | --- | --- |
| Google Chrome | 80 | 12.23 GB |
| macOS | 745 | 8.85 GB |
| Visual Studio Code | 44 | 4.24 GB |
| Safari | 16 | 1.42 GB |
| Notes | 9 | 526.7 MB |
| Finder | 3 | 499.9 MB |

*One real reading of a MacBook Pro (M1 Max, 64 GB), grouped by Bondi.*

Chrome was using more memory than all of macOS, spread across 80 processes so no single row stood out.

## Why apps do this

Browsers run each tab, extension and site in its own process, so one misbehaving page can't crash the rest. Code editors do the same for extensions and language tools. It's good for stability, and bad for anyone trying to read Activity Monitor.

## How to add it up in Activity Monitor

1. Open Activity Monitor and choose the **Memory** tab.
2. Choose **View → All Processes, Hierarchically**. Helper processes now sit under the app that started them.
3. Expand the app and add up its rows. There's no total, so it's a little arithmetic.

Typing an app's name in the search field also filters to its processes, which helps for browsers whose helpers share its name.

## Does it matter?

Only if memory pressure is high. If the **Memory Pressure** graph is green, a big app is fine. It's using memory macOS has spare. If it's yellow or red, the app at the top of the grouped list is your best lever: close tabs, quit it, or restart it. More in [Memory pressure on Mac, explained](/blog/memory-pressure-explained/).

## How Bondi shows it

Bondi does the grouping for you: every helper counts toward the app that started it, so Chrome is one row. Switch between CPU, memory and energy to see who's busiest by each.

![Bondi's Busiest Right Now list, by memory.](/assets/features/busiest.png)
*Bondi's busiest apps, each a single row with all its helpers included.*
