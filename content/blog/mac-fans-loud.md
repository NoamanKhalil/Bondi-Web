---
title: Why is my Mac so hot and loud?
description: Loud fans on a MacBook or any Mac mean the chip is working hard or running hot. Here's how to find what's heating it, and what to do if they won't settle.
date: 2026-10-03
updated: 2026-10-04
topic: heat
status: published
order: 4
image: /assets/features/fans.png
related: kernel-task-high-cpu, why-is-my-mac-slow, mac-slow-after-update
---
On a MacBook Pro or a desktop Mac, the fans get loud for one reason: the chip is producing more heat than the Mac can lose quietly. Find what's making it work hard, give it some air, and the fans settle within minutes.

## Key points

- Loud fans mean the chip is working hard or running hot.
- Check Activity Monitor's CPU tab: a busy app, kernel_task, or Spotlight and Photos indexing are the usual causes.
- A hard surface, quitting unused apps and letting indexing finish usually quiets them.

## First: is something working hard?

Open Activity Monitor (Command-Space, then type "Activity Monitor"), choose the **CPU** tab and sort by **% CPU**. Look at the top of the list:

- **An app you're using**, like a video export, a build or a game: the fans are doing their job. They'll calm down when it finishes.
- **An app you're not using**: often a browser tab or a helper process stuck at high CPU. Quit it, or close the tab.
- **kernel_task near the top**: the Mac is already hot and slowing apps to cool down. See [kernel_task high CPU on Mac?](/blog/kernel-task-high-cpu/)
- **mds_stores, mdworker or photoanalysisd**: Spotlight or Photos catching up after an update or import. It finishes on its own. See [Is Spotlight indexing slowing down your Mac?](/blog/spotlight-indexing-slow-mac/)

Also check the **GPU** (in Activity Monitor: **Window → GPU History**). Games, video and some web pages heat the graphics side more than the CPU.

## MacBook fans loud with nothing open

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

![Mac fan control in Bondi: Auto, with a slider to boost the fans.](/assets/features/fans.png)
*Bondi's Fan boost on a Mac whose fans range from about 1,180 to 5,779 rpm.*

## Questions

### Why are my MacBook's fans loud with nothing open?

Usually macOS background work after an update (Spotlight, Photos, iCloud), a process left running, charging in a warm room, or a soft surface blocking the airflow.

### Is it bad if my Mac's fans run loud?

No. The fans are protecting the chip. If they stay loud for no clear reason, find the busy process in Activity Monitor, or restart.

### Can I control my Mac's fan speed?

macOS doesn't offer fan control. Bondi's Fan boost can raise it, never slower than your fans already were, and returns to automatic control when you quit Bondi.
