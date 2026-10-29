<div align="center">

# ✈️ Amadus Training Simulator (1A)

**High-fidelity, browser-based Amadus Global Distribution System (GDS) simulator built for aviation academies, ticketing agents, and students.**

Replicates the authentic **Amadeus Selling Platform Connect** cryptic terminal experience with CRT Green, CRT Amber, and Modern Dark themes, global airline/airport datasets, role-based workflows (Cadets & Instructors), and interactive training drills — powered by **PHP (Laravel 13), Tailwind CSS v4, & Alpine.js**.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B%20%2F%208.3-777bb4?logo=php)](https://php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-ff2d20?logo=laravel)](https://laravel.com/)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-v4-38bdf8?logo=tailwind-css)](https://tailwindcss.com/)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8bc0d0?logo=alpine.js)](https://alpinejs.dev/)
[![Database](https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL%20%7C%20MariaDB-green?logo=sqlite)](https://sqlite.org/)
[![Tests](https://img.shields.io/badge/Tests-119%20Passed%20%2F%20675%20Assertions-emerald.svg)](tests/Feature/)

</div>

---

## ✨ Key Features

- **Altéa Cryptic Terminal Emulator**: Authentic multi-work area terminal (**A–F**), command history recall, monospaced CRT display (Green, Amber, Modern Dark), and responsive mobile/tablet layout with slide-out navigation.
- **Realistic PNR Lifecycle Engine**: Complete **P.R.I.N.T.** validation (**P**hone, **R**eceived From, **I**tinerary, **N**ame, **T**icketing), 6-character Alpha-Numeric Record Locators, TST fare pricing (`FXP`/`FXX`), Form of Payment (`FP`), and 13-digit e-ticket issuance (`TTP`).
- **Global Aviation Datasets**: Pre-seeded with **6,071 airports** (IATA/ICAO), **992 airlines** with real ticket number prefixes, and **44 aircraft equipment types**.
- **Role-Based Training Ecosystem**:
  - **Cadet / Student**: Interactive drill scenarios, live command syntax validator, slide-out PNR inspector drawer, and visual compliance meters.
  - **Flight Instructor**: Trainee cockpit spectator, batch cadet provisioning (up to 500 accounts), custom scenario builder, and simulated Queue bank injector (Queues 1, 7, 8, 9, 87).
- **Automated Installers**: Browser wizard (`/install`) and headless CLI (`php artisan app:install`) with automatic domain (`APP_URL`) and database detection.

---

## 💻 System Requirements

| Component | Minimum | Recommended |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP 8.2 | **PHP 8.3** or **8.4** |
| **PHP Extensions** | `pdo`, `pdo_sqlite` (or `pdo_mysql`), `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo` | + `bcmath`, `opcache`, `redis` |
| **Composer** | Composer 2.x | Composer 2.8+ |
| **Database** | SQLite 3.35+ (zero-config default) | **MySQL 8.0+** or **MariaDB 10.5+** |
| **Node.js** | Node.js 18.x (asset building only) | **Node.js 20 LTS** & npm 10+ |
| **Web Server** | PHP built-in CLI server (Dev) | **Nginx 1.20+** or **Apache 2.4+** |

---

## 🚀 Quick Installation

### Development Setup

```bash
# 1. Clone & enter repository
git clone https://github.com/saifullahshams2/gds-amadeus.git
cd gds-amadeus

# 2. Install dependencies & configure environment
composer install
cp .env.example .env
php artisan key:generate

# 3. Initialize database & instructor account
php artisan app:install

# 4. Start local development server
php artisan serve
# Access application at http://localhost:8000
```
> **Alternative Web Setup:** Visit `http://localhost:8000/install` in your browser to configure via the 4-step Web Wizard.

### Production Deployment

```bash
# 1. Clone & install production dependencies
git clone https://github.com/saifullahshams2/gds-amadeus.git /var/www/gds-amadeus
cd /var/www/gds-amadeus
composer install --no-dev --optimize-autoloader
npm ci && npm run build && rm -rf node_modules

# 2. Configure .env & permissions
cp .env.example .env
php artisan key:generate --force
# (Edit .env with production DB credentials and APP_URL=https://your-domain.com)
sudo chown -R www-data:www-data /var/www/gds-amadeus
sudo chmod -R 775 storage bootstrap/cache

# 3. Provision database & lock installer
php artisan app:install --force --app-url="https://your-domain.com"
php artisan config:cache && php artisan route:cache && php artisan view:cache

# 4. Background queue & scheduler
# Run queue worker: php artisan queue:work --daemon (or configure via Supervisor)
# Add cron: * * * * * cd /var/www/gds-amadeus && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔑 Default Accounts

| Role | Username | Password | Purpose |
| :--- | :--- | :--- | :--- |
| **Instructor** | `instructor` | `amadeus123` | Classroom management, trainee spectator, scenario builder |
| **Cadet** | `student` | `amadeus123` | Trainee terminal simulator and drill exercises |

*Instructor credentials can be customized during `php artisan app:install` or via the web installer.*

---

## 📜 Essential Cryptic Commands

| Command | Action / Syntax | Example |
| :--- | :--- | :--- |
| `JI` / `SI` | Sign In with duty code | `JI*1234AB/GS` |
| `SO` | Sign Out of active terminal session | `SO` |
| `DAN` / `DNA` / `DNE` | Decode Airport / Airline / Aircraft | `DAN LHR`, `DNA BA`, `DNE 77W` |
| `AN` | Neutral flight availability display | `AN15DECLHRJFK` |
| `SS` | Short sell flight seats | `SS1Y1` (line 1, Y class, 1 seat) |
| `NM` | Passenger name entry | `NM1SMITH/JOHN MR` |
| `AP` | Contact phone / email | `AP LON 44-20-7946-0123`, `APE USER//TEST.COM` |
| `TKTL` / `TKOK` | Ticketing time limit / Ticket OK status | `TKTL15DEC`, `TKOK` |
| `RF` | Received From (*mandatory to store PNR*) | `RF PASSENGER` |
| `FXP` / `FXX` | Price itinerary (with / without TST) | `FXP` |
| `FP` | Form of payment (cash or card) | `FP CASH` |
| `ET` / `ER` | End transaction / End and redisplay | `ET`, `ER` |
| `RT` | Retrieve active PNR buffer or by locator | `RT` or `RT Z8P3X9` |
| `TTP` / `TWD` | Issue electronic ticket / Ticket display | `TTP`, `TWD` |
| `QS` / `Q<num>` | Queue bank sign-in / Queue access | `QS`, `Q1` |
| `HE` | Terminal help directory | `HE` |

---

## 🧪 Testing

Run the automated test suite (119 integration and unit tests):

```bash
php artisan test --compact
```

---

## 📄 License

The Amadus Training Simulator is open-sourced software licensed under the [MIT License](LICENSE).
