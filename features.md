# Amadus GDS Training Platform — Features Specification

> **Platform Architecture:** Single-user Amadus Global Distribution System (GDS) training simulator with an Altéa cryptic command engine, PNR lifecycle manager, personal training workspace, and account/profile management. Each installation provisions exactly one account; there is no registration, role switching, or multi-user administration.

---

## Table of Contents
1. [Public, Authentication & Onboarding](#1-public-authentication--onboarding)
2. [Personal Dashboard](#2-personal-dashboard)
3. [Account & Profile Management](#3-account--profile-management)
4. [Cryptic Terminal Simulator](#4-cryptic-terminal-simulator)
5. [Amadeus Altéa Cryptic Command Engine](#5-amadeus-altéa-cryptic-command-engine)
6. [REST API Reference](#6-rest-api-reference)
7. [Core Domain Models & Schemas](#7-core-domain-models--schemas)

---

## 1. Public, Authentication & Onboarding

| # | Feature Name | Description | Key Routes / APIs |
|---|---|---|---|
| **01** | **Single-User Installer (Web)** | 4-step deployment wizard verifying PHP runtime and extensions (`pdo`, `pdo_sqlite`/`pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo`), directory write permissions, automatic migrations/seeders, provisioning of the **single** application account, and post-installation lock (`storage/installed`). | `GET /install`<br>`POST /install`<br>`GET /install/requirements`<br>`POST /install/test-db` |
| **02** | **Single-User Installer (CLI)** | Interactive or `--no-interaction` headless installer. A password of at least 6 characters is **required** (`--password`); there is no default password. Supports `--username`, `--email`, `--app-url`, `--office`, `--agent`, and `--force`. | `php artisan app:install` |
| **03** | **User Authentication** | Session login by username **or** email using bcrypt, constant-time credential checks, session fixation protection (`session()->regenerate()`), optional remember-me, and rate limiting (10 req/min). | `GET /login`<br>`POST /login`<br>`POST /api/auth/login` |
| **04** | **Password Recovery** | Request a reset link by username or email with a non-disclosing response, per-account token throttling, and a tokenised reset page. Successfully resetting revokes other active sessions. | `GET /forgot-password`<br>`POST /forgot-password`<br>`GET /reset-password/{token}`<br>`POST /reset-password` |
| **05** | **Security Headers & CSRF** | Strict CSRF validation on all web and `/api/*` routes, CSP with local-only script origin, `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, and HSTS over HTTPS. | Middleware Pipeline |
| **06** | **Trusted-Proxy Control** | Optional `TRUSTED_PROXIES` allowlist; when unset (default) all client-supplied `X-Forwarded-*` headers are ignored so IP-based throttling cannot be spoofed. | `.env` / `bootstrap/app.php` |

---

## 2. Personal Dashboard

| # | Feature Name | Description | Key Routes / APIs |
|---|---|---|---|
| **07** | **Navigation Shell** | Sidebar with office (`LON1A0980`), agent duty code (`1234AB`), work-area indicator, GDS session status, My Profile button, and quick launch to the Terminal. | `GET /dashboard` |
| **08** | **Learning Analytics KPIs** | KPI summary cards: syntax accuracy, command efficiency, stored PNRs, and drill progress. | `GET /dashboard` |
| **09** | **Live Syntax Validator** | Inline command validator that evaluates Amadeus cryptic entries and returns actionable correction hints plus a recent-mistakes log. | `POST /api/dashboard/validate-syntax` |
| **10** | **Active PNR Workspace** | Dedicated page showing the active Record Locator, booking status, passenger count, segments, ticketing deadline, and **P.R.I.N.T.** compliance, with a delete-active-PNR action. | `GET /dashboard/workspace`<br>`GET /api/dashboard/active-pnr`<br>`DELETE /api/dashboard/active-pnr` |
| **11** | **PNR Inspector Drawer** | Slide-out drawer rendering the formatted cryptic image (`RLR`) and structured elements (passengers, segments, contacts, ticketing, TSTs). | Dashboard UI Component |
| **12** | **Drills & Assignments** | Filterable drill list with category and difficulty badges, briefing modal, and automated grading on submission. | `GET /dashboard/drills`<br>`POST /api/dashboard/scenarios/{id}/submit` |
| **13** | **Custom Assignment Builder** | Create your own practice drills (passengers, target flight segments, optional TST/e-ticket requirements) and delete the ones you created. System drills are read-only. | `POST /api/dashboard/scenarios`<br>`DELETE /api/dashboard/scenarios/{id}` |
| **14** | **Automated Drill Grading** | Submission evaluates the active PNR against mandatory PRINT rules, pricing (`FXP`), and ticketing (`TTP`), producing a 0–100 score with per-check audit details. | `POST /api/dashboard/scenarios/{id}/submit` |

---

## 3. Account & Profile Management

| # | Feature Name | Description | Key Routes / APIs |
|---|---|---|---|
| **15** | **Profile Overview** | Identity card (avatar, username, email, stored-PNR count) with inline forms for user name, display name, and email address. | `GET /profile` |
| **16** | **User Name Change** | Change the login identifier after confirming the current password; cascades across PNRs, submissions, terminal activity, queues, lesson progress and authored drills. Reserved sentinels (`guest`, `system`) are rejected. | `PUT /profile/name` |
| **17** | **Display Name Change** | Update the name shown across the interface. | `PUT /profile/display-name` |
| **18** | **Email Change** | Update the account email (unique, current password required). | `PUT /profile/email` |
| **19** | **Password Change** | Change the password (current password verified, confirmation required). Rotates the remember token and signs out all other active sessions. | `GET /profile/password`<br>`PUT /profile/password` |
| **20** | **SMTP Configuration (Owner)** | Enable and configure a mail server (host, port 25/465/587/2525, TLS/SSL/none, credentials, from address/name) and send a test email. Restricted to the installer-provisioned owner; internal/loopback/reserved hosts and SSRF targets are rejected; stored password is cleared when the host changes. | `GET /profile/smtp`<br>`PUT /profile/smtp`<br>`POST /profile/smtp/test` |

---

## 4. Cryptic Terminal Simulator

| # | Feature Name | Description | Key Routes / APIs |
|---|---|---|---|
| **21** | **Full-Width Cryptic Console** | Distraction-free green/amber/dark screen emulator expanding to the full viewport for authentic Altéa workstation feel. | `GET /terminal` |
| **22** | **Multi-Work Area Engine (A–F)** | 6 independent concurrent work areas with isolated command histories, active PNRs, and availability buffers. | `POST /terminal/execute`<br>`POST /api/terminal/execute` |
| **23** | **Display Theme Switcher** | Toggle between **Modern Dark**, **CRT Green** (phosphor scanlines) and **CRT Amber** themes; the CRT effect is scoped to the black screen only. | Terminal UI Component |
| **24** | **Command Line with History Recall** | Monospaced prompt with Up/Down history navigation, cursor memory, and quick actions (Clear, `HE`, `SO`). | Terminal UI Component |
| **25** | **Hardened State Handling** | Client-supplied terminal state is normalised and size-bounded; malformed state or parser faults return an error response instead of an unhandled 500, and unbounded copy/split operations are capped. | Terminal Backend |

---

## 5. Amadeus Altéa Cryptic Command Engine

| # | Command Syntax | Function & Description | Example |
|---|---|---|---|
| **26** | `AN<date><city_pair>[/A<carrier>]` | **Flight Availability Neutral**: Displays schedules, carriers, cabin buckets, flight numbers, equipment, timings, stops, and e-ticket tags. | `AN15DECLHRJFK`<br>`AN20NOVDOHLHR/ABA` |
| **27** | `MD` / `MU` | **Availability Paging**: Move Down / Move Up through multi-page flight listings. | `MD`<br>`MU` |
| **28** | `SS<idx><class><seats>` | **Sell Segment**: Sells seats from the active availability screen into the itinerary. | `SS1Y1`<br>`SS2J2` |
| **29** | `XE<segment_number>` | **Cancel Segment**: Removes a flight leg from the itinerary buffer. | `XE1` |
| **30** | `NM1<last>/<first> <title>` | **Name Element**: Enters passenger names (adults, children, infants-in-lap). | `NM1SMITH/JOHN MR`<br>`NM2DOE/JANE MS/BABY INF` |
| **31** | `AP <contact_info>` | **Phone / Contact Element**: Records agency or passenger contact details. | `AP LON +44 20 7946 0912` |
| **32** | `RF <caller_name>` | **Received From**: Identifies who authorized the transaction (mandatory to store a PNR). | `RF PASSENGER` |
| **33** | `TKOK` / `TKTL<date>` | **Ticketing Arrangement**: Confirms ticketing (`TKOK`) or sets a time limit (`TKTL`). | `TKOK`<br>`TKTL15DEC` |
| **34** | `SR <code[-text]>` | **Special Service Request**: Records passenger service requests. | `SR VGML`<br>`SR WCHR` |
| **35** | `RM <text>` / `OS <carrier> <text>` | **Remarks & OSI**: Internal agent remarks or Other Service Information. | `RM VIP CLIENT`<br>`OS BA PAX HONEYMOON` |
| **36** | `ET` / `ER` | **End Transaction / End & Retrieve**: Validates mandatory PRINT elements, assigns a 6-character Record Locator, saves the PNR, and clears the area. | `ET`<br>`ER` |
| **37** | `RT` / `RT <locator>` / `RT<name>` | **Retrieve PNR**: Opens a booking by Record Locator, last name, or refreshes the buffer. | `RT`<br>`RT Z8P3X9`<br>`RTSMITH` |
| **38** | `IG` / `IR` | **Ignore Changes / Ignore & Retrieve**: Discards unsaved modifications. | `IG`<br>`IR` |
| **39** | `FXP` | **Fare Quote & Price**: Computes tariffs, surcharges and taxes, and creates a TST. | `FXP` |
| **40** | `FQD <origin><dest>[/A<carrier>]` | **Fare Quote Display**: Shows published tariff tables for a route. | `FQD LHRJFK/ABA` |
| **41** | `TQT` | **Display TST Record**: Shows the stored pricing breakdown. | `TQT` |
| **42** | `TTP` | **Issue E-Ticket**: Validates the TST and issues 13-digit electronic tickets with coupons. | `TTP` |
| **43** | `TWD` | **Display E-Ticket Record**: Shows ticket coupons, baggage allowance and endorsements (also tolerates manual `FH` tickets). | `TWD` |
| **44** | `QS` | **Queue Summary**: Displays pending item counts across Queues 1, 7, 8, 9 and 87. | `QS` |
| **45** | `QD <queue_number>` | **Queue Display**: Enters a queue bank and loads the first PNR. | `QD 1`<br>`QD 7`<br>`QD 87` |
| **46** | `QN` / `QI` | **Queue Next / Queue Ignore**: Advance to the next item or exit queue mode. | `QN`<br>`QI` |
| **47** | `DAN <name>` | **Decode Airport/City Name** → IATA codes. | `DAN LONDON` → `LHR, LGW, LCY` |
| **48** | `DAC <iata_code>` | **Encode Airport Code** → city and country. | `DAC DXB` → `DUBAI, UAE` |
| **49** | `DNA <carrier_code>` | **Decode Airline** → name and accounting code. | `DNA BA` → `BRITISH AIRWAYS (125)` |
| **50** | `DNE <equipment_code>` | **Decode Aircraft** → make and model. | `DNE 77W` → `BOEING 777-300ER` |
| **51** | `JI <office><agent>` | **Sign In** to the Altéa workstation. | `JI LON1A0980/1234AB` |
| **52** | `JO` / `SO` | **Sign Out** of the workstation. | `SO` |
| **53** | `HE` | **Help Index**: Cryptic command summary and usage manual. | `HE` |

---

## 6. REST API Reference

All API routes return JSON. Routes marked *Auth* require an authenticated session; CSRF applies to web and `/api/*` requests.

### Authentication & Account APIs
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/api/auth/login` | Authenticate by username/email | Guest (10/min) |
| `GET` | `/api/auth/me` | Fetch the authenticated user profile | Auth |
| `POST` | `/api/auth/me` | Log out / invalidate the session | Auth |
| `POST` | `/api/auth/forgot-password` | Request a password reset link | Guest (6/min) |
| `POST` | `/api/auth/reset-password` | Reset the password with a token | Guest (6/min) |

### Terminal & Training APIs
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/terminal/execute` | Process a cryptic command (web terminal) | Auth (60/min) |
| `POST` | `/api/terminal/execute` | Process a cryptic command (API) | Guest/Auth (60/min) |
| `GET` | `/api/training/modules` | List training modules and lessons | Guest (60/min) |
| `GET` | `/api/training/progress` | Fetch the caller's lesson progress | Auth |
| `POST` | `/api/training/progress` | Save validated lesson progress | Auth |
| `POST` | `/api/dashboard/validate-syntax` | Validate command syntax and return hints | Guest (60/min) |

### Personal Workspace APIs
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/api/dashboard/active-pnr` | Fetch the caller's active PNR and PRINT status | Auth |
| `DELETE` | `/api/dashboard/active-pnr` | Delete the caller's active stored PNR | Auth (15/min) |
| `POST` | `/api/dashboard/scenarios` | Create a custom practice drill | Auth (15/min) |
| `DELETE` | `/api/dashboard/scenarios/{id}` | Delete a drill you created | Auth (15/min) |
| `POST` | `/api/dashboard/scenarios/{id}/submit` | Submit the active PNR for automated grading | Auth (15/min) |

---

## 7. Core Domain Models & Schemas

### `User`
```typescript
interface User {
  id: number;
  name: string;
  username: string;
  email: string;
  role: string;          // defaults to "user" (single-user application)
  office_id: string;     // e.g. "LON1A0980"
  agent_id: string;      // e.g. "1234AB"
  created_at: string;
}
```

### `Pnr`
```typescript
interface Pnr {
  record_locator: string; // 6-char alphanumeric, e.g. "Z8P3X9" (primary key)
  user_id: string;        // owning username
  office_id: string;
  status: 'ACTIVE' | 'TICKETED' | 'CANCELLED';
  data_json: {
    passengers: Array<{ id: string; lastName: string; firstName: string; title: string; type: 'ADT' | 'CHD' | 'INF' }>;
    segments: Array<{
      segmentNumber: number;
      airlineCode: string;
      flightNumber: string;
      bookingClass: string;
      departureDate: string;
      departureAirport: string;
      arrivalAirport: string;
      seats: number;
      statusCode: string;
    }>;
    contacts: Array<{ id: number; type: string; detail: string }>;
    ticketing: { type: 'TKOK' | 'TKTL'; raw: string; date?: string };
    receivedFrom: string;
    remarks: string[];
    tsts: Array<{ tstNumber: string; baseFare: number; totalFare: number; currency: string }>;
    tickets: Array<{ ticketNumber: string; status: 'OPEN'; issuedAt: string }>;
  };
}
```

### `Scenario`
```typescript
interface Scenario {
  id: string;            // e.g. "SCN-RT-01" (system) or "SCN-USER-XXXXXXXX" (custom)
  title: string;
  description: string;
  category: string;      // e.g. "Round-trip", "One Way", "Infant-in-lap"
  difficulty: 'Beginner' | 'Intermediate' | 'Advanced';
  passenger_profile: { names: Array<Record<string, string>> };
  flight_segments: Array<{ origin: string; destination: string; date: string; airline: string; class: string }>;
  validation_criteria: {
    mandatoryPrint: Record<string, boolean>;
    minSegments: number;
    requireTst: boolean;
    requireETicket: boolean;
  };
  time_limit_minutes: number;
  created_by: string;    // "system" or the username of the author
}
```

### `Submission`
```typescript
interface Submission {
  id: number;
  user_id: string;
  scenario_id: string;
  status: 'submitted' | 'reviewed';
  score: number;          // 0 - 100
  pnr_record_locator?: string;
  auto_grade_results: {
    score: number;
    checks: Array<{ element: string; passed: boolean; message: string }>;
  };
  time_elapsed_seconds: number;
}
```

### `QueueItem`
```typescript
interface QueueItem {
  id: number;
  queue_number: number;   // 1 | 7 | 8 | 9 | 87
  pnr_locator: string;
  office_id: string;
  target_user_id?: string; // owning username; guest sessions never persist
  status: string;
  category: string;        // e.g. "SCHEDULE_CHANGE", "TTL_EXPIRING"
  message: string;
}
```

### `SmtpSetting` (singleton)
```typescript
interface SmtpSetting {
  id: number;
  host: string | null;
  port: number;            // default 587; allowed 25/465/587/2525
  username: string | null;
  password: string | null; // stored encrypted
  encryption: 'tls' | 'ssl' | 'none';
  from_address: string | null;
  from_name: string | null;
  enabled: boolean;
}
```
