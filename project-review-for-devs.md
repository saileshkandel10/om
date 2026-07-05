# ABC Connect — User Roles, Devices & Required Pages Specification

## Project Overview

ABC Connect is a pharmaceutical loyalty, engagement, and intelligence platform owned by ABC Pharma, a medicine manufacturing company.

The platform connects:

* Pharmacies / Retail Shops
* Doctors
* Shareholders
* Customers / Patients
* Internal Company Staff

The main business goal is to:

* Increase product engagement
* Encourage loyalty through rewards
* Collect medicine purchase intelligence
* Track pharmacy and prescription trends
* Build downstream pharmaceutical analytics
* Support future forecasting and production planning

The platform revolves around receipt and prescription scanning using OCR technology.

Core workflow:

```text
User scans receipt
        ↓
OCR extracts medicine data
        ↓
System validates products
        ↓
Rewards are calculated
        ↓
ABC Pharma gains market intelligence
        ↓
Users stay engaged through loyalty
```
---

## End-to-End Flow

```
[Master Data Setup]
        ↓
[User Onboarding]
        ↓
    {User Type}
    /    |    \
Shareholder  Pharmacy  Doctor
    |         |        |
[BOID &      [Store    [Doctor
 Username    Validation  Verification
 Validation] & Approval] & Approval]
    \         |        /
     └───────┬┴────────┘
             ↓
    [Mobile App Access]
    (or Doctor Portal)
             ↓
      ┌──────┼──────┐
      │      │      │
  Browse   Upload   Verify
 Products  Bill  Product
      │      │      │
      │ [OCR Pipeline]
      │ Image Preproc
      │ Entity Extract
      │ Product Match
      │ {Confidence}
      │ High/Low Score
      │      │      │
      └───┬──┴──────┘
          ↓
    [Reward Engine]
          ↓
  [Loyalty / Royalty]
          ↓
  [Purchase History]
          ↓
  [Behaviour Analysis]
          ↓
  [Chronic Illness Segments]
          ↓
  [Awareness Campaigns]
          ↓
  [Demand Analytics]
          ↓
  [Forecast Engine]
          ↓
  [Stock Planning]
```
---

# Platform Structure

## Web Systems

| System           | Users                     |
| ---------------- | ------------------------- |
| Admin Web Portal | ABC Pharma internal staff |
| Public Website   | Public visitors           |

---

## Mobile Application

| App                    | Users                                        |
| ---------------------- | -------------------------------------------- |
| ABC Connect Mobile App | Shareholders, Pharmacies, Doctors, Customers |

---

# 1. Admin / Internal Staff Portal

## Device

* Web Only

## Technology

* Laravel

## Users

* Admins
* Marketing Team
* Operations Team
* OCR Reviewers
* Finance Team
* Analytics Team

---

## Required Pages

### Authentication

* Login
* Forgot Password
* Verify Email
* 2FA (Future)

---

### Dashboard

Displays:

* Total uploaded bills
* OCR success rate
* Pending OCR reviews
* Active users
* Active pharmacies
* Rewards issued
* Top products
* Product demand trends
* Revenue insights

---

### User Management

#### Users

* View all users
* Filter by role
* Activate / deactivate users

#### Shareholders

* BOID validation
* Shareholder approvals
* Shareholder metrics

#### Pharmacies

* Pharmacy approvals
* License verification
* Pharmacy activity tracking

#### Doctors

* Doctor approvals
* Registration verification

---

### Product Management

#### Products

* Create products
* Edit products
* Delete products
* Product status management

#### Product Categories

* Therapeutic categories
* Illness categories
* Dosage forms

#### Product Performance

* Product popularity
* Product movement
* Regional demand insights

---

### OCR & Receipt Management

#### OCR Dashboard

* OCR jobs
* OCR success rates
* Failed OCR jobs
* Processing statistics

#### OCR Review Queue

* Low-confidence receipts
* Manual correction workflow
* Approve / reject workflow

#### Receipt Management

* Uploaded receipts
* Duplicate detection
* Fraud flags
* Receipt history

#### OCR Logs

* Raw OCR text
* Parsed OCR data
* Confidence scores
* OCR processing history

---

### Reward & Loyalty Management

#### Reward Rules

* Points rules
* Campaign rules
* Bonus logic
* Date windows

#### Reward Transactions

* Issued rewards
* Redemption history
* Reversals

#### Wallet Monitoring

* Points analytics
* Bonus summaries
* Loyalty analytics

---

### Campaigns & Notifications

#### Campaign Management

* Promotions
* Awareness campaigns
* Product campaigns

#### Notifications

* Push notifications
* In-app notifications
* Notices

---

### Shareholder Content

* AGM notices
* Financial reports
* Announcements
* Shareholder-exclusive rewards

---

### Analytics & Forecasting

#### Demand Analytics

* Product demand trends
* Regional analytics
* Pharmacy activity insights

#### User Behaviour Analytics

* Repeat purchases
* Loyalty engagement
* User segmentation

#### Forecast Dashboard

* Forecast trends
* Production planning
* Stock pressure insights

---

### Reports

* OCR reports
* Reward reports
* Product performance reports
* Exportable analytics

---

### Audit & Security

* Audit logs
* Admin activity logs
* Security logs

---

# 2. Pharmacy Users

## Device

* Mobile App Only

## Technology

* React Native

## Description

Pharmacies are lightweight loyalty and engagement users.

Main actions:

* Scan receipts
* Earn rewards
* View company products
* Participate in campaigns
* Track performance

---

## Required Pages

### Authentication

* Signup
* Login
* OTP verification
* Store approval status

---

### Home Dashboard

Displays:

* Reward summary
* Recent uploads
* Company announcements
* Featured products
* Campaigns

---

### Scan Receipt (Core Feature)

Features:

* Camera scanning
* Image upload
* Crop and adjustment
* Upload progress

---

### OCR Result Page

Displays:

* Extracted medicines
* Quantities
* Invoice totals
* Validation status
* Confidence indicators

---

### Upload History

* Uploaded receipts
* Approval status
* Rejected receipts
* Processing history

---

### Rewards Wallet

* Points earned
* Bonuses
* Loyalty level
* Reward history

---

### Product Catalog

* Medicine listing
* New launches
* Promotions
* Featured products

---

### Product Details

* Medicine information
* Pack size
* Dosage
* MRP
* Promotional offers

---

### Campaigns & Promotions

* Awareness campaigns
* Product promotions
* Discount offers

---

### Performance Page

Displays:

* Monthly uploads
* Reward performance
* Rankings
* Engagement statistics

---

### Notifications

* Reward alerts
* OCR approvals
* Campaign alerts
* Product launches

---

### Profile

* Pharmacy information
* License details
* Settings
* Password management

---

# 3. Doctor Users

## Device

* Mobile App Only

## Technology

* React Native

## Description

Doctors are engagement and prescription intelligence users.

Main actions:

* View products
* Participate in campaigns
* Upload prescriptions (future)
* Earn rewards
* Receive company updates

---

## Required Pages

### Authentication

* Signup
* Verification
* Login

---

### Doctor Dashboard

Displays:

* Reward summary
* Company announcements
* Product highlights
* Campaigns

---

### Prescription Upload (Future)

* Upload prescriptions
* Manual prescription entry

---

### Rewards Page

* Reward points
* Bonuses
* Loyalty history

---

### Product Catalog

* Medicine information
* Therapeutic categories
* New launches

---

### Company Updates

* Awareness programs
* Educational campaigns
* Product updates

---

### Performance Page

Displays:

* Engagement statistics
* Activity summaries
* Participation metrics

---

### Notifications

* Campaign notifications
* Product updates
* Reward alerts

---

### Profile

* Doctor information
* Specialty
* Verification status
* Settings

---

# 4. Shareholder Users

## Device

* Mobile App Only

## Technology

* React Native

## Description

Shareholders are loyalty and company engagement users.

Main actions:

* Scan receipts
* Earn rewards
* Access shareholder content
* View company announcements

---

## Required Pages

### Authentication

* BOID validation
* Signup
* Login

---

### Home Dashboard

Displays:

* Shareholder notices
* Rewards summary
* Campaigns
* Featured products

---

### Scan Receipt

* Receipt upload
* Camera scan
* OCR processing

---

### OCR Result Page

* Extracted medicine details
* Validation status
* Reward estimation

---

### Rewards Wallet

* Points
* Bonuses
* Loyalty history

---

### Shareholder Zone

* AGM notices
* Financial reports
* Announcements
* Shareholder rewards

---

### Product Catalog

* Product listings
* New products
* Promotions

---

### Performance Page

Displays:

* Upload activity
* Rewards earned
* Engagement summaries

---

### Notifications

* Reward notifications
* Company updates
* Campaign alerts

---

### Profile

* BOID information
* Personal profile
* Settings

---

# 5. Customer / Patient Users

## Device

* Mobile App Only

## Technology

* React Native

## Description

Customers are simple loyalty users.

Main actions:

* Upload receipts
* Earn rewards
* Browse products
* Receive offers

---

## Required Pages

### Authentication

* Signup
* Login
* OTP verification

---

### Home Page

Displays:

* Promotions
* Featured products
* Rewards summary

---

### Scan Receipt

* Camera upload
* Gallery upload
* OCR processing

---

### OCR Result Page

* Extracted medicine data
* Validation results
* Reward estimation

---

### Rewards Wallet

* Points
* Discounts
* Loyalty activity

---

### Product Catalog

* Medicine listings
* Categories
* Product details

---

### Campaigns & Offers

* Discounts
* Awareness campaigns
* Promotions

---

### Notifications

* Reward updates
* Product launches
* Campaigns

---

### Profile

* Personal information
* Settings
* Password management

---

# 6. Public Website

## Device

* Web

## Technology

* Laravel
* React Frontend (Optional)

## Description

The public website is mainly for:

* Branding
* Product visibility
* Company information
* Shareholder communication

---

## Required Pages

### Homepage

Displays:

* Company introduction
* Featured medicines
* Campaigns
* Promotions

---

### About Us

* Company information
* Manufacturing information
* Vision and mission

---

### Product Catalog

* Medicine listings
* Categories
* Product search

---

### Product Details

* Medicine information
* Dosage
* Strength
* Pack size

---

### Shareholder Information

* Notices
* Reports
* Announcements

---

### News & Campaigns

* Awareness campaigns
* Product launches
* Company news

---

### Contact Us

* Inquiry form
* Company contacts
* Support information

---

# MVP Priority Features

## Mobile MVP

Highest priority:

1. Authentication
2. Receipt scanning
3. OCR processing
4. Rewards wallet
5. Product catalog
6. Notifications
7. Performance tracking

---

## Web MVP

Highest priority:

1. Product management
2. OCR review queue
3. User approvals
4. Reward management
5. Campaign management
6. Analytics dashboard

---

# Core Business Workflow

```text
User uploads receipt
        ↓
OCR extracts medicine data
        ↓
System validates products
        ↓
Rewards are calculated
        ↓
User engagement increases
        ↓
ABC Pharma gains pharmaceutical intelligence
```

---

# Recommended Technology Stack

## Backend

* Laravel
* Sanctum Authentication
* Laravel Horizon
* Redis
* MySQL

---

## Admin Panel

* FilamentPHP

---

## Mobile App

* React Native

---

## OCR

* Google Vision API

---

## Search

* Meilisearch

---

## Storage

* S3-Compatible Storage

---

## Queue Processing

* Redis + Horizon

---

## Future AI/ML Layer

* Python FastAPI
* OpenCV
* Forecasting services

---

# Final Notes

ABC Connect is fundamentally:

> A pharmaceutical loyalty, engagement, and intelligence ecosystem powered by OCR receipt scanning and reward-driven user participation.

The platform should prioritize:

* OCR stability
* Reward reliability
* Product engagement
* User retention
* Analytics quality
* Scalability

Advanced forecasting and intelligence modules should be introduced after enough clean transactional data has been collected.
