# CLAUDE.md: Bondi-Web

Instructions for Claude Code in this repo: everything for **trybondi.app**, the website of Bondi (a native macOS system monitor by Jabble Inc.).

The Mac app lives in its own repo next to this one: **`../TryBondi`** (readable from here; see `.claude/settings.json`). Its `CLAUDE.md` and `docs/PLAN.md` are the source of truth for the product: features, wording, colours and owner decisions. Read them when a web change touches what the app does or looks like, and don't change files there from this repo.

## What's here

| Folder | What it is | On Hostinger |
| --- | --- | --- |
| `public_html/` | The website (`index.html`), the license API (`api/`), the admin page (`admin/`), the buy page (`buy/`, not linked until launch), legal pages, `assets/` | Contents go into Hostinger's `public_html` |
| `bondi/` | Private PHP: settings (`config.php`, never committed), database, Paddle, email, licenses, sign-ups | `domains/trybondi.app/bondi/`, **beside** `public_html`, never inside it |
| `sql/` | Database setup, imported in phpMyAdmin in order (001, 003, 004; 002 only for old installs) | — |
| `dev/` | Local tests: `dev/test.sh` (a throwaway MySQL and PHP server, 51 checks) | Not uploaded |
| `video/` | The website films, made with Remotion. Use the `website-video` skill | Renders go to `public_html/assets` |

`README.md` has the Hostinger and Paddle setup steps.

## How to work

- The owner is a product manager, not a developer. Explain changes in plain words and say exactly which files to upload to Hostinger (and which to delete).
- Work on `main`. Commit small, working steps with clear messages. Never force-push. Never commit secrets (`bondi/config.php`).
- Before a large change, say the plan in a few lines and wait for approval.
- Run `dev/test.sh` after any change to `bondi/`, `api/`, `admin/` or `sql/`; commit only when it passes.
- Check page changes by rendering them (headless Chrome, `--user-data-dir` in the scratchpad) and looking at the screenshots before saying they're done. Never run screen recording, `osascript` or System Events without asking.

## Preview

The owner reviews the site as a private claude.ai artifact: **https://claude.ai/artifact/CkKQtAkApitweCdYVSNigD**. To update it, copy `public_html/index.html` into the scratchpad with the `<!doctype>`, `<html>`, `<head>` and `<body>` tags stripped, and publish that file to the same URL, passing any new or changed `assets/...` files in `files` (removed ones as `null`).

## Product rules that apply to the site

- **Real numbers only.** Figures, app lists and films come from real readings of the owner's Mac (Bondi's engine, `--dump-engine` in the app's Debug build). Where something is an example (demo rows), say so.
- **Privacy:** no analytics, no tracking, no third-party lookups of visitors. The sign-up form stores name, email, IP and country (from a CDN header or the browser's time zone) and the privacy policy says so. Keep the policy in step with anything new the site collects.
- **Pre-launch:** "Beta launching soon, full launch soon after." No buy buttons until the owner says so. Price: $6.99 for the first 250, then $29.99; 1 Mac per license; 7-day trial (at launch). Paddle is the merchant of record.
- Legal pages are drafts; "[company mailing address]" still needs Jabble Inc.'s address, and a lawyer should review them.
- Don't copy any asset, text or layout detail from Vitals or any other app. Wording "Bondi for Apple Mac" is the owner's choice.

## Design

- Apple's design language: SF Pro (the system font), large tight headlines, dark theme. Page background `#131315`; cards `#161617`; Bondi Blue `#2cc0de` (ink `#5fd4ea`).
- The demo window is a replica of the app's main window with the app's own colours: take them from `../TryBondi/Bondi/Resources/Colors.xcassets` (dark values), named as in the app's `docs/PLAN.md`. Its content area is 16:9, every tab keeps one height, nothing scrolls inside it. Overview = AI summary, At a glance, Right now.
- App icons: `../TryBondi/Bondi-Icons/` (Black default, White, Bondi Blue) and `../TryBondi/Bondi/Resources/Assets.xcassets`. Other apps' icons are exported from the Mac with `NSWorkspace` (see `video/scripts/refresh-reading.swift`).
- Screens to replicate live in `../TryBondi/Bondi/UI/` (MenuBar, Window, Settings, Components). The app's Debug build can render real screens without screen recording: `--render-screenshots DIR` and `--render-feature-shots DIR`.
- Support light and dark mode, Reduce Motion, keyboard and screen readers; phone width with no sideways scroll.
- In-page links scroll without adding `#section` to the address.
