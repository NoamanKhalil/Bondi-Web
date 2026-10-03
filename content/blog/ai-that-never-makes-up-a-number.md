---
title: How Bondi's AI explains your Mac without making up a number
description: AI models are good with words and bad with numbers. Here's how Bondi uses an on-device model to write the sentence while every figure stays measured and checked.
date: 2026-10-03
status: draft
order: 10
image: /assets/features/why.png
related: bondi-vs-activity-monitor, why-is-my-mac-slow, memory-pressure-explained
---
Bondi uses an AI model to write one plain sentence about your Mac, but the AI never decides a single number. Bondi measures everything itself, decides what matters, lets the model write the words around those facts, and then checks the sentence before you see it.

## The problem with asking an AI about your Mac

Language models are good at sounding right. That's exactly the risk with system monitoring: a confident sentence with a wrong number ("Chrome is using 4 GB" when it's using 12) is worse than no sentence at all. And sending your process list to a cloud model to ask means your apps and habits leave your Mac.

So I set two rules for Bondi's AI: it runs on your Mac, and it never gets to make up a number.

## How a sentence gets written

**1. Bondi measures.** Every figure is worked out in code from your Mac's own readings: memory pressure, which app is using what (with all its helper processes added up), CPU, disk, battery and more.

**2. Bondi picks the point.** Bondi decides what the sentence is about: a problem, the disk, the battery, something sitting idle, or all calm. It also picks the tone, for example a gentle note.

**3. The on-device model writes.** On Macs with Apple Intelligence, Bondi uses Apple's on-device foundation model, built into macOS and running on your Mac's Apple silicon. Other Macs can download a small free open model (Qwen2.5 0.5B) from Bondi's Settings. Either way the model only adds the plain English; the facts it's given are Bondi's.

**4. Bondi checks it.** Before you see the sentence, Bondi checks it: wrong tone, wrong subject, or any number that doesn't match what Bondi measured, and Bondi throws it away and uses its own sentence instead.

## What it looks like

On a calm moment on the Mac I measure on, Bondi wrote:

![Everything is fine. Memory pressure is Normal, with 44.73 GB of 64.00 GB used. Google Chrome is using the most memory, 13.09 GB across 82 processes.](/assets/features/why.png)
*Every number here was measured by Bondi; the model only wrote the words around them.*

And when a forgotten dev server was holding memory: *"A dev server is sitting idle. Vite in shop has been idle for 3h 20m and holds 612.0 MB."*

## Asking about the past

The same rules apply when you ask Bondi a question about its 30 days of history. Bondi looks up the figures in the history file on your Mac, and the model phrases the answer. Asked "What slowed my Mac yesterday?", Bondi answered:

> Yesterday, memory used averaged 35.29 GB and peaked at 54.26 GB at 1:51 PM, and the CPU averaged 19.8% and peaked at 40.0% at 7:02 PM. Google Chrome used the most memory, 9.63 GB on average, then macOS (6.39 GB) and Visual Studio Code (3.48 GB).

## What leaves your Mac

Nothing about your apps or your usage. The model runs on your Mac, offline; your history stays in a file on your Mac. Bondi only contacts our server to check its trial or license and to look for updates. The details are in the [privacy policy](/privacy/).
