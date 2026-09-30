#!/bin/bash
# Takes a fresh reading of this Mac with Bondi's engine and rewrites the films' data from it:
# src/reading.json (counts, apps, icon colours, process names) and public/icons/*.png (real app icons).
# Needs the Debug build of Bondi (⇧⌘B in VS Code, or the build command in CLAUDE.md).
set -euo pipefail
cd "$(dirname "$0")/.."
ROOT="$(cd ../.. && pwd)"
BONDI="$ROOT/build/Build/Products/Debug/Bondi.app/Contents/MacOS/Bondi"
[ -x "$BONDI" ] || { echo "No Debug build of Bondi at $BONDI. Build it first (⇧⌘B)."; exit 1; }
DUMP="$(mktemp -t bondi-dump)"
"$BONDI" --dump-engine > "$DUMP" 2>/dev/null
swift scripts/refresh-reading.swift "$DUMP" "$PWD/public/icons" "$PWD/src/reading.json"
rm -f "$DUMP"
echo "Updated src/reading.json and public/icons. Preview with: npm run studio"
