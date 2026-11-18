#!/usr/bin/env bash
#
# Local iOS build helper for om_connect.
# Run this on macOS (requires Xcode + Flutter).
#
# Usage:
#   ./build_ios.sh release
#   ./build_ios.sh profile
#   ./build_ios.sh debug
#
# The script packages the built .app bundle into a Payload/*.ipa zip.

set -euo pipefail

BUILD_TYPE="${1:-release}"
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
ARTIFACT_DIR="${PROJECT_DIR}/artifacts"

echo "==> Building om_connect for iOS (${BUILD_TYPE}) in ${PROJECT_DIR}"

cd "${PROJECT_DIR}"

echo "==> flutter pub get"
flutter pub get

echo "==> Running code generation (build_runner)"
flutter pub run build_runner build --delete-conflicting-outputs

echo "==> flutter analyze"
flutter analyze --no-fatal-infos || true

echo "==> flutter test"
flutter test --no-pub

echo "==> Building iOS (${BUILD_TYPE})"
case "${BUILD_TYPE}" in
  debug)
    flutter build ios --debug --no-pub
    ;;
  profile)
    flutter build ios --profile --no-pub
    ;;
  release)
    flutter build ios --release --no-pub
    ;;
  *)
    echo "Unknown build type: ${BUILD_TYPE}" >&2
    exit 1
    ;;
esac

echo "==> Packaging IPA"
APP_PATH="build/ios/iphoneos/Runner.app"
if [ ! -d "${APP_PATH}" ]; then
  echo "Error: built app not found at ${APP_PATH}" >&2
  exit 1
fi

rm -rf "${ARTIFACT_DIR}"
mkdir -p "${ARTIFACT_DIR}/Payload"
cp -R "${APP_PATH}" "${ARTIFACT_DIR}/Payload/Runner.app"

IPA_NAME="om_connect-${BUILD_TYPE}.ipa"
(
  cd "${ARTIFACT_DIR}"
  zip -r "${IPA_NAME}" Payload
)

echo "==> IPA created: ${ARTIFACT_DIR}/${IPA_NAME}"
ls -lh "${ARTIFACT_DIR}/${IPA_NAME}"