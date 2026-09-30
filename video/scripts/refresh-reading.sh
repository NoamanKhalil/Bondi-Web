#!/bin/bash
# Takes a fresh reading of this Mac with Bondi's engine and rewrites the films' data from it:
# src/reading.json (counts, apps, icon colours, process names) and public/icons/*.png (real app icons).
# Needs the Debug build of Bondi from the TryBondi repo (⇧⌘B there, or the build command in its CLAUDE.md).
set -euo pipefail
cd "$(dirname "$0")/.."
# The app lives in its own repo, next to this one (override with BONDI_APP_REPO=/path/to/TryBondi).
APP_REPO="${BONDI_APP_REPO:-$(cd ../.. && pwd)/TryBondi}"
BONDI="$APP_REPO/build/Build/Products/Debug/Bondi.app/Contents/MacOS/Bondi"
[ -x "$BONDI" ] || { echo "No Debug build of Bondi at $BONDI. Build it in the TryBondi repo first (⇧⌘B)."; exit 1; }
DUMP="$(mktemp -t bondi-dump)"
"$BONDI" --dump-engine > "$DUMP" 2>/dev/null
swift scripts/refresh-reading.swift "$DUMP" "$PWD/public/icons" "$PWD/src/reading.json"
rm -f "$DUMP"
echo "Updated src/reading.json and public/icons. Preview with: npm run studio"
