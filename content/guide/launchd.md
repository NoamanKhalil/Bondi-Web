---
title: What is launchd on Mac?
name: launchd
group: processes
description: launchd is the first process macOS starts, and it starts every other service. Why launchd is rarely busy, and what high CPU from it usually means.
answer: launchd is the first process macOS starts: it starts, stops and restarts every other background service. It's essential and can't be quit. It's rarely busy; when it is, another service is usually being restarted over and over.
status: draft
reviewed: 2026-10-10
related: loginwindow, spindump, systemuiserver
searches: launchd mac launchd macos launchd mac high cpu launchd mac gui launchd macbook launchd mac activity monitor launchd mac tutorial launchd macos disk usage launchd macos gui launchd mac process what is launchdarkly what is launchd on mac what is launchdarkly used for what is launchd what is launchdaemons what is launchdarkly and how does it work what is launchdaemons mac what is launchd in macos what is launchdarkly feature flag
---
launchd is the **first process macOS starts**, with process ID 1. It starts, stops and restarts every other background service, and it's essential.

## Key points

- launchd starts and supervises every background service on the Mac.
- It's rarely busy. If it is, some other service is being started over and over.
- Leave it running: macOS can't run without it.

## What launchd does

Every background service and agent, from Spotlight's indexer to apps' update checkers, is started by launchd: at startup, at login, on a schedule, or when something needs it. If a service crashes, launchd starts it again. Apps add their own agents to launchd too, which is how they run in the background.

## Why is launchd using so much CPU?

It's rarely busy. If it is, some other service is being started over and over: usually one that crashes as soon as it starts, often from an app you've removed or one that needs an update. Restarting the Mac, or updating or removing the app the service belongs to, usually fixes it.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, launchd was running, but as a system process it can be measured only with administrator permission, which that reading didn't have.

## Can I quit launchd?

Leave it running: macOS needs it. It can't be quit; it's the parent of every other process.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **launchd** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is launchd a virus?

No. launchd is part of macOS: it's the first process the Mac starts, and it starts all the others.

### Why is launchd using high CPU?

Another service is probably crashing and being restarted again and again. Restart the Mac, and update or remove the app that service belongs to.

### Can I quit launchd?

No. It's the process every other process comes from.
