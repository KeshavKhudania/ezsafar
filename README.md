# EzSafar Automotive Mobility Platform

**EzSafar** is a modern, full-stack automotive mobility web application built with **Core PHP** and **MySQL/MariaDB**, tailored for car rentals, certified used car sales, online car valuations, and automotive travel guides in **Jind, Haryana**.

---

## Key Features

### 1. Customer-Facing Mobility Portal
- **Self-Drive & Chauffeur Car Rentals (`car-rentals.php`)**:
  - Browse verified fleet (Hatchbacks, Sedans, Compact SUVs, Thar 4x4, 7-Seater Innova Crysta).
  - Instant online booking modal with pickup date selection and pricing calculation.
  - Zero security deposit and free daily mileage allowance policies.
- **Buy Certified Used Cars (`buy-cars.php`)**:
  - Filter by budget, brand, body type, fuel, and transmission.
  - 140-point inspection matrix and free Haryana RC transfer guarantee.
  - One-click Test Drive scheduling.
- **Instant Car Valuation & Sell (`sell-your-car.php`)**:
  - 3-step instant valuation calculator.
  - Direct lead capture with door-to-door inspection scheduling.
- **Dedicated SEO Vehicle Details (`vehicle.php?slug=...`)**:
  - Comprehensive car specification breakdown, features, and photo showcase.
  - Schema.org JSON-LD structured data (`@type: Car`, `Offer`, `AutoDealer`).
  - Canonical URLs and OpenGraph social preview tags.
- **Blog & Knowledge Hub (`blog.php` & `blog-post.php`)**:
  - Searchable automotive guides, road trip itineraries, and Haryana RTO tutorials.
  - Social media share integration (WhatsApp, X, Clipboard).
  - View counters, reading time estimates, and schema markup (`@type: BlogPosting`).

### 2. Admin Management Panel (`/admin`)
- **Dashboard (`admin/index.php`)**: Real-time revenue metrics, fleet count, active bookings, and quick action shortcuts.
- **Rental Bookings (`admin/bookings.php`)**: Manage customer booking requests and statuses (Confirmed, Completed, Cancelled).
- **Rental Fleet Management (`admin/rental-cars.php`)**: Add, edit, toggle availability, and configure custom SEO metadata per vehicle.
- **Certified Used Cars Inventory (`admin/used-cars.php`)**: Track stock, price, EMI, registration state, and inspection points.
- **Sell Car Leads (`admin/sell-requests.php`)**: Review seller inquiries, car details, and inspect requests.
- **Customer Inquiries (`admin/inquiries.php`)**: Manage general questions and test-drive appointments.
- **Blog Management (`admin/blogs.php` & `admin/blog-edit.php`)**: Full-featured article editor with quick formatting toolbar, live Google SERP preview, and category filtering.
- **Website & SEO Settings (`admin/settings.php`)**: Global settings manager with live Google SERP preview, GA tag injection, currency symbol, and hub contact details.

---

## Technical Stack

- **Backend**: Core PHP 8.2+ (Clean MVC-style modular scripts, PDO MySQL prepared statements)
- **Database**: MariaDB / MySQL with relational integrity, foreign keys, and indexes
- **Frontend / Styling**: Tailwind CSS with Stitch design tokens, Google Material Symbols, Plus Jakarta Sans typography
- **SEO & Social**: Schema.org JSON-LD structured data, dynamic XML Sitemap (`sitemap.php`), `robots.txt`, and OpenGraph metadata

---

## Installation & Setup

### 1. Requirements
- Apache 2.4+
- PHP 8.1 or 8.2+ (with PDO and pdo_mysql extensions enabled)
- MySQL 5.7+ or MariaDB 10.4+
- XAMPP / LAMP / WAMP environment

### 2. Directory Setup
Clone or extract the project to your Apache web directory:
```bash
# Example for XAMPP on Linux
cd /opt/lampp/htdocs/
git clone <repo-url> ezsafar
```

### 3. Database Import
1. Start MySQL/MariaDB.
2. Create database `ezsafar`:
```sql
CREATE DATABASE ezsafar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
3. Import the database schema and seed data from `database.sql`:
```bash
mysql -u root -p ezsafar < /opt/lampp/htdocs/ezsafar/database.sql
```

### 4. Database Configuration
Verify your database credentials in `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ezsafar');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Access the Platform
- **Public Website**: `http://localhost/ezsafar/`
- **Rental Fleet**: `http://localhost/ezsafar/car-rentals.php`
- **Certified Cars**: `http://localhost/ezsafar/buy-cars.php`
- **Blog Hub**: `http://localhost/ezsafar/blog.php`
- **XML Sitemap**: `http://localhost/ezsafar/sitemap.php`
- **Admin Control Center**: `http://localhost/ezsafar/admin/login.php`
  - **Username**: `admin` (or `admin@ezsafar.in`)
  - **Password**: `admin123`

---

## Project Structure

```
ezsafar/
├── admin/                     # Admin control center
│   ├── includes/              # Admin header, footer, navigation
│   ├── blogs.php              # Blog post listing & quick status toggle
│   ├── blog-edit.php          # Article editor with SERP preview
│   ├── bookings.php           # Rental booking management
│   ├── inquiries.php          # Test drives & customer inquiries
│   ├── login.php / logout.php # Admin authentication
│   ├── rental-cars.php        # Rental fleet management
│   ├── sell-requests.php      # Customer car valuation leads
│   ├── settings.php           # Website & SEO global settings
│   └── used-cars.php          # Certified used cars inventory
├── api/                       # Future AJAX / REST endpoints
├── config/                    # Configuration files
│   ├── constants.php          # Application-wide constants & branding
│   └── db.php                 # PDO database singleton connection
├── includes/                  # Public shared components
│   ├── functions.php          # Sanitization, rupee formatter, slugify, helpers
│   ├── header.php             # Global HTML head, SEO tags, top nav
│   └── footer.php             # Footer links, hub contact details
├── blog.php                   # Public blog hub
├── blog-post.php              # Public article reader with JSON-LD
├── buy-cars.php               # Certified used cars listing & filtering
├── car-rentals.php            # Rental fleet listing & instant booking
├── database.sql               # Complete MySQL schema & seed data
├── index.php                  # Main homepage
├── robots.txt                 # Search engine crawler directives
├── sell-your-car.php          # Instant car valuation & lead form
├── sitemap.php                # Dynamic XML sitemap generator
├── vehicle.php                # Dedicated car product detail page
└── README.md                  # Project documentation
```

---

## License

Proprietary © EzSafar Mobility Solutions Pvt. Ltd. All rights reserved.
