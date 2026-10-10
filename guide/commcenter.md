# What is CommCenter on Mac?

> CommCenter handles phone calls and text messages that work with your iPhone on a Mac. Why it asks for your keychain, and whether CommCenter is a virus.

**Quick answer:** CommCenter handles the phone and text message features that work with your iPhone, such as calls and SMS on your Mac. It's part of macOS and safe, not a virus. If it asks for your login keychain password, type your Mac's login password.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/commcenter/

CommCenter handles the **phone and text message features that work with your iPhone**: taking calls on your Mac, and sending and receiving SMS through your iPhone. It's part of macOS and safe.

## Key points

- CommCenter runs calls and text messages that come through your iPhone.
- It's rarely busy.
- Quitting it doesn't help for long: macOS starts it again.

## What CommCenter does

The name comes from the iPhone, where CommCenter runs the phone's connection to the mobile network. On a Mac, it handles the parts of that which reach your Mac through your iPhone, working with [rapportd](https://trybondi.app/guide/rapportd/), which lets the two devices find each other.

## "CommCenter wants to use the login keychain"

This prompt is genuine: CommCenter keeps its keys in your login keychain. Type your Mac's login password. If it keeps asking, the login keychain's password may no longer match your Mac's password, often after a password change.

## Why is CommCenter using CPU?

It's rarely busy. Brief activity is normal during a call or when messages arrive.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, CommCenter was using **0.0% of the CPU** and **9.5 MB of memory**.

## Can I quit CommCenter?

Stopping it doesn't help for long: macOS starts it again when it's needed.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **CommCenter** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is CommCenter a virus?

No. CommCenter is part of macOS: it handles phone and text message features that work with your iPhone.

### Why does CommCenter want to use my keychain?

It stores its keys in your login keychain. Type your Mac's login password; if it keeps asking, the keychain's password may be out of step with your Mac's.

### What does CommCenter do on a Mac?

It handles calls and SMS that come through your iPhone, so you can take them on your Mac.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
