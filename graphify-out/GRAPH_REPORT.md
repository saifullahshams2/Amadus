# Graph Report - amadus  (2026-11-23)

## Corpus Check
- 5 files · ~273,226 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1378 nodes · 1984 edges · 120 communities (71 shown, 49 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 77 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Coverage Ledger Validator
- Findings Validator
- PHP Composer Dependencies
- Database Migrations & Core Middleware
- Amadeus GDS Core Services
- Deploying to Cloud Skill
- Security Hardening Tests
- AGENTS.md Coding Conventions
- CLAUDE.md Coding Conventions
- TypeScript GDS Type Definitions
- Amadeus Commands Group 2
- PNR Model & Test Helpers
- NPM Package.json Config
- Password Reset Flow
- Installer Service & Tests
- Amadeus Command Reference 2 Tests
- Amadeus Commands Group 2
- Aircraft & Airline Models
- Security Audit Skill: Principles
- TypeScript Source Files
- Misc: re | sys | json | requests | urllib_pars...
- Dashboard Controller
- Aircraft & Airline Models
- Base Controller & Training
- Aircraft & Airline Models
- Decode Service
- Security Hardening Tests
- Feature Specifications
- Password Reset Flow
- Infer Conventions Checklists
- Infer Conventions Skill Config
- Laravel Best Practices: Architecture
- Terminal Controller
- Laravel Best Practices: Security
- Security Audit: Client Side
- Security Audit: Cloud & Deployment
- Security Audit: Web Protocol & Auth
- Tailwind CSS Skill
- Installer Service & Tests
- Authentication Controller & User Model
- Installer Service & Tests
- Submission Model
- Installer Service & Tests
- User Requirements Verification Tests
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- MCP Configuration
- TypeScript Source Files
- TypeScript Source Files
- Laravel Best Practices: Rules
- TypeScript Source Files
- TypeScript Source Files
- Laravel Configuration
- Dashboard UI Tests
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- TypeScript Source Files
- TypeScript Source Files
- TypeScript Source Files
- Amadeus Commands Group 2
- Amadeus Commands Group 2
- Authentication Controller & User Model
- Blade Views & Layouts
- Idle/Queue Models
- Misc: app_services_amadeus_availabilityservice...
- Misc: app_services_amadeus_pricingservice_pric...
- Application Bootstrap
- Blade Views & Layouts
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Amadeus Commands Group 2
- Installer Service & Tests
- Submission Model
- Misc: datetime | datetimezone | app_services_a...
- Dashboard UI Tests
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Misc: app_services_amadeus_flightinfoservice_f...
- Misc: app_services_amadeus_helpservice | app_s...
- Laravel Best Practices: Rules
- Laravel Best Practices: Rules
- Misc: app_services_amadeus_ticketingservice | ...
- TypeScript Source Files
- TypeScript Source Files
- Misc: app_services_amadeus_aisservice | app_se...
- Misc: app_services_amadeus_hotelcarservice | a...
- Misc: app_services_amadeus_timaticservice | ap...
- Laravel Configuration
- Amadeus Commands Group 2
- TypeScript Source Files
- Misc: artisan | argvinput | application...

## God Nodes (most connected - your core abstractions)
1. `InstallerService` - 34 edges
2. `SecurityHardeningTest` - 32 edges
3. `User` - 25 edges
4. `InstallerTest` - 24 edges
5. `AmadeusCmdReference2Test` - 24 edges
6. `TestCase` - 21 edges
7. `AmadeusCmdReferenceTest` - 21 edges
8. `AmadeusParser` - 20 edges
9. `UserRequirementsVerificationTest` - 16 edges
10. `collectUnitErrors()` - 16 edges

## Surprising Connections (you probably didn't know these)
- `InstallerTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/InstallerTest.php → app/Services/Installer/InstallerService.php
- `UserRequirementsVerificationTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/UserRequirementsVerificationTest.php → app/Services/Installer/InstallerService.php
- `LandingAndApprovalTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/LandingAndApprovalTest.php → tests/TestCase.php
- `AmadeusCmdReference2Test` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/AmadeusCmdReference2Test.php → tests/TestCase.php
- `AmadeusCmdReferenceTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/AmadeusCmdReferenceTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Amadus Simulator Core Architecture** — readme_amadus_training_simulator, readme_altea_cryptic_terminal, readme_pnr_lifecycle_engine, readme_flight_instructor_hub [EXTRACTED 1.00]
- **Core GDS Subsystems** — features_trainee_student_dashboard, features_instructor_operations_hub, features_cryptic_terminal_simulator, features_altea_cryptic_command_engine [INFERRED 0.85]

## Communities (120 total, 49 thin omitted)

### Community 0 - "Coverage Ledger Validator"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 1 - "Findings Validator"
Cohesion: 0.04
Nodes (42): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+34 more)

### Community 2 - "PHP Composer Dependencies"
Cohesion: 0.06
Nodes (3): AppServiceProvider, InstallerTest, UserRequirementsVerificationTest

### Community 3 - "Database Migrations & Core Middleware"
Cohesion: 0.10
Nodes (38): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+30 more)

### Community 4 - "Amadeus GDS Core Services"
Cohesion: 0.10
Nodes (17): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+9 more)

### Community 5 - "Deploying to Cloud Skill"
Cohesion: 0.11
Nodes (3): AmadeusParser, CommandExecutionResult, QueueService

### Community 6 - "Security Hardening Tests"
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 8 - "CLAUDE.md Coding Conventions"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 9 - "TypeScript GDS Type Definitions"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 10 - "Amadeus Commands Group 2"
Cohesion: 0.07
Nodes (26): AutoGradeResult, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck, LearningAnalytics (+18 more)

### Community 11 - "PNR Model & Test Helpers"
Cohesion: 0.11
Nodes (18): assert, confirmed(), errorsFor(), evidence(), fs, {
  LIMITS,
  collect,
  collectSchemaErrors,
  validateDocument,
}, needsValidation(), os (+10 more)

### Community 12 - "NPM Package.json Config"
Cohesion: 0.12
Nodes (24): ATTEMPT_FIELDS, ATTEMPT_STATUSES, createErrorList(), escapeUnsafeDiagnosticCharacters(), fs, hasValidUnicodeScalarValues(), hasVisibleProse(), path (+16 more)

### Community 13 - "Password Reset Flow"
Cohesion: 0.17
Nodes (3): {closure#1}(), InstallerController, InstallerService

### Community 14 - "Installer Service & Tests"
Cohesion: 0.09
Nodes (22): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, name, optionalDependencies (+14 more)

### Community 15 - "Amadeus Command Reference 2 Tests"
Cohesion: 0.15
Nodes (5): PasswordResetController, SecurityHeadersMiddleware, {closure#1}(), {closure#3}(), {closure#4}()

### Community 17 - "Aircraft & Airline Models"
Cohesion: 0.13
Nodes (3): {closure#1}(), EnsureAppIsInstalled, User

### Community 18 - "Security Audit Skill: Principles"
Cohesion: 0.11
Nodes (13): LIMITS, archivedAttempt(), assert, fs, {
  LIMITS,
  canonicalCoverageId,
  encodeCanonicalRef,
  isSafeAgentId,
  isSafeRelativePath,
  preflightJsonText,
  validateDocument,
}, localCheck(), os, path (+5 more)

### Community 19 - "TypeScript Source Files"
Cohesion: 0.15
Nodes (4): Pnr, AmadeusGdsIntegrationTest, ExampleTest, TestCase

### Community 21 - "Dashboard Controller"
Cohesion: 0.27
Nodes (18): canonicalCoverageId(), collectUnitErrors(), encodeCanonicalRef(), hasOwn(), hasValidSemanticFields(), isCanonicalRef(), isObject(), isOwnedArtifactPath() (+10 more)

### Community 22 - "Aircraft & Airline Models"
Cohesion: 0.18
Nodes (4): {closure#1}(), {closure#2}(), DashboardController, {closure#1}()

### Community 23 - "Base Controller & Training"
Cohesion: 0.18
Nodes (4): Aircraft, Airline, Airport, DatabaseSeeder

### Community 24 - "Aircraft & Airline Models"
Cohesion: 0.26
Nodes (8): api_headers(), execute(), login(), main(), new_session(), phase2(), phase3(), show()

### Community 27 - "Feature Specifications"
Cohesion: 0.19
Nodes (3): Controller, TrainingController, CurriculumService

### Community 30 - "Infer Conventions Skill Config"
Cohesion: 0.22
Nodes (3): Scenario, {closure#1}(), ScenarioService

### Community 31 - "Laravel Best Practices: Architecture"
Cohesion: 0.17
Nodes (13): Amadeus Altéa Cryptic Command Engine, Automated Drill Grading & Submission, Complete REST API Reference, Core Domain Models & Schemas, Cryptic Terminal Simulator, Amadeus GDS Platform Features Specification, Instructor Operations Hub & Classroom Management, Live Classroom Student Grid (+5 more)

### Community 33 - "Laravel Best Practices: Security"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 34 - "Security Audit: Client Side"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 35 - "Security Audit: Cloud & Deployment"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 36 - "Security Audit: Web Protocol & Auth"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 37 - "Tailwind CSS Skill"
Cohesion: 0.18
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 38 - "Installer Service & Tests"
Cohesion: 0.40
Nodes (11): JsonStructureError, preflightJsonText(), beginContainer(), beginString(), beginValue(), closeContainer(), countValue(), currentContainer() (+3 more)

### Community 39 - "Authentication Controller & User Model"
Cohesion: 0.18
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 40 - "Installer Service & Tests"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 41 - "Submission Model"
Cohesion: 0.22
Nodes (3): {closure#1}(), Submission, TerminalActivityLog

### Community 42 - "Installer Service & Tests"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 43 - "User Requirements Verification Tests"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 44 - "Laravel Best Practices: Rules"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 45 - "Laravel Best Practices: Rules"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 46 - "Laravel Best Practices: Rules"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 47 - "Laravel Best Practices: Rules"
Cohesion: 0.20
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 48 - "MCP Configuration"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 49 - "TypeScript Source Files"
Cohesion: 0.20
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 50 - "TypeScript Source Files"
Cohesion: 0.20
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 51 - "Laravel Best Practices: Rules"
Cohesion: 0.20
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 55 - "Laravel Configuration"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 56 - "Dashboard UI Tests"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 57 - "Laravel Best Practices: Rules"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 58 - "Laravel Best Practices: Rules"
Cohesion: 0.22
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 59 - "Laravel Best Practices: Rules"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 60 - "TypeScript Source Files"
Cohesion: 0.31
Nodes (3): {closure#1}(), {closure#2}(), QueueItem

### Community 63 - "Amadeus Commands Group 2"
Cohesion: 0.22
Nodes (8): Altéa Cryptic Terminal Emulator, Amadus Training Simulator (1A), Automated Installers (Web & CLI), Cadet Student Cockpit, Flight Instructor Operations Hub, Global Aviation Datasets, Realistic PNR Lifecycle Engine, Role-Based Training Ecosystem

### Community 64 - "Amadeus Commands Group 2"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 65 - "Authentication Controller & User Model"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 66 - "Blade Views & Layouts"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 67 - "Idle/Queue Models"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 70 - "Application Bootstrap"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 71 - "Blade Views & Layouts"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 72 - "Laravel Best Practices: Rules"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 73 - "Laravel Best Practices: Rules"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 74 - "Laravel Best Practices: Rules"
Cohesion: 0.29
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 75 - "Laravel Best Practices: Rules"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 76 - "Amadeus Commands Group 2"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 78 - "Submission Model"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 79 - "Misc: datetime | datetimezone | app_services_a..."
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 80 - "Dashboard UI Tests"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 81 - "Laravel Best Practices: Rules"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 82 - "Laravel Best Practices: Rules"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 86 - "Laravel Best Practices: Rules"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 87 - "Laravel Best Practices: Rules"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 92 - "Laravel Best Practices: Rules"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 93 - "Misc: app_services_amadeus_flightinfoservice_f..."
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

## Knowledge Gaps
- **403 isolated node(s):** `php`, `layouts.app`, `layouts.app`, `layouts.app`, `partials.dynamic-popup` (+398 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 725 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **49 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `SecurityHardeningTest` connect `AGENTS.md Coding Conventions` to `TypeScript Source Files`, `Laravel Best Practices: Security`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **What connects `php`, `layouts.app`, `layouts.app` to the rest of the system?**
  _403 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Coverage Ledger Validator` be split into smaller, more focused modules?**
  _Cohesion score 0.04081632653061224 - nodes in this community are weakly interconnected._
- **Why does `InstallerService` connect `Password Reset Flow` to `PHP Composer Dependencies`, `Amadeus Command Reference 2 Tests`, `Aircraft & Airline Models`, `Laravel Best Practices: Security`, `Laravel Best Practices: Rules`, `Security Hardening Tests`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **Should `Findings Validator` be split into smaller, more focused modules?**
  _Cohesion score 0.043478260869565216 - nodes in this community are weakly interconnected._
- **Why does `Airport` connect `Base Controller & Training` to `Misc: app_services_amadeus_availabilityservice...`, `TypeScript Source Files`, `Misc: app_services_amadeus_helpservice | app_s...`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **Should `PHP Composer Dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.06363636363636363 - nodes in this community are weakly interconnected._