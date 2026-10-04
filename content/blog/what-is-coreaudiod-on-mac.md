---
title: What is coreaudiod on Mac?
description: coreaudiod handles all sound on your Mac: playback, recording and audio devices. Here's why it uses CPU and what happens if you quit it.
date: 2026-10-04
topic: processes
status: draft
order: 18
related: why-is-my-mac-slow, mac-fans-loud, which-app-is-using-my-mac-memory
---
coreaudiod handles all sound on your Mac: playback, recording and audio devices. It's part of macOS and safe; when it's busy, many apps are playing or recording, an audio plug-in is working hard, or an audio device is misbehaving.

## Key points

- coreaudiod handles all sound on the Mac: playback, recording and audio devices.
- It gets busy with many apps playing or recording, audio plug-ins, or a misbehaving audio device.
- Leave it running: quitting it cuts all sound until macOS starts it again.

## What coreaudiod does

Every app that plays or records sound, from music and video calls to system alerts, goes through coreaudiod, and so does every audio device, from built-in speakers to Bluetooth headphones and USB interfaces. Apps that change volume per app, like Bondi's Sound feature, work alongside it.

## Why coreaudiod is using CPU

Bondi's note: *Many apps playing or recording, audio plug-ins, or an audio device that misbehaves.*

If it stays busy for a long time, look at what else is happening: a big download, an import, an update or a sync is usually the reason, and it settles once that finishes. If your whole Mac feels slow, [Why is my Mac slow?](/blog/why-is-my-mac-slow/) walks through the other usual causes.

## Can I quit coreaudiod?

Leave it running: macOS needs it. Quitting it cuts all sound until macOS starts it again.

## How to check it

Op

Bondi shows the same in plain words: its process guide has notes on 125 common macOS processes, including coreaudiod, with what each one is using right now.

## Questions

### Is coreaudiod a virus?

No. coreaudiod is part of macOS: it handles all sound on the Mac.

### Why is coreaudiod using CPU?

Many apps are playing or recording, an audio plug-in is working hard, or an audio device is misbehaving. Unplugging or switching the device often settles it.

### What happens if I quit coreaudiod?

All sound stops until macOS starts it again. Leave it running: macOS needs it.
