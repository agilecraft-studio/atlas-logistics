#!/usr/bin/env bash
set -euo pipefail

NEW_NAME="Atlas Logistics"
NEW_URL="https://app.atlaslogistics.co.mw"   # adjust if you want a different link

# Files to process: text files only, skipping heavy/generated dirs
mapfile -d '' FILES < <(grep -rIlZiE "hyvikk" . \
  --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=.git \
  --exclude-dir=storage --exclude-dir=cache --exclude=rebrand.sh)

for f in "${FILES[@]}"; do
  sed -i \
    -e "s#https\?://\(www\.\)\?hyvikk\.com/\?#${NEW_URL}#gI" \
    -e "s#Hyvikk Solutions#${NEW_NAME}#gI" \
    -e "s#Hyvikk#${NEW_NAME}#gI" \
    "$f"
  echo "Updated: $f"
done
