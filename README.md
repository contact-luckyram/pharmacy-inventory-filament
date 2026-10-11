# MediStock — Pharmacy Inventory & Billing (Laravel + Filament)

A demo admin panel for a retail pharmacy, built with **Laravel 13** and **Filament 5**.
It tracks medicines by batch and expiry date, sells the oldest stock first, warns about
low stock and expiring batches, and creates bills with live totals.

> Demo project by **Lucky Ram** (PHP / Laravel / Filament developer). All data is fictional seed data.

---

## Features

| Area | What it does |
| --- | --- |
| **Dashboard** | Today's sales vs yesterday (with 7-day sparkline), stock value at cost, low-stock and expiring-soon counts, 14-day revenue chart, table of batches expiring in 60 days |
| **Medicines** | Brand + generic name, dosage form, strength, MRP, reorder level, Rx-only flag. Tabs for *All / Low stock / Prescription only*, filters, global search (`Ctrl + K`) and a red sidebar badge for items to reorder |
| **Stock batches** | Each medicine has batches with quantity, cost, manufacturing and expiry date. Colour-coded expiry badges; expired stock is never counted as sellable |
| **Billing (POS)** | New bill with a repeater of medicines. Price fills in automatically, quantity can't exceed stock, the same medicine can't be added twice, and the total updates live. Invoice numbers like `INV-20261012-0001` |
| **Stock logic** | Selling deducts units **FEFO** (first expiry, first out) inside a database transaction |
| **Setup** | Categories and suppliers (with GSTIN validation) managed in quick modal forms |
| **Auth** | Login and profile page from Filament |

## Tech stack

- PHP 8.3+, Laravel 13
- Filament 5 (Livewire 4, Alpine.js, Tailwind CSS)
- SQLite by default (MySQL / PostgreSQL work too)
- PHPUnit feature tests

## Quick start

**Requirements:** PHP 8.3+ with `intl`, `pdo_sqlite` (or `pdo_mysql`) and `zip` extensions, and Composer 2.

```bash
git clone https://github.com/contact-luckyram/pharmacy-inventory-filament.git
cd pharmacy-inventory-filament

composer run setup   # installs packages, creates .env, key, SQLite DB, migrates and seeds demo data
php artisan serve
```

Open **http://127.0.0.1:8000/admin** and sign in:

| Email | Password |
| --- | --- |
| `admin@medistock.test` | `password` |

<details>
<summary>Manual setup (step by step)</summary>

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite          # Windows: type nul > database\database.sqlite
php artisan migrate --seed
php artisan serve
```

To use MySQL, set `DB_CONNECTION=mysql` and the `DB_*` values in `.env`, then run `php artisan migrate --seed`.

To reset the demo data at any time: `php artisan migrate:fresh --seed`.
</details>

## Run the tests

```bash
php artisan test
```

The tests cover FEFO stock deduction, expired-stock rules, overselling protection,
the low-stock query, and loading the main admin pages.

## Project structure

```text
app/
├── Enums/                    MedicineForm, PaymentMethod (labels + colours for Filament)
├── Exceptions/               InsufficientStockException
├── Filament/
│   ├── Resources/
│   │   ├── Categories/       simple resource (modal create/edit)
│   │   ├── Suppliers/        simple resource (modal create/edit)
│   │   ├── Medicines/
│   │   │   ├── MedicineResource.php
│   │   │   ├── Pages/        List (with tabs), Create, Edit
│   │   │   ├── Schemas/      MedicineForm.php
│   │   │   ├── Tables/       MedicinesTable.php
│   │   │   └── RelationManagers/BatchesRelationManager.php
│   │   └── Sales/
│   │       ├── SaleResource.php
│   │       ├── Pages/        List, Create (bill), View (invoice)
│   │       ├── Schemas/      SaleForm.php (live totals), SaleInfolist.php
│   │       └── Tables/       SalesTable.php
│   └── Widgets/              PharmacyStatsOverview, SalesChart, ExpiringBatches
├── Models/                   Category, Supplier, Medicine, Batch, Sale, SaleItem, User
├── Providers/Filament/       AdminPanelProvider.php (brand, colours, navigation groups)
└── Services/StockService.php FEFO stock deduction
database/
├── migrations/
└── seeders/                  CatalogSeeder (30 medicines, batches), SalesSeeder (14 days of bills)
tests/Feature/StockAndBillingTest.php
```

## How the stock works

```text
Receive stock  ──► Batch (qty, cost, expiry)
                         │
New bill ──► SaleItem created ──► StockService::deduct()
                                    • only unexpired batches
                                    • earliest expiry first (FEFO)
                                    • whole bill rolls back if stock is short
                         │
                    Sale total recalculated from its items
```

## Want something like this?

I build custom admin panels, dashboards, CRMs and internal tools with Laravel and Filament.

- Email: contact.luckyram@gmail.com
- LinkedIn: [linkedin.com/in/lucky-ram](https://linkedin.com/in/lucky-ram)

## License

MIT — see [LICENSE](LICENSE).
