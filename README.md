<div align="center">

# ✈️ Amadeus GDS Training Simulator (1A)

**A high-fidelity, browser-based Amadeus Global Distribution System (GDS) simulator built for aviation academies, ticketing agents, travel agencies, and students.**

Replicates the authentic **Amadeus Selling Platform Connect** cryptic terminal experience with full-screen CRT/Modern themes, global airline and airport datasets, database flexibility (SQLite, MySQL, & MariaDB), role-based multi-user accounts, and an intelligent interactive training curriculum — powered 100% by **PHP (Laravel) & Alpine.js**.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4?logo=php)](https://php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11%2F13-ff2d20?logo=laravel)](https://laravel.com/)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-3.4-38bdf8?logo=tailwind-css)](https://tailwindcss.com/)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8bc0d0?logo=alpine.js)](https://alpinejs.dev/)
[![Database](https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL%20%7C%20MariaDB-green?logo=sqlite)](https://sqlite.org/)
[![Tests](https://img.shields.io/badge/Tests-41%20Assertions%20Passed-emerald.svg)](tests/Feature/AmadeusGdsIntegrationTest.php)

</div>

---

## ⚡ Quick Start

### Prerequisites
- **PHP** (8.2 or newer) with SQLite extension (`pdo_sqlite`)
- **Composer** (2.x)

### 1. Initialize Database & Seed Datasets
```bash
php artisan migrate:fresh --seed
```
*This creates the database tables and seeds 6,071 airports, 992 airlines, 44 aircraft models, and demo accounts.*

### 2. Start the Local Server
```bash
php artisan serve
```
Open **[http://localhost:8000](http://localhost:8000)** in your browser.

### 3. Run the Automated Test Suite
```bash
php artisan test
```

---

## 🗄️ Database Configuration (SQLite, MySQL, or MariaDB)

The application supports SQLite, MySQL, and MariaDB through Laravel's database abstraction.

### Option 1: SQLite (Default - Recommended for Local Dev & Testing)
Zero-configuration required! The app defaults to `database/database.sqlite`.

### Option 2: MySQL or MariaDB (Production / External Database)
1. In your `.env` file, configure the connection:
```env
# For MySQL:
DB_CONNECTION=mysql
# For MariaDB:
# DB_CONNECTION=mariadb

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amadeus_gds
DB_USERNAME=your_username
DB_PASSWORD=your_password
```
2. Run the migration and seeder:
```bash
php artisan migrate:fresh --seed
```

---

## 🔑 Demo Accounts

| Role | Username | Password | Purpose |
| :--- | :--- | :--- | :--- |
| **Student** | `student` | `amadeus123` | Trainee portal & guided lessons |
| **Instructor** | `instructor` | `amadeus123` | Instructor operations hub & student PNR audit |

---

## 🧭 Project Architecture

```
app/
├── Http/Controllers/
│   ├── AuthController.php            # Session & API login/register
│   ├── TerminalController.php        # Cryptic execution & objective check
│   ├── TrainingController.php        # Curriculum & progress tracking
│   └── InstructorController.php      # Trainee roster & PNR audit hub
├── Models/
│   ├── User.php                      # Student/Instructor accounts
│   ├── Pnr.php                       # Stored PNR records & JSON data
│   ├── LessonProgress.php            # Trainee progress tracking
│   ├── Airport.php                   # 6,071 IATA/ICAO airports
│   ├── Airline.php                   # 992 airlines with ticket prefixes
│   └── Aircraft.php                  # Global aircraft models
└── Services/
    ├── Amadeus/
    │   ├── AmadeusParser.php         # Central cryptic command parser
    │   ├── AvailabilityService.php   # AN/SN neutral flight availability
    │   ├── PnrService.php            # PRINT validation & 80-column formatter
    │   ├── PricingService.php        # FXP/FXX fare pricing & TST builder
    │   ├── TicketingService.php      # TTP 13-digit e-tickets & TWD viewer
    │   └── DecodeService.php         # DAN/DAC/DNA/DNE IATA lookups
    └── Training/
        └── CurriculumService.php     # 5 modules, 8 lessons, 3-tier hints
resources/views/
├── layouts/app.blade.php             # Base layout with CRT scanline filters
├── terminal/index.blade.php          # Interactive terminal + companion + inspector
├── instructor/index.blade.php        # Instructor operations dashboard
└── auth/                             # Login & register views with 1-click buttons
```

---

## 📜 Amadeus Cryptic Command Cheat Sheet

| Command | Action / Description | Example Entry |
| :--- | :--- | :--- |
| `JI` / `SI` | Sign in to active work area | `JI*1234AB/GS` |
| `DAN` | Decode / encode city or airport | `DAN LONDON` or `DAN DOH` |
| `DNA` | Decode / encode airline | `DNA QR` or `DNA EMIRATES` |
| `DNE` | Decode aircraft equipment type | `DNE 77W` or `DNE 359` |
| `AN` | Neutral flight availability display | `AN15NOVLHRJFK` or `AN15NOVDOHLHR/AQR` |
| `SS` | Direct short sell from display line | `SS1Y1` (Sell 1 seat in Y class from Line 1) |
| `NM` | Enter passenger name element | `NM1DOE/JOHN MR` |
| `AP` | Enter agency / passenger phone | `AP LON 44-20-7946-0912-A` |
| `APE`| Enter passenger email address | `APE JOHN.DOE@GMAIL.COM` |
| `TKTL`| Set ticketing arrangement & time limit | `TKTL15NOV/LON1A0980` |
| `RF` | Set Received From person (mandatory) | `RF PASSENGER` |
| `FXP`| Price itinerary & store TST fare record | `FXP` |
| `FP` | Add form of payment | `FP CASH` or `FP CCVI4111111111111111/1226` |
| `ET` | End transaction & issue Record Locator | `ET` |
| `ER` | End transaction & redisplay PNR | `ER` |
| `RT` | Retrieve PNR by record locator / surname | `RT M8RB7F` or `RT DOE` |
| `TTP`| Issue simulated 13-digit electronic tickets | `TTP` |
| `TWD`| Display electronic ticket document & coupons | `TWD` |
| `HE` | Display terminal command help | `HE` |
