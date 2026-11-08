# Graph Report - amadus  (2026-11-08)

## Corpus Check
- 170 files · ~301,333 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 18, .example 1, .xml 1)

## Summary
- 1531 nodes · 2320 edges · 133 communities (72 shown, 61 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 149 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Coverage Ledger Validation
- Security Findings Validation
- PHP Package Dependencies
- Application Service Provider
- Database Migrations & Schema
- User Authentication & Passwords
- Adding a cache to an existing 
- Dashboard & Scenario Management
- User Authentication & Passwords
- Frontend GDS Type Definitions
- Application Installer & Setup
- Security Hardening Tests
- APIs  Eloquent Resources
- APIs  Eloquent Resources
- Copy PNR Entries
- Node & Asset Build Pipeline
- Amadeus Command 2 Feature Tests
- Amadeus Reservation Reference
- Authentication & Role Middleware
- Amadeus Command Reference Tests
- Anti-patterns
- PNR Lifecycle Service
- Student & Instructor Tests
- Scenarios & Auto-Grading
- Dashboard & Scenario Management
- DualRoleDashboardTest
- Aircraftphp
- Hotel & Car Rental System
- IATA Encode & Decode Engine
- Scenarios & Auto-Grading
- A Validation  HTTP input
- Edge cases
- InstructorStudentManagementTes
- User Authentication & Passwords
- Architecture Best Practices
- Apply Cross-Site Request Forge
- Client-Side and Browser Huntin
- Cloud and Deployment Hunting
- Application-state and device-l
- Binary loading and runtime att
- Basic Usage
- InstallAppCommandphp
- TrainingController
- LandingAndApprovalTest
- Advanced Query Best Practices
- Cache Event Discovery During P
- Database Migrations & Schema
- Back Off Transient Failures
- AI LLM and Agent Hunting
- Core discipline include in eve
- Core hunting method  include i
- Candidate-verifier prompt
- API-key and mTLS attack classe
- Scenarios & Auto-Grading
- Queue Distribution Service
- Caching Best Practices
- Add Indexes for Measured Query
- Apply Global Scopes Sparingly
- Attack Classes
- Broker and queue isolation att
- Computational amplification at
- CI and automation attack class
- PNR Cancellation XEXI
- Amadeus Carrier Access Levels
- QueueItemphp
- AmadeusParserphp
- Flight Availability Service
- Pricing & Tariff Engine
- UserFactoryphp
- Alta Cryptic Terminal Emulator
- DashboardSubpagesAndTerminalHe
- ResponsiveNavigationAndSidebar
- architecturemd
- Blade and View Best Practices
- Add Context to Exception Class
- Bound Work Inside the Task
- Endpoint Coverage
- Other Services Information OS
- Scenarios & Auto-Grading
- Flight Operational Info & MCT
- Date & Math Conversions
- Choose Between cursor and lazy
- Fake HTTP Requests in Tests
- Assert the Delivery Mode
- Keep Controllers Focused on HT
- Convention and Style Best Prac
- Add Cross-Field Validation Aft
- Arrange Act Assert
- Built-in Laravel Assertion Met
- Database
- Common Errors
- Assertions
- Terminal Help System
- Configuration Best Practices
- File Layout
- Customer Profile Engine
- TicketingServicephp
- DatabaseSeederphp
- Architecture summary and compa
- Data Providers
- Consistency First
- Retrieve History RHRLH
- PHPUnitFrameworkTestCase
- mcpjson
- forgot-passwordbladephp
- loginbladephp
- pending-approvalbladephp
- registerbladephp
- reset-passwordbladephp
- instructorbladephp
- studentbladephp
- indexbladephp
- appbladephp
- dashboardbladephp
- indexbladephp

## God Nodes (most connected - your core abstractions)
1. `Amadeus Quick Reference Guide` - 54 edges
2. `User` - 37 edges
3. `InstallerService` - 34 edges
4. `TestCase` - 31 edges
5. `SecurityHardeningTest` - 30 edges
6. `AmadeusCmdReference2Test` - 24 edges
7. `InstallerTest` - 24 edges
8. `DashboardController` - 22 edges
9. `AmadeusCmdReferenceTest` - 21 edges
10. `AmadeusParser` - 20 edges

## Surprising Connections (you probably didn't know these)
- `InstallerTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/InstallerTest.php → app/Services/Installer/InstallerService.php
- `UserRequirementsVerificationTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/UserRequirementsVerificationTest.php → app/Services/Installer/InstallerService.php
- `Segment Sell (SS/SO/SI)` --semantically_similar_to--> `Segment Sell Entries`  [INFERRED] [semantically similar]
  amadeus-cmd-1.md → amadeus-cmd-2.md
- `Other Services Information (OS)` --semantically_similar_to--> `Other Service Information`  [INFERRED] [semantically similar]
  amadeus-cmd-1.md → amadeus-cmd-2.md
- `{closure#2}()` --references--> `User`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Models/User.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Amadus Simulator Core Architecture** — readme_amadus_training_simulator, readme_altea_cryptic_terminal, readme_pnr_lifecycle_engine, readme_flight_instructor_hub [EXTRACTED 1.00]
- **PNR Creation and Booking Lifecycle** — amadeus_cmd_1_name_element, amadeus_cmd_1_segment_sell, amadeus_cmd_1_phone_field, amadeus_cmd_1_ticketing_arrangement, amadeus_cmd_1_received_from, amadeus_cmd_1_end_transaction [EXTRACTED 1.00]
- **Core GDS Subsystems** — features_trainee_student_dashboard, features_instructor_operations_hub, features_cryptic_terminal_simulator, features_altea_cryptic_command_engine [INFERRED 0.85]

## Communities (133 total, 61 thin omitted)

### Community 0 - "Coverage Ledger Validation"
Cohesion: 0.06
Nodes (66): ATTEMPT_FIELDS, ATTEMPT_STATUSES, canonicalCoverageId(), collectUnitErrors(), createErrorList(), encodeCanonicalRef(), escapeUnsafeDiagnosticCharacters(), fs (+58 more)

### Community 1 - "Security Findings Validation"
Cohesion: 0.06
Nodes (56): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+48 more)

### Community 2 - "PHP Package Dependencies"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 3 - "Application Service Provider"
Cohesion: 0.06
Nodes (3): AppServiceProvider, InstallerTest, UserRequirementsVerificationTest

### Community 4 - "Database Migrations & Schema"
Cohesion: 0.09
Nodes (19): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+11 more)

### Community 5 - "User Authentication & Passwords"
Cohesion: 0.10
Nodes (6): {closure#1}(), Pnr, User, AmadeusGdsIntegrationTest, ExampleTest, TestCase

### Community 6 - "Adding a cache to an existing "
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 8 - "User Authentication & Passwords"
Cohesion: 0.07
Nodes (5): AuthController, Controller, LandingController, PasswordResetController, TerminalController

### Community 9 - "Frontend GDS Type Definitions"
Cohesion: 0.07
Nodes (29): AutoGradeResult, ClassroomStudentStatus, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck (+21 more)

### Community 10 - "Application Installer & Setup"
Cohesion: 0.16
Nodes (3): {closure#1}(), InstallerController, InstallerService

### Community 12 - "APIs  Eloquent Resources"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 13 - "APIs  Eloquent Resources"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 14 - "Copy PNR Entries"
Cohesion: 0.11
Nodes (25): Copy PNR Entries, Customer Profile System, Date and Time Conversions, Dual City Pair Schedules, Encode and Decode System, Extended Security Control, Ghost Segment Entry, Amadeus Quick Reference Guide (+17 more)

### Community 15 - "Node & Asset Build Pipeline"
Cohesion: 0.09
Nodes (22): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, name, optionalDependencies (+14 more)

### Community 17 - "Amadeus Reservation Reference"
Cohesion: 0.13
Nodes (21): Amadeus Basic Reservation Guide, Flight Availability Display (AN/SN), Change Booking Class & Date (SB), End Transaction (ET/ER), Frequent Flyer Number (FFN/SR FQTV), Manual Ticket Number Entry (FH), Name Elements (NM), Phone Field (AP) (+13 more)

### Community 18 - "Authentication & Role Middleware"
Cohesion: 0.16
Nodes (6): EnsureAppIsInstalled, EnsureUserIsInstructor, SecurityHeadersMiddleware, {closure#1}(), {closure#3}(), {closure#4}()

### Community 20 - "Anti-patterns"
Cohesion: 0.11
Nodes (18): Anti-patterns, Core principles, Cost budget, Coverage and prior runs, Full audit planning, Full audit setup, Full audit workflow, Operating modes (+10 more)

### Community 24 - "Dashboard & Scenario Management"
Cohesion: 0.19
Nodes (6): {closure#1}(), {closure#2}(), {closure#4}(), {closure#5}(), LessonProgress, TerminalActivityLog

### Community 26 - "Aircraftphp"
Cohesion: 0.21
Nodes (4): Aircraft, Airline, Airport, AisResult

### Community 27 - "Hotel & Car Rental System"
Cohesion: 0.20
Nodes (6): AisService, AmadeusParser, HotelCarResult, HotelCarService, TimaticResult, TimaticService

### Community 29 - "Scenarios & Auto-Grading"
Cohesion: 0.17
Nodes (13): Amadeus Altéa Cryptic Command Engine, Automated Drill Grading & Submission, Complete REST API Reference, Core Domain Models & Schemas, Cryptic Terminal Simulator, Amadeus GDS Platform Features Specification, Instructor Operations Hub & Classroom Management, Live Classroom Student Grid (+5 more)

### Community 30 - "A Validation  HTTP input"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 31 - "Edge cases"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 34 - "Architecture Best Practices"
Cohesion: 0.18
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 35 - "Apply Cross-Site Request Forge"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 36 - "Client-Side and Browser Huntin"
Cohesion: 0.18
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 37 - "Cloud and Deployment Hunting"
Cohesion: 0.18
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 38 - "Application-state and device-l"
Cohesion: 0.18
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 39 - "Binary loading and runtime att"
Cohesion: 0.18
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 40 - "Basic Usage"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 44 - "Advanced Query Best Practices"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 45 - "Cache Event Discovery During P"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 46 - "Database Migrations & Schema"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 47 - "Back Off Transient Failures"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 48 - "AI LLM and Agent Hunting"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 49 - "Core discipline include in eve"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 50 - "Core hunting method  include i"
Cohesion: 0.20
Nodes (10): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+2 more)

### Community 51 - "Candidate-verifier prompt"
Cohesion: 0.20
Nodes (9): Candidate-verifier prompt, `FINDINGS-DETAIL.md`, `NEEDS-VALIDATION.md`, Phase 3: Independently validate every candidate, Phase 4: Write and validate `findings.json`, Phase 5: Verify the final records with fresh eyes, Phase 6: Produce target-neutral reports from final records, `REPORT.md` (+1 more)

### Community 52 - "API-key and mTLS attack classe"
Cohesion: 0.20
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 55 - "Caching Best Practices"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 56 - "Add Indexes for Measured Query"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 57 - "Apply Global Scopes Sparingly"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 59 - "Broker and queue isolation att"
Cohesion: 0.22
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 60 - "Computational amplification at"
Cohesion: 0.22
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 61 - "CI and automation attack class"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 62 - "PNR Cancellation XEXI"
Cohesion: 0.22
Nodes (8): PNR Cancellation (XE/XI), Connection Points Display, Dual City Pair Availability, Flight Availability, Flight Information Display, Minimum Connecting Time, PNR Modifications and Cancellations, Display Scrolling Entries

### Community 63 - "Amadeus Carrier Access Levels"
Cohesion: 0.25
Nodes (9): Amadeus Carrier Access Levels, Availability Change Entries, Amadeus Best Pricer, Direct Access Mode, Fare Diagnostic, PNR Pricing Entries, Segment Sell Entries, Amadeus Segment Status Codes (+1 more)

### Community 64 - "QueueItemphp"
Cohesion: 0.31
Nodes (3): {closure#1}(), {closure#2}(), QueueItem

### Community 69 - "Alta Cryptic Terminal Emulator"
Cohesion: 0.22
Nodes (8): Altéa Cryptic Terminal Emulator, Amadus Training Simulator (1A), Automated Installers (Web & CLI), Cadet Student Cockpit, Flight Instructor Operations Hub, Global Aviation Datasets, Realistic PNR Lifecycle Engine, Role-Based Training Ecosystem

### Community 72 - "architecturemd"
Cohesion: 0.25
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 73 - "Blade and View Best Practices"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 74 - "Add Context to Exception Class"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 75 - "Bound Work Inside the Task"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 76 - "Endpoint Coverage"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 77 - "Other Services Information OS"
Cohesion: 0.29
Nodes (8): Other Services Information (OS), Amadeus Information System Pages, Frequent Flyer Number Integration, Other Service Information, Mandatory PNR Elements, PNR Name Element, PNR Phone Element, PNR Ticketing Arrangement Element

### Community 82 - "Choose Between cursor and lazy"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 83 - "Fake HTTP Requests in Tests"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 84 - "Assert the Delivery Mode"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 85 - "Keep Controllers Focused on HT"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 86 - "Convention and Style Best Prac"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 87 - "Add Cross-Field Validation Aft"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 88 - "Arrange Act Assert"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 89 - "Built-in Laravel Assertion Met"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 90 - "Database"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 91 - "Common Errors"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 92 - "Assertions"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 94 - "Configuration Best Practices"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 95 - "File Layout"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 100 - "Architecture summary and compa"
Cohesion: 0.40
Nodes (5): Architecture summary and companion selection, Deterministic coverage ledger, Phase 1: Map the source and plan coverage, Prior-run input, Reconnaissance

### Community 101 - "Data Providers"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 102 - "Consistency First"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

### Community 105 - "Retrieve History RHRLH"
Cohesion: 0.50
Nodes (4): Retrieve History (RH/RLH), PNR History Tracking, Amadeus Queues Management, Search by Office

## Knowledge Gaps
- **424 isolated node(s):** `php`, `concurrently`, `tailwindcss`, `@laravel/multiplex`, `layouts.app` (+419 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 812 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **61 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Authentication & Passwords` to `Userphp`, `AmadeusParserphp`, `UserFactoryphp`, `Dashboard & Scenario Management`, `Queue Distribution Service`, `InstallAppCommandphp`, `Application Installer & Setup`, `Authentication & Role Middleware`, `Scenarios & Auto-Grading`, `Dashboard & Scenario Management`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **What connects `php`, `concurrently`, `tailwindcss` to the rest of the system?**
  _424 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Coverage Ledger Validation` be split into smaller, more focused modules?**
  _Cohesion score 0.06219918548685672 - nodes in this community are weakly interconnected._
- **Why does `AmadeusParser` connect `Hotel & Car Rental System` to `AmadeusParserphp`, `Flight Availability Service`, `Pricing & Tariff Engine`, `Customer Profile Engine`, `TicketingServicephp`, `User Authentication & Passwords`, `Flight Operational Info & MCT`, `Date & Math Conversions`, `PNR Lifecycle Service`, `Queue Distribution Service`, `Dashboard & Scenario Management`, `IATA Encode & Decode Engine`, `Terminal Help System`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Should `Security Findings Validation` be split into smaller, more focused modules?**
  _Cohesion score 0.05541346973572037 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `User Authentication & Passwords` to `InstructorStudentManagementTes`, `User Authentication & Passwords`, `Application Service Provider`, `DashboardSubpagesAndTerminalHe`, `ResponsiveNavigationAndSidebar`, `LandingAndApprovalTest`, `Security Hardening Tests`, `Amadeus Command 2 Feature Tests`, `Amadeus Command Reference Tests`, `Student & Instructor Tests`, `Scenarios & Auto-Grading`, `DualRoleDashboardTest`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Should `PHP Package Dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.04081632653061224 - nodes in this community are weakly interconnected._