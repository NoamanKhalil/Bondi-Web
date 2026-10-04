---
title: What is syspolicyd on Mac?
description: syspolicyd is Gatekeeper on your Mac: it checks apps the first time you open them. Here's why it gets busy and why you should leave it running.
date: 2026-10-04
status: draft
order: 19
related: what-is-xprotect-on-mac, mac-slow-after-update, why-is-my-mac-slow
---
syspolicyd is Gatekeeper: the part of macOS that checks apps the first time you open them, to make sure they come from an identified developer and haven't been tampered with. It's safe; when it's busy, you've opened a new or very large app, or several at once.

## Key points

- syspolicyd is Gatekeeper: it checks apps the first time you open them.
- It gets busy when you open a new or very large app, or many at once.
- Leave it running: macOS needs it to keep apps you open safe.

## What syspolicyd does

The first time you open an app you've downloaded, Gatekeeper checks it before it runs, which is why a big app can take a moment to open the first time and then opens quickly after that. syspolicyd does that checking.

## Why syspolicyd is using CPU

Bondi's note: *Opening a new or very large app, or many at once.*

If it stays busy for a long time, look at what else is happening: a big download, an import, an update or a sync is usually the reason, and it settles once that finishes. If your whole Mac feels slow, [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual causes.

## Can I quit syspolicyd?

Leave it running: macOS needs it.

## How to check it

Op

Bondi shows the same in plain words: its process guide has notes on 125 common macOS processes, including syspolicyd, with what each one is using right now.

## Questions

### Is syspolicyd a virus?

No. syspolicyd is part of macOS: it's Gatekeeper, which checks apps the first time you open them.

### Why is syspolicyd using CPU?

You've opened a new or very large app, or several at once. It settles once the checks are done.

### Can I disable syspolicyd?

Leave it running: macOS needs it, and it's what checks the apps you open.
