---
title: How to check uptime on a Mac
description: Three ways to see how long your Mac or MacBook has been running since its last restart, what the uptime command's numbers mean, and whether a long uptime matters.
answer: Open Terminal and type `uptime`. Or hold Option, choose System Information from the Apple menu, and click Software to see Time since boot. A long uptime isn't a problem by itself.
date: 2026-10-10
updated: 2026-10-10
topic: tools
status: published
order: 23
image: /assets/features/cpu.png
related: why-is-my-mac-slow, task-manager-for-mac, memory-pressure-explained
---
Your Mac's uptime is how long it has been running since it last started up. The quickest way to check it on a Mac or MacBook: open **Terminal** and type `uptime`. Or, without Terminal, hold **Option**, open the Apple menu, choose **System Information**, and click **Software**: it shows **Time since boot**.

## Key points

- Terminal: type `uptime` and press Return.
- No Terminal: Apple menu (with Option held) → System Information → Software → Time since boot.
- A long uptime isn't a problem by itself. Restart when updates ask for it, or when the Mac slows down and memory stays tight.

## 1. The uptime command in Terminal

Open **Terminal** (Command-Space, type "Terminal"), type `uptime` and press **Return**. On the Mac I measure on, it said:

```
11:30  up 4 days, 21:06, 1 user, load averages: 4.07 14.72 15.89
```

- **11:30** is the time now.
- **up 4 days, 21:06** is the uptime: 4 days, 21 hours and 6 minutes.
- **1 user** counts sign-in sessions, not people. Each open Terminal window can add one, which is why it often says **2 users** when you're the only person on the Mac.
- **load averages** are how busy the processor was over the last 1, 5 and 15 minutes.

For the exact moment it started, type `sysctl -n kern.boottime`. Mine said **Mon Oct 5 14:25:08 2026**. `last reboot` lists earlier restarts too.

## 2. System Information, no Terminal needed

1. Hold **Option** and click the Apple menu (the Apple logo in the top-left corner).
2. Choose **System Information**.
3. Click **Software** in the sidebar. **Time since boot** is near the bottom: mine said **4 days, 21 hours, 6 minutes**, the same as `uptime`.

## 3. A system monitor

Most system monitors show uptime next to the processor's figures. In Bondi, it's in the CPU details, under the load average.

![Mac uptime in Bondi: CPU details with load average and an uptime of 34d 16h, on a MacBook Pro with an M1 Max.](/assets/features/cpu.png)
*Bondi's CPU details on the same MacBook Pro, on another day: up 34 days and 16 hours.*

## Does a long uptime slow a Mac down?

Not by itself. Macs sleep and wake for weeks without trouble. What can build up over days is **swap**: memory moved to the disk when apps need more than the Mac has. If **Memory Pressure** in Activity Monitor stays yellow or red even after you've closed apps, a restart clears it. See [Memory pressure on Mac: what yellow and red mean](/blog/memory-pressure-explained/).

Restart when a macOS update asks you to, after installing software that asks for it, or when things are slow for no visible reason. [Why is my Mac so slow?](/blog/why-is-my-mac-slow/) helps find the reason.

## Questions

### How do I check how long my Mac has been on?

Open Terminal and type uptime. Or hold Option, choose System Information from the Apple menu, click Software and read Time since boot.

### Why does Mac uptime say 2 users?

The number counts sign-in sessions, not people. Being signed in at the screen is one; an open Terminal window usually adds another.

### Should I restart my Mac every day?

No. There's no need to restart on a schedule. Restart for updates, or when memory pressure stays high or the Mac is slow and closing apps doesn't help.
