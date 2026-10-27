# Amadeus GDS Training Platform — Features Specification

> **Platform Architecture:** Dual-Role (Trainee Cadet & Flight Instructor) Airline Global Distribution System (GDS) Simulator, Altéa Cryptic Command Engine, PNR Lifecycle Manager, and Classroom Operations Hub.

---

## Table of Contents
1. [Public, Authentication & Onboarding](#1-public-authentication--onboarding)
2. [Trainee / Student Dashboard](#2-trainee--student-dashboard)
3. [Instructor Operations Hub & Classroom Management](#3-instructor-operations-hub--classroom-management)
4. [Cryptic Terminal Simulator](#4-cryptic-terminal-simulator)
5. [Amadeus Altéa Cryptic Command Engine](#5-amadeus-alt%C3%A9a-cryptic-command-engine)
6. [Complete REST API Reference](#6-complete-rest-api-reference)
7. [Core Domain Models & Schemas](#7-core-domain-models--schemas)

---

## 1. Public, Authentication & Onboarding

| # | Feature Name | Description | Key Routes / APIs |
|---|---|---|---|
| **01** | **Full-Viewport Landing Page** | Responsive `100vh`/`100vw` modern cockpit landing page with brand header, live host connection indicator (`LON1A0980 HOST ONLINE`), interactive CRT green/amber terminal showcase, feature highlights grid, and footer. | `GET /` |
| **02** | **Web Platform Setup Installer** | 4-step deployment wizard checking PHP 8.2+ runtime, extensions (`pdo`, `sqlite3`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo`), directory write permissions, automatic migrations/seeders, primary instructor provisioning, and post-installation lock (`storage/installed`). | `GET /install`<br>`POST /install`<br>`GET /install/requirements` |
| **03** | **CLI Installer Command** | Non-interactive and interactive terminal installer supporting custom instructor credentials, office ID, and force flags (`php artisan app:install`). | `php artisan app:install` |
| **04** | **User Authentication & Auto-Redirect** | Secure username/password authentication using bcrypt. On successful login, approved users are automatically routed to their workspace (`/dashboard`). | `GET /login`<br>`POST /login`<br>`POST /api/auth/login` |
| **05** | **1-Click Demo Accounts** | Quick preset buttons on login screen for development environments (`student / amadeus123`, `instructor / amadeus123`). | `GET /login` |
| **06** | **Cadet Self-Registration** | Public registration form (`/register`) enforcing student role and creating accounts in pending status (`is_approved = false`). | `GET /register`<br>`POST /register`<br>`POST /api/auth/register` |
| **07** | **Instructor Approval Gate & Holding Portal** | Prevents unapproved cadets from logging in or using the simulator. Redirects to `/pending-approval` detailing the required flight academy authorization. | `GET /pending-approval` |
| **08** | **Security & Traffic Throttling** | Strict CSRF token validation and rate limiting on sensitive routes (Authentication: 10 req/min; Terminal: 60 req/min). | Middleware Pipeline |

---

## 2. Trainee / Student Dashboard

| # | Feature Name | Description | Key Routes / APIs |
|---|---|---|---|
| **09** | **Persistent Navigation Shell** | High-density dark cockpit sidebar with host connection pill (`LON1A0980`), agent duty code (`1234AB`), active work area indicator, and quick launch to Terminal Simulator. | `GET /dashboard?role=student` |
| **10** | **Active PNR Status Monitor** | Live card displaying the active Record Locator, booking status (`CONFIRMED`, `HOLD`, `TICKETED`), passenger count, flight segments, ticketing deadline, and stored airfare. | `GET /api/dashboard/active-pnr` |
| **11** | **Mandatory P.R.I.N.T. Checklist** | Real-time visual compliance meter ensuring all 5 mandatory Amadeus elements exist before booking closure: **P**hone (`AP`), **R**eceived From (`RF`), **I**tinerary (`SS`), **N**ame (`NM`), and **T**icketing (`TK`). | Dashboard UI Component |
| **12** | **Slide-Out PNR Inspector Drawer** | Collapsible drawer rendering formatted Amadeus cryptic image (`RLR`) and structured tabbed elements (Passengers, Segments, Contacts, Ticketing, TSTs). | `GET /api/dashboard/active-pnr` |
| **13** | **Assigned Booking Scenarios & Drills** | Filterable assignment list with difficulty and category badges (Round-Trip, Multi-City, Schedule Change, Urgent Ticketing, Complex Fares). | `GET /dashboard` |
| **14** | **Scenario Briefing Modal** | Modal displaying detailed passenger personas, travel routing requirements, class codes, and validation conditions. | Dashboard UI Component |
| **15** | **Automated Drill Grading & Submission** | 1-click submission evaluating student's active PNR against mandatory PRINT rules, fare pricing (`FXP`), and e-ticketing (`TTP`), generating a 0–100 score and audit details. | `POST /api/dashboard/scenarios/{id}/submit` |
| **16** | **Interactive Command Reference** | Searchable cheat sheet categorized by Amadeus function (Availability, Booking, PNR Management, Pricing, Ticketing, Queues, IATA Decoding). | Dashboard UI Component |
| **17** | **Live Syntax Validator** | Real-time command validator evaluating Amadeus cryptic entries as typed and delivering actionable correction hints. | `POST /api/dashboard/validate-syntax` |
| **18** | **Syntax Mistakes Audit Log** | Audit table showing historical student syntax mistakes with tailored correction tips. | Dashboard UI Component |
| **19** | **Learning Analytics & Metrics** | Visual KPI rings for syntax accuracy percentage, command efficiency score, and scenario completion progress. | Dashboard UI Component |

---

## 3. Instructor Operations Hub & Classroom Management

| # | Feature Name | Description | Key Routes / APIs |
|---|---|---|---|
| **20** | **Pending Cadet Approvals Widget** | Prominent alert banner displaying unapproved student sign-ups with 1-click **Approve Access** and **Reject Registration** actions. | `POST /api/instructor/students/{id}/approve`<br>`POST /api/instructor/students/{id}/reject` |
| **21** | **Live Classroom Student Grid** | Real-time trainee cards with live session badges: `Online`, `In Terminal`, `Idle`, and `Error Loop` (triggered when error rate $\ge 60\%$). | `GET /instructor`<br>`GET /api/instructor/students` |
| **22** | **Single Cadet Enrollment** | Modal form enabling instructors to manually enroll and immediately approve a new trainee account. | `POST /instructor/students`<br>`POST /api/instructor/students` |
| **23** | **Batch Cadet Provisioning** | Generator provisioning up to 500 cadet accounts simultaneously with sequential callsigns (`trainee_1`, `trainee_2`) and exportable credentials. | `POST /api/instructor/students/batch` |
| **24** | **Live Keystroke Spectator** | Modal streaming a student's chronological terminal commands, outputs, active work area, error flags, and active PNR buffer in real-time. | `GET /api/instructor/spectate/{username}` |
| **25** | **Scenario & Drill Builder** | Custom form interface to design and publish new booking assignments with routing, passenger persona, and validation criteria. | `POST /api/instructor/scenarios` |
| **26** | **Scenario Management** | Ability for instructors to review, audit, and permanently delete drill scenarios. | `DELETE /api/instructor/scenarios/{id}` |
| **27** | **Submissions Gradebook** | Comprehensive log of completed student drills with auto-scores, final scores, timestamps, and full PNR snapshots. | `GET /instructor` |
| **28** | **Manual Grade Override & Feedback** | Modal allowing instructors to adjust scores (0–100) and submit personalized guidance notes via AJAX. | `POST /api/instructor/submissions/{id}/grade` |
| **29** | **Simulated Queue Bank Counters** | Real-time counters for Queues 1 (TTL), 7 (Schedule Change), 8 (Waitlist), 9 (General Messages), and 87 (Exchanges). | `GET /instructor` |
| **30** | **Queue Item Broadcast Tool** | Modal allowing instructors to inject simulated flight disruption or exchange PNRs into a single student's queue or broadcast to the entire class. | `POST /api/instructor/queues/broadcast` |
| **31** | **Classroom Sandbox State Reset** | 1-click reset wiping all student active buffers, test PNRs, and queues back to clean training baseline. | `POST /api/instructor/sandbox/reset` |
| **32** | **Dev/Demo Role Switcher** | Interactive role toggle allowing instructors to test both Trainee and Instructor perspectives with session persistence. | `POST /dashboard/switch-role` |

---

## 4. Cryptic Terminal Simulator

| # | Feature Name | Description | Key Routes / APIs |
|---|---|---|---|
| **33** | **Full-Width Cryptic Console** | Dedicated, distraction-free green-screen emulator expanding 100% viewport width matching Altéa workstation hardware. | `GET /terminal` |
| **34** | **Multi-Work Area Engine (A–F)** | 6 independent concurrent work areas with isolated command histories, active PNRs, and availability buffers. | `POST /terminal/execute`<br>`POST /api/terminal/execute` |
| **35** | **Display Theme Switcher** | Toggle between **Modern Dark** (slate & sky-blue), **CRT Green** (vintage phosphor glow), and **CRT Amber** (amber monochrome). | Terminal UI Component |
| **36** | **Command Line with History Recall** | Monospaced prompt with Up/Down arrow key command history navigation, cursor memory, and quick action buttons (Clear, Help `HE`, Sign Out `SO`). | Terminal UI Component |
| **37** | **Collapsible Visual Inspector** | Optional split-screen card view rendering live PNR elements docked directly underneath the terminal output. | Terminal UI Component |
| **38** | **Dashboard Hub Link** | Persistent header badge for one-click return to the user's role-based dashboard. | `GET /terminal` |

---

## 5. Amadeus Altéa Cryptic Command Engine

| # | Command Syntax | Function & Description | Example |
|---|---|---|---|
| **39** | `AN<date><city_pair>[/A<carrier>]` | **Flight Availability Neutral**: Displays schedules, carriers (BA, QR, AF, LH, EK, DL, AA, UA), multi-cabin buckets (J, C, D, I, W, E, T, Y, B, H, K, M, L, V, S, N, Q, O, G), flight numbers, equipment (77W, 359, 388, 321), timings, stops, and e-ticket tags (`/E`). | `AN15DECLHRJFK`<br>`AN20NOVDOHLHR/ABA`<br>`ANCDGJFK/AYY` |
| **40** | `MD` / `MU` | **Availability Paging**: Move Down (`MD`) and Move Up (`MU`) to navigate multi-page flight listings. | `MD`<br>`MU` |
| **41** | `SS<idx><class><seats>` | **Sell Segment**: Sells passenger seats directly from the active availability screen into the active itinerary. | `SS1Y1`<br>`SS2J2`<br>`SS1M1` |
| **42** | `XE<segment_number>` | **Cancel Segment**: Removes a flight leg from the itinerary buffer. | `XE1`<br>`XE2` |
| **43** | `NM1<last>/<first> <title>` | **Name Element**: Enters passenger names into PNR buffer; supports adults, children, and infants-in-lap. | `NM1SMITH/JOHN MR`<br>`NM2DOE/JANE MS/BABY INF` |
| **44** | `AP <contact_info>` | **Phone / Contact Element**: Records agency or passenger contact numbers. | `AP LON +44 20 7946 0912`<br>`AP MUC +49 89 123456` |
| **45** | `RF <caller_name>` | **Received From**: Identifies who authorized the transaction (mandatory for booking closure). | `RF PASSENGER`<br>`RF JOHN SMITH` |
| **46** | `TKOK` / `TKTL<date>` | **Ticketing Arrangement**: Confirms ticketing status (`TKOK`) or sets ticketing time limit deadline (`TKTL`). | `TKOK`<br>`TKTL15DEC` |
| **47** | `SR <code[-text]>` | **Special Service Request (SSR)**: Records passenger service requests (e.g. special meals, wheelchair assistance). | `SR VGML`<br>`SR WCHR` |
| **48** | `RM <text>` / `OS <carrier> <text>` | **Remarks & OSI**: Adds internal agent remarks (`RM`) or Other Service Information (`OS`) for airlines. | `RM VIP CLIENT`<br>`OS BA PAX CELEBRATING HONEYMOON` |
| **49** | `ET` / `ER` | **End Transaction / End & Retrieve**: Validates mandatory PRINT elements, assigns unique 6-character Alpha-Numeric Record Locator, saves PNR, and clears area. | `ET`<br>`ER` |
| **50** | `RT` / `RT <locator>` / `RT<name>` | **Retrieve PNR**: Opens existing booking by Record Locator, passenger last name, or refreshes active buffer. | `RT`<br>`RT Z8P3X9`<br>`RTSMITH` |
| **51** | `IG` / `IR` | **Ignore Changes / Ignore & Retrieve**: Discards unsaved modifications in the active buffer. | `IG`<br>`IR` |
| **52** | `FXP` | **Fare Quote & Price**: Computes distance, cabin tariffs, fuel surcharges, taxes (APD), and creates Transitional Stored Ticket (TST). | `FXP` |
| **53** | `FQD <origin><dest>[/A<carrier>]` | **Fare Quote Display**: Displays published tariff tables for a route. | `FQD LHRJFK/ABA` |
| **54** | `TQT` | **Display TST Record**: Displays the stored pricing calculation breakdown. | `TQT` |
| **55** | `TTP` | **Ticket Print (Issue E-Ticket)**: Validates stored TST and issues 13-digit electronic tickets with flight coupons (`OPEN`). | `TTP` |
| **56** | `TWD` | **Display E-Ticket Record**: Displays electronic ticket coupons, baggage allowances (`2PC` / `30K`), and fare endorsements. | `TWD` |
| **57** | `QS` | **Queue Summary**: Displays live count of pending items across Queues 1, 7, 8, 9, and 87. | `QS` |
| **58** | `QD <queue_number>` | **Queue Display**: Enters queue bank, loads first PNR into buffer, and shows change reason. | `QD 1`<br>`QD 7`<br>`QD 87` |
| **59** | `QN` | **Queue Next**: Advances to next queued PNR in the active queue bank. | `QN` |
| **60** | `QI` | **Queue Ignore / Exit**: Exits queue processing mode. | `QI` |
| **61** | `DAN <name>` | **Decode Airport/City Name**: Translates city or airport name into IATA codes. | `DAN LONDON` &rarr; `LHR, LGW, LCY` |
| **62** | `DAC <iata_code>` | **Encode Airport Code**: Translates 3-letter IATA airport code into full city and country name. | `DAC DXB` &rarr; `DUBAI, UAE` |
| **63** | `DNA <carrier_code>` | **Decode Airline Carrier**: Translates 2-letter airline code into carrier name and 3-digit accounting code. | `DNA BA` &rarr; `BRITISH AIRWAYS (125)` |
| **64** | `DNE <equipment_code>` | **Decode Aircraft Equipment**: Translates 3-character aircraft code into manufacturer and model name. | `DNE 77W` &rarr; `BOEING 777-300ER` |
| **65** | `JI <office><agent>` | **Jump In / Sign In**: Signs agent into Altéa terminal workstation. | `JI LON1A0980/1234AB` |
| **66** | `JO` / `SO` | **Jump Out / Sign Out**: Signs agent out of workstation. | `SO` |
| **67** | `HE` | **Help Index**: Displays cryptic command summary and usage manual. | `HE` |

---

## 6. Complete REST API Reference

All API routes return JSON and require appropriate session or role privileges.

### Authentication & Account APIs
| Method | Endpoint | Description | Role / Auth |
|---|---|---|---|
| `POST` | `/api/auth/login` | Authenticate user credentials, check approval status, return session | Guest |
| `POST` | `/api/auth/register` | Register new cadet account with `is_approved = false` | Guest |
| `GET` | `/api/auth/me` | Fetch authenticated user profile & permissions | Authenticated |
| `POST` | `/api/auth/me` | Invalidate session & log out | Authenticated |

### Terminal & Simulation APIs
| Method | Endpoint | Description | Role / Auth |
|---|---|---|---|
| `POST` | `/terminal/execute` | Process Amadeus cryptic command on web terminal | CSRF Protected, 60 req/min |
| `POST` | `/api/terminal/execute` | Process Amadeus cryptic command on API terminal | Open / 60 req/min |

### Student Workspace APIs
| Method | Endpoint | Description | Role / Auth |
|---|---|---|---|
| `GET` | `/api/dashboard/active-pnr` | Fetch student's current active PNR record and PRINT status | Authenticated |
| `POST` | `/api/dashboard/validate-syntax` | Validate command syntax and receive real-time correction hints | Authenticated |
| `POST` | `/api/dashboard/scenarios/{id}/submit` | Submit booking PNR for automated grading against scenario rules | Student |

### Instructor Operations APIs
| Method | Endpoint | Description | Role / Auth |
|---|---|---|---|
| `GET` | `/api/instructor/students` | Get enrolled student roster, active sessions, and pending cadets | Instructor |
| `POST` | `/api/instructor/students` | Enroll single new student with instant approval | Instructor |
| `POST` | `/api/instructor/students/batch` | Batch provision multiple cadet accounts (up to 500) | Instructor |
| `POST` | `/api/instructor/students/{id}/approve` | Approve a self-registered cadet account | Instructor |
| `POST` | `/api/instructor/students/{id}/reject` | Reject and remove an unapproved cadet registration | Instructor |
| `GET` | `/api/instructor/spectate/{username}` | Live keystroke stream, command history & active PNR inspector | Instructor |
| `POST` | `/api/instructor/scenarios` | Create custom booking scenario drill | Instructor |
| `DELETE` | `/api/instructor/scenarios/{id}` | Delete a scenario drill | Instructor |
| `POST` | `/api/instructor/submissions/{id}/grade` | Override student grade (0–100) and save feedback notes | Instructor |
| `POST` | `/api/instructor/queues/broadcast` | Inject simulated queue items into student queue banks | Instructor |
| `POST` | `/api/instructor/sandbox/reset` | Reset all student buffers, test PNRs, and queues to baseline | Instructor |

---

## 7. Core Domain Models & Schemas

### `User`
```typescript
interface User {
  id: number;
  name: string;
  username: string;
  email: string;
  role: 'student' | 'instructor';
  is_approved: boolean;
  office_id: string; // e.g. "LON1A0980"
  agent_id: string;  // e.g. "1234AB" or "IN01GS"
  created_at: string;
}
```

### `Pnr`
```typescript
interface Pnr {
  id: number;
  record_locator: string; // 6-char Alpha-Numeric, e.g. "Z8P3X9"
  user_id: string;
  office_id: string;
  agent_id: string;
  status: 'CONFIRMED' | 'TICKETED' | 'HOLD' | 'CANCELLED';
  data_json: {
    passengers: Array<{ id: number; name: string; type: 'ADT' | 'CHD' | 'INF' }>;
    itinerary: Array<{
      segmentNumber: number;
      carrier: string;
      flightNumber: string;
      classOfService: string;
      date: string;
      origin: string;
      destination: string;
      departureTime: string;
      arrivalTime: string;
      status: 'HK' | 'TK' | 'CONFIRMED';
    }>;
    contacts: string[];
    ticketing: { arrangement: 'TKOK' | 'TKTL'; deadline?: string };
    receivedFrom: string;
    remarks: string[];
    ssrs: Array<{ code: string; text: string }>;
    tst?: { tstNumber: string; baseFare: number; totalFare: number; currency: string };
    tickets?: Array<{ ticketNumber: string; status: 'OPEN'; issuedAt: string }>;
  };
}
```

### `Scenario`
```typescript
interface Scenario {
  id: string;       // e.g. "SCN-RT-01"
  code: string;
  title: string;
  description: string;
  category: 'ROUND_TRIP' | 'MULTI_CITY' | 'QUEUE_WORK' | 'TICKETING' | 'SSR_SPECIAL';
  difficulty: 'BEGINNER' | 'INTERMEDIATE' | 'ADVANCED';
  persona_json: {
    adults: number;
    children?: number;
    infants?: number;
    names: string[];
  };
  target_flights_json: {
    origin: string;
    destination: string;
    preferredCarrier?: string;
  };
  validation_rules_json: {
    requiresPricing: boolean;
    requiresTicketing: boolean;
    minSegments: number;
  };
}
```

### `Submission`
```typescript
interface Submission {
  id: number;
  user_id: string;
  scenario_id: string;
  status: 'submitted' | 'reviewed';
  auto_score: number;       // 0 - 100
  final_score: number;      // 0 - 100
  pnr_record_locator: string;
  pnr_snapshot_json: Record<string, any>;
  grading_details_json: {
    mandatoryPrintPassed: boolean;
    pricingPassed: boolean;
    ticketingPassed: boolean;
    checks: Array<{ element: string; passed: boolean; message: string }>;
  };
  instructor_feedback?: string;
}
```

### `QueueItem`
```typescript
interface QueueItem {
  id: number;
  queue_number: 1 | 7 | 8 | 9 | 87;
  pnr_record_locator: string;
  office_id: string;
  user_id?: string; // null for classroom-wide broadcast
  status: 'pending' | 'worked' | 'cleared';
  reason_code: string; // e.g. "SCHEDULE_CHANGE", "TTL_EXPIRING"
  message: string;
}
```
