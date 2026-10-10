# What is unified memory on a Mac? Unified memory vs RAM

> Unified memory is the RAM in Apple silicon Macs, shared by the processor, graphics and Neural Engine. What it means, how it differs from RAM and storage, and how much you need.

**Quick answer:** Unified memory is the RAM in Apple silicon Macs, shared by the processor, graphics and Neural Engine. It isn't storage, and it can't be upgraded later. Memory pressure in Activity Monitor tells you whether you have enough.

By Noaman Khalil, maker of Bondi. Published October 10, 2026.
Web page: https://trybondi.app/blog/unified-memory-mac/

Unified memory is the RAM in a Mac with Apple silicon (M1, M2, M3, M4 and later). It's called unified because the processor, the graphics and the Neural Engine all share one pool of it, instead of the graphics having separate memory of its own. When a MacBook says **16 GB unified memory**, that's its RAM: 16 GB, shared by everything on the chip.

## Key points

- Unified memory is RAM, shared by the CPU, GPU and Neural Engine on Apple silicon.
- It isn't storage: the SSD holds your files, memory holds what's running right now.
- It can't be upgraded later, so the amount you buy is the amount you keep. Memory pressure tells you if it's enough.

## Unified memory vs RAM

It is RAM. What's different is the arrangement:

- **Shared:** on most PCs, graphics cards have their own memory, and data is copied between it and the main RAM. On Apple silicon, the graphics read the same memory as the processor, so nothing is copied and the graphics can use a large share of it.
- **Built into the chip's package:** it sits right next to the processor, which makes it fast, but it can't be swapped or added to after you buy the Mac.

So the graphics can use a lot of it. On the Mac I measure on, LM Studio held **23.97 GB** of memory with a local AI model loaded, in the same 64 GB the apps were using.

## Unified memory vs SSD storage

Two different things that are easy to mix up:

- **Memory (unified memory):** the workspace for what's open right now. It's emptied when the Mac restarts.
- **Storage (the SSD):** where your files and apps live, kept when the Mac is off.

A MacBook with "16 GB unified memory, 512 GB SSD storage" has 16 GB of RAM and 512 GB of space for files. When memory runs out, macOS moves some of it to the SSD (called swap), which works but is much slower.

## Is 16 GB of unified memory enough?

For browsing, email, documents and photos, usually yes. For heavy video editing, large music projects, many browser tabs alongside development tools, or local AI models, more helps. Since late 2024, Apple's Macs start at 16 GB.

The numbers that tell you if *your* Mac has enough aren't the GB used. macOS fills spare memory on purpose, so "used" is high on almost every Mac. Look at **memory pressure** instead: in Activity Monitor's **Memory** tab, a green graph means enough; yellow or red most days means your work needs more. More in [Memory pressure on Mac: what yellow and red mean](https://trybondi.app/blog/memory-pressure-explained/).

## How to see how much your Mac has

Apple menu (the Apple logo, top left) → **About This Mac**: the **Memory** line shows it, for example **64 GB**. In Activity Monitor's Memory tab, **Physical Memory** at the bottom shows the same.

## How Bondi shows it

Bondi leads with pressure, not GB used: on the Mac I measure on, **45.1 GB of 64 GB** was in use, and pressure was **Normal**, with only 0.5 GB of swap.

![Unified memory on a MacBook Pro in Bondi: 45.1 GB of 64 GB used, memory pressure Normal, 0.5 GB swap.](https://trybondi.app/assets/features/popover-top.png)
*Bondi on a MacBook Pro with 64 GB of unified memory: plenty used, no problem.*

## Questions

### What does unified memory mean on a MacBook?

It's the MacBook's RAM. On Apple silicon, the processor, graphics and Neural Engine share it, which is why Apple calls it unified.

### Is unified memory the same as RAM?

Yes. It's RAM that the processor and graphics share, built into the chip's package. It can't be upgraded after purchase.

### Is unified memory the same as storage?

No. Unified memory holds what's running now and is emptied at restart; storage (the SSD) keeps your files.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
