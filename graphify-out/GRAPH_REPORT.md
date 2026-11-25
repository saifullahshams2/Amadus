# Graph Report - amadus  (2026-11-25)

## Corpus Check
- 20 files · ~279,253 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1476 nodes · 2206 edges · 125 communities (67 shown, 58 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 86 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Authentication & Session Flow
- Test Coverage Validation
- Security Audit Validation
- PHP Composer Dependencies
- Database Schema & Migrations
- GDS Terminal & Parser Engine
- Authentication & Session Flow
- Cloud Deployment Checklists
- Security Hardening Tests
- APIs & Eloquent Resources Module
- APIs & Eloquent Resources Module
- TypeScript GDS Types & AutoGrade
- Authentication & Session Flow
- Frontend Tooling & Bundling
- Authentication & Session Flow
- InstallerTest Module
- PNR Data & GDS Integration
- Database Schema & Migrations
- PNR Data & GDS Integration
- Anti-patterns Module
- Authentication & Session Flow
- PNR Data & GDS Integration
- Aircraft Module
- DecodeResult Module
- Controller Module
- Authentication & Session Flow
- Authentication & Session Flow
- Authentication & Session Flow
- Authentication & Session Flow
- Edge cases Module
- Scenario Module
- Architecture Best Practices Module
- Authentication & Session Flow
- Client-Side and Browser Hunting Module
- Frontend Tooling & Bundling
- Submission Module
- Authentication & Session Flow
- PNR Data & GDS Integration
- Advanced Query Best Practices Module
- Cache Event Discovery During Production Deployment Module
- Database Schema & Migrations
- Back Off Transient Failures Module
- AI, LLM, and Agent Hunting Module
- Attack Classes Module
- Cloud and Deployment Hunting Module
- Database Schema & Migrations
- Application-state and device-lifecycle attack classes (subagent_type: `general`) Module
- Core hunting method — include in every hunter prompt Module
- Binary loading and runtime attack classes (subagent_type: `general`) Module
- Broker and queue isolation attack classes (subagent_type: `general`) Module
- Computational amplification attack classes (subagent_type: `general`) Module
- Candidate-verifier prompt Module
- Authentication & Session Flow
- LessonProgress Module
- PricingService Module
- Authentication & Session Flow
- Caching Best Practices Module
- Add Indexes for Measured Query Patterns Module
- Apply Global Scopes Sparingly Module
- CI and automation attack classes (subagent_type: `general`) Module
- EnsureAppIsInstalled Module
- QueueItem Module
- AvailabilityService Module
- bootstrap/app Module
- Altéa Cryptic Terminal Emulator Module
- architecture Module
- Blade and View Best Practices Module
- Add Context to Exception Classes Module
- Bound Work Inside the Task Module
- Authentication & Session Flow
- MathDateService Module
- PNR Data & GDS Integration
- DashboardSubpagesAndTerminalHeaderTest Module
- Choose Between `cursor()` and `lazy()` Module
- Fake HTTP Requests in Tests Module
- SMTP & Email Services
- Keep Controllers Focused on HTTP Concerns Module
- Convention and Style Best Practices Module
- Add Cross-Field Validation After Base Rules Module
- Arrange, Act, Assert Module
- Built-in Laravel Assertion Methods Module
- Database Module
- Common Errors Module
- Assertions Module
- FlightInfoResult Module
- HelpService Module
- UserFactory Module
- Configuration Best Practices Module
- File Layout Module
- InstallAppCommand Module
- ProfileService Module
- TicketingService Module
- Architecture summary and companion selection Module
- Data Providers Module
- Consistency First Module
- GDS Terminal & Parser Engine
- HotelCarService Module
- TimaticService Module
- Test Infrastructure & TestCase
- php Module
- layouts.app Module
- layouts.dashboard Module
- forgot-password.blade Module
- Authentication & Session Flow
- reset-password.blade Module
- app.blade Module
- dashboard.blade Module
- profile.blade Module
- profile/index.blade Module
- password.blade Module
- smtp.blade Module

## God Nodes (most connected - your core abstractions)
1. `InstallerService` - 34 edges
2. `SecurityHardeningTest` - 32 edges
3. `AmadeusCmdReference2Test` - 24 edges
4. `InstallerTest` - 24 edges
5. `User` - 23 edges
6. `ProfileTest` - 22 edges
7. `TestCase` - 21 edges
8. `AmadeusCmdReferenceTest` - 21 edges
9. `AmadeusParser` - 20 edges
10. `DashboardController` - 17 edges

## Surprising Connections (you probably didn't know these)
- `InstallerTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/InstallerTest.php → app/Services/Installer/InstallerService.php
- `UserRequirementsVerificationTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/UserRequirementsVerificationTest.php → app/Services/Installer/InstallerService.php
- `{closure#1}()` --references--> `SmtpTestMail`  [EXTRACTED]
  tests/Feature/ProfileTest.php → app/Mail/SmtpTestMail.php
- `InstallerController` --references--> `InstallerService`  [EXTRACTED]
  app/Http/Controllers/InstallerController.php → app/Services/Installer/InstallerService.php
- `EnsureAppIsInstalled` --references--> `InstallerService`  [EXTRACTED]
  app/Http/Middleware/EnsureAppIsInstalled.php → app/Services/Installer/InstallerService.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Amadus Simulator Core Architecture** — readme_amadus_training_simulator, readme_altea_cryptic_terminal, readme_pnr_lifecycle_engine, readme_flight_instructor_hub [EXTRACTED 1.00]
- **Core GDS Subsystems** — features_trainee_student_dashboard, features_instructor_operations_hub, features_cryptic_terminal_simulator, features_altea_cryptic_command_engine [INFERRED 0.85]

## Communities (125 total, 58 thin omitted)

### Community 0 - "Authentication & Session Flow"
Cohesion: 0.05
Nodes (10): AuthController, {closure#1}(), {closure#2}(), DashboardController, InstallerController, PasswordResetController, ProfileController, {closure#1}() (+2 more)

### Community 1 - "Test Coverage Validation"
Cohesion: 0.06
Nodes (66): ATTEMPT_FIELDS, ATTEMPT_STATUSES, canonicalCoverageId(), collectUnitErrors(), createErrorList(), encodeCanonicalRef(), escapeUnsafeDiagnosticCharacters(), fs (+58 more)

### Community 2 - "Security Audit Validation"
Cohesion: 0.06
Nodes (56): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+48 more)

### Community 3 - "PHP Composer Dependencies"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 4 - "Database Schema & Migrations"
Cohesion: 0.09
Nodes (18): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+10 more)

### Community 5 - "GDS Terminal & Parser Engine"
Cohesion: 0.09
Nodes (4): TerminalController, AmadeusParser, CommandExecutionResult, QueueService

### Community 6 - "Authentication & Session Flow"
Cohesion: 0.10
Nodes (4): SmtpTestMail, SmtpSetting, {closure#1}(), ProfileTest

### Community 7 - "Cloud Deployment Checklists"
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 9 - "APIs & Eloquent Resources Module"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 10 - "APIs & Eloquent Resources Module"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 11 - "TypeScript GDS Types & AutoGrade"
Cohesion: 0.07
Nodes (26): AutoGradeResult, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck, LearningAnalytics (+18 more)

### Community 12 - "Authentication & Session Flow"
Cohesion: 0.12
Nodes (5): Pnr, AmadeusGdsIntegrationTest, ExampleTest, LandingAndApprovalTest, TestCase

### Community 13 - "Frontend Tooling & Bundling"
Cohesion: 0.09
Nodes (22): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, name, optionalDependencies (+14 more)

### Community 19 - "Anti-patterns Module"
Cohesion: 0.11
Nodes (18): Anti-patterns, Core principles, Cost budget, Coverage and prior runs, Full audit planning, Full audit setup, Full audit workflow, Operating modes (+10 more)

### Community 20 - "Authentication & Session Flow"
Cohesion: 0.26
Nodes (8): api_headers(), execute(), login(), main(), new_session(), phase2(), phase3(), show()

### Community 22 - "Aircraft Module"
Cohesion: 0.23
Nodes (3): Aircraft, Airline, Airport

### Community 24 - "Controller Module"
Cohesion: 0.22
Nodes (3): Controller, TrainingController, CurriculumService

### Community 26 - "Authentication & Session Flow"
Cohesion: 0.17
Nodes (13): Amadeus Altéa Cryptic Command Engine, Automated Drill Grading & Submission, Complete REST API Reference, Core Domain Models & Schemas, Cryptic Terminal Simulator, Amadeus GDS Platform Features Specification, Instructor Operations Hub & Classroom Management, Live Classroom Student Grid (+5 more)

### Community 28 - "Authentication & Session Flow"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 29 - "Edge cases Module"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 31 - "Architecture Best Practices Module"
Cohesion: 0.18
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 32 - "Authentication & Session Flow"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 33 - "Client-Side and Browser Hunting Module"
Cohesion: 0.18
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 34 - "Frontend Tooling & Bundling"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 35 - "Submission Module"
Cohesion: 0.22
Nodes (3): {closure#1}(), Submission, TerminalActivityLog

### Community 38 - "Advanced Query Best Practices Module"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 39 - "Cache Event Discovery During Production Deployment Module"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 40 - "Database Schema & Migrations"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 41 - "Back Off Transient Failures Module"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 42 - "AI, LLM, and Agent Hunting Module"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 44 - "Cloud and Deployment Hunting Module"
Cohesion: 0.20
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 45 - "Database Schema & Migrations"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 46 - "Application-state and device-lifecycle attack classes (subagent_type: `general`) Module"
Cohesion: 0.20
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 47 - "Core hunting method — include in every hunter prompt Module"
Cohesion: 0.20
Nodes (10): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+2 more)

### Community 48 - "Binary loading and runtime attack classes (subagent_type: `general`) Module"
Cohesion: 0.20
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 49 - "Broker and queue isolation attack classes (subagent_type: `general`) Module"
Cohesion: 0.20
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 50 - "Computational amplification attack classes (subagent_type: `general`) Module"
Cohesion: 0.20
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 51 - "Candidate-verifier prompt Module"
Cohesion: 0.20
Nodes (9): Candidate-verifier prompt, `FINDINGS-DETAIL.md`, `NEEDS-VALIDATION.md`, Phase 3: Independently validate every candidate, Phase 4: Write and validate `findings.json`, Phase 5: Verify the final records with fresh eyes, Phase 6: Produce target-neutral reports from final records, `REPORT.md` (+1 more)

### Community 52 - "Authentication & Session Flow"
Cohesion: 0.20
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 57 - "Caching Best Practices Module"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 58 - "Add Indexes for Measured Query Patterns Module"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 59 - "Apply Global Scopes Sparingly Module"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 60 - "CI and automation attack classes (subagent_type: `general`) Module"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 62 - "QueueItem Module"
Cohesion: 0.31
Nodes (3): {closure#1}(), {closure#2}(), QueueItem

### Community 64 - "bootstrap/app Module"
Cohesion: 0.28
Nodes (3): {closure#1}(), {closure#3}(), {closure#4}()

### Community 65 - "Altéa Cryptic Terminal Emulator Module"
Cohesion: 0.22
Nodes (8): Altéa Cryptic Terminal Emulator, Amadus Training Simulator (1A), Automated Installers (Web & CLI), Cadet Student Cockpit, Flight Instructor Operations Hub, Global Aviation Datasets, Realistic PNR Lifecycle Engine, Role-Based Training Ecosystem

### Community 66 - "architecture Module"
Cohesion: 0.25
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 67 - "Blade and View Best Practices Module"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 68 - "Add Context to Exception Classes Module"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 69 - "Bound Work Inside the Task Module"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 70 - "Authentication & Session Flow"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 74 - "Choose Between `cursor()` and `lazy()` Module"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 75 - "Fake HTTP Requests in Tests Module"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 76 - "SMTP & Email Services"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 77 - "Keep Controllers Focused on HTTP Concerns Module"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 78 - "Convention and Style Best Practices Module"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 79 - "Add Cross-Field Validation After Base Rules Module"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 80 - "Arrange, Act, Assert Module"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 81 - "Built-in Laravel Assertion Methods Module"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 82 - "Database Module"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 83 - "Common Errors Module"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 84 - "Assertions Module"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 88 - "Configuration Best Practices Module"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 89 - "File Layout Module"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 93 - "Architecture summary and companion selection Module"
Cohesion: 0.40
Nodes (5): Architecture summary and companion selection, Deterministic coverage ledger, Phase 1: Map the source and plan coverage, Prior-run input, Reconnaissance

### Community 94 - "Data Providers Module"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 95 - "Consistency First Module"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

## Knowledge Gaps
- **407 isolated node(s):** `php`, `layouts.app`, `layouts.app`, `layouts.app`, `partials.dynamic-popup` (+402 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 752 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **58 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `SecurityHardeningTest` connect `Security Hardening Tests` to `Authentication & Session Flow`, `Authentication & Session Flow`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **What connects `php`, `layouts.app`, `layouts.app` to the rest of the system?**
  _407 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Authentication & Session Flow` be split into smaller, more focused modules?**
  _Cohesion score 0.05422838031533684 - nodes in this community are weakly interconnected._
- **Why does `User` connect `Authentication & Session Flow` to `Authentication & Session Flow`, `GDS Terminal & Parser Engine`, `Authentication & Session Flow`, `Database Schema & Migrations`, `UserFactory Module`, `InstallerService Module`, `Authentication & Session Flow`, `EnsureAppIsInstalled Module`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Should `Test Coverage Validation` be split into smaller, more focused modules?**
  _Cohesion score 0.06054054054054054 - nodes in this community are weakly interconnected._
- **Why does `Airport` connect `Aircraft Module` to `GDS Terminal & Parser Engine`, `PricingService Module`, `MathDateService Module`, `AvailabilityService Module`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **Should `Security Audit Validation` be split into smaller, more focused modules?**
  _Cohesion score 0.05662862159789289 - nodes in this community are weakly interconnected._