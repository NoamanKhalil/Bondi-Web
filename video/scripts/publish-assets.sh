#!/bin/bash
# Makes the website's copies of the rendered films and their posters:
# - the films re-encoded for the web (x264 veryslow, aq-mode 3, fast start): the same picture at about half the size
#   (VMAF about 95 against the render, checked 2026-10-04), with the render's bt709 colour tags;
# - phone copies at 1280 × 720 (-small), which the page picks on screens up to 700 px wide;
# - JPEG posters (smaller than PNG).
# The 24-second film is already small as rendered (CRF 26), so only its phone copy is made here.
set -euo pipefail
cd "$(dirname "$0")/.."
ASSETS=../public_html/assets
web() { # source crf output [filter]
  ffmpeg -v error -y -i "$1" -an ${4:+-vf "$4"} -c:v libx264 -preset veryslow -crf "$2" -pix_fmt yuv420p -profile:v high \
    -x264-params aq-mode=3 -movflags +faststart "$3"
}
for name in bondi-stars bondi-window; do
  [ -f "out/$name.mp4" ] || { echo "Missing out/$name.mp4: run npm run render:all first."; exit 1; }
  sips -s format jpeg -s formatOptions 80 "out/$name-poster.png" --out "$ASSETS/$name-poster.jpg" >/dev/null
done
web out/bondi-window.mp4 27 "$ASSETS/bondi-window.mp4" &
web out/bondi-stars.mp4 28 "$ASSETS/bondi-stars.mp4" &
web out/bondi-stars.mp4 25 "$ASSETS/bondi-stars-small.mp4" "scale=1280:720:flags=lanczos" &
web "$ASSETS/bondi-film.mp4" 25 "$ASSETS/bondi-film-small.mp4" "scale=1280:720:flags=lanczos" &
wait
ls -la "$ASSETS"/bondi-stars* "$ASSETS"/bondi-window* "$ASSETS"/bondi-film*
echo "Now run: php ../dev/build-site.php"
