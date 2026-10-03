---
title: Why are my Mac's fans so loud?
description: Loud fans mean your Mac's chip is working hard or running hot. Here's how to find what's heating it, and what to do when the fans won't settle.
date: 2026-10-03
status: draft
order: 4
image: /assets/features/fans.png
related: kernel-task-high-cpu, why-is-my-mac-slow, mac-slow-after-update
---
Your Mac's fans get loud for one reason: the chip is producing more heat than the Mac can lose quietly. Find what's making it work hard, give it some air, and the fans settle within minutes.

## First: is something working hard?

Open Activity Monitor (Command-Space, then type "Activity Monitor"), choose the **CPU** tab and sort by **% CPU**. Look at the top of the list:

- **An app you're using**, like a video export, a build or a game: the fans are doing their job. They'll calm down when it finishes.
- **An app you're not using**: often a browser tab or a helper process stuck at high CPU. Quit it, or close the tab.
- **kernel_task near the top**: the Mac is already hot and slowing apps to cool down. See [kernel_task using lots of CPU?](/blog/kernel-task-high-cpu/)
- **mds_stores, mdworker or photoanalysisd**: Spotlight or Photos catching up after an update or import. It finishes on its own. See [Is Spotlight indexing slowing down your Mac?](/blog/spotlight-indexing-slow-mac/)

Also check the **GPU** (in Activity Monitor: **Window → GPU History**). Games, video and some web pages heat the graphics side more than the CPU.

## Loud fans with nothing open

This is the most frustrating version. The usual causes:

1. **Background indexing** after a macOS update or a big download (Spotlight, Photos, iCloud).
2. **A process left running**: a dev server, a Docker container, a local AI model, or an app that kept running after you closed its window.
3. **Charging in a warm room**, or the Mac sitting on a soft surface that blocks airflow.
4. **External displays** keeping the graphics busy.

## What helps

- **Hard, flat surface**, vents uncovered.
- **Quit what you're not using**, especially browsers with many tabs.
- **Let indexing finish**: leave the Mac plugged in and awake for a while after an update.
- **Restart** if the fans stay high with nothing busy. It clears stuck processes.
- On **Intel Macs**, resetting the SMC can fix fans that misbehave. Apple silicon Macs don't have one to reset; a shut down does the equivalent.

## The opposite problem: wanting more fan

Sometimes you'd rather have *more* cooling: during a long export or a hot afternoon. macOS doesn't offer a fan control. Bondi's **Fan boost** does, with three rules: it's never slower than your fans already were, it goes to full speed whenever macOS reports the Mac running hot, and it returns to Apple's automatic control when you quit Bondi, sleep or crash.

![Bondi's fan control: Auto, with a slider to boost.](/assets/features/fans.png)
*Bondi's Fan boost on a Mac whose fans range from about 1,180 to 5,779 rpm.*
