# iOS Signing Guide for om_connect

This guide covers the Apple Developer credentials required to build and
distribute the `om_connect` Flutter app for iOS.

## Required Apple Developer Account

- An active **Apple Developer Program** membership (Individual or
  Organization). The free account is not sufficient for device installs.

## Certificates

Create the following certificates in the Apple Developer portal and export
them as `.p12` files:

1. **iOS Distribution** (for App Store / TestFlight / Ad Hoc installs)
   - Type: iOS Distribution
   - Export as `DistributionCertificate.p12`

2. **Apple Development** (for development / debug builds on real devices)
   - Type: iOS Development
   - Export as `DevelopmentCertificate.p12`

3. **Apple Distribution** (for App Store distribution, if using App Store
  Connect with automatic signing)
   - Type: Apple Distribution
   - Export as `AppStoreDistributionCertificate.p12`

## Provisioning Profiles

Create the following profiles in the Apple Developer portal and export them
as `.mobileprovision` files:

1. **App Store** profile (bundle ID: `com.omconnect.omConnect`)
   - For App Store submission.

2. **Ad Hoc** profile (bundle ID: `com.omconnect.omConnect`)
   - For distributing the IPA to registered test devices.

3. **Development** profile (bundle ID: `com.omconnect.omConnect`)
   - For development builds on real devices.

## Local Setup (macOS)

Option A — Manual install (Xcode):

1. Open **Keychain Access**.
2. Double-click each `.p12` file to import the certificate.
3. Double-click each `.mobileprovision` file to install the profile.

Option B — Fastlane match (recommended for CI):

```bash
# Install fastlane
brew install fastlane

# Initialize match
cd ios
fastlane match init
# Edit Fastfile to configure match for app_store, adhoc, development
fastlane match development --force
fastlane match adhoc --force
fastlane match appstore --force
```

## CI Setup (GitHub Actions)

The `.github/workflows/ios-build.yml` workflow uses the built-in macOS runner
and the `subosito/flutter-action` action. It does **not** handle signing
automatically. To sign the build in CI, choose one of the following:

1. **Manual signing** — store the `.p12` and `.mobileprovision` files as
   encrypted repository secrets and install them in the workflow step before
   `flutter build ios`.

2. **Fastlane match** — store the match certificates in a private repository
   (or encrypted repository secrets) and call `fastlane match` from the
   workflow.

3. **Xcode Cloud / App Store Connect** — use Apple's hosted CI for signing
   and distribution.

## Bundle Identifier

The project is configured with:

- **Bundle ID**: `com.omconnect.omConnect`
- **Development Team**: (not set — configure in `ios/Runner.xcodeproj` or
  Xcode Signing & Capabilities)

If you change the bundle ID, update it in:
- `ios/Runner.xcodeproj/project.pbxproj` (`PRODUCT_BUNDLE_IDENTIFIER`)
- `ios/Runner/Info.plist` (if overridden)

## Build Outputs

- **Debug**: `build/ios/iphoneos/Runner.app` (device build)
- **Profile**: `build/ios/iphoneos/Runner.app` (device build)
- **Release**: `build/ios/iphoneos/Runner.app` (device build)

The local helper `build_ios.sh` and the CI workflow package the `.app`
bundle into `Payload/Runner.app` and zip it as `om_connect-<type>.ipa`.

## Distributing the IPA

- **Ad Hoc**: install on devices registered in the Apple Developer portal.
- **TestFlight**: upload the IPA via App Store Connect or `fastlane pilot`.
- **App Store**: submit via App Store Connect or `fastlane deliver`.