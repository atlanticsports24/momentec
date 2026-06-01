# Momentec

Laravel 12 + Filament 3 catalog and OpenCart-style store admin.

## Requirements

- PHP 8.2+
- MySQL (`momentec` database)
- Composer, Node (optional for assets)

## Setup

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install
npm run build
```

For frontend development with hot reload: `npm run dev` (keep it running alongside `php artisan serve`).

Admin: **http://127.0.0.1:8000/admin** — `admin@momentec.local` / `password` (super_admin).

Storefront: **http://127.0.0.1:8000/shop**

Set `APP_URL=http://127.0.0.1:8000` so product image URLs resolve correctly.

## Catalog (admin)

- **Sync Center** — import `storage/app/imports/product-data-std-all.csv`
- **Brands, Categories, Products, Variants** — CRUD and images under `/storage/products/`
- Brand codes from `config/brand_codes.php` and `storage/app/imports/momentec_spec.xlsx`

## Store module (OpenCart-like)

### Localisation

| Admin menu | Description |
|------------|-------------|
| Countries | ISO codes, enable/disable |
| Zones | States/provinces per country (US 50 states + DC, CA provinces, UK regions seeded) |
| Geo Zones | Group zones for payment/shipping rules (North America seeded with US+CA) |
| Currencies | USD default |

### Store settings

**Store → Store Settings** — store name/email, default country, zone, currency, **default order status** for new orders (e.g. Missing), and **enable/disable tax** for checkout.

### Tax (per zone)

**Localisation → Zones** — set **Tax rate (%)** for each state/province. When tax is enabled in Store Settings, checkout applies that rate to the cart **subtotal** (shipping is not taxed). Example: California 7.25% on a $100 subtotal = $7.25 tax.

### Order statuses

CRUD for statuses (Missing, Pending, Processing, Shipped, Complete, Canceled, etc.). Core statuses are seeded and flagged `is_core`.

### Extensions

| Type | Codes (seeded) |
|------|----------------|
| Payment | `cod` (enabled), `stripe`, `authorize_net` (AIM card form + gateway), `paypal` (disabled; configure in admin) |

**Authorize.Net (AIM)** — Admin → Extensions → Payment Methods → Authorize.Net: Login ID, Transaction Key, MD5 Hash (optional), Transaction Server (Live/Test), Transaction Mode, Transaction Method (Authorization vs Payment), Order Status, Geo Zone, Status. Checkout shows card fields when this method is selected and charges via `transact.dll` like OpenCart.
| Shipping | `flat`, `free` (enabled), `ups` + `usps` carrier modules (live API quotes) |

**Carrier modules** (OpenCart-style defaults under `app/Services/Store/Carriers/`):

- **UPS** — OAuth + Rating API (`UpsCarrier` / `UpsShippingService`). Full admin form: credentials, origin, pickup, packaging, services, surcharges.
- **USPS** — RateV4 XML API (`UspsCarrier` / `UspsShippingService`). Admin: User ID, origin zip, container, dimensions, enabled services.

Checkout loads **UPS and USPS** live rates when city + postcode are entered (enable each method under Extensions → Shipping Methods).

Each payment method has **success order status** (applied after successful payment). COD marks the order paid immediately and moves to Processing.

### Orders

View orders placed via checkout; add **order history** entries to change status (like OpenCart order history).

### Checkout flow

1. Customer adds variants to cart at `/shop`
2. Checkout loads enabled payment/shipping for address (country + zone → geo zones)
3. New order gets **default order status** from store settings
4. COD (and similar) → **success order status** from payment method + `paid_at` set

```bash
php artisan db:seed --class=StoreSeeder   # re-seed store defaults only
```

## Permissions

After adding resources:

```bash
php artisan shield:generate --all --minimal -n
```

## Queue

For large catalog syncs, set `QUEUE_CONNECTION=database` and run `php artisan queue:work`.

## Tests

```bash
php artisan test
```
