<div align="center">

# ✈️ Amadus Training Simulator (1A)

**A high-fidelity, browser-based Amadus Global Distribution System (GDS) simulator built for aviation academies, ticketing agents, travel agencies, and students.**

Replicates the authentic **Amadeus Selling Platform Connect** cryptic terminal experience with full-screen CRT Green, CRT Amber, and Modern Dark themes, global airline and airport datasets, database flexibility (SQLite, MySQL, & MariaDB), role-based multi-user accounts (Cadets & Instructors), and an intelligent interactive training curriculum — powered 100% by **PHP (Laravel 13), Tailwind CSS v4, & Alpine.js**.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B%20%2F%208.3-777bb4?logo=php)](https://php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-ff2d20?logo=laravel)](https://laravel.com/)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-v4-38bdf8?logo=tailwind-css)](https://tailwindcss.com/)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8bc0d0?logo=alpine.js)](https://alpinejs.dev/)
[![Vite](https://img.shields.io/badge/Vite-8-646cff?logo=vite)](https://vitejs.dev/)
[![Database](https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL%20%7C%20MariaDB-green?logo=sqlite)](https://sqlite.org/)
[![Tests](https://img.shields.io/badge/Tests-99%20Passed%20%2F%20558%20Assertions-emerald.svg)](tests/Feature/)

</div>

---

## 📋 Table of Contents

- [Key Features](#-key-features)
- [System Requirements](#-system-requirements)
- [🛠️ Development Mode Installation](#-development-mode-installation)
  - [Prerequisites](#development-prerequisites)
  - [Step-by-Step Local Setup](#step-by-step-local-setup)
  - [Running the Development Environment](#running-the-development-environment)
  - [Running Tests](#running-tests)
- [🚀 Production Mode Installation & Deployment](#-production-mode-installation--deployment)
  - [Production Architecture & Prerequisites](#production-architecture--prerequisites)
  - [Step 1: Server Provisioning & Dependencies](#step-1-server-provisioning--dependencies)
  - [Step 2: Deploying Codebase](#step-2-deploying-codebase)
  - [Step 3: Installing Dependencies (Optimized)](#step-3-installing-dependencies-optimized)
  - [Step 4: Production Environment Configuration](#step-4-production-environment-configuration)
  - [Step 5: Filesystem Permissions & Ownership](#step-5-filesystem-permissions--ownership)
  - [Step 6: Database Setup & Provisioning](#step-6-database-setup--provisioning)
  - [Step 7: Production Optimization & Caching](#step-7-production-optimization--caching)
  - [Step 8: Nginx Web Server Configuration](#step-8-nginx-web-server-configuration)
  - [Step 9: Queue Worker Setup (Supervisor / Systemd)](#step-9-queue-worker-setup-supervisor--systemd)
  - [Step 10: Task Scheduler (Cron)](#step-10-task-scheduler-cron)
  - [Step 11: SSL / TLS Certificate (Let's Encrypt)](#step-11-ssl--tls-certificate-lets-encrypt)
  - [Production Update & Zero-Downtime Routine](#production-update--zero-downtime-routine)
- [🔑 Roles, Accounts & Authentication](#-roles-accounts--authentication)
- [🧭 Project Architecture](#-project-architecture)
- [📜 Amadeus Cryptic Command Cheat Sheet](#-amadeus-cryptic-command-cheat-sheet)
- [❓ Troubleshooting & FAQ](#-troubleshooting--faq)
- [📄 License](#-license)

---

## ✨ Key Features

- **Full-Screen Altéa Cryptic Terminal Emulator**: Authentic terminal interface supporting multi-work areas (**A** through **F**), command history recall, monospaced typography, and CRT Green / Amber / Modern Dark display modes.
- **Realistic PNR Lifecycle Engine**: Full implementation of mandatory **P.R.I.N.T.** requirements (**P**hone, **R**eceived From, **I**tinerary, **Name**, **T**icketing), automated 6-character Alpha-Numeric Record Locator generation, TST fare calculation (`FXP`), Form of Payment (`FP`), and 13-digit e-ticket issuance (`TTP`).
- **Global Aviation Datasets**: Pre-seeded with **6,071 airports** (IATA/ICAO), **992 airlines** with real ticket prefix numbers, and **44 aircraft equipment types** (Airbus A350/A380, Boeing 77W/787/737MAX, Embraer, ATR).
- **Dual-Role Training Ecosystem**:
  - **Trainee Cadet Dashboard**: Interactive scenario drills, real-time command syntax validator, slide-out PNR inspector drawer, and visual compliance meters.
  - **Instructor Operations Hub**: Live trainee cockpit grid, real-time keystroke spectator modal, batch cadet provisioning (up to 500 accounts), custom scenario builder, and simulated Queue bank injector (Queues 1, 7, 8, 9, 87).
- **Dual Installation Engines**: Fast-track setup via browser-based 4-step Web Wizard (`/install`) or headless CLI installer (`php artisan app:install`).

---

## 💻 System Requirements

| Component | Minimum Requirement | Recommended |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP 8.2 | **PHP 8.3** or **PHP 8.4** |
| **PHP Extensions** | `pdo`, `pdo_sqlite` (or `pdo_mysql`), `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo` | + `zip`, `bcmath`, `opcache`, `redis` |
| **Node.js** | Node.js 18.x | **Node.js 20.x LTS** & npm 10+ |
| **Composer** | Composer 2.x | Composer 2.8+ |
| **Database** | SQLite 3.35+ (zero-config) | **MySQL 8.0+** or **MariaDB 10.5+** |
| **Web Server** | Built-in PHP CLI Server (Dev) | **Nginx 1.20+** or **Apache 2.4+** |

---

## 🛠️ Development Mode Installation

Follow these steps to set up a local development environment on macOS, Linux, or Windows.

### Development Prerequisites

Ensure PHP 8.2+, Composer, and Node.js are installed on your machine:
```bash
php -v
composer -V
node -v
npm -v
```

### Step-by-Step Local Setup

#### 1. Clone the Repository
```bash
git clone https://github.com/your-username/gds-amadeus.git
cd gds-amadeus
```

#### 2. Install PHP Dependencies
```bash
composer install
```

#### 3. Install Frontend Dependencies
```bash
npm install
```

#### 4. Configure Environment File
Copy the example environment configuration:
```bash
# Linux / macOS
cp .env.example .env

# Windows (Command Prompt / PowerShell)
copy .env.example .env
```

Generate the Laravel application encryption key:
```bash
php artisan key:generate
```

#### 5. Database Configuration
By default, the application is pre-configured to use **SQLite** (requiring zero setup):

```bash
# Create the local SQLite database file if it does not exist:
# Linux / macOS
touch database/database.sqlite

# Windows (PowerShell)
New-Item -ItemType File -Force -Path database/database.sqlite
```

> **Prefer MySQL/MariaDB for local dev?**
> Update your `.env` file:
> ```env
> DB_CONNECTION=mysql
> DB_HOST=127.0.0.1
> DB_PORT=3306
> DB_DATABASE=amadeus_gds
> DB_USERNAME=root
> DB_PASSWORD=
> ```

#### 6. Initialize Database & Provision Account

You have three convenient options to initialize the database and master datasets:

##### Option A: CLI Automated Setup (Recommended)
Run the built-in installer to migrate tables, seed global datasets (airports, airlines, aircraft, drills, queues), and provision the Flight Instructor account:
```bash
php artisan app:install
```
*Follow the interactive prompt or press Enter to accept defaults (`instructor` / `amadeus123`).*

##### Option B: Standard Laravel Seed
```bash
php artisan migrate:fresh --seed
php artisan app:remove-installer
```

##### Option C: Browser Web Setup Wizard
1. Start the server: `php artisan serve`
2. Open your browser at **[http://localhost:8000/install](http://localhost:8000/install)**.
3. Follow the 4-step wizard to verify system requirements, connect the database, run migrations/seeders, and configure your primary instructor account.

---

### Running the Development Environment

You can start the local development services using either of the following workflows:

#### Workflow 1: Single Command (Laravel + Vite Concurrently)
```bash
composer run dev
```
*This uses `php artisan dev` to concurrently run the PHP development server and Vite asset bundler.*

#### Workflow 2: Two Terminal Windows
**Terminal 1 (Backend Server):**
```bash
php artisan serve
```
The application will be accessible at **[http://localhost:8000](http://localhost:8000)**.

**Terminal 2 (Frontend Hot-Module-Replacement with Vite & Tailwind v4):**
```bash
npm run dev
```

If you prefer static asset compilation instead of running the Vite dev server, build the production bundle:
```bash
npm run build
```

---

### Running Tests

The test suite contains 99 feature & unit tests covering terminal cryptic commands, PNR lifecycle, pricing, ticketing, instructor classroom controls, and API contracts.

Run the test suite:
```bash
php artisan test
```

Or run tests in compact mode:
```bash
php artisan test --compact
```

---

## 🚀 Production Mode Installation & Deployment

This section provides an enterprise-ready, battle-tested deployment manual for Linux servers (Ubuntu 22.04/24.04 LTS or Debian 12).

### Production Architecture & Prerequisites

- **Web Server**: Nginx with HTTP/2 and TLS 1.3
- **PHP Process Manager**: PHP-FPM 8.3
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Background Worker**: Supervisor or Systemd daemon for Queue processing
- **Scheduler**: Linux Cron calling `php artisan schedule:run`
- **Domain**: Fully-qualified domain name (e.g., `gds.yourdomain.com`) with DNS A record pointing to your server IP.

---

### Step 1: Server Provisioning & Dependencies

Update server repositories and install all required packages:
```bash
sudo apt update && sudo apt upgrade -y

# Install Nginx, Git, Curl, Unzip, and MySQL Server
sudo apt install -y nginx git curl unzip supervisor mysql-server certbot python3-certbot-nginx

# Install PHP 8.3 and all required extensions
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-sqlite3 \
    php8.3-mbstring php8.3-xml php8.3-curl php8.3-tokenizer \
    php8.3-fileinfo php8.3-zip php8.3-bcmath php8.3-intl php8.3-opcache

# Install Composer 2.x
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js 20 LTS & npm
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

Verify installations:
```bash
php -v && composer -V && node -v && nginx -v
```

---

### Step 2: Deploying Codebase

Clone the application into `/var/www/gds-amadeus`:
```bash
sudo mkdir -p /var/www/gds-amadeus
sudo git clone https://github.com/your-username/gds-amadeus.git /var/www/gds-amadeus
cd /var/www/gds-amadeus
```

---

### Step 3: Installing Dependencies (Optimized)

Install backend packages without development tools, and compile the production frontend assets:
```bash
# Install production PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Install frontend dependencies and compile static assets with Vite & Tailwind CSS v4
npm ci
npm run build

# Optional: Clean up node_modules to save server disk space
rm -rf node_modules
```

---

### Step 4: Production Environment Configuration

Create the production `.env` file:
```bash
cp .env.example .env
php artisan key:generate --force
```

Configure `.env` for production:
```bash
nano .env
```

Apply the following production settings:
```env
APP_NAME="Amadus"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gds.yourdomain.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=warning

# Database Configuration (MySQL / MariaDB recommended)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amadeus_gds_prod
DB_USERNAME=gds_prod_user
DB_PASSWORD=YOUR_STRONG_DATABASE_PASSWORD_HERE

# Session & Security (Secure cookie over HTTPS)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# Queues & Cache
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local

VITE_APP_NAME="${APP_NAME}"
```

---

### Step 5: Filesystem Permissions & Ownership

Grant ownership to the web server user (`www-data`) and ensure write permissions for storage and caching directories:
```bash
sudo chown -R www-data:www-data /var/www/gds-amadeus
sudo find /var/www/gds-amadeus -type f -exec chmod 644 {} \;
sudo find /var/www/gds-amadeus -type d -exec chmod 755 {} \;

# Give read/write permissions to storage and bootstrap cache
sudo chmod -R 775 /var/www/gds-amadeus/storage
sudo chmod -R 775 /var/www/gds-amadeus/bootstrap/cache
```

---

### Step 6: Database Setup & Provisioning

#### 1. Create MySQL Database and User
Log in to MySQL:
```bash
sudo mysql -u root -p
```

Execute SQL commands:
```sql
CREATE DATABASE IF NOT EXISTS amadeus_gds_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'gds_prod_user'@'127.0.0.1' IDENTIFIED BY 'YOUR_STRONG_DATABASE_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON amadeus_gds_prod.* TO 'gds_prod_user'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

#### 2. Provision Database & Primary Flight Instructor via CLI
Execute the non-interactive setup command to migrate tables, seed reference datasets (airports, airlines, aircraft, drills, queues), and provision your production instructor:
```bash
sudo -u www-data php artisan app:install --force \
  --name="Head of Flight Operations" \
  --username="chief_instructor" \
  --email="instructor@yourdomain.com" \
  --password="YourVerySecurePassword123!" \
  --office="LON1A0980" \
  --agent="IN01GS"
```

#### 3. Lock & Secure Installer
Ensure the installer lock file is in place so that the `/install` web route is permanently deactivated:
```bash
sudo -u www-data php artisan app:remove-installer --force
```

---

### Step 7: Production Optimization & Caching

Compile routes, Blade views, events, and configuration into high-speed cached files:
```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
```

---

### Step 8: Nginx Web Server Configuration

Create an Nginx server block configuration:
```bash
sudo nano /etc/nginx/sites-available/gds-amadeus.conf
```

Paste the following optimized configuration (replace `gds.yourdomain.com` with your actual domain):
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name gds.yourdomain.com;
    root /var/www/gds-amadeus/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    index index.php index.html;
    charset utf-8;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
    }

    # Cache static build assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    # Deny access to hidden files (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site configuration and test Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/gds-amadeus.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

### Step 9: Queue Worker Setup (Supervisor / Systemd)

The simulator uses asynchronous queues for simulated queue banks, notifications, and background processing. Configure a daemon worker to process jobs continuously.

#### Option A: Using Supervisor (Recommended)
Create a Supervisor worker configuration:
```bash
sudo nano /etc/supervisor/conf.d/gds-worker.conf
```

Add the following:
```ini
[program:gds-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gds-amadeus/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/gds-amadeus/storage/logs/worker.log
stopwaitsecs=3600
```

Load and start the worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gds-worker:*
```

#### Option B: Using Systemd Service
```bash
sudo nano /etc/systemd/system/gds-worker.service
```

```ini
[Unit]
Description=Amadeus GDS Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/gds-amadeus/artisan queue:work database --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Enable and start the service:
```bash
sudo systemctl daemon-reload
sudo systemctl enable --now gds-worker
```

---

### Step 10: Task Scheduler (Cron)

Configure Laravel's scheduler in the server cron:
```bash
sudo crontab -u www-data -e
```

Add the following entry:
```cron
* * * * * cd /var/www/gds-amadeus && php artisan schedule:run >> /dev/null 2>&1
```

---

### Step 11: SSL / TLS Certificate (Let's Encrypt)

Secure your domain with a free SSL/TLS certificate via Certbot:
```bash
sudo certbot --nginx -d gds.yourdomain.com
```
*Certbot will automatically configure HTTPS redirection and renewal timers.*

---

### Production Update & Zero-Downtime Routine

When deploying new commits or releases to production, execute this update routine:

```bash
cd /var/www/gds-amadeus

# 1. Put app into maintenance mode
php artisan down --render="errors::503" --secret="your-bypass-token"

# 2. Pull latest code
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
rm -rf node_modules

# 4. Migrate database
php artisan migrate --force

# 5. Refresh caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart queue workers
php artisan queue:restart
# or: sudo supervisorctl restart gds-worker:*

# 7. Bring application back up
php artisan up
```

---

## 🔑 Roles, Accounts & Authentication

### 1. Account Roles

| Role | Access Level | Primary Features |
| :--- | :--- | :--- |
| **Instructor** (`instructor`) | Administrative & Instructor Hub | Live cadet cockpit spectator, pending approval gate, manual & batch cadet enrollment, scenario creator & grading, queue broadcaster. |
| **Cadet / Student** (`student`) | Trainee Terminal & Dashboard | Cryptic Terminal (Areas A–F), assigned drill scenarios, live command syntax validator, compliance checker, IATA decoding. |

### 2. Cadet Onboarding Workflow

1. **Self-Registration**: Trainees sign up via `/register`.
2. **Pending Approval Gate**: Newly registered cadets are flagged with `is_approved = false` and held at `/pending-approval`.
3. **Instructor Approval**: The Instructor reviews pending signups in `/instructor` and activates them with 1-click (**Approve Access**).
4. **Batch Provisioning**: Instructors can alternatively provision up to 500 cadets at once with sequential callsigns (`trainee_1`, `trainee_2`) directly from the Instructor Hub.

### 3. Local Evaluation Accounts

When initialized with seeders (`php artisan db:seed` or `php artisan app:install`), the following credentials are ready for testing:

| Role | Username | Password | Notes |
| :--- | :--- | :--- | :--- |
| **Instructor** | `instructor` | `amadeus123` | Default primary instructor |
| **Cadet** | `student` | `amadeus123` | Trainee account with scenario progress |

---

## 🧭 Project Architecture

```
gds-amadeus/
├── app/
│   ├── Console/Commands/
│   │   ├── InstallAppCommand.php         # CLI installer (php artisan app:install)
│   │   └── RemoveInstallerCommand.php    # Lock web installer (php artisan app:remove-installer)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php        # Session & API login/register/logout
│   │   │   ├── DashboardController.php   # Student/Instructor unified hub & APIs
│   │   │   ├── InstallerController.php   # 4-step web deployment wizard
│   │   │   ├── InstructorController.php  # Trainee management, approvals & audits
│   │   │   ├── LandingController.php     # Public landing cockpit portal
│   │   │   ├── PasswordResetController.php # Password recovery & token verification
│   │   │   ├── TerminalController.php    # Amadeus cryptic command dispatcher
│   │   │   └── TrainingController.php    # Interactive curriculum progress
│   │   └── Middleware/
│   │       ├── EnsureAppIsInstalled.php  # Locks /install & enforces system setup
│   │       ├── EnsureUserIsInstructor.php # Role-based access control
│   │       └── SecurityHeadersMiddleware.php # CSP & security response headers
│   ├── Models/                           # Eloquent models (User, Pnr, Scenario, etc.)
│   └── Services/
│       ├── Amadeus/                      # Amadeus Altéa cryptic parsing engine
│       │   ├── AmadeusParser.php         # Primary command parser & router
│       │   ├── AvailabilityService.php   # AN/SN neutral flight schedules
│       │   ├── DecodeService.php         # DAN/DAC/DNA/DNE IATA decoders
│       │   ├── PnrService.php            # P.R.I.N.T. validator & 80-col formatter
│       │   ├── PricingService.php        # FXP/FXX fare pricing & TST records
│       │   └── TicketingService.php      # TTP 13-digit e-tickets & TWD coupons
│       ├── Installer/
│       │   └── InstallerService.php      # Setup requirement checks & DB runner
│       └── Training/
│           └── CurriculumService.php     # Drills, modules & hints
├── database/
│   ├── data/
│   │   ├── airlines_full.json            # 992 global airlines
│   │   └── airports_full.json            # 6,071 global airports
│   ├── migrations/                       # Database schema migrations
│   └── seeders/DatabaseSeeder.php        # Master data & scenario seeders
├── resources/
│   ├── css/app.css                       # Tailwind CSS v4 source
│   ├── js/app.js                         # Application frontend scripts
│   └── views/                            # Blade UI templates (Terminal, Hub, Auth)
├── routes/
│   ├── web.php                           # Web routes & REST API endpoints
│   └── console.php                       # Console command registrations
└── tests/
    └── Feature/                          # 99 automated integration & unit tests
```

---

## 📜 Amadeus Cryptic Command Cheat Sheet

| Command Syntax | Category | Action / Purpose | Example Entry |
| :--- | :--- | :--- | :--- |
| `JI` / `SI` | Sign In | Sign in to active work area with duty code | `JI*1234AB/GS` |
| `SO` | Sign Out | Sign out of the active terminal session | `SO` |
| `DAN <name/code>` | Decoding | Decode or encode airport or city name | `DAN LONDON` or `DAN LHR` |
| `DNA <airline>` | Decoding | Decode airline 2-letter IATA code or name | `DNA BA` or `DNA EMIRATES` |
| `DNE <code/name>` | Decoding | Decode aircraft equipment type | `DNE 77W` or `DNE 359` |
| `AN<date><pair>` | Availability | Neutral flight availability display | `AN15DECLHRJFK` |
| `AN.../A<carrier>` | Availability | Filter availability display by carrier | `AN20NOVDOHLHR/AQR` |
| `MD` / `MU` | Availability | Move Down / Move Up across display pages | `MD` or `MU` |
| `SS<line><cls><qty>` | Sell Segment | Short sell seats directly from availability line | `SS1Y1` or `SS2J2` |
| `XE<segment>` | Cancel | Cancel flight segment from itinerary buffer | `XE1` or `XE2` |
| `NM1<last>/<first> <title>` | Passenger Name | Add adult passenger name | `NM1SMITH/JOHN MR` |
| `AP <contact>` | Contact | Add agency or passenger telephone contact | `AP LON 44-20-7946-0123-A` |
| `APE <email>` | Contact | Add passenger email contact | `APE JOHN.SMITH//GMAIL.COM` |
| `TKTL<date>` | Ticketing | Set Ticketing Time Limit deadline | `TKTL15DEC` |
| `TKOK` | Ticketing | Confirm ticket already issued / OK | `TKOK` |
| `RF <name>` | Received From | Identification of requester (*mandatory to close PNR*) | `RF PASSENGER` |
| `FXP` | Fare Pricing | Price itinerary and store Transitional Stored Ticket (TST) | `FXP` |
| `FXX` | Fare Pricing | Price itinerary without storing TST record | `FXX` |
| `FP <method>` | Form of Payment | Add cash or simulated credit card payment | `FP CASH` or `FP CCVI4111.../1226` |
| `ET` | End Transaction | Validate PRINT rules, issue Record Locator, and clear buffer | `ET` |
| `ER` | End & Redisplay | End transaction, issue Record Locator, and redisplay PNR | `ER` |
| `RT` / `RT <locator>` | Retrieve | Retrieve active PNR buffer or lookup by 6-char locator | `RT` or `RT Z8P3X9` |
| `IG` / `IR` | Ignore | Ignore changes / Ignore and redisplay saved PNR | `IG` or `IR` |
| `TTP` | Electronic Ticket | Issue 13-digit simulated electronic airline ticket | `TTP` |
| `TWD` | Ticket Display | Display electronic ticket receipt and coupon coupons | `TWD` |
| `QS` / `Q<num>` | Queues | Sign into Queue Bank / Access specific queue | `QS` or `Q1` |
| `HE` | Help System | Access terminal command help index | `HE` |

---

## ❓ Troubleshooting & FAQ

### 1. `Vite manifest not found` Error
**Cause**: The compiled assets in `public/build` have not been generated.  
**Solution**: Run `npm install && npm run build` (or keep `npm run dev` running during local development).

### 2. `Application is not installed. Please run setup at /install`
**Cause**: The installation lock file `storage/installed` does not exist.  
**Solution**: Complete the web setup at `http://localhost:8000/install`, or run:
```bash
php artisan app:install
```

### 3. `Application is already installed and installer is locked (403)`
**Cause**: Security feature preventing malicious re-installation over an active database.  
**Solution**: If you intentionally want to wipe and re-install, delete `storage/installed` or run:
```bash
php artisan app:install --force
```

### 4. `The stream or file ".../laravel.log" could not be opened: failed to open stream: Permission denied`
**Cause**: Incorrect permissions on the `storage/` directory.  
**Solution**:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 5. `No application encryption key has been specified`
**Cause**: `APP_KEY` is missing from `.env`.  
**Solution**: Run `php artisan key:generate`.

---

## 📄 License

The Amadus Training Simulator is open-sourced software licensed under the [MIT License](LICENSE).
