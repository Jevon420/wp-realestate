#!/usr/bin/env bash
# Builds installable zips for the fits-properties-core plugin and the
# fits-properties theme, ready to attach to a GitHub Release.
#
# Usage: bin/build-release.sh
# Output: dist/fits-properties-core.zip, dist/fits-properties-theme.zip

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

rm -rf dist
mkdir -p dist

(cd plugins && zip -rq "$ROOT_DIR/dist/fits-properties-core.zip" fits-properties-core \
    -x "*.DS_Store" -x "fits-properties-core/.gitignore")

(cd themes && zip -rq "$ROOT_DIR/dist/fits-properties-theme.zip" fits-properties \
    -x "*.DS_Store" -x "fits-properties/.gitignore")

echo "Built:"
echo "  dist/fits-properties-core.zip"
echo "  dist/fits-properties-theme.zip"
