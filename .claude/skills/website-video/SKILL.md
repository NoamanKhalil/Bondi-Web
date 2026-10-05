---
name: website-video
description: Update, re-render or re-place the trybondi.app films made with Remotion (the star film, the intro film over the website's app window, the 24-second film). Use when the owner asks to change, refresh, re-time, recolour or re-shoot any website video or animation, to take a new reading of the Mac for them, or to change how a film plays on the page.
---

# Website films (Remotion)

Bondi's website has three films, all made in `video/` of this repo (Bondi-Web) with Remotion 4. The app itself is in the TryBondi repo next to this one (`../TryBondi`).

| Film | Composition | Output | On the page |
| --- | --- | --- | --- |
| Window intro | `WindowFilm` (1920×1080, **60 fps**, 15 s; also 2560×1440) | `bondi-window.mp4` + `-hd.mp4` (Retina, wide windows) + `-poster.jpg` | Over the app window (`#app`), plays once at 60% in view |
| Star film | `Constellation` (1920×1080, 30 fps, 15 s) | `bondi-stars.mp4` + `-small.mp4` (phones) + `-poster.jpg` | Section `#stars`, above the comparison, loops while on screen |
| 24-second film | `BondiFilm` (1920×1080, 30 fps, 24 s) | `bondi-film.mp4` + `-small.mp4` (phones) + `-poster.png` | Section `#film`, loops while on screen |

The window intro and the star film are the same scene (`src/Constellation.tsx`): every process on the Mac is a star, the stars swirl into the apps they belong to and become each app's real icon, sized by process count, with the app's own colour glowing under it. Headlines: "955 processes." → "43 apps you know." → end card "Bondi for Apple Mac · Beta launching soon · trybondi.app".

## Rules (owner decisions; keep them)

- **Real data only.** Every count comes from Bondi's engine on the owner's Mac, every icon is the app's own Finder icon. Never type numbers in by hand; refresh the reading instead.
- **Colour:** always render with `--color-space=bt709` (untagged video looks washed out in browsers). Browsers still colour-manage video slightly differently from page CSS, so never rely on a video's background matching a page colour exactly. The window intro therefore uses the film's own teal-to-black backdrop, and the app window's content area uses the same CSS gradient: `radial-gradient(ellipse at 50% 60%, #0b1a20 0%, #000 70%)`.
- **Window intro:** 60 fps ("slightly smooth"), standard widescreen, 15 s, no fade from or to black (its first frame is the poster; the page does the fades). It holds on the end card.
- **The app window's content area is 16:9** so the intro fills it exactly (`.win .win-body` min-height in `index.html`). Every tab keeps one height; nothing scrolls inside the window. Overview = AI summary, At a glance, Right now (no Ask box; Ask has its own feature tile).
- **Size on the page:** `npm run assets` re-encodes the renders for the web (x264 veryslow, aq-mode 3: window CRF 27, stars CRF 28; VMAF about 95, half the size or less, colour tags kept) and makes 1280 × 720 phone copies (`-small`, CRF 25) that the page uses on screens up to 700 px wide. The window intro also has a 2560 × 1440 copy (`-hd`, rendered with `--scale=4/3`, same CRF), which the page uses when the window would show the 1920 film more than 10% stretched (window width × device pixel ratio > 2,112), that is, on Retina screens with a wide window. Render both from the same reading. The films load only when needed (`preload="none"`): the intro when it's about to play, the others as they come near the screen; they pause off screen. Refer to films only in HTML attributes (`src`, `data-small`, `poster`) so `dev/build-site.php` can stamp their addresses for the year-long cache.
- **Playback on the page:** waits on the dimmed poster; starts when 60% of the window is in view (or it fills 60% of a short screen); fades in and out slowly with easing (1.6 s); Skip intro (top right) and "▶ Replay intro" under the window; skipped with Reduce Motion and on windows narrower than 700 px.
- Remove nothing the owner asked for (the "+11" tile was removed on request: apps without an icon are stars that fade out).
- "Bondi for Apple Mac" is the owner's wording. Apple's guidelines prefer "for Mac"; mention it once if it comes up, don't change it.

## Update the films with a fresh reading

1. Build Bondi (Debug) in the TryBondi repo: `⇧⌘B` in VS Code there, or the build command in its `CLAUDE.md`. The reading script finds it at `../TryBondi` (or set `BONDI_APP_REPO`).
2. `cd video && npm run reading`
   Runs `Bondi --dump-engine`, then `scripts/refresh-reading.swift`, which writes `src/reading.json` (counts, the 32 biggest apps that have an app bundle, each icon's colour, process names) and exports `public/icons/*.png` (real icons, 256 px, plus Bondi's own "macOS" tile). `public/icons/bondi-blue.png` (end card) is kept.
3. `npm run studio` to preview (opens Remotion Studio in the browser).
4. `npm run render:all` renders the star film and the window intro (1920 × 1080 and 2560 × 1440), with posters.
   The 24-second film: `npm run render` (its data is in `src/data.ts`, typed by hand from a reading; update it from the same dump if its numbers must match).
5. `npm run assets` makes the web and phone copies of the films in `public_html/assets` and the JPEG posters. Then `php ../dev/build-site.php` (stamps the films' addresses).
6. Check before showing the owner (see Verify).
7. Publish the preview and commit (see Publish).

## Change the look or timing

- Scene: `src/Constellation.tsx`. Timeline `T` is in frames at 30 fps (`gatherStart` 78, `gatherLength` 92, `iconsStart` 158, `swapHeadline` 170, `countsStart` 214, `outroStart` 340). At 60 fps the component converts frames, so edit timings in 30 fps frames.
- Icon size: `sizeFor(processes) = 62 + 24·log2(processes)`; layout packs icons along a spiral from the middle, stretched to the frame's shape.
- Backgrounds and fades are props: `background`, `fadeFromBlack`, `fadeToBlack`, `endCard` (see `src/Root.tsx`).
- Film fonts: the system font (SF Pro) through `-apple-system`.
- Don't put a `{/* comment */}` or `//` comment inside a JSX tag's props; it breaks the render.

## Verify

- `npx tsc --noEmit -p .` in `video`.
- Stills: `npx remotion still src/index.ts WindowFilm /tmp/f.png --frame=560 --scale=0.5` (frames 0, 300, 560, 880 cover the sky, the gathering, the icons and the end card). Look at them.
- Colour tags: `npx remotion ffprobe -v error -show_entries stream=pix_fmt,color_space,color_primaries,r_frame_rate,duration -of compact out/bondi-window.mp4` → `yuv420p`, `bt709`, `60/1`, `15.000000`.
- Headless Chrome can't decode these H.264 files for pixel checks and only reports IntersectionObserver once at load, so test the page's trigger and fades by reasoning plus events (dispatch `ended`, click Skip and Replay) and ask the owner to watch it once in Safari and Chrome.
- Page checks worth running: every tab of the window has the same height (click each `[data-wtabs] button` and measure `.win`), the intro area is 16:9 (`[data-intro]` width/height ≈ 1.777), no script errors.

## Publish

- Preview (claude.ai artifact `https://claude.ai/artifact/CkKQtAkApitweCdYVSNigD`): strip `<!doctype>`, `<html>`, `<head>`, `<body>` tags from `public_html/index.html` into the scratchpad copy, then publish with `files` mapping any new or changed `assets/...` paths to the files in `public_html/assets` (removed files: map to `null`).
- Commit on `main` in Bondi-Web with a plain message; push. `video/out` and `node_modules` are gitignored.
- Tell the owner which files to upload to Hostinger (`public_html/index.html` and the changed `assets/*`) and which to delete.

## Files

- `video/src/Constellation.tsx` scene; `src/constellation-data.ts` reads `src/reading.json`; `src/Root.tsx` compositions; `src/Film.tsx` + `src/data.ts` the 24-second film.
- `video/scripts/refresh-reading.sh` / `.swift` the reading; `scripts/publish-assets.sh` copies renders to the site.
- `public_html/index.html`: `.win-intro` CSS and the "Intro film over the app window" script; `#stars` section; `#film` section.
