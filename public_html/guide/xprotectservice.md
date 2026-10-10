# What is XProtect (XprotectService) on Mac?

> XProtect is Apple's built-in malware protection on every Mac. What XprotectService does, why it uses CPU, and why you should leave it running.

**Quick answer:** XProtect is Apple's built-in malware protection on every Mac. Its processes, such as XprotectService, check apps and files against known malware and update their rules quietly. It's safe; when it's busy, it's scanning after a rules update or a new download.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/xprotectservice/

XProtect is **Apple's built-in malware protection**, on every Mac. You'll see it in Activity Monitor as **XprotectService**, and sometimes as XProtect, XProtectBridgeService or XProtectUpdateService. It's safe.

## Key points

- XProtect checks apps and files against known malware.
- It gets busy scanning after a rules update, or checking newly downloaded files.
- Leave it running: it's part of the Mac's protection.

## What XProtect does

Apple keeps a list of known malware and updates it on its own, separately from macOS updates. XProtect uses that list to check apps when they're opened and when their files change, and to look for known threats in the background. If it finds one, macOS tells you and offers to move it to the Trash.

## Why is XprotectService using so much CPU?

Scans after a rules update, or checking newly downloaded files. A big download, or many new files at once, means more to check.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, XprotectService was using **0.0% of the CPU** and **6.5 MB of memory** (the 2 of its 6 copies that could be measured).

## Can I quit XprotectService?

Leave it running: macOS needs it. Quitting it would only pause your Mac's malware checks until macOS starts it again.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **XProtect** in the search field at the top right.
3. Look at **% CPU** and **Memory**. Several copies with XProtect in their name are normal.

## Questions

### Is XprotectService a virus?

No. It's part of XProtect, Apple's built-in malware protection on every Mac.

### Why is XprotectService using so much CPU?

It's scanning: after its rules update, or while checking newly downloaded files. It settles once the scan is done.

### Do I need antivirus software if my Mac has XProtect?

XProtect covers known malware and updates on its own. Keeping macOS up to date and downloading apps from the App Store or developers you trust matters most.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
