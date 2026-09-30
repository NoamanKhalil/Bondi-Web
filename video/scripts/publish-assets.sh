#!/bin/bash
# Copies the rendered films into the website and makes their posters (JPEG, smaller than PNG).
set -euo pipefail
cd "$(dirname "$0")/.."
ASSETS=../public_html/assets
for name in bondi-stars bondi-window; do
  [ -f "out/$name.mp4" ] || { echo "Missing out/$name.mp4: run npm run render:all first."; exit 1; }
  cp "out/$name.mp4" "$ASSETS/$name.mp4"
  sips -s format jpeg -s formatOptions 80 "out/$name-poster.png" --out "$ASSETS/$name-poster.jpg" >/dev/null
done
ls -la "$ASSETS"/bondi-stars* "$ASSETS"/bondi-window*
