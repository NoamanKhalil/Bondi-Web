# What is mDNSResponder on Mac?

> mDNSResponder finds devices on your network and looks up web addresses for every app. Why it uses network or CPU, how to restart it, and why not to disable it.

**Quick answer:** mDNSResponder finds devices on your network (Bonjour) and looks up web addresses (DNS) for every app. It's essential and safe; don't disable it, or names stop working. Restarting it also clears your Mac's DNS cache.

From Bondi's Mac guide, reviewed by Noaman Khalil on October 10, 2026. Web page: https://trybondi.app/guide/mdnsresponder/

mDNSResponder is the part of macOS that turns names into addresses: it looks up web addresses (**DNS**) for every app, and finds printers, speakers and other devices on your network (**Bonjour**). It's essential and safe.

## Key points

- mDNSResponder handles DNS lookups and Bonjour for every app.
- It gets busy with many network lookups, or on a network with many devices.
- Don't disable it: without it, websites and network devices stop being found.

## What mDNSResponder does

When any app opens a website, mDNSResponder looks up the site's address and remembers it for a while (the DNS cache). It also listens for devices that announce themselves on your network, which is how AirPlay speakers and printers simply appear.

## Why is mDNSResponder using so much network or CPU?

Many network lookups, or a network with many devices. Browsers with many tabs, apps that check for updates, and busy home networks (lots of smart devices announcing themselves) all add up. "High network" figures are usually many small packets, not big downloads.

In one reading of a MacBook Pro (M1 Max, 64 GB) on October 10, 2026, mDNSResponder was running, but as a system process it can be measured only with administrator permission, which that reading didn't have.

## How to restart mDNSResponder (and clear the DNS cache)

If websites won't load but your connection works, clear the DNS cache:

1. Open **Terminal**.
2. Type `sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder` and press Return.
3. Enter your Mac's password. The lookups start fresh.

## Can I quit or disable mDNSResponder?

Leave it running: macOS needs it. Disabling it would stop name lookups for every app.

## How to check it

1. Open **Activity Monitor**: press Command-Space, type "Activity Monitor" and press Return.
2. Type **mDNSResponder** in the search field at the top right.
3. Look at **% CPU** and **Memory**.

## Questions

### Is mDNSResponder a virus?

No. mDNSResponder is part of macOS: it looks up web addresses and finds devices on your network.

### Can I disable mDNSResponder?

No. Without it, apps can't look up website addresses or find devices on your network.

### How do I restart mDNSResponder?

In Terminal, type sudo killall -HUP mDNSResponder and press Return. Adding sudo dscacheutil -flushcache first also clears the DNS cache.

---
Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence. https://trybondi.app/
