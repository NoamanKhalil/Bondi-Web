---
title: What is trustd on Mac?
name: trustd
group: processes
description: trustd checks the security certificates of the websites and servers your apps connect to. Why trustd uses CPU on a Mac, and why several copies are normal.
answer: trustd checks the security certificates of the websites and servers your apps connect to, so connections are what they claim to be. It's part of macOS and safe; it's busy when your Mac makes many new connections at once.
status: draft
reviewed: 2026-10-10
related: syspolicyd, xprotectservice, mdnsresponder
searches: trustd mac trustd mac process trustd macos trustd macbook trustd mac activity monitor trustd mac cpu trustd process mac trustd macbook pro macos trustd high cpu macos trustd cpu what is trustd what is trustd on mac what is trustdale what is trustd process on mac
---
trustd is the part of macOS that checks **security certificates**: when an app connects to a website or server, trustd makes sure the certificate is valid, so the connection is what it claims to be. It's safe.

## Key points

- trustd checks certificates for every secure connection your apps make.
- It gets busy with many new connections at once.
- Leave it running: secure connections depend on it.

## What trustd does

Every padlock in a browser and every secure connection an app makes relies on a certificate check. trustd does those checks for the whole Mac and remembers results for a while, so the next connection is quicker. Several copies run at once: one for the system, and others for each user account, including macOS's own background accounts. That's normal.

## Why is trustd using so much CPU?

Many new connections at once: opening a browser with many tabs, a sync starting up, or an app that connects to many servers.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, trustd was using **0.0% of the CPU** and **9.4 MB of memory** (the 1 of its 6 copies that could be measured).

## Can I quit trustd?

Leave it running: macOS needs it.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **trustd** in the search field at the top right.
3. Look at **% CPU** and **Memory**. Several copies are normal.

## Questions

### Is trustd a virus?

No. trustd is part of macOS: it checks the security certificates of the sites and servers your apps connect to.

### Why is trustd using CPU on my Mac?

Your Mac is making many new secure connections at once, for example a browser opening many tabs. It settles quickly.

### Why are there several trustd processes?

macOS runs one for the system and others for each user account, including its own background accounts. That's normal.
