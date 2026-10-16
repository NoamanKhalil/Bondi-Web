---
title: What is WindowServer on Mac?
name: WindowServer
group: processes
description: WindowServer is the part of macOS that draws everything on your screens. What it does, why it uses CPU and memory, why it can crash or keep your Mac awake, and whether you can quit it.
answer: WindowServer is the part of macOS that draws everything you see: windows, the Dock, the menu bar and every animation, on every display. It's essential: quitting it logs you out. Some CPU and several hundred MB of memory are normal; many windows, external displays and screen recording make it work harder.
status: published
reviewed: 2026-10-16
related: loginwindow, systemuiserver, launchd
article: windowserver-high-cpu
searches: windowserver mac windowserver mac high cpu windowserver mac high memory windowserver macos windowserver mac process windowserver mac crash windowserver macbook windowserver macos high cpu windowserver mac preventing sleep windowserver mac high ram what is windowserver on mac what is windowserver what is windowserver process on mac what is windowserver on mac activity monitor what is windowserver in activity monitor what is windowserver on a mac what is windowserver on macbook what is windowserver on macos what is windowserver running on my mac what is apple windowserver
---
WindowServer is the part of macOS that **draws everything on your screens**: every window, the Dock, the menu bar and every animation, on the built-in display and any you plug in. It's essential, and it's always running.

## Key points

- WindowServer puts every window on screen for every app, so it's always using some CPU and memory.
- Many windows, external or very high-resolution displays, screen recording and video make it work harder.
- Leave it running: quitting it logs you out and closes every app.

## What WindowServer does

Apps don't draw straight onto the screen. Each one hands its windows to WindowServer, which puts them together into the picture you see, with the Dock, the menu bar, shadows, transparency and animations, as often as the screen refreshes: up to 120 times a second on ProMotion displays. It also passes your clicks and typing to the right window.

In Activity Monitor it appears as **WindowServer**, run by a special macOS account called **_windowserver**. That's why searches for "_windowserver" lead here: it's the same thing.

## Why is WindowServer using so much CPU?

Because your screens are asking a lot of it. The usual causes are many open windows (especially across several desktops), external displays (particularly several at once, or "scaled" resolutions), screen recording or sharing, video, and busy animations.

In readings of a 14-inch MacBook Pro (M1 Max, 64 GB) on its built-in display, WindowServer used **1.0% CPU** at a quiet moment in an earlier reading, and **18.5% CPU** with apps open and drawing on October 16, 2026. A few percent is normal; it's worth looking into when it stays above about 30% while nothing on screen is changing.

For what to do about it, see [WindowServer using high CPU on Mac: what it means and what helps](/blog/windowserver-high-cpu/).

## Why is WindowServer using so much memory?

It holds a copy of what's on every screen and every open window, so more windows and more or larger displays need more memory. In the readings above it used **639.9 MB** and **758.6 MB**, as Activity Monitor counts them. Closing windows you're not using, and unplugging displays you don't need, bring it down.

## Is WindowServer keeping my Mac awake?

Usually not. If you check what's keeping your Mac awake (in Terminal, `pmset -g assertions`), WindowServer is often listed as "UserIsActive": that's macOS noting that you just touched the keyboard or trackpad, and it ends by itself. When a Mac won't sleep, another app is usually the cause. In our reading, it was Google Chrome holding the Mac awake for an open video call ("WebRTC has active PeerConnections").

## Why did WindowServer crash?

When WindowServer crashes, the screen goes black for a moment and you're back at the login window, with your apps closed. It's usually set off by a display: an external monitor, a dock or adapter, or a macOS bug with either. Update macOS, update the dock's or adapter's software if it has any, and if it keeps happening with one display connected, try without it.

## Can I quit WindowServer?

No. Quitting it logs you out and closes every app, and macOS starts it again at the login window. It settles by itself as soon as there's less on your screens.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **WindowServer** in the search field at the top right.
3. Look at **% CPU** and **Memory**. In Activity Monitor, 100% CPU means one full core.

## Questions

### What is WindowServer on Mac?

The part of macOS that draws everything on your screens: windows, the Dock, the menu bar and every animation. Every app hands its windows to it.

### Is WindowServer a virus?

No. WindowServer is part of macOS, run by its own system account, _windowserver.

### Why is WindowServer using so much CPU?

Many open windows, external or high-resolution displays, screen recording and video make it work harder. A few percent is normal; above about 30% while nothing on screen is changing is worth looking into.

### Can I quit WindowServer?

No. Quitting it logs you out and closes every app.
