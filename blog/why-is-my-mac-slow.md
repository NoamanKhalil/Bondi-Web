# Why is my Mac so slow? How to find the real cause

> Mac running slow? It usually comes down to one of a handful of causes. Here's how to tell which one it is, with real readings from a real MacBook Pro.

By Noaman Khalil, maker of Bondi. Published October 3, 2026, updated October 4, 2026.
Web page: https://trybondi.app/blog/why-is-my-mac-slow/

A slow Mac, whether it's a MacBook Air, a MacBook Pro or an iMac, almost always has one specific cause: memory running short, one app working hard, macOS doing background work, or the Mac protecting itself from heat. The trick is finding which one, because the fix for each is different.

## Key points

- A slow Mac usually has one cause: low memory, one busy app, macOS background work, or heat.
- Check memory pressure first (Activity Monitor → Memory). A high "Memory Used" on its own is normal.
- Apps like Chrome split into many processes, so add them up before deciding which app is to blame.

Here are the usual suspects, in the order I'd check them, and how to spot each one.

## 1. Memory is running short

When the apps you have open need more memory than your Mac has, macOS starts compressing memory and moving some of it to the disk (swap). Everything still works, just slower: switching apps stutters and the beach ball appears.

**How to tell:** open Activity Monitor (press Command-Space and type "Activity Monitor"), choose the **Memory** tab, and look at the **Memory Pressure** graph at the bottom. Green is fine. Yellow means macOS is working to keep up. Red means it's out of room.

A high "Memory Used" number on its own is *not* a problem: macOS deliberately fills spare memory with cached files. Pressure is what matters. There's more in [Memory pressure on Mac: what yellow and red mean](https://trybondi.app/blog/memory-pressure-explained/).

## 2. One app is using more than you think

Modern apps are many processes. On the Mac I measure on, Google Chrome was **80 separate processes using 12.23 GB of memory** in total, while Activity Monitor showed it as dozens of rows of a few hundred megabytes each. No single row looked big, so the real culprit was easy to miss.

**How to tell:** in Activity Monitor, choose **View → All Processes, Hierarchically**, or add up the helper processes that share an app's name. [What is using memory on my Mac?](https://trybondi.app/blog/which-app-is-using-my-mac-memory/) walks through it.

## 3. macOS is doing background work

After an update, a big download or importing photos, macOS catches up in the background:

- **Spotlight** re-indexes your files (the processes `mds_stores` and `mdworker`).
- **Photos** analyzes your library for people, places and Memories (`photoanalysisd`).
- **iCloud** syncs files (`cloudd` and `bird`).

These finish on their own, usually within hours. Keeping the Mac plugged in and awake helps them finish sooner. See [Why is my Mac slow after a macOS update?](https://trybondi.app/blog/mac-slow-after-update/)

## 4. Your Mac is hot

When the chip gets hot, macOS deliberately slows things down to cool it. In Activity Monitor this shows up as **kernel_task** using a lot of CPU. It isn't a bug: it's macOS taking CPU time away from apps so the heat drops. Charging in a warm room or working on a soft surface makes it more likely. More in [kernel_task high CPU on Mac?](https://trybondi.app/blog/kernel-task-high-cpu/)

## 5. Something is quietly holding on

Developers know this one: a dev server, a Docker container or a local AI model you started hours ago and forgot. On my Mac, a Vite dev server had been **idle for 3 hours 20 minutes and was still holding 612.0 MB**. None of these show up as an app in the Dock, so they're easy to forget.

## 6. The disk is nearly full

macOS needs free space for swap, caches and updates. When the startup disk is close to full, everything that touches the disk slows down. Check it in **System Settings → General → Storage**.

## Mac running slow? The quick checklist

| Symptom | Most likely cause | Where to look |
| --- | --- | --- |
| Beach ball when switching apps | Memory pressure | Activity Monitor → Memory |
| Fans loud, everything sluggish | Heat (kernel_task) or one busy app | Activity Monitor → CPU |
| Slow for a day after updating | Spotlight, Photos or iCloud catching up | Activity Monitor → CPU |
| Slow only with certain apps open | That app's helper processes | View → All Processes, Hierarchically |
| Everything slow, Mac nearly out of space | Disk nearly full, or heavy swap | System Settings → Storage |

## Why I built Bondi for this

All of this is detective work, and Activity Monitor gives you the clues but not the answer. Bondi does the detective work for you and says it in one sentence. On that same Mac, it said:

![Bondi explaining why a MacBook Pro is slow: Everything is fine. Memory pressure is Normal, with 44.73 GB of 64.00 GB used. Google Chrome is using the most memory, 13.09 GB across 82 processes.](https://trybondi.app/assets/features/why.png)
*Bondi's one-sentence answer, from a real reading on a MacBook Pro (M1 Max, 64 GB).*

Every number in that sentence is measured by Bondi itself, and the sentence is written on your Mac, so nothing about your apps ever leaves it.

## Questions

### How do I find out why my Mac is slow?

Open Activity Monitor. On the Memory tab, look at the Memory Pressure graph; on the CPU tab, sort by % CPU. Yellow or red pressure means memory is short; a process near the top that you're not using is the busy app.

### Is high memory usage on a Mac bad?

No. macOS fills spare memory with cached files and gives it back when apps need it. Memory pressure, not Memory Used, tells you whether you need more.

### Why is my Mac slow after an update?

macOS rebuilds the Spotlight index, re-analyzes photos and syncs iCloud in the background. It settles on its own, usually within hours.

---
Bondi is a Mac app that tells you why your Mac is slow in one plain sentence, written on the Mac by an on-device AI: https://trybondi.app/
