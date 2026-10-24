# Amadeus GDS Training Simulator (1A)

A modern, high-fidelity **Amadeus Global Distribution System (GDS) Simulator** designed for airline ticketing academies, travel agency staff, and students. Replicates the authentic **Amadeus Selling Platform Connect** cryptic terminal experience with full-screen CRT/Modern themes, dual-database flexibility (SQLite & PostgreSQL), global airline & airport databases, and an intelligent interactive curriculum.

---

## ✈️ Key Features

- **Authentic Cryptic Terminal Emulator**:
  - Exact 80-column monospace screen layout with command prompt (`>`).
  - Multi-work area navigation: **Area A, B, C, D, E, and F** with independent session scratchpads.
  - Command history navigation (Up / Down arrows).
  - Dual aesthetics: **Modern Amadeus Selling Platform Connect** and **Vintage CRT Phosphor Screen** (with Green & Amber themes and CRT scanlines).

- **Comprehensive Global Datasets**:
  - **6,071 active IATA world airports & city codes** (Heathrow, JFK, Hamad, Dubai, Tokyo, Singapore, Paris, etc.).
  - **992 active IATA commercial airlines** with 2-letter codes, callsigns, and authentic 3-digit e-ticket prefixes (e.g. `157` for Qatar Airways, `125` for British Airways, `176` for Emirates).
  - Complete aircraft equipment directory (77W, 359, 789, 388, 320, 738, etc.).

- **Complete Cryptic Command Engine**:
  - `DAN`, `DAC`, `DNA`, `DNE`, `DC`: Global airport, city, airline, aircraft, and country encoding/decoding.
  - `AN`, `SN`: Neutral flight availability and timetable schedules with real Amadeus column formatting and class availability buckets (`J9 C9 D9 Y9 B9 M9...`).
  - `MD`, `MU`, `MT`, `MB`: Screen paging.
  - `SS`: Short sell seat from availability line (`SS1Y1`, `SS2J3`).
  - `NM`: Passenger name entry (Adult, Child `CHD`, Infant `INF`).
  - `AP`, `APE`: Passenger/agency phone and email contacts.
  - `TKTL`, `TKOK`: Ticketing status and time limits.
  - `RF`: Received from element.
  - `RM`, `RC`, `SSR`, `OSI`: General/confidential remarks and special service requests (e.g. `SR VGML`, `SR WCHR`).
  - `FQD`: Published fare quote display.
  - `FXP`, `FXX`, `FXB`: Fare calculator, best buy rebooking, and Transitional Stored Ticket (TST) creation.
  - `FP`: Form of payment (`FP CASH`, `FP CCVI4111111111111111/1226`).
  - `ET`, `ER`: End Transaction with mandatory **PRINT** validation; issues unique 6-character PNR record locators (e.g. `K9X2QP`).
  - `RT`: PNR retrieval by record locator, passenger surname, or active display.
  - `XE`, `XI`: Segment and element cancellation.
  - `TTP`: Electronic ticket document print / issuance (generates 13-digit ticket numbers).
  - `TWD`: Electronic ticket coupon display.

- **Dual-Database Support (SQLite & PostgreSQL)**:
  - **SQLite (Default)**: Zero-config local database (`data/gds.db`) in WAL mode. Runs instantly without external database setup.
  - **PostgreSQL Ready**: Switch by setting `DATABASE_TYPE=postgres` and `DATABASE_URL=postgresql://user:pass@host:5432/dbname`.

- **Multi-User Role-Based Access Control**:
  - **Student / Trainee**: Access guided lessons, earn scores, save personal PNRs.
  - **Instructor / Administrator**: Live operations hub (`/instructor`) to inspect trainee rosters, audit student PNRs, and review complete cryptic booking records.
  - Built-in 1-click demo logins:
    - **Student**: `student` / `amadeus123`
    - **Instructor**: `instructor` / `amadeus123`

- **Interactive Training Companion & PNR Inspector**:
  - Structured modules with scenario story briefings and step-by-step objectives.
  - Progressive 3-tier hint engine (Hint 1 $\rightarrow$ Syntax Guide $\rightarrow$ "Show Solution in Terminal").
  - Live PNR Visualizer checking mandatory **PRINT** elements (**P**hone, **R**eceived From, **I**tinerary, **N**ame, **T**icketing) and stored TSTs.

---

## 🚀 Quick Start

### 1. Installation

```bash
# Clone repository
cd /home/saif/Documents/Projects/gds_amadeus

# Initialize database (creates SQLite DB & default users)
npm run seed
```

### 2. Development Server

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) in your web browser.

### 3. Production Build & Start

```bash
npm run build
npm start
```

### 4. Run Automated Test Suite

```bash
npm test
```

Runs the 21 automated integration tests validating the terminal API, authentication, PNR lifecycle, pricing, ticketing, and instructor hub.

---

## 🗄️ Database Configuration

### SQLite (Default)
By default, the simulator uses a local SQLite database at `data/gds.db`. No environment variables are required.

### PostgreSQL
To connect to an external PostgreSQL database:
1. Create a `.env.local` file:
```env
DATABASE_TYPE=postgres
DATABASE_URL=postgresql://postgres:password@localhost:5432/amadeus_gds
JWT_SECRET=your-secure-jwt-secret
```
2. Run database initialization:
```bash
npm run seed
```

---

## ⌨️ Amadeus Cryptic Command Cheat Sheet

| Purpose | Syntax Example | Notes |
| :--- | :--- | :--- |
| **Sign-In** | `JI*1234AB/GS` or `JI` | Sign into work area |
| **Sign-Out** | `JO` | Sign out of current area |
| **Decode Airport / City** | `DAN LONDON` or `DAN DOH` | Shows all airports in area |
| **Decode Airline** | `DNA QR` or `DNA EMIRATES` | Shows carrier name, ICAO, ticket prefix |
| **Decode Aircraft** | `DNE 77W` or `DNE 359` | Shows aircraft manufacturer and category |
| **Flight Availability** | `AN15NOVLHRJFK` | Neutral flight availability |
| **Filter by Airline** | `AN20DECDXBLHR/AEK` | Filter availability by carrier |
| **Paging** | `MD`, `MU`, `MT`, `MB` | Move Down, Up, Top, Bottom |
| **Short Sell Seat** | `SS1Y1` or `SS2J3` | Sell seats from display line |
| **Passenger Name** | `NM1DOE/JOHN MR` | Adult passenger |
| **Child Passenger** | `NM1BROWN/TIM(CHD/15MAY16)` | Child with birthdate |
| **Infant Passenger** | `NM1DOE/BABY(INF/01JAN24)` | Infant passenger |
| **Contact Element** | `AP DOH 974-555-1234-A` | Agency/Passenger phone |
| **Email Contact** | `APE JOHN.DOE@GMAIL.COM` | Email contact |
| **Ticketing Status** | `TKTL15NOV/LON1A0980` | Ticketing time limit |
| **Ticket OK** | `TKOK` | Confirmed ticketing status |
| **Received From** | `RF PASSENGER` | Person providing reservation details |
| **Special Request** | `SR VGML` or `SR WCHR` | Special Service Request (SSR) |
| **Remarks** | `RM VIP PASSENGER` | General PNR remark |
| **Fare Quote Display** | `FQDDOHLHR/D15NOV/AQR` | View published tariff table |
| **Pricing & Store TST** | `FXP` | Price itinerary and store TST |
| **Best Buy Pricing** | `FXB` | Rebook to cheapest available class & price |
| **Form of Payment** | `FP CASH` or `FP CCVI4111111111111111/1226` | Cash or Credit Card |
| **Commission** | `FM 0` | Commission percentage |
| **End Transaction** | `ET` | Validates PRINT and creates 6-char PNR |
| **End & Redisplay** | `ER` | Saves and redisplays PNR |
| **Ignore Changes** | `IG` | Clears transient work area buffer |
| **Retrieve PNR** | `RT 6XYZ12` or `RT DOE` or `RT` | Retrieve by locator, surname, or active |
| **Cancel Element** | `XE 1` or `XE 2` | Cancel segment or element |
| **Issue E-Ticket** | `TTP` | Ticket Print (13-digit e-ticket document) |
| **Display Ticket** | `TWD` | Display electronic ticket coupon details |
| **Help Reference** | `HE` or `HELP` | Quick cryptic syntax reference |

---

## 🎓 Training Curriculum Modules

1. **Module 1: System Access & IATA Decoding / Encoding**
   - Lesson 1: Work Area Sign-In and System Access
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

## 🛡️ License & Academic Use
Developed for educational simulation and training purposes. All simulated airline and GDS entries mirror IATA standard formats.
