# Recommended Laravel Stack

## Authentication

### Laravel Sanctum

**Best for:**
- Mobile auth
- API tokens
- SPA/API auth

> Avoid Passport unless you truly need OAuth2.

## Roles & Permissions

### Spatie Laravel Permission

**Perfect for:**
- Shareholders
- Pharmacies
- Doctors
- Admins
- Reviewers

## Admin Panel

### Option 1 (BEST): FilamentPHP

**Why:**
- Incredibly fast development
- Queues
- Dashboards
- Tables
- Forms
- Review queues
- Analytics panels

**This is PERFECT for:**
- OCR review queues
- Approvals
- Reward management
- Pharmacy management

### Option 2: Laravel Nova

- Good but paid
- Filament is probably the better choice here

## Queue System

**Critical for your project.**

Use:
- Laravel Queues → Redis → Horizon

**Install:**
- Laravel Horizon

**Perfect for:**
- OCR jobs
- Notifications
- Reward calculations
- Analytics jobs
- Forecasting jobs

## Search + Product Matching

**Later add:**
- Laravel Scout with Meilisearch

**This becomes very useful for:**
- Medicine matching
- OCR fuzzy matching
- Product search
- Typo handling

## Recommended Infrastructure

### Storage

Use S3-compatible object storage.

**Good options:**
- AWS S3
- Cloudflare R2
- DigitalOcean Spaces

**Store:**
- Bills
- OCR images
- Prescriptions

> NOT local storage long-term.

## Recommended API Architecture

Use:
- Service classes
- DTOs
- API Resources
- Queued actions

**Avoid:**
- Fat controllers
- OCR logic in controllers
- Giant repository overengineering

## VERY IMPORTANT FOR YOUR PROJECT

**Use AI ONLY AFTER OCR**

| Correct | Wrong |
|---------|-------|
| OCR → structured parsing → confidence scoring → AI enrichment | image → AI directly |

**The second becomes:**
- Expensive
- Inconsistent
- Harder to audit
- Difficult for compliance

---

## Final Recommendation

For ABC Connect:

### Backend
- [Laravel](https://laravel.com)
- [Laravel Horizon](https://laravel.com/docs/horizon)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)

### Mobile
- [React Native](https://reactnative.dev)
- [React Native Vision Camera](https://mroushey.com/react-native-vision-camera)

### OCR
- [Google Vision OCR](https://cloud.google.com/vision)

### Search
- [Meilisearch](https://www.meilisearch.com)

### Infrastructure
- [Redis](https://redis.io)
- [AWS S3](https://aws.amazon.com/s3/)
- [Cloudflare R2](https://www.cloudflare.com/developer-platform/r2/)
- [Laravel Queues](https://laravel.com/docs/queues)
- [Laravel Horizon](https://laravel.com/docs/horizon)

---

| Phase | Tech Focus |
|-------|-------------|
| MVP | Laravel + RN + Google OCR |
| Stabilization | queues + review flows |
| Scale | Redis + Horizon + caching |
| Intelligence | OpenAI + ML enrichment |
| Forecasting | Python microservices |