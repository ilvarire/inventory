# API Routes Reference

## Base URL

```
/api/v1
```

All routes require `auth:sanctum` middleware unless specified as public.

---

## Authentication

### User Info

```http
GET /api/v1/user
Authorization: Bearer {token}
```

Returns authenticated user with role and section.

---

## User Management

### List Users

```http
GET /api/v1/users
Authorization: Bearer {token}
Roles: Admin, Manager

Query Parameters:
- role_id (optional)
- section_id (optional)
- is_active (optional: true/false)
- search (optional: search by name or email)
- per_page (optional, default: 15)
```

### Create User

```http
POST /api/v1/users
Authorization: Bearer {token}
Roles: Admin, Manager

Body:
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role_id": 5,
  "section_id": 1,
  "is_active": true
}
```

### View User

```http
GET /api/v1/users/{id}
Authorization: Bearer {token}
Roles: Admin, Manager
```

### Update User

```http
PUT /api/v1/users/{id}
Authorization: Bearer {token}
Roles: Admin, Manager

Body:
{
  "name": "Updated Name",
  "email": "newemail@example.com",
  "password": "newpassword",
  "role_id": 2,
  "section_id": 1,
  "is_active": true
}
```

### Delete User

```http
DELETE /api/v1/users/{id}
Authorization: Bearer {token}
Roles: Admin only
```

### Toggle User Status

```http
POST /api/v1/users/{id}/toggle-status
Authorization: Bearer {token}
Roles: Admin, Manager
```

### Get All Roles

```http
GET /api/v1/users/roles
Authorization: Bearer {token}
Roles: Admin, Manager
```

### Get All Sections

```http
GET /api/v1/users/sections
Authorization: Bearer {token}
Roles: Admin, Manager
```

---

## Procurement

### List Procurements

```http
GET /api/v1/procurements
Authorization: Bearer {token}

Query Parameters:
- supplier_id (optional)
- start_date (optional)
- end_date (optional)
- status (optional)
- per_page (optional, default: 15)
```

### Create Procurement

```http
POST /api/v1/procurements
Authorization: Bearer {token}
Roles: Procurement, Admin

Body:
{
  "supplier_id": 1,
  "purchase_date": "2026-01-16",
  "items": [
    {
      "raw_material_id": 1,
      "quantity": 100,
      "unit_cost": 5.50,
      "quality_note": "Grade A",
      "expiry_date": "2026-02-16"
    }
  ]
}
```

### View Procurement

```http
GET /api/v1/procurements/{id}
Authorization: Bearer {token}
```

---

## Inventory

### List Raw Materials

```http
GET /api/v1/inventory
Authorization: Bearer {token}

Query Parameters:
- category (optional)
- search (optional)
```

### View Material Details

```http
GET /api/v1/inventory/{id}
Authorization: Bearer {token}
```

Returns material with FIFO batches and current stock.

### View Movement History

```http
GET /api/v1/inventory/{id}/movements
Authorization: Bearer {token}

Query Parameters:
- movement_type (optional)
- start_date (optional)
- end_date (optional)
- per_page (optional, default: 20)
```

### Low Stock Items

```http
GET /api/v1/inventory/low-stock
Authorization: Bearer {token}
```

### Expiring Items

```http
GET /api/v1/inventory/expiring
Authorization: Bearer {token}

Query Parameters:
- days (optional, default: 7)
```

---

## Material Requests

### List Material Requests

```http
GET /api/v1/material-requests
Authorization: Bearer {token}

Query Parameters:
- status (optional: pending, approved, rejected, fulfilled)
- section_id (optional)
- per_page (optional, default: 15)
```

### Create Material Request

```http
POST /api/v1/material-requests
Authorization: Bearer {token}
Roles: Chef

Body:
{
  "items": [
    {
      "raw_material_id": 1,
      "quantity": 10
    }
  ]
}
```

### View Material Request

```http
GET /api/v1/material-requests/{id}
Authorization: Bearer {token}
```

### Approve Material Request

```http
POST /api/v1/material-requests/{id}/approve
Authorization: Bearer {token}
Roles: Manager, Admin
```

### Reject Material Request

```http
POST /api/v1/material-requests/{id}/reject
Authorization: Bearer {token}
Roles: Manager, Admin

Body:
{
  "rejection_reason": "Insufficient budget"
}
```

### Fulfill Material Request

```http
POST /api/v1/material-requests/{id}/fulfill
Authorization: Bearer {token}
Roles: Store Keeper, Manager, Admin
```

Uses FIFO to issue materials from batches.

---

## Recipes

### List Recipes

```http
GET /api/v1/recipes
Authorization: Bearer {token}

Query Parameters:
- section_id (optional)
- status (optional: draft, active, archived)
- search (optional)
- per_page (optional, default: 15)
```

### Create Recipe

```http
POST /api/v1/recipes
Authorization: Bearer {token}
Roles: Chef, Manager, Admin

Body:
{
  "name": "Grilled Chicken",
  "section_id": 1
}
```

### View Recipe

```http
GET /api/v1/recipes/{id}
Authorization: Bearer {token}
```

Returns recipe with all versions.

### Update Recipe

```http
PUT /api/v1/recipes/{id}
Authorization: Bearer {token}
Roles: Chef, Manager, Admin

Body:
{
  "name": "Updated Recipe Name",
  "status": "active"
}
```

### Delete Recipe

```http
DELETE /api/v1/recipes/{id}
Authorization: Bearer {token}
Roles: Manager, Admin
```

### Create Recipe Version

```http
POST /api/v1/recipes/{id}/versions
Authorization: Bearer {token}
Roles: Chef, Manager, Admin

Body:
{
  "ingredients": [
    {
      "raw_material_id": 1,
      "quantity_required": 0.5
    },
    {
      "raw_material_id": 2,
      "quantity_required": 0.2
    }
  ]
}
```

### View Recipe Version

```http
GET /api/v1/recipes/{id}/versions/{versionId}
Authorization: Bearer {token}
```

---

## Production

### List Production Logs

```http
GET /api/v1/productions
Authorization: Bearer {token}

Query Parameters:
- section_id (optional)
- start_date (optional)
- end_date (optional)
- per_page (optional, default: 15)
```

### Log Production

```http
POST /api/v1/productions
Authorization: Bearer {token}
Roles: Chef

Body:
{
  "recipe_version_id": 1,
  "quantity_produced": 50,
  "production_date": "2026-01-16",
  "materials": [
    {
      "raw_material_id": 1,
      "procurement_item_id": 5,
      "quantity_used": 25
    }
  ],
  "expiry_date": "2026-01-19"
}
```

Returns production with total cost and cost per unit.

### View Production

```http
GET /api/v1/productions/{id}
Authorization: Bearer {token}
```

Returns production with cost breakdown.

### Approve Production

```http
POST /api/v1/productions/{id}/approve
Authorization: Bearer {token}
Roles: Manager, Admin
```

---

## Sales

### List Sales

```http
GET /api/v1/sales
Authorization: Bearer {token}

Query Parameters:
- section_id (optional)
- start_date (optional)
- end_date (optional)
- per_page (optional, default: 15)
```

### Record Sale

```http
POST /api/v1/sales
Authorization: Bearer {token}
Roles: Frontline Sales

Body:
{
  "sale_date": "2026-01-16",
  "payment_method": "cash",
  "items": [
    {
      "prepared_inventory_id": 1,
      "quantity": 2,
      "unit_price": 15.00
    }
  ]
}
```

Returns sale with profit calculation.

### View Sale

```http
GET /api/v1/sales/{id}
Authorization: Bearer {token}
```

### Generate Receipt

```http
GET /api/v1/sales/{id}/receipt
Authorization: Bearer {token}
```

---

## Expenses

### List Expenses

```http
GET /api/v1/expenses
Authorization: Bearer {token}
Roles: Manager, Admin

Query Parameters:
- section_id (optional, use "general" for general expenses)
- type (optional)
- start_date (optional)
- end_date (optional)
- per_page (optional, default: 15)
```

### Log Expense

```http
POST /api/v1/expenses
Authorization: Bearer {token}
Roles: Manager, Admin

Body:
{
  "section_id": 1,
  "type": "utilities",
  "amount": 500.00,
  "description": "Electricity bill for January",
  "expense_date": "2026-01-16"
}
```

### View Expense

```http
GET /api/v1/expenses/{id}
Authorization: Bearer {token}
Roles: Manager, Admin
```

---

## Waste Management

### List Waste Logs

```http
GET /api/v1/waste
Authorization: Bearer {token}

Query Parameters:
- section_id (optional)
- reason (optional: spoilage, expiry, damage, handling_error, other)
- start_date (optional)
- end_date (optional)
- approved (optional: true/false)
- per_page (optional, default: 15)
```

### Report Waste

```http
POST /api/v1/waste
Authorization: Bearer {token}

Body:
{
  "waste_type": "raw_material",
  "raw_material_id": 1,
  "quantity": 5,
  "reason": "spoilage",
  "notes": "Found mold on vegetables"
}
```

Or for prepared food:

```json
{
    "waste_type": "prepared_food",
    "production_log_id": 10,
    "quantity": 3,
    "reason": "expiry"
}
```

### View Waste Log

```http
GET /api/v1/waste/{id}
Authorization: Bearer {token}
```

### Approve Waste

```http
POST /api/v1/waste/{id}/approve
Authorization: Bearer {token}
Roles: Manager, Admin
```

---

## Reports

### Admin Dashboard

```http
GET /api/v1/reports/dashboard
Authorization: Bearer {token}
Roles: Manager, Admin

Query Parameters:
- start_date (optional, default: start of month)
- end_date (optional, default: today)
```

Returns: revenue, profit, expenses, waste_cost, low_stock_items, top_selling_items

### Section Dashboard

```http
GET /api/v1/reports/sections/{sectionId}/dashboard
Authorization: Bearer {token}

Query Parameters:
- start_date (optional)
- end_date (optional)
```

Returns: production_batches, profit, waste_cost, expenses, prepared_inventory

### Inventory Health Report

```http
GET /api/v1/reports/inventory-health
Authorization: Bearer {token}
```

Returns all materials with stock status and reorder requirements.

### Sales Report

```http
GET /api/v1/reports/sales
Authorization: Bearer {token}

Query Parameters:
- start_date (required)
- end_date (required)
- section_id (optional)
```

### Profit & Loss Report

```http
GET /api/v1/reports/profit-loss
Authorization: Bearer {token}
Roles: Manager, Admin

Query Parameters:
- start_date (required)
- end_date (required)
- section_id (optional)
```

Returns detailed P&L with revenue, cost_of_sales, expenses, waste.

### Waste Report

```http
GET /api/v1/reports/waste
Authorization: Bearer {token}

Query Parameters:
- start_date (required)
- end_date (required)
```

Returns waste summary grouped by reason.

### Expense Report

```http
GET /api/v1/reports/expenses
Authorization: Bearer {token}
Roles: Manager, Admin

Query Parameters:
- start_date (required)
- end_date (required)
```

Returns general expenses and breakdown by section.

### Top Selling Items

```http
GET /api/v1/reports/top-selling
Authorization: Bearer {token}

Query Parameters:
- start_date (optional)
- end_date (optional)
- limit (optional, default: 10)
```

---

## Report Exports

All export endpoints return file downloads (Excel or PDF format).

### Export Sales Report (Excel)

```http
GET /api/v1/reports/sales/export/excel
Authorization: Bearer {token}

Query Parameters:
- start_date (required)
- end_date (required)
- section_id (optional)
```

Returns: Excel file download (`sales_report_YYYY-MM-DD_HHMMSS.xlsx`)

### Export Sales Report (PDF)

```http
GET /api/v1/reports/sales/export/pdf
Authorization: Bearer {token}

Query Parameters:
- start_date (required)
- end_date (required)
- section_id (optional)
```

Returns: PDF file download (`sales_report_YYYY-MM-DD_HHMMSS.pdf`)

### Export Profit & Loss (Excel)

```http
GET /api/v1/reports/profit-loss/export/excel
Authorization: Bearer {token}
Roles: Manager, Admin

Query Parameters:
- start_date (required)
- end_date (required)
- section_id (optional)
```

Returns: Excel file download (`profit_loss_YYYY-MM-DD_HHMMSS.xlsx`)

### Export Profit & Loss (PDF)

```http
GET /api/v1/reports/profit-loss/export/pdf
Authorization: Bearer {token}
Roles: Manager, Admin

Query Parameters:
- start_date (required)
- end_date (required)
- section_id (optional)
```

Returns: PDF file download (`profit_loss_YYYY-MM-DD_HHMMSS.pdf`)

### Export Waste Report (Excel)

```http
GET /api/v1/reports/waste/export/excel
Authorization: Bearer {token}

Query Parameters:
- start_date (required)
- end_date (required)
```

Returns: Excel file download (`waste_report_YYYY-MM-DD_HHMMSS.xlsx`)

### Export Waste Report (PDF)

```http
GET /api/v1/reports/waste/export/pdf
Authorization: Bearer {token}

Query Parameters:
- start_date (required)
- end_date (required)
```

Returns: PDF file download (`waste_report_YYYY-MM-DD_HHMMSS.pdf`)

### Export Expense Report (Excel)

```http
GET /api/v1/reports/expenses/export/excel
Authorization: Bearer {token}
Roles: Manager, Admin

Query Parameters:
- start_date (required)
- end_date (required)
```

Returns: Excel file download (`expense_report_YYYY-MM-DD_HHMMSS.xlsx`)

### Export Expense Report (PDF)

```http
GET /api/v1/reports/expenses/export/pdf
Authorization: Bearer {token}
Roles: Manager, Admin

Query Parameters:
- start_date (required)
- end_date (required)
```

Returns: PDF file download (`expense_report_YYYY-MM-DD_HHMMSS.pdf`)

### Export Inventory Health (Excel)

```http
GET /api/v1/reports/inventory-health/export/excel
Authorization: Bearer {token}
```

Returns: Excel file download (`inventory_health_YYYY-MM-DD_HHMMSS.xlsx`)

### Export Inventory Health (PDF)

```http
GET /api/v1/reports/inventory-health/export/pdf
Authorization: Bearer {token}
```

Returns: PDF file download (`inventory_health_YYYY-MM-DD_HHMMSS.pdf`)

### Export Top Selling Items (Excel)

```http
GET /api/v1/reports/top-selling/export/excel
Authorization: Bearer {token}

Query Parameters:
- start_date (optional)
- end_date (optional)
- limit (optional, default: 10)
```

Returns: Excel file download (`top_selling_items_YYYY-MM-DD_HHMMSS.xlsx`)

---

## Response Format

### Success Response

```json
{
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response

```json
{
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### Pagination Response

```json
{
  "current_page": 1,
  "data": [...],
  "first_page_url": "...",
  "from": 1,
  "last_page": 5,
  "last_page_url": "...",
  "next_page_url": "...",
  "path": "...",
  "per_page": 15,
  "prev_page_url": null,
  "to": 15,
  "total": 75
}
```

---

## HTTP Status Codes

-   `200 OK` - Successful GET request
-   `201 Created` - Successful POST request
-   `400 Bad Request` - Validation error
-   `401 Unauthorized` - Not authenticated
-   `403 Forbidden` - Not authorized (wrong role)
-   `404 Not Found` - Resource not found
-   `422 Unprocessable Entity` - Validation failed
-   `500 Internal Server Error` - Server error

---

## Role-Based Access Summary

| Endpoint                    | Admin | Manager | Procurement | Store Keeper | Chef | Sales |
| --------------------------- | ----- | ------- | ----------- | ------------ | ---- | ----- |
| Procurements (Create)       | ✅    | ❌      | ✅          | ❌           | ❌   | ❌    |
| Material Requests (Create)  | ❌    | ❌      | ❌          | ❌           | ✅   | ❌    |
| Material Requests (Approve) | ✅    | ✅      | ❌          | ❌           | ❌   | ❌    |
| Material Requests (Fulfill) | ✅    | ✅      | ❌          | ✅           | ❌   | ❌    |
| Recipes (Create)            | ✅    | ✅      | ❌          | ❌           | ✅   | ❌    |
| Production (Create)         | ❌    | ❌      | ❌          | ❌           | ✅   | ❌    |
| Production (Approve)        | ✅    | ✅      | ❌          | ❌           | ❌   | ❌    |
| Sales (Create)              | ❌    | ❌      | ❌          | ❌           | ❌   | ✅    |
| Expenses (View/Create)      | ✅    | ✅      | ❌          | ❌           | ❌   | ❌    |
| Waste (Approve)             | ✅    | ✅      | ❌          | ❌           | ❌   | ❌    |
| Reports (Dashboard)         | ✅    | ✅      | ❌          | ❌           | ❌   | ❌    |

---

## Testing with cURL

### Example: Login (via Laravel Breeze)

```bash
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"chef@example.com","password":"password"}'
```

### Example: Create Procurement

```bash
curl -X POST http://localhost/api/v1/procurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "supplier_id": 1,
    "purchase_date": "2026-01-16",
    "items": [{
      "raw_material_id": 1,
      "quantity": 100,
      "unit_cost": 5.50
    }]
  }'
```

### Example: Get Inventory

```bash
curl -X GET http://localhost/api/v1/inventory \
  -H "Authorization: Bearer YOUR_TOKEN"
```
