# User Seeder - Test Accounts

This seeder creates test user accounts for all roles in the system.

## Running the Seeder

```bash
# Run all seeders (including users)
php artisan db:seed

# Or run just the UserSeeder
php artisan db:seed --class=UserSeeder

# Fresh migration with all seeders
php artisan migrate:fresh --seed
```

## Test Accounts Created

### Admin Account (from .env)

- **Email**: `admin@inventory.com` (or value from `ADMIN_EMAIL`)
- **Password**: `password` (or value from `ADMIN_PASSWORD`)
- **Name**: System Administrator (or value from `ADMIN_NAME`)
- **Role**: Admin
- **Section**: None (access to all sections)

### Manager Account

- **Email**: `manager@inventory.com`
- **Password**: `password`
- **Name**: John Manager
- **Role**: Manager
- **Section**: None (access to all sections)

### Procurement Account

- **Email**: `procurement@inventory.com`
- **Password**: `password`
- **Name**: Sarah Procurement
- **Role**: Procurement
- **Section**: None (works across all sections)

### Store Keeper Account

- **Email**: `storekeeper@inventory.com`
- **Password**: `password`
- **Name**: Mike Store
- **Role**: Store Keeper
- **Section**: None (manages all inventory)

### Chef Accounts (one per section)

- **Eatery Chef**
    - Email: `chef.eatery@inventory.com`
    - Password: `password`
    - Name: Chef David (Eatery)
    - Section: Eatery

- **Café Chef**
    - Email: `chef.cafe@inventory.com`
    - Password: `password`
    - Name: Chef Emma (Café)
    - Section: Café

- **Lounge Chef**
    - Email: `chef.lounge@inventory.com`
    - Password: `password`
    - Name: Chef James (Lounge)
    - Section: Lounge

- **Grills Chef**
    - Email: `chef.grills@inventory.com`
    - Password: `password`
    - Name: Chef Maria (Grills)
    - Section: Grills

### Sales Representative Accounts (one per section)

- **Eatery Sales**
    - Email: `sales.eatery@inventory.com`
    - Password: `password`
    - Section: Eatery

- **Café Sales**
    - Email: `sales.cafe@inventory.com`
    - Password: `password`
    - Section: Café

- **Lounge Sales**
    - Email: `sales.lounge@inventory.com`
    - Password: `password`
    - Section: Lounge

- **Grills Sales**
    - Email: `sales.grills@inventory.com`
    - Password: `password`
    - Section: Grills

## Customizing Admin Account

You can customize the admin account by setting environment variables in your `.env` file:

```env
ADMIN_NAME="Your Name"
ADMIN_EMAIL=your.email@example.com
ADMIN_PASSWORD=your-secure-password
```

## Security Note

⚠️ **IMPORTANT**: Change the default passwords before deploying to production!

The default password `password` is only for development and testing purposes.
