# POS Kuliner - Point of Sale & Recipe Costing (HPP) System

POS Kuliner is an integrated Point of Sale (POS) and inventory control application built specifically for culinary businesses, restaurants, cafes, and cloud kitchens. The platform bridges point-of-sale cashier transactions with automated raw material inventory deductions and real-time recipe cost of goods sold (HPP - Harga Pokok Penjualan) tracking.

---

## Key Modules & Capabilities

### 1. Point of Sale Cashier
* Touch-friendly cashier ordering interface designed for rapid order entry.
* Cash session management: register opening balance, shift handovers, and end-of-day cash reconciliation.
* Support for multiple settlement methods: cash, digital wallets, bank transfers, and custom payment types.
* Itemized receipt generation with tax and service charge calculations.

### 2. Menu Catalog & Variant Management
* Structured catalog organizing items by category (Appetizers, Main Dishes, Beverages, Desserts).
* Flexible pricing structures and modifier/variant support.
* External delivery platform aliases for unified item management across channels.

### 3. Dynamic Recipe Builder & Automated HPP
* Multi-ingredient recipe formulation for every menu item.
* Portion-level unit conversion (grams, milliliters, pieces, tablespoons).
* Automated HPP recalculation whenever ingredient purchasing costs fluctuate.
* Real-time gross profit margin indicators alerting managers to low-margin offerings.

### 4. Inventory Control & Automated Stock Deductions
* Real-time stock movement ledger tracking purchases, transaction deductions, waste, and manual stock counts.
* Automatic ingredient deduction upon completed cashier checkout based on the underlying recipe.
* Multi-outlet inventory support with separate stock balances.
* Reorder threshold notifications for critical raw materials.

### 5. AI Menu Ingestion Pipeline
* Optical document analysis engine for extracting menu items, categories, and prices from scanned restaurant menus and digital receipts.
* Bulk import workflow with verification and field-mapping previews before database insertion.

### 6. Audit & Operational Transparency
* Audit logging for price modifications, voids, discount applications, and drawer opening actions.
* Historical reporting for shift turnover and daily sales performance.

---

## Technical Stack

* Framework: Laravel 13
* Runtime: PHP 8.5
* Database: MySQL 8.0
* Frontend: Tailwind CSS, Blade Templating Engine
* Containerization: Docker, Nginx

---

## Project Structure

```
app/
├── Http/Controllers/     Cashier, Menu, Recipe, Inventory, and Report controllers
├── Models/               Eloquent models (Menu, Recipe, Ingredient, Transaction, CashSession, Outlet)
resources/
├── views/                Blade views (POS register, Inventory ledger, Recipe builder, Reports)
routes/
├── web.php               Application endpoints and route groups
database/
├── migrations/           Database schema definitions
├── seeders/              Initial culinary categories, sample menus, and recipes
```

---

## Installation & Deployment

### Prerequisites
* Docker and Docker Compose
* Git

### Setup Instructions
1. Clone the repository into your local development environment:
   ```bash
   git clone <repository_url>
   cd <repository_folder>
   ```

2. Copy the sample environment configuration:
   ```bash
   cp .env.example .env
   ```

3. Update the database credentials in `.env`:
   ```env
   APP_NAME=POS Kuliner
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel13_kuliner
   DB_USERNAME=usr_kuliner
   DB_PASSWORD=your_secure_password
   ```

4. Launch the application container:
   ```bash
   docker compose up -d
   ```

5. Install dependencies and build assets:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

6. Generate the encryption key:
   ```bash
   php artisan key:generate
   ```

7. Execute database migrations and seed sample menus:
   ```bash
   php artisan migrate --seed
   ```

8. Cache application configuration for optimal performance:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## License

This software is distributed under the proprietary license of the author. All rights reserved.
