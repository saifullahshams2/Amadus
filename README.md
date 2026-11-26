<div align="center">

# ✈️ Amadus Training Simulator (1A)
## **(It's "Amadus" Not Amadeus)**

**High-fidelity, browser-based Amadeus Global Distribution System (GDS) simulator for individual ticketing agents and aviation students.**

Replicates the authentic **Amadus Selling Platform Connect** cryptic terminal experience with CRT Green, CRT Amber, and Modern Dark themes, global airline/airport datasets, and interactive training drills — powered by **PHP (Laravel 13), Tailwind CSS v4, & Alpine.js**.

[![PHP](https://img.shields.io/badge/PHP-8.5%2B-777bb4?logo=php)](https://php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-ff2d20?logo=laravel)](https://laravel.com/)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-v4-38bdf8?logo=tailwind-css)](https://tailwindcss.com/)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8bc0d0?logo=alpine.js)](https://alpinejs.dev/)
[![Database](https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL%20%7C%20MariaDB-green?logo=sqlite)](https://sqlite.org/)
[![Vibe Coded](https://img.shields.io/badge/Built%20With-AI%20%26%20Vibe%20Coding-8a2be2.svg)](#-built-with-ai--vibe-coding)

</div>

---

> **Single-user application.** Amadus is designed for **one account per installation**. The installer provisions exactly one user, and there is no public registration, role switching, or admin/student management. To run separate accounts, deploy separate copies of the application.

## ✨ Key Features

- **Altéa Cryptic Terminal Emulator**: Authentic multi-work area terminal (**A–F**), command history recall, monospaced CRT display (Green, Amber, Modern Dark), and responsive mobile/tablet layout with slide-out navigation.
- **Realistic PNR Lifecycle Engine**: Complete **P.R.I.N.T.** validation (**P**hone, **R**eceived From, **I**tinerary, **N**ame, **T**icketing), 6-character Alpha-Numeric Record Locators, TST fare pricing (`FXP`/`FXX`), Form of Payment (`FP`), and 13-digit e-ticket issuance (`TTP`).
- **Global Aviation Datasets**: Pre-seeded with **6,071 airports** (IATA/ICAO), **992 airlines** with real ticket number prefixes, and **44 aircraft equipment types**.
- **Personal Training Workspace**:
  - Live command syntax validator and an active-PNR workspace with visual compliance meters.
  - Training drills and custom assignment builder — create, practice, and delete your own drills.
  - Learning analytics (syntax accuracy, stored PNRs, drill scores) shown directly on the dashboard.
- **Account & Profile Management**: Inline profile page to change your user name, display name, email address and password, plus optional custom SMTP configuration for password-reset emails.
- **Automated Installers**: Browser wizard (`/install`) and headless CLI (`php artisan app:install --password=...`) with automatic domain (`APP_URL`) and database detection.

---

## 💻 System Requirements

| Component | Minimum | Recommended |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP 8.5 | **PHP 8.5+** |
| **PHP Extensions** | `pdo`, `pdo_sqlite` (or `pdo_mysql`), `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo` | + `bcmath`, `opcache`, `redis` |
| **Database** | SQLite 3.35+ (zero-configuration default) | **MySQL 8.0+** or **MariaDB 10.5+** |
| **Web Server** | PHP built-in CLI server (Dev) | **Nginx 1.20+** or **Apache 2.4+** |
| **Node.js** | Node 20+ (local development builds only) | Node 22+ |

---

## 🚀 Automated Installation & Deployment (Recommended)

Production releases are packaged as standalone archives containing all backend vendor packages and pre-compiled assets. No manual build tools, Node.js, or Composer commands are required on your server.

### 1. Download & Extract Release
1. Download the latest `amadus-vX.X.X.zip` package from [GitHub Releases](https://github.com/saifullahshams2/gds-amadeus/releases).
2. Extract the archive into your server's web root (e.g. `/var/www/gds-amadeus` or your shared hosting directory).
3. Ensure the web server document root points to the **`public/`** folder.
4. Ensure the `storage/` and `bootstrap/cache/` directories are writable:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

### 2. Run Automatic Web Setup Wizard
Open your domain in any browser:
```text
https://your-domain.com/install
```
The built-in 4-step wizard will automatically:
- Verify system environment and required PHP extensions.
- Detect or configure your database (instant zero-config SQLite or MySQL/MariaDB).
- Auto-detect your application domain (`APP_URL`) for secure redirects and email notifications.
- Run migrations, seed global airports & airline reference datasets, and provision your **single** application account.
- Permanently lock the installer for security once completed.

> **Prefer the command line?** Run the headless installer in your terminal. A password of at least 6 characters is **required** (there is no default password):
> ```bash
> php artisan app:install --username=agent --email=agent@example.com --password='choose-a-strong-one' --app-url=https://your-domain.com
> ```
> In interactive mode (`php artisan app:install`) the command prompts for the password instead.

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

## 🤖 Built with AI & Vibe Coding

This entire application was **vibe coded and developed using autonomous AI coding agents**.

- **Autonomous Architecture**: From the ground up, the full-stack architecture, database models, and service layer were planned, iterated, and crafted through AI-assisted pair programming.
- **Cryptic Parser & Domain Modeling**: The comprehensive Amadus Altéa cryptic parsing engine, PNR lifecycle engine, flight availability schedules, and IATA decoding tables were implemented and refined with agentic AI tooling.
- **Retro Interface & UX**: The authentic CRT terminal emulator (including phosphor green/amber scanlines, multi-screen buffers, and mobile responsiveness) was designed and styled iteratively through AI prompts and real-time verification.

---

## 🛠️ Local Development Setup

For developers contributing to or modifying the source code:

```bash
# 1. Clone the repository
git clone https://github.com/saifullahshams2/amadus.git
cd amadus

# 2. Install backend & frontend dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Build frontend assets (Tailwind CSS v4 + Alpine.js are bundled locally)
npm run build   # or `npm run dev` for hot-reloading

# 5. Run automated installer (password is required)
php artisan app:install --password='a-strong-password'

# 6. Start local development server
php artisan serve
# Open http://localhost:8000
```

---

## 📄 License

The Amadus Training Simulator is open-sourced software licensed under the [MIT License](LICENSE).
