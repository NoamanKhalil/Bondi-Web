# What is coreaudiod on Mac?

> coreaudiod handles all sound on your Mac. Why it uses CPU, how to restart coreaudiod when the sound stops or crackles, and why you shouldn't quit it.

**Quick answer:** coreaudiod handles all sound on your Mac: playback, recording and audio devices. It's part of macOS and safe. If the sound stops or crackles, restarting it with sudo killall coreaudiod in Terminal often fixes it.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/coreaudiod/

coreaudiod is the part of macOS that handles **all sound**: what you hear, what microphones record, and every audio device, from the built-in speakers to AirPods and USB interfaces. It's safe.

## Key points

- coreaudiod runs every sound on your Mac.
- It gets busy with many apps playing or recording, audio plug-ins, or a misbehaving audio device.
- Quitting it cuts all sound until macOS starts it again; restarting it is a common fix for sound problems.

## What coreaudiod does

Every app that plays or records sound goes through coreaudiod, which mixes them and sends the result to your output device. Audio devices that apps add, like those from video-call or screen-recording software, plug into it too.

## Why is coreaudiod using so much CPU?

Many apps playing or recording, audio plug-ins, or an audio device that misbehaves. An added audio device that's stuck, or a plug-in in a music app, can keep it busy with nothing playing.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, coreaudiod was running, but as a system process it can be measured only with administrator permission, which that reading didn't have.

## How to restart coreaudiod

If your Mac has no sound, the sound crackles, or a device won't show up:

1. Open **Terminal** (Command-Space, type "Terminal").
2. Type `sudo killall coreaudiod`, press Return, and enter your Mac's password.
3. macOS starts it again within seconds. Play something to check.

If that doesn't help, restart the Mac.

## Can I quit coreaudiod?

Leave it running: macOS needs it. Quitting it cuts all sound until macOS starts it again, which is why restarting it is the fix above.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **coreaudiod** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is coreaudiod a virus?

No. coreaudiod is part of macOS: it handles all sound, recording and audio devices.

### How do I restart coreaudiod on a Mac?

In Terminal, type sudo killall coreaudiod and press Return, then enter your password. macOS starts it again within seconds.

### Why is coreaudiod using CPU when nothing is playing?

Usually an audio plug-in or an added audio device (from a video-call or recording app) is still active. Quit those apps, or restart coreaudiod.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
