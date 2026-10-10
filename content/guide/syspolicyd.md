---
title: What is syspolicyd on Mac?
name: syspolicyd
group: processes
description: syspolicyd is Gatekeeper: it checks apps the first time you open them. Why syspolicyd uses CPU on your Mac, and why it's busy for developers.
answer: syspolicyd is Gatekeeper: it checks apps the first time you open them, to make sure they come from an identified developer and haven't been changed. It's part of macOS and safe; it's busy when you open a new or very large app.
status: draft
reviewed: 2026-10-10
related: xprotectservice, trustd, launchd
searches: syspolicyd mac syspolicyd mac cpu syspolicyd macbook syspolicyd mac process macos syspolicyd cpu usage macos syspolicyd high cpu mac syspolicyd とは what is syspolicyd what is syspolicyd on my mac what is syspolicyd on macbook what is usr libexec syspolicyd
---
syspolicyd is **Gatekeeper**: the part of macOS that checks apps the first time you open them, to make sure they come from an identified developer and haven't been tampered with. It's safe.

## Key points

- syspolicyd checks each app the first time it's opened.
- It gets busy when you open a new or very large app, or many at once.
- Leave it running: it's part of your Mac's security.

## What syspolicyd does

When you open an app you've just downloaded, syspolicyd checks its signature and, for apps from outside the App Store, whether Apple has notarized it. That's why a new app can take a moment to open the first time, and why some show a warning before they open.

## Why is syspolicyd using so much CPU?

Opening a new or very large app, or many at once. A big app (an IDE, a game, a creative suite) has a lot to check the first time.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, syspolicyd was running, but as a system process it can be measured only with administrator permission, which that reading didn't have.

Developers see it often: each freshly built app or command-line tool counts as new, so building and running code repeatedly keeps syspolicyd checking.

## Can I quit syspolicyd?

Leave it running: macOS needs it. It's part of what keeps unknown software from running unchecked.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **syspolicyd** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is syspolicyd a virus?

No. syspolicyd is part of macOS: it's Gatekeeper, which checks apps the first time you open them.

### Why is syspolicyd using so much CPU?

You've opened a new or very large app, or several at once. If you build software, each new build is checked too. It settles once the checks are done.

### Can I disable syspolicyd?

No, and you wouldn't want to: it's part of the Mac's protection against unsafe apps.
