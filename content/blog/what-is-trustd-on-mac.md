---
title: What is trustd on Mac?
description: trustd checks the security certificates of websites and servers your apps connect to. Here's why trustd gets busy and why it's safe to leave running.
date: 2026-10-04
topic: processes
status: draft
order: 20
related: what-is-mdnsresponder-on-mac, why-is-my-mac-slow, what-is-xprotect-on-mac
---
trustd is the part of macOS that checks the security certificates of the websites and servers your apps connect to, so your connections are what they claim to be. It's safe; when it's busy, your Mac is making many new connections at once.

## Key points

- trustd checks the security certificates of websites and servers that apps connect to.
- It gets busy when many new connections happen at once.
- Leave it running: macOS needs it for secure connections.

## What trustd does

Every secure connection, the padlock in your browser and the connections apps make in the background, relies on a certificate. trustd checks those certificates for the whole system, so it's busiest when a browser opens many sites or apps start many connections at once.

## Why trustd is using CPU

Bondi's note: *Many new connections at once.*

If it stays busy for a long time, look at what else is happening: a big download, an import, an update or a sync is usually the reason, and it settles once that finishes. If your whole Mac feels slow, [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual causes.

## Can I quit trustd?

Leave it running: macOS needs it.

## How to check it

Op

Bondi shows the same in plain words: its process guide has notes on 125 common macOS processes, including trustd, with what each one is using right now.

## Questions

### Is trustd a virus?

No. trustd is part of macOS: it checks the security certificates of websites and servers your apps connect to.

### Why is trustd using CPU?

Your Mac is making many new connections at once, for example a browser opening many tabs or apps syncing at startup.

### Can I quit trustd?

Leave it running: macOS needs it for secure connections.
