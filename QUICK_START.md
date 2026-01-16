# Quick Start Guide

## Setup Instructions

### 1. Database Setup

```bash
# Run migrations and seeders
php artisan migrate:fresh --seed
```

This will create:

-   All 26 database tables
-   6 user roles (Admin, Manager, Procurement, Store Keeper, Chef, Frontline Sales)
-   4 business sections (Eatery, Café, Lounge, Grills)

### 2. Create Test Users

```bash
php artisan tinker
```

```php
// Create Admin user
$adminRole = \App\Models\Role::where('name', 'Admin')->first();
\App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role_id' => $adminRole->id,
    'is_active' => true
]);

// Create Chef user
$chefRole = \App\Models\Role::where('name', 'Chef')->first();
$eaterySection = \App\Models\Section::where('name', 'Eatery')->first();
\App\Models\User::create([
    'name' => 'Chef John',
    'email' => 'chef@example.com',
    'password' => bcrypt('password'),
    'role_id' => $chefRole->id,
    'section_id' => $eaterySection->id,
    'is_active' => true
]);

// Create Sales user
$salesRole = \App\Models\Role::where('name', 'Frontline Sales')->first();
\App\Models\User::create([
    'name' => 'Sales Person',
    'email' => 'sales@example.com',
    'password' => bcrypt('password'),
    'role_id' => $salesRole->id,
    'section_id' => $eaterySection->id,
    'is_active' => true
]);
```

### 3. Start the Server

```bash
php artisan serve
```

### 4. Run Background Jobs (Optional)

```bash
# Start queue worker
php artisan queue:work

# Run scheduler (in production, add to cron)
php artisan schedule:work
```

---

## Testing the API

### Authentication

Use Laravel Breeze endpoints (already configured):

```http
POST /login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

### Example Workflow

#### 1. Create Supplier (via tinker or direct DB)

```php
\App\Models\Supplier::create([
    'name' => 'Fresh Foods Ltd',
    'contact_person' => 'John Doe',
    'phone' => '1234567890',
    'email' => 'supplier@freshfoods.com'
]);
```

#### 2. Create Raw Material

```php
\App\Models\RawMaterial::create([
    'name' => 'Chicken Breast',
    'unit' => 'kg',
    'category' => 'Meat',
    'min_quantity' => 10,
    'reorder_quantity' => 50,
    'preferred_supplier_id' => 1
]);
```

#### 3. Create Procurement (via API)

```http
POST /api/v1/procurements
Authorization: Bearer {token}

{
  "supplier_id": 1,
  "purchase_date": "2026-01-17",
  "items": [
    {
      "raw_material_id": 1,
      "quantity": 100,
      "unit_cost": 8.50,
      "expiry_date": "2026-02-17"
    }
  ]
}
```

#### 4. Check Inventory

```http
GET /api/v1/inventory
Authorization: Bearer {token}
```

#### 5. Create Recipe (as Chef)

```http
POST /api/v1/recipes
Authorization: Bearer {chef_token}

{
  "name": "Grilled Chicken",
  "section_id": 1
}
```

#### 6. Create Recipe Version

```http
POST /api/v1/recipes/1/versions
Authorization: Bearer {chef_token}

{
  "ingredients": [
    {
      "raw_material_id": 1,
      "quantity_required": 0.25
    }
  ]
}
```

#### 7. Log Production

```http
POST /api/v1/productions
Authorization: Bearer {chef_token}

{
  "recipe_version_id": 1,
  "quantity_produced": 20,
  "production_date": "2026-01-17",
  "materials": [
    {
      "raw_material_id": 1,
      "procurement_item_id": 1,
      "quantity_used": 5
    }
  ],
  "expiry_date": "2026-01-20"
}
```

#### 8. Record Sale

```http
POST /api/v1/sales
Authorization: Bearer {sales_token}

{
  "sale_date": "2026-01-17",
  "payment_method": "cash",
  "items": [
    {
      "prepared_inventory_id": 1,
      "quantity": 3,
      "unit_price": 25.00
    }
  ]
}
```

#### 9. View Reports

```http
GET /api/v1/reports/dashboard?start_date=2026-01-01&end_date=2026-01-17
Authorization: Bearer {admin_token}
```

---

## Key Features

### ✅ FIFO Inventory Management

-   Automatic batch tracking
-   First-in-first-out consumption
-   Full traceability

### ✅ Cost Tracking

-   Batch-level cost locking
-   Automatic cost calculation per unit
-   Profit tracking per sale

### ✅ Approval Workflows

-   Material requests: Chef → Manager → Store Keeper
-   Production: Chef → Manager
-   Waste: Any user → Manager

### ✅ Alerts (Scheduled Daily)

-   Low stock alerts (8:00 AM)
-   Expiring items (8:30 AM)
-   High wastage (9:00 AM)

### ✅ Audit Logging

-   All critical operations logged
-   User tracking
-   Change history

---

## API Documentation

See **API_ROUTES.md** for complete API reference with all endpoints, parameters, and examples.

---

## Troubleshooting

### Queue not processing?

```bash
php artisan queue:work
```

### Scheduler not running?

Add to crontab (Linux/Mac):

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually:

```bash
php artisan schedule:work
```

### Database issues?

```bash
php artisan migrate:fresh --seed
```

---

## Production Checklist

-   [ ] Set up proper database (MySQL/PostgreSQL)
-   [ ] Configure `.env` with production credentials
-   [ ] Set up queue worker as system service
-   [ ] Configure cron for scheduler
-   [ ] Set up email service (SMTP/Mailgun/SES)
-   [ ] Enable HTTPS
-   [ ] Set up backups
-   [ ] Configure logging
-   [ ] Run `php artisan optimize`
