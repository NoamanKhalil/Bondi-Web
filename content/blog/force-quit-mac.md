---
title: How to force quit on a Mac, even when it's frozen
description: How to force quit an app on a MacBook or any Mac: the keyboard shortcut, three other ways, and what to do when the whole screen is frozen.
date: 2026-10-10
updated: 2026-10-10
topic: tools
status: draft
order: 22
image: /assets/features/warning.png
related: task-manager-for-mac, why-is-my-mac-slow, check-mac-uptime
---
To force quit on a Mac, press **Option-Command-Esc**, pick the app that isn't responding, and click **Force Quit**. It works the same on a MacBook Air, a MacBook Pro or a desktop Mac. If the whole screen is frozen and that window won't open, hold the power button until the Mac turns off, then start it again.

## Key points

- Option-Command-Esc opens the Force Quit window: select the app, click Force Quit.
- You can also use the Apple menu, the app's Dock icon with Option held, or Activity Monitor.
- Force Quit closes an app without saving. Try a normal Quit first, and wait a few seconds if you see the spinning wheel.

## Four ways to force quit an app

1. **Keyboard:** press **Option-Command-Esc**, select the app, click **Force Quit**.
2. **Apple menu:** click the Apple logo in the top-left corner, choose **Force Quit**, then the app.
3. **Dock:** hold **Option** and Control-click (or right-click) the app's icon. **Quit** turns into **Force Quit**.
4. **Activity Monitor:** select the app, click the **Stop** button (ⓧ) and choose **Force Quit**. This is the way to stop a process that isn't a normal app. See [Task manager for Mac](/blog/task-manager-for-mac/).

Finder can't be force quit, only relaunched: select it in the Force Quit window and the button says **Relaunch**.

## How to force quit on a Mac when the screen is frozen

If the pointer moves, the Mac usually isn't frozen: one app is. Press **Option-Command-Esc** and give the window a few seconds to appear.

If nothing responds at all:

1. **Wait a minute.** A Mac that's very short on memory or very hot can stall and then recover.
2. **Hold the power button** (the Touch ID button on a MacBook) for about 10 seconds, until the screen goes dark.
3. **Press it again** to start up. Apps that save automatically reopen with your work; anything unsaved since their last save is lost.

If it keeps happening, something is wearing the Mac down. [Why is my Mac so slow?](/blog/why-is-my-mac-slow/) walks through the usual causes.

## Quit or Force Quit?

**Quit** asks the app to close: it saves what it can, tidies up and stops its helper processes. **Force Quit** stops it immediately, so anything unsaved is gone. Use it only when Quit does nothing.

The spinning wheel often clears by itself, so give an app a few seconds first. A force quit doesn't fix the reason it stalled, either: if the same app freezes again, it's usually short of memory or stuck on one task.

## How Bondi does it

Bondi always asks first. Quit Google Chrome from Bondi and it says how many processes will close (on the Mac I measure on, 82), quits them together, and only if Chrome hasn't closed after 5 seconds asks: *"Google Chrome isn't responding. Force Quit? Unsaved work will be lost."*

![Force quit on a Mac with Bondi: Memory pressure is Elevated, Google Chrome is using 13.09 GB, with a Quit Google Chrome button.](/assets/features/warning.png)
*Bondi's warning on a MacBook Pro: one cause, one fix, and nothing quits until you click.*

## Questions

### What is the shortcut to force quit on a Mac?

Option-Command-Esc. It opens the Force Quit window, where you select the app and click Force Quit.

### How do I force quit on a MacBook when the screen is frozen?

If Option-Command-Esc doesn't open the Force Quit window, hold the power button (the Touch ID button) for about 10 seconds until the Mac turns off, then press it again to start up.

### Does force quitting lose my work?

Anything unsaved since the app's last save can be lost. Many Mac apps save automatically and reopen where you left off, but try a normal Quit first.
