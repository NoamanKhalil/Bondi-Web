# What is apsd on Mac?

> apsd is Apple's push notification service on your Mac. What it does, why it's rarely busy, and what to do when apsd asks for your keychain password.

**Quick answer:** apsd is Apple's push notification service: it receives notifications from the internet for your apps. It's part of macOS and safe. If it asks for your login keychain password, type your Mac's login password.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/apsd/

apsd is **Apple Push Notifications**: it keeps one connection to Apple open and receives notifications from the internet for your apps, from messages to iCloud updates. It's part of macOS and safe.

## Key points

- apsd receives push notifications for your apps and Apple's services.
- It's rarely busy.
- Leave it running: notifications and iCloud updates depend on it.

## What apsd does

Rather than every app checking its server again and again, apsd keeps a single connection to Apple's push service. When something new arrives, a message, a calendar change or an iCloud update, apsd passes it to the right app. [cloudd](https://trybondi.app/guide/cloudd/) relies on it to know when to sync.

## Why is apsd using CPU?

It's rarely busy. A brief spike happens when many notifications arrive at once, for example when the Mac wakes up or reconnects to a network.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, apsd was running, but as a system process it can be measured only with administrator permission, which that reading didn't have.

## apsd and keychain password prompts

If a window says **apsd wants to use the "login" keychain**, it's genuine: apsd stores its security keys there. Type your Mac's login password. If it keeps asking, the login keychain's password may no longer match your Mac's password, which can happen after a password change; Apple's support pages explain how to reset the login keychain.

## Can I quit apsd?

Leave it running: macOS needs it.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **apsd** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is apsd a virus?

No. apsd is part of macOS: it's Apple's push notification service.

### Why does apsd want to use my login keychain?

It keeps its security keys there. Type your Mac's login password; if it keeps asking, the keychain's password may be out of step with your Mac's.

### What does apsd do on a Mac?

It receives push notifications from the internet for your apps and Apple's services, over one connection to Apple.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
