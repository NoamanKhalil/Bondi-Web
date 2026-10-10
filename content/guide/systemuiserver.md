---
title: What is SystemUIServer on Mac?
name: SystemUIServer
group: processes
description: SystemUIServer runs some of the menu bar's icons on your Mac. Why it's rarely busy, and how to restart it when menu bar icons stop responding.
answer: SystemUIServer runs some of the icons in your Mac's menu bar. It's part of macOS and safe, and rarely busy. If menu bar icons freeze, restarting it with killall SystemUIServer in Terminal reloads them.
status: published
reviewed: 2026-10-10
related: loginwindow, coreaudiod, launchd
searches: systemuiserver mac systemuiserver mac capture screen systemuiserver mac os systemuiserver macbook systemuiserver mac currently sharing systemuiserver mac microphone mac systemuiserver high cpu mac systemuiserver not responding killall systemuiserver mac systemuiserver app mac what is systemuiserver what is systemuiserver on mac what is systemuiserver app what is systemuiserver is capturing your screen what is com apple systemuiserver
---
SystemUIServer runs some of the **icons in your Mac's menu bar**, the strip at the top of the screen. It's part of macOS and safe.

## Key points

- SystemUIServer runs some of the menu bar's icons.
- It's rarely busy.
- Quitting it doesn't help for long: macOS starts it again, which also makes it a quick fix for a stuck menu bar.

## What SystemUIServer does

Some of the icons on the right of the menu bar are drawn by SystemUIServer rather than by an app. Control Center has its own process in recent versions of macOS, so SystemUIServer does less than it used to.

## Why is SystemUIServer using CPU?

It's rarely busy. If it is, an icon in the menu bar is usually stuck.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, SystemUIServer was using **0.0% of the CPU** and **11.6 MB of memory**.

## How to restart SystemUIServer

If menu bar icons stop responding:

1. Open **Terminal**.
2. Type `killall SystemUIServer` and press Return.
3. The menu bar icons disappear for a second and come back.

## Can I quit SystemUIServer?

Stopping it doesn't help for long: macOS starts it again when it's needed. That's what makes restarting it safe.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **SystemUIServer** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is SystemUIServer a virus?

No. SystemUIServer is part of macOS: it runs some of the menu bar's icons.

### How do I restart SystemUIServer?

In Terminal, type killall SystemUIServer and press Return. macOS starts it again within a second.

### Why is SystemUIServer not responding?

Usually a menu bar icon is stuck. Restarting it reloads the icons.
