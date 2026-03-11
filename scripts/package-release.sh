#!/usr/bin/env bash
set -euo pipefail

VERSION="${1:-dev}"
ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BUILD_DIR="$ROOT_DIR/dist"
PKG_DIR="$BUILD_DIR/tacticalrmmremote"

rm -rf "$BUILD_DIR"
mkdir -p "$PKG_DIR"

rsync -a           --exclude '.git'           --exclude '.github'           --exclude 'dist'           --exclude '*.tar.gz'           --exclude '*.zip'           "$ROOT_DIR/" "$PKG_DIR/"

sed -i.bak "s/define('PLUGIN_TACTICALRMMREMOTE_VERSION', '[^']*');/define('PLUGIN_TACTICALRMMREMOTE_VERSION', '${VERSION#v}');/" "$PKG_DIR/setup.php"
rm -f "$PKG_DIR/setup.php.bak"

(
  cd "$BUILD_DIR"
  tar -czf tacticalrmmremote.tar.gz tacticalrmmremote
  zip -qr tacticalrmmremote.zip tacticalrmmremote
)

echo "Built:"
echo "  $BUILD_DIR/tacticalrmmremote.tar.gz"
echo "  $BUILD_DIR/tacticalrmmremote.zip"
