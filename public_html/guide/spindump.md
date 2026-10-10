# What is spindump on Mac?

> spindump records what an app was doing when it stopped responding. Why it uses CPU right after a hang, and where its reports go.

**Quick answer:** spindump records what an app was doing when it stopped responding, for a hang report. It's part of macOS and safe; it's busy right after an app hangs and finishes quickly.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/spindump/

spindump records **what an app was doing when it stopped responding**: the spinning wait cursor moments. It's part of macOS and safe.

## Key points

- spindump takes a snapshot of every process when an app hangs.
- It's busy right after an app hangs; it finishes quickly.
- Quitting it doesn't help for long: macOS starts it again.

## What spindump does

When an app stops responding, or you force quit one, spindump samples what every process was doing at that moment and writes a hang report. The reports stay on your Mac, and are sent to Apple and the app's developer only if you've chosen to share analytics in **System Settings → Privacy & Security → Analytics & Improvements**.

## Why is spindump using so much CPU?

Right after an app hangs; it finishes quickly. If it keeps coming back, an app keeps hanging: that app is the one to update, restart or replace. [How to force quit on a Mac](https://trybondi.app/blog/force-quit-mac/) covers stuck apps.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, spindump was running, but as a system process it can be measured only with administrator permission, which that reading didn't have.

## Can I quit spindump?

Stopping it doesn't help for long: macOS starts it again when it's needed.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **spindump** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is spindump a virus?

No. spindump is part of macOS: it writes reports when apps stop responding.

### Why is spindump using high CPU?

An app just stopped responding, and spindump is recording what happened. It finishes within moments; if it keeps returning, an app keeps hanging.

### Does spindump send data to Apple?

Only if you've chosen to share analytics in System Settings → Privacy & Security → Analytics & Improvements. Otherwise its reports stay on your Mac.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
