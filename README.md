# Inventory Management System

A robust Inventory Management System built with Laravel. This application helps manage stock, procurement, production (recipes), waste tracking, and reporting.

## Features

- **User Management:** Role-based access control (Admin, Manager, Procurement, Store Keeper, Chef, Frontline Sales).
- **Inventory Management:** Track raw materials, stock levels, and expiry dates.
- **Procurement:** Manage suppliers and purchase orders with approval workflows.
- **Production:** Recipe management and production logging with automatic stock deduction.
- **Sales:** POS interface for frontline sales with daily reporting.
- **Waste:** Track and approve waste logs.
- **Reports:** Profit & Loss, Expenses, Inventory Health, and more.

## Installation (Local Development)

1.  Clone the repository.
2.  Run `composer install` to install PHP dependencies.
3.  Copy `.env.example` to `.env` and configure your database.
4.  Run `php artisan key:generate`.
5.  Run `php artisan migrate --seed` to setup the database and default users.
6.  Run `npm install && npm run build` to compile assets.
7.  Serve the application: `php artisan serve`.

## Deployment on Shared Hosting

Deploying to shared hosting (e.g., cPanel) requires a few specific steps since you may not have root access.

### 1. Preparation

1.  Run `npm run build` locally to compile assets for production.
2.  Compress your project files into a `zip` archive (exclude `node_modules`, `tests`, and `.git`).

### 2. Uploading

1.  Upload the `zip` file to your hosting file manager (e.g., inside `public_html` or a subdirectory).
2.  Extract the files.

### 3. Folder Structure (Important)

Shared hosts usually serve from `public_html`. Laravel serves from `public`.
**Option A (Recommended if you have SSH):**

- Place the core code in a folder _outside_ `public_html` (e.g., `~/inventory_app`).
- Symlink the `public` folder to `public_html`.
    ```bash
    ln -s ~/inventory_app/public ~/public_html
    ```
    **Option B (Easy Method):**
- Place everything in the root directory.
- Create an `.htaccess` file in the root to redirect traffic to the `public/` folder.
    ```apache
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteRule ^(.*)$ public/$1 [L]
    </IfModule>
    ```

### 4. Database Setup

1.  Create a MySQL database and user via your hosting control panel.
2.  Update `.env` with your production database credentials:
    ```ini
    DB_DATABASE=your_db_name
    DB_USERNAME=your_db_user
    DB_PASSWORD=your_db_password
    APP_URL=https://your-domain.com
    APP_ENV=production
    APP_DEBUG=false
    ```

### 5. Running Migrations & Seeds

If you have SSH access:

```bash
php artisan migrate --seed --force
```

If you **do not** have SSH access:

1.  Run migrations locally on a test database.
2.  Export the local database to an SQL file.
3.  Import the SQL file into your production database using phpMyAdmin.

### 6. File Permissions

Ensure the following directories are writable (775):

- `storage/`
- `bootstrap/cache/`

## Default Seed Users

The application comes pre-configured with the following users for testing and initial setup.
**Default Password:** `password`

| Role               | Email                        |
| :----------------- | :--------------------------- |
| **Admin**          | `admin@inventory.com`        |
| **Manager**        | `manager@inventory.com`      |
| **Procurement**    | `procurement@inventory.com`  |
| **Store Keeper**   | `storekeeper@inventory.com`  |
| **Chef (Eatery)**  | `chef.eatery@inventory.com`  |
| **Chef (Cafe)**    | `chef.cafe@inventory.com`    |
| **Chef (Lounge)**  | `chef.lounge@inventory.com`  |
| **Chef (Grills)**  | `chef.grills@inventory.com`  |
| **Sales (Eatery)** | `sales.eatery@inventory.com` |
| **Sales (Cafe)**   | `sales.cafe@inventory.com`   |
| **Sales (Lounge)** | `sales.lounge@inventory.com` |
| **Sales (Grills)** | `sales.grills@inventory.com` |

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
