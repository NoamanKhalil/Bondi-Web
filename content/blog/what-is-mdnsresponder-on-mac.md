---
title: What is mDNSResponder on Mac?
description: mDNSResponder finds devices on your network (Bonjour) and looks up web addresses (DNS) for your Mac. Here's why it gets busy and whether you can quit it.
date: 2026-10-04
topic: processes
status: draft
order: 12
related: why-is-my-mac-slow, which-app-is-using-my-mac-memory, what-is-trustd-on-mac
---
mDNSResponder is the part of macOS that finds devices on your network, like printers, speakers and other Macs (Apple calls this Bonjour), and looks up web addresses (DNS) for your apps. It's essential and safe; when it's busy, your Mac is making a lot of network lookups.

## Key points

- mDNSResponder is part of macOS: it finds devices on your network (Bonjour) and looks up web addresses (DNS).
- It gets busy with many network lookups, or on a network with many devices.
- Leave it running: quitting it would break network lookups until macOS starts it again.

## What mDNSResponder does

Two jobs run through mDNSResponder:

- **Bonjour:** finding devices and services on your local network, such as AirPlay speakers, printers and shared Macs, without you typing an address.
- **DNS:** turning web addresses like trybondi.app into the numeric addresses your apps connect to.

Because every app's web lookups go through it, its activity rises and falls with how much your Mac is doing online.

## Why mDNSResponder is using CPU

Bondi's note: *Many network lookups, or a network with many devices.*

If it stays busy for a long time, look at what else is happening: a big download, an import, an update or a sync is usually the reason, and it settles once that finishes. If your whole Mac feels slow, [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual causes.

## Can I quit mDNSResponder?

Leave it running: macOS needs it.

## How to check it

Op

Bondi shows the same in plain words: its process guide has notes on 125 common macOS processes, including mDNSResponder, with what each one is using right now.

## Questions

### Is mDNSResponder a virus?

No. mDNSResponder is part of macOS. It finds devices on your network (Bonjour) and looks up web addresses (DNS).

### Why is mDNSResponder using CPU?

Your Mac is making many network lookups, or it's on a network with many devices. Busy browsers, sync apps and network tools all add to it.

### Can I quit mDNSResponder?

Leave it running: macOS needs it for network lookups and finding devices.
