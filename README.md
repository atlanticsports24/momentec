# Momentec — Laravel catalog admin

Production-oriented **Laravel 12** admin for importing **standard product CSV** feeds (parent SKU + variant rows), with **Filament 3**, **Spatie Permission**, **Filament Shield**, queued sync, and optional **image downloads** to `public` storage.

## Requirements

- PHP **8.3+**, Composer, Node (for Vite assets if you build the default Laravel frontend).
- **MySQL/MariaDB** (recommended for production) or SQLite for local smoke tests.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env` database credentials (MySQL on Laragon, or SQLite).

```bash
php artisan migrate --seed
php artisan storage:link
```

### Demo admin

After `migrate --seed`:

- **URL:** `/admin`
- **Email:** `admin@momentec.local`
- **Password:** `password`

Assign roles and permissions under **Shield → Roles** (super_admin bypasses resource checks).

## CSV imports

Place one or more `.csv` files under:

`storage/app/imports/`

Example: `storage/app/imports/product-data-std-all.csv`

The feed is expected to match columns like `Parent_SKU`, `Item_SKU`, `Category` (pipe-separated path), `Brand`, pricing, image URLs, `Variation_Theme`, `Color`, `Size`, etc.

A **second CSV** can be selected on the Sync Center page; rows are stored in `secondary_feed_rows` as JSON for inspection until a dedicated schema is defined.

### Brand codes → names

The product CSV `Brand` column uses numeric codes (e.g. `81`). Names are resolved from:

1. `config/brand_codes.php` (defaults)
2. `storage/app/imports/momentec_spec.xlsx` — **Brand** row, **Field Spec** column (`10 = Augusta`, etc.)

Close Excel before syncing if the spec file is open (Windows lock file `~$momentec_spec.xlsx`).

Re-run **Products** or **Sync all** to refresh existing brand names in the database.

## Queues (required for sync & images)

```bash
php artisan queue:work database --tries=3 --timeout=120
```

Set in `.env`:

```env
QUEUE_CONNECTION=database
```

## Sync Center

**Admin → Sync Center**

- Pick the primary CSV, optional secondary CSV.
- Optional **truncate** (per entity) — requires typing `DELETE` in the confirmation field.
- Run **Sync all** or individual steps: categories, products, variants, aggregates, image download jobs.

Merge rules are documented in `App\Services\Catalog\ProductCatalogSyncService`.

## Filament Shield / permissions

Run after adding new Filament resources or pages:

```bash
php artisan shield:generate --all -n --minimal
```

`config/filament-shield.php` enables discovery for resources and pages.

## Tests

```bash
php artisan test
```

## Composer / platform note

This project pins **Laravel 12** because **Filament 3** does not yet support **Laravel 13**. When Filament adds Laravel 13 support, you can bump `laravel/framework` in `composer.json`.

## Security

Change the seeded admin password before deploying. Review Filament Shield roles so non-admin users only receive the permissions they need.
