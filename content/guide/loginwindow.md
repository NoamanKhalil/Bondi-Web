---
title: What is loginwindow on Mac?
name: loginwindow
group: processes
description: loginwindow is your Mac's login session. Why loginwindow can use hundreds of MB of memory, what loginwindow Secure Input means, and why not to quit it.
answer: loginwindow runs your login session: logging in and out, the lock screen, and reopening apps after a restart. It's part of macOS and safe. Hundreds of MB of memory can be normal; quitting it logs you out.
status: draft
reviewed: 2026-10-10
related: launchd, systemuiserver, rapportd
searches: loginwindow mac loginwindow macos loginwindow mac high memory loginwindow mac process loginwindow mac secure input loginwindow mac high cpu loginwindow mac activity monitor loginwindow macbook loginwindow mac что это loginwindow mac que es what is loginwindow on mac what is loginwindow on mac activity monitor what is loginwindow what is loginwindow in activity monitor what is loginwindow app
---
loginwindow runs your **login session**: logging in and out, the lock screen, and reopening apps after a restart. It's part of macOS and safe.

## Key points

- loginwindow manages logging in and out, the lock screen, and restoring apps.
- It's rarely busy, but its memory can grow over a long session.
- Leave it running: quitting it logs you out.

## What loginwindow does

loginwindow starts your session when you log in, shows the lock screen, and remembers which apps and windows to reopen after a restart. It's also involved in **Secure Input**, which stops other apps reading your keystrokes while you type a password.

## Why is loginwindow using so much memory?

It's rarely busy, but it holds parts of your session, so its memory figure can grow the longer the Mac stays logged in.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, loginwindow was using **0.0% of the CPU** and **731.7 MB of memory**. That Mac had been up for about five days.

Logging out and back in, or restarting, brings it back down.

## loginwindow and Secure Input

Text expanders and keyboard tools sometimes warn that **loginwindow has turned on Secure Input**, and stop working. Locking the Mac (Control-Command-Q) and unlocking it usually turns it off; logging out and in always does.

## Can I quit loginwindow?

Leave it running: macOS needs it. Quitting it logs you out and closes your apps.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **loginwindow** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is loginwindow a virus?

No. loginwindow is part of macOS: it runs your login session and the lock screen.

### Why is loginwindow using so much memory?

It holds parts of your login session, and that grows over days. Hundreds of MB is common on a Mac that has been logged in for a while; logging out or restarting resets it.

### What does "loginwindow has Secure Input on" mean?

macOS is protecting keystrokes, for example after a password field. Lock the Mac with Control-Command-Q and unlock it; if that doesn't clear it, log out and back in.
