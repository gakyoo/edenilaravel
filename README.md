# 🏘️ Edenire.co.tz

**Tanzania's real estate platform** — buy, sell, and rent properties with confidence. Built by **Edeni Realtors** (Arusha, Tanzania).

A full-featured property marketplace: agents and the company list properties for sale or rent, buyers search with powerful filters (price, bedrooms, distance), and everyone communicates through WhatsApp.

## ✨ Features

### Public site
- **Landing page** — branded, SEO-optimized, light/dark theme
- **Property listings** — search, sort (price, alphabetical, distance, newest, most viewed), filters (price range, bedrooms, bathrooms, area, region, type), 📍 "Near me" radius search
- **Zillow-style property details** — photo gallery with lightbox, facts & features, similar homes, sticky agent contact card, **💬 WhatsApp enquiry** button
- **SEO-friendly URLs** — `/properties/{id}/{slug}` with canonical redirects
- **Brand palette** — dark charcoal, lime green, sky blue

### Backend dashboard (merged admin)
- **Overview** — KPIs (properties, value in TZS, views, conversion, users), alerts, activity feed, financial snapshot, analytics
- **Property management** — full CRUD with photo uploads, amenities, status workflow (active/pending/sold/rented/off-market)
- **Enquiry management** — lead pipeline: new → contacted → qualified → closed
- **Tasks & reminders** — follow-ups, inspections, showings with due dates
- **Export** — properties to CSV
- **Roles** — admin, agent, buyer, seller, tenant (company-owned model)

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.5) |
| Database | MySQL 8 |
| Frontend | Vue 3 + Inertia.js + Tailwind CSS |
| Auth | Laravel Breeze (email/password) |
| Charts | CSS-based (no heavy deps) |
| Maps | Distance search via Haversine (Leaflet planned) |

## 🚀 Quick Start

```bash
# 1. Clone
git clone https://github.com/gakyoo/edenilaravel.git
cd edenilaravel

# 2. Install dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate
# → edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Database
mysql -u root -p -e "CREATE DATABASE edenire_co_tz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed

# 5. Storage link (for property photos)
php artisan storage:link

# 6. Run
npm run build          # production assets
php artisan serve      # http://localhost:8000
```

### Seed data
- `PropertySeeder` — sample Tanzania properties (Dar es Salaam, Arusha, Zanzibar)
- `ArushaPropertySeeder` — real listings from arushahomes.com (USD→TZS converted)
- `RealtorPropertySeeder` — real listings from arusharealtor.co.tz (farms, plots, houses)

## 📁 Key Directories

```
app/Http/Controllers/   # Dashboard (merged admin), Property, Auth
app/Models/             # Property, PropertyMedia, Enquiry, Task, User
database/migrations/    # Schema (properties, media, favorites, enquiries, tasks)
database/seeders/       # Sample + real scraped data
resources/js/Pages/     # Vue pages (Landing, Properties, Dashboard, Admin)
resources/js/Layouts/   # AuthenticatedLayout (sidebar) + GuestLayout
resources/js/Components/# ThemeToggle, lightbox, etc.
public/img/placeholders/# Branded SVG placeholders per property type
```

## 🌍 Localization Notes
- Target market: **Tanzania** — English & Swahili (planned), prices in **TZS**
- WhatsApp-first communication (the standard in the TZ market)
- No parcel/APN numbers (US concept — excluded)

## 📄 License
Proprietary — © Edeni Realtors / Edenire.co.tz
