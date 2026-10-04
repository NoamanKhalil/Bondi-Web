# Memory pressure on Mac: what yellow and red mean

> "Memory Used" being high is normal on a Mac. Memory pressure, swap and compressed memory tell you whether you actually need more. Here's how to read them.

By Noaman Khalil, maker of Bondi. Published October 3, 2026, updated October 4, 2026.
Web page: https://trybondi.app/blog/memory-pressure-explained/

Memory pressure tells you how hard macOS is working to fit everything you're running into your Mac's memory. Green means it's comfortable, yellow means it's compressing and juggling, and red means it's out of room and leaning on the disk. It's a far better guide than "Memory Used", which is high on almost every Mac, and that's fine.

## Key points

- Memory pressure (green, yellow or red) shows how hard macOS is working to fit everything into memory.
- A high "Memory Used" is normal: macOS fills spare memory with cached files.
- Yellow means heavy compression; red means swapping to disk, which is when a Mac slows down.

## Why "Memory Used" being high is normal

macOS treats empty memory as wasted memory. It fills spare memory with **cached files** (things you've opened recently, kept in case you need them again) and hands that space back the moment an app needs it.

So on a healthy Mac, Memory Used can look alarming. On the Mac I measure on, Bondi reported **44.73 GB of 64.00 GB used, and memory pressure Normal**. Plenty used, no problem.

## What macOS does as memory fills up

1. **Uses free memory**, then **drops cached files** it doesn't need.
2. **Compresses memory**: squeezes the memory of apps you're not using right now, so more fits. This costs a little CPU but is fast.
3. **Swaps to disk**: when compression isn't enough, it moves memory to the startup disk. This is where things get slow, because the disk is far slower than memory.

Memory pressure rises through those stages: green while macOS can do this easily, yellow when it's compressing a lot, red when it's swapping heavily.

## How to read it in Activity Monitor

Open Activity Monitor and choose the **Memory** tab. At the bottom:

| What you see | What it means |
| --- | --- |
| **Memory Pressure** graph | The one to watch: green, yellow or red. |
| **App Memory** | Memory apps are actively using. |
| **Wired Memory** | Memory macOS itself must keep in place. |
| **Compressed** | App memory squeezed to save room. |
| **Cached Files** | Recently used files, given back when needed. |
| **Swap Used** | Memory moved to the disk. A little is normal; a lot, with yellow or red pressure, is why things feel slow. |

## What to do when memory pressure is yellow or red

1. **Find the app using the most.** Usually it's a browser with many tabs, but helper processes hide this. See [What is using memory on my Mac?](https://trybondi.app/blog/which-app-is-using-my-mac-memory/)
2. **Quit it or close what you don't need**, then watch the graph drop.
3. **Look for things left running**: dev servers, containers and local AI models hold memory even when idle.
4. **Restart** if swap has grown large over days of uptime.

If it's red every day with your normal set of apps, your work needs more memory than this Mac has.

## How Bondi says it

Bondi watches memory pressure for you. When it rises, the menu bar says so and names the cause, with one fix that always asks first:

![Memory pressure warning on a MacBook Pro, from Bondi: Memory pressure is Elevated. Google Chrome is using 13.09 GB. Quit Google Chrome.](https://trybondi.app/assets/features/warning.png)
*A real warning from Bondi. It suggests one fix at a time and never quits anything without asking.*

## Questions

### What does yellow memory pressure mean on a Mac?

macOS is compressing a lot of memory to fit everything in. The Mac still works; closing the app using the most memory helps.

### What is swap used on a Mac?

Memory macOS has moved to the startup disk because RAM ran short. A little is normal; a lot, with yellow or red pressure, makes the Mac slow.

### Do I need more RAM?

If memory pressure is red every day with the apps you normally use, your work needs more memory than this Mac has.

---
Bondi is a Mac app that tells you why your Mac is slow in one plain sentence, written on the Mac by an on-device AI: https://trybondi.app/
