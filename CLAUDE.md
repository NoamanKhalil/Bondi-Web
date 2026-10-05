# CLAUDE.md: Bondi-Web

Instructions for Claude Code in this repo: everything for **trybondi.app**, the website of Bondi (a native macOS system monitor by Jabble Super Intelligence Inc.).

The Mac app lives in its own repo next to this one: **`../TryBondi`** (readable from here; see `.claude/settings.json`). Its `CLAUDE.md` and `docs/PLAN.md` are the source of truth for the product: features, wording, colours and owner decisions. Read them when a web change touches what the app does or looks like, and don't change files there from this repo.

## What's here

| Folder | What it is | On Hostinger |
| --- | --- | --- |
| `public_html/` | The website's readable source (`index.html`), the license API (`api/`), the admin page (`admin/`), the buy page (`buy/`, not linked until launch), legal pages, `assets/` | Hostinger's Git deploy serves this repository's top level: `php dev/build-site.php` copies `public_html` there with CSS minified and the films' addresses version-stamped. `/public_html/…` redirects to the real pages |
| `bondi/` | Private PHP: settings (`config.php`, never committed), database, Paddle, email, licenses, sign-ups | Live at `public_html/bondi/` (the owner's host wants everything in `public_html`); its `.htaccess` blocks web access. Beside `public_html` also works. After an upload, check `https://trybondi.app/bondi/config.php` answers 403 |
| `sql/` | Database setup, imported in phpMyAdmin in order (001, 003, 004, 005, 006; 002 only for old installs) | — |
| `dev/` | Local tests: `dev/test.sh` (a throwaway MySQL and PHP server, 110 checks) | Not uploaded |
| `video/` | The website films, made with Remotion. Use the `website-video` skill | Renders go to `public_html/assets` |

`README.md` has the Hostinger and Paddle setup steps.

## How to work

- The owner is a product manager, not a developer. Explain changes in plain words and say exactly which files to upload to Hostinger (and which to delete).
- Work on `main`. Commit small, working steps with clear messages. Never force-push. Never commit secrets (`bondi/config.php`).
- Before a large change, say the plan in a few lines and wait for approval.
- Run `dev/test.sh` after any change to `bondi/`, `api/`, `admin/` or `sql/`; commit only when it passes.
- After changing anything in `public_html`, run `php dev/build-site.php` before committing (`dev/build-blog.php` runs it too). Never hand-edit the top-level copies.
- Films load only near the screen (`preload="none"`, `data-lazy`; phones get the 1280 × 720 `-small` copies via `data-small`) and are cached for a year by address: refer to them in HTML attributes (`src`, `data-small`, `poster`), never build their URLs in scripts, so `build-site.php` can stamp them. `npm run assets` in `video/` compresses new renders (see the website-video skill).
- Check page changes by rendering them (headless Chrome, `--user-data-dir` in the scratchpad) and looking at the screenshots before saying they're done. Never run screen recording, `osascript` or System Events without asking.

## Preview

The owner reviews the site as a private claude.ai artifact: **https://claude.ai/artifact/CkKQtAkApitweCdYVSNigD**. To update it, copy `public_html/index.html` into the scratchpad with the `<!doctype>`, `<html>`, `<head>` and `<body>` tags stripped, and publish that file to the same URL, passing any new or changed `assets/...` files in `files` (removed ones as `null`).

## Product rules that apply to the site

- **Real numbers only.** Figures, app lists and films come from real readings of the owner's Mac (Bondi's engine, `--dump-engine` in the app's Debug build). Where something is an example (demo rows), say so.
- **Privacy:** no analytics, no tracking, no third-party lookups of visitors. The sign-up form stores name, email, IP and country (from the IP via `bondi/ip-country-*.bin`, refreshed with `php dev/update-ip-country.php`; else the browser's time zone) and the privacy policy says so. Keep the policy in step with anything new the site collects.
- **Pre-launch:** "Beta launching soon, full launch soon after." No buy buttons until the owner says so. Price: $6.99 for the first 250, then $29.99; 1 Mac per license; 7-day trial (at launch). Paddle is the merchant of record.
- **Your data and unsubscribing** (GDPR/CCPA, `bondi/lib/privacy.php`): every list email has an Unsubscribe link and Gmail's one-click header; `/your-data/` emails a one-time link to see, download (JSON) or delete what we hold (purchase records stay, for tax). Each unsubscribe or deletion gets a confirmation number (BR-XXXXX-XXXX) in `data_requests`, which keeps a keyed fingerprint of the email (never the email) for 3 years. After someone leaves, only a confirmation email, never marketing; win-back lives on the web page (undo). Not directed at children (13; 16 in the EU/UK).
- Legal pages are drafts; "[company mailing address]" still needs Jabble Super Intelligence Inc.'s address, and a lawyer should review them.
- Don't copy any asset, text or layout detail from Vitals or any other app. Wording "Bondi for Apple Mac" is the owner's choice.

## AI assistants and search

- `llms.txt` (short) and `llms-full.txt` (everything, with the app's 125 process notes). Rebuild `llms-full.txt` with `php dev/build-llms-full.php` when the site's facts or `../TryBondi/Bondi/Resources/ProcessGuide.json` change.
- "Ask AI about Bondi" in every footer (a column with logos and names on the homepage, a row of logos elsewhere): plain links to ChatGPT, Claude, Perplexity, Google AI Mode and Grok that open with a prepared question. The question and the list live in `dev/ask-ai.php`; run it to update every page. Logos: `dev/ai-logos` (Lobe Icons, MIT), drawn inline.
- Competitors named (owner): Activity Monitor, iStat Menus, Stats and Vitals. Re-check what's said about them, and that each Ask-AI link still opens its assistant, every few months.
- **Pitch line, word for word everywhere:** "Bondi is a system monitor for Mac that tells you why it's slow, in one plain sentence." It's in the homepage hero, meta and JSON-LD, the beta page, llms.txt, llms-full.txt and every article's closing box (`dev/build-blog.php`). Change them all together.
- **Comparison table** (homepage `#vs`, llms.txt, llms-full.txt): only facts from each app's own website, with the date checked; "—" means not found there. Vitals also groups processes into apps, keeps 30 days of history, finds dev servers and sets fan speed: say so. Checked 2026-10-04: iStat Menus $11.99 once, Stats free (MIT), Vitals $29 once.
- Homepage "Mac acting up?" (`#help`) answers the top questions in a line each and links to the guides; keep each answer in step with its article's opening paragraph. The FAQ leads with "Why is my Mac slow?".
- Every new sign-up gets a thank-you email from Noaman (`welcome_email()` in `bondi/lib/mail.php`, sent once per email, logged as `welcome`); the admin sign-ups page sends it to anyone who hasn't had it. Noaman's X: @khalilnoaman (`MAKER_X`), also in every footer.
- Homepage search tags target "why is my Mac slow", "system monitor for Mac", "Activity Monitor alternative" and "task manager for Mac". Questions on the site use the wording Google autocomplete shows people typing (checked 2026-10-04): "Why is my Mac so slow?", "so hot and loud", "kernel_task / WindowServer high CPU", "yellow memory pressure", "Spotlight indexing", "MacBook". Check new wording the same way before using it. Its JSON-LD has SoftwareApplication and an FAQPage copied word for word from the FAQ section: change both together.
- **Blog** (`/blog/`): articles are Markdown in `content/blog/` (header: title, description, date, topic, status draft|published, order, image, related). Topics (`TOPICS` in the build script: slow, memory, heat, processes, bondi) group the index and llms.txt. `php dev/build-blog.php` publishes the `published` ones (pages, index, RSS, sitemap and llms.txt entries, top-level copy); `--preview DIR` builds drafts too. Articles carry the owner's byline, so they go live only after he approves them; numbers come from real readings only. First 10 drafted 2026-10-03.
- Guide pages (process pages from the app's guide, topic guides, comparisons): later, one page at a time, as the owner decides.

## Design

- Apple's design language: SF Pro (the system font), large tight headlines, dark theme. Page background `#131315`; cards `#161617`; Bondi Blue `#2cc0de` (ink `#5fd4ea`).
- The demo window is a replica of the app's main window with the app's own colours: take them from `../TryBondi/Bondi/Resources/Colors.xcassets` (dark values), named as in the app's `docs/PLAN.md`. Its content area is 16:9, every tab keeps one height, nothing scrolls inside it. Overview = AI summary, At a glance, Right now. `beta/` has a copy of it (styles, markup, intro-film and demo scripts): change both together.
- App icons: `../TryBondi/Bondi-Icons/` (Black default, White, Bondi Blue) and `../TryBondi/Bondi/Resources/Assets.xcassets`. Other apps' icons are exported from the Mac with `NSWorkspace` (see `video/scripts/refresh-reading.swift`).
- Screens to replicate live in `../TryBondi/Bondi/UI/` (MenuBar, Window, Settings, Components). The app's Debug build can render real screens without screen recording: `--render-screenshots DIR` and `--render-feature-shots DIR`.
- Support light and dark mode, Reduce Motion, keyboard and screen readers; phone width with no sideways scroll.
- Light/dark switch: `assets/theme.js` (loaded in every page's head) and the moon/sun `data-theme-toggle` button; it sets `<html data-theme>`. The homepage's window replica and feature tiles use named colours with the app's light values; screenshots and films stay dark until the app's light mode (ES-12) exists.
- In-page links scroll without adding `#section` to the address.
