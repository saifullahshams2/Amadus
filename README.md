<div align="center">

# ✈️ Amadeus GDS Training Simulator (1A)

**A high-fidelity, browser-based Amadeus Global Distribution System (GDS) simulator built for aviation academies, ticketing agents, travel agencies, and students.**

Replicates the authentic **Amadeus Selling Platform Connect** cryptic terminal experience with full-screen CRT/Modern themes, global airline and airport datasets, dual-database flexibility (SQLite & PostgreSQL), role-based multi-user accounts, and an intelligent interactive training curriculum.

[![GitHub License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Next.js](https://img.shields.io/badge/Next.js-14.2-black?logo=next.js)](https://nextjs.org/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.6-blue?logo=typescript)](https://www.typescriptlang.org/)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-3.4-38bdf8?logo=tailwind-css)](https://tailwindcss.com/)
[![Database](https://img.shields.io/badge/Database-SQLite%20%7C%20PostgreSQL-green?logo=sqlite)](https://sqlite.org/)
[![Tests](https://img.shields.io/badge/Tests-21%20Passed-emerald.svg)](scripts/test_api.js)

[Features](#-key-features) • [Quick Start](#-quick-start) • [Database Setup](#-dual-database-configuration) • [Command Cheat Sheet](#-amadeus-cryptic-command-cheat-sheet) • [Curriculum](#-training-curriculum-modules) • [Instructor Hub](#-instructor-operations-hub)

</div>

---

## 📖 Overview

Training new travel agents and airline ticketing specialists on live GDS production environments comes with high financial risk: accidental booking penalties, Agency Debit Memos (ADMs), cancellation fees, and expensive GDS transaction query charges.

The **Amadeus GDS Training Simulator** provides a realistic, 100% risk-free simulated environment that replicates authentic Amadeus cryptic terminal inputs and outputs down to exact character columns, flight availability displays, passenger name record (PNR) lifecycle states, and 13-digit e-ticket document issuance.

```
--- RLR ---
RP/LON1A0980/1234AB            24OCT26/1830Z   M8RB7F
  1.DOE/JOHN MR
  1  QR 001 Y 15NOV 5 DOHLHR HK1  0630 1000   *1A/E*
  2 AP LON 44-20-7946-0912-A
  3 TK TL15NOV/LON1A0980
  4 FA PAX 157-9823019283/ETQR/24OCT26
  5 FP CASH
  6 RF PASSENGER
```

---

## ✨ Key Features

### 🖥️ Authentic Cryptic Terminal Screen
- **Standard 80-Column Grid**: Monospaced terminal display matching real Amadeus Selling Platform Connect formatting.
- **Multi-Work Area Support**: Fully functional tabs for **Area A, B, C, D, E, and F**, each with independent transient scratchpad buffers.
- **Toggleable Visual Themes**:
  - **Modern 1A Connect**: Crisp modern agency theme with slate background and Amadeus cyan accents.
  - **Vintage CRT Green Phosphor**: Classic green phosphor terminal with CRT scanlines and subtle cathode-ray glow.
  - **Vintage CRT Amber Phosphor**: Warm retro amber terminal aesthetic.
- **Terminal Controls**: Command history navigation with `↑` and `↓` arrow keys, instant screen clear, and auto-scroll.

### 🌍 Comprehensive Worldwide Datasets
- **6,071 Active IATA World Airports**: Complete global airport directory with 3-letter IATA codes, cities, countries, coordinates, and timezones (`airports_full.json`).
- **992 Active IATA Commercial Airlines**: World airlines with 2-letter codes, callsigns, countries, and authentic 3-digit e-ticket prefixes (`157` for Qatar Airways, `125` for British Airways, `176` for Emirates, `220` for Lufthansa, `001` for American Airlines, etc.).
- **Aircraft Equipment Directory**: Global widebody, narrowbody, regional, and turboprop aircraft types (`77W`, `359`, `789`, `388`, `320`, `738`, `AT7`, etc.).

### 🗄️ Dual-Database Support (SQLite & PostgreSQL)
- **SQLite (Default)**: Zero-config local database (`data/gds.db`) in WAL mode. No external database installation needed—works instantly out-of-the-box!
- **PostgreSQL**: Production-ready support. Switch seamlessly by configuring `DATABASE_TYPE=postgres` and `DATABASE_URL=postgresql://...`.
- **Database Abstraction**: Intelligent parameter mapping converts PostgreSQL parameters (`$1, $2, ...`) to SQLite positional parameters (`?`) automatically.

### 🔐 Multi-User & Role-Based Access Control
- **Student / Trainee**: Sign in, complete guided training modules, earn scores, and store personal PNR reservations.
- **Instructor / Administrator**: Live **Instructor Operations Hub** (`/instructor`) to monitor enrolled students, inspect trainee progress, and audit student PNR records.
- **1-Click Demo Accounts**:
  | Role | Username | Password | Purpose |
  | :--- | :--- | :--- | :--- |
  | **Student** | `student` | `amadeus123` | Trainee portal & guided lessons |
  | **Instructor** | `instructor` | `amadeus123` | Instructor operations hub & PNR audit |

### 🧭 Interactive Training Companion & PNR Inspector
- **Curriculum Lessons**: 5 comprehensive modules (8 lessons) covering everything from IATA encoding to e-ticket issuance.
- **Progressive 3-Tier Hint Engine**:
  - `Tier 1`: Concept Hint (subtle nudge).
  - `Tier 2`: Syntax Guide (command format rule).
  - `Tier 3`: Exact Entry (one-click "Show Solution in Terminal").
- **Live Objective Validation**: Evaluates student commands in real-time and validates PNR state before granting credit.
- **Visual PNR Inspector**: Beginner-friendly breakdown displaying the mandatory **PRINT** checklist (**P**hone, **R**eceived From, **I**tinerary, **N**ame, **T**icketing), booked segment cards, and stored TST fares.

---

## ⚡ Quick Start

### Prerequisites
- **Node.js** (v18.x or newer, v20+ recommended)
- **npm** (v9.x or newer)

### 1. Clone the Repository
```bash
git clone https://github.com/saifullahshams2/gds-amadeus.git
cd gds-amadeus
```

### 2. Install Dependencies
```bash
npm install
```

### 3. Initialize the Database & Seed Demo Users
```bash
npm run seed
```
*This creates `data/gds.db` and configures the default Student and Instructor accounts.*

### 4. Run the Development Server
```bash
npm run dev
```
Open **[http://localhost:3000](http://localhost:3000)** in your browser.

### 5. Production Build & Start
```bash
npm run build
npm start
```

### 6. Run the Automated Test Suite
```bash
npm test
```
*Executes all 21 automated integration tests validating cryptic commands, PNR state validation, e-ticketing, and instructor APIs.*

---

## 🗄️ Dual-Database Configuration

The simulator includes a unified database adapter that operates identically on both SQLite and PostgreSQL.

### Option A: SQLite (Default - Recommended for Local Dev & Testing)
No configuration required! The app defaults to SQLite stored in `./data/gds.db`.

### Option B: PostgreSQL (Recommended for Production & Cloud Deployments)
1. Copy the example environment file:
   ```bash
   cp .env.example .env.local
   ```
2. Set `DATABASE_TYPE` and your PostgreSQL connection string in `.env.local`:
   ```env
   DATABASE_TYPE=postgres
   DATABASE_URL=postgresql://username:password@localhost:5432/amadeus_gds
   JWT_SECRET=your-secure-jwt-secret
   ```
3. Initialize the database schema:
   ```bash
   npm run seed
   ```

---

## ⌨️ Amadeus Cryptic Command Cheat Sheet

The engine parses authentic Amadeus cryptic syntax:

| Functional Area | Command Syntax | Description | Example |
| :--- | :--- | :--- | :--- |
| **System Access** | `JI*<AgentID>/<Role>` | Sign into active work area | `JI*1234AB/GS` or `JI` |
| **System Access** | `JO` | Sign out of current work area | `JO` |
| **Encoding / Decoding** | `DAN <city/airport>` | Decode or encode location/airport | `DAN LONDON` or `DAN DOH` |
| **Encoding / Decoding** | `DNA <airline>` | Decode or encode airline code/name | `DNA QR` or `DNA EMIRATES` |
| **Encoding / Decoding** | `DNE <equipment>` | Decode aircraft equipment type | `DNE 77W` or `DNE 359` |
| **Flight Availability** | `AN<date><orig><dest>` | Neutral flight availability display | `AN15NOVLHRJFK` |
| **Flight Availability** | `AN.../A<carrier>` | Availability filtered by airline | `AN20DECDXBLHR/AEK` |
| **Paging** | `MD`, `MU`, `MT`, `MB` | Move Down, Up, Top, Bottom | `MD` |
| **Short Sell Seat** | `SS<seats><class><line>` | Book seat directly from display | `SS1Y1` or `SS2J3` |
| **Passenger Name** | `NM1<SURNAME>/<NAME> MR` | Add adult passenger name | `NM1DOE/JOHN MR` |
| **Passenger Name** | `NM1...(CHD/<DOB>)` | Add child passenger with birthdate | `NM1BROWN/TIM(CHD/15MAY16)` |
| **Passenger Name** | `NM1...(INF/<DOB>)` | Add infant passenger | `NM1DOE/BABY(INF/01JAN24)` |
| **Contact Element** | `AP <phone>` | Add contact telephone number | `AP LON 44-20-7946-0912-A` |
| **Contact Element** | `APE <email>` | Add passenger/agency email | `APE PASSENGER@GMAIL.COM` |
| **Ticketing Status** | `TKTL<date>[/<office>]` | Set ticketing time limit | `TKTL15NOV/LON1A0980` |
| **Ticketing Status** | `TKOK` | Confirmed ticket arrangement | `TKOK` |
| **Received From** | `RF <name>` | Record who requested transaction | `RF PASSENGER` or `RF MR SMITH` |
| **Special Service** | `SR <code generic>` | Special Service Request (SSR) | `SR VGML` or `SR WCHR` |
| **Remarks** | `RM <free text>` | General passenger remark | `RM VIP CLIENT REQ AISLE` |
| **Fare Quote Display** | `FQD <citypair>/D<date>/A<c>` | Display published tariffs | `FQDDOHLHR/D15NOV/AQR` |
| **Pricing & TST** | `FXP` | Price itinerary and store TST | `FXP` |
| **Informational Price** | `FXX` | Price itinerary without storing TST | `FXX` |
| **Best Buy Pricing** | `FXB` | Rebook to lowest fare class & store TST | `FXB` |
| **Form of Payment** | `FP CASH` or `FP CC...` | Set payment method | `FP CASH` or `FP CCVI4111.../1226` |
| **Commission** | `FM <percent>` | Record agency commission | `FM 0` |
| **End Transaction** | `ET` | Validate PRINT & generate 6-char PNR | `ET` |
| **End & Redisplay** | `ER` | Save PNR and immediately redisplay | `ER` |
| **Ignore Changes** | `IG` | Discard transient scratchpad edits | `IG` |
| **Ignore & Redisplay**| `IR` | Revert to saved PNR and redisplay | `IR` |
| **Retrieve PNR** | `RT <locator>` or `RT <name>` | Retrieve PNR into active work area | `RT M8RB7F` or `RT DOE` |
| **Cancel Element** | `XE <line>` | Cancel flight segment or element | `XE 1` |
| **Cancel Itinerary** | `XI` | Cancel all flight segments | `XI` |
| **Issue E-Ticket** | `TTP` | Ticket Print (13-digit e-ticket) | `TTP` |
| **Display Ticket** | `TWD` | Display electronic ticket coupons | `TWD` |
| **System Help** | `HE` or `HELP` | Display quick command reference | `HE` |

---

## 🎓 Training Curriculum Modules

The simulator includes a progressive, scenario-based curriculum:

```mermaid
graph LR
    M1[Module 1: IATA Encoding] --> M2[Module 2: Schedules & Availability]
    M2 --> M3[Module 3: Mandatory PRINT Elements]
    M3 --> M4[Module 4: Pricing & TST FXP]
    M4 --> M5[Module 5: Payment & E-Ticketing TTP]
```

1. **Module 1: System Access & IATA Decoding / Encoding**
   - Lesson 1: Work Area Sign-In and System Access (`JI`, `JO`, `HE`)
   - Lesson 2: Decoding Locations, Airlines, & Aircraft (`DAN`, `DNA`, `DNE`)
2. **Module 2: Flight Availability & Schedules**
   - Lesson 1: Searching Neutral Flight Availability (`AN`, `SN`, paging `MD`/`MU`)
3. **Module 3: The Core PNR - Mandatory PRINT Elements**
   - Lesson 1: Building a Confirmed PNR from Scratch (`NM`, `SS`, `AP`, `TKTL`, `RF`, `ET`)
4. **Module 4: Fares, Pricing & TST Storage**
   - Lesson 1: Pricing Active Itineraries with `FXP` and `FQD`
5. **Module 5: Payment & Electronic Ticketing**
   - Lesson 1: Form of Payment and Ticket Issuance (`FP`, `TTP`, `TWD`)

---

## 🛡️ Instructor Operations Hub

Instructors have access to a dedicated dashboard at `/instructor`:
- **Trainee Roster**: Track all registered students, enrollment dates, and completed lessons.
- **PNR Audit Trail**: Inspect all reservations created by students across all sessions.
- **Cryptic PNR Viewer**: Click any student reservation to view its complete Amadeus cryptic layout, passenger names, flight segments, and issued e-ticket documents.

---

## 📁 Project Structure

```
gds-amadeus/
├── data/                       # Local SQLite database directory (gds.db)
├── scripts/
│   ├── download_all_airports_airlines.py  # Global datasets generator
│   ├── seed.js                 # Standalone database initialization script
│   └── test_api.js             # 21-test automated integration suite
├── src/
│   ├── app/                    # Next.js 14 App Router
│   │   ├── api/
│   │   │   ├── auth/           # Login, Register, Session APIs
│   │   │   ├── instructor/     # Instructor roster & PNR review APIs
│   │   │   ├── terminal/       # Cryptic command execution API
│   │   │   └── training/       # Student progress API
│   │   ├── instructor/         # Instructor dashboard page
│   │   ├── login/              # Sign-in page with 1-click demo accounts
│   │   ├── register/           # Trainee registration page
│   │   ├── layout.tsx          # Root app layout
│   │   └── page.tsx            # Main simulator workspace
│   ├── components/
│   │   ├── companion/          # Training sidebar & visual PNR inspector
│   │   ├── layout/             # Top navbar & theme selector
│   │   └── terminal/           # 80-column Amadeus terminal emulator
│   ├── lib/
│   │   ├── amadeus/            # Command parser, availability, PNR, pricing, ticketing
│   │   ├── auth/               # JWT authentication & bcrypt hashing
│   │   ├── curriculum/         # Training modules, lessons & objectives
│   │   ├── data/               # Global airport & airline JSON databases
│   │   └── db/                 # Dual SQLite / PostgreSQL database client
│   ├── store/
│   │   └── terminalStore.ts    # Zustand client state for terminal & work areas
│   └── types/                  # TypeScript interface definitions
├── .env.example                # Example environment configuration
├── package.json
├── tailwind.config.js
└── tsconfig.json
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:
1. Fork the repository (`https://github.com/saifullahshams2/gds-amadeus`).
2. Create your feature branch (`git checkout -b feature/NewCrypticCommand`).
3. Commit your changes (`git commit -m 'Add new cryptic command support'`).
4. Ensure all tests pass (`npm test`).
5. Push to the branch (`git push origin feature/NewCrypticCommand`).
6. Open a Pull Request.

---

## 📄 License

Distributed under the **MIT License**. See `LICENSE` for more information.

---

<div align="center">
  <b>Developed for aviation academies, airline personnel, and travel professionals.</b><br>
  <sub>All simulated flight entries mirror standard IATA and Amadeus 1A formats.</sub>
</div>
