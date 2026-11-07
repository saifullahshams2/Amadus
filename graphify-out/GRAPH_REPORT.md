# Graph Report - amadus  (2026-11-07)

## Corpus Check
- 162 files · ~281,827 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 18, .example 1, .xml 1)

## Summary
- 1420 nodes · 2088 edges · 111 communities (70 shown, 41 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 118 edges (avg confidence: 0.87)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Application Installer & Setup Commands
- Terminal Simulator & Parser Engine
- Amadeus Reservation Command Catalog
- PHP Dependencies & Composer Config
- Aviation Flight Domain Models
- Authentication & Password Management
- Audit Finding Schema Validation
- Core Database Migration Schemas
- Instructor Dashboard Operations
- Laravel Cloud Infrastructure Checklists
- Frontend TypeScript GDS Types
- Security Hardening Feature Tests
- Agent Guidelines & Laravel Standards
- Claude Architecture Rules
- Audit Findings Validation Tests
- Security Coverage Ledger Validator
- Authentication Controller Actions
- Dashboard & Training Controllers
- Node NPM Build Dependencies
- Installation & Role Middleware
- Coverage Ledger Test Suite
- Amadeus Command Reference Tests
- Security Audit Workflow Skills
- Audit Ledger Canonical Formatting
- Training Scenarios & Submissions
- User Role Separation Tests
- Dual Role Dashboard Tests
- Platform Feature Specifications
- Codebase Conventions Checklists
- Convention Inference Process Rules
- Community 30
- InstructorStudentManagement setUp instru
- PasswordReset api_password_reset_flow fo
- Community 33
- Community 34
- Community 35
- JsonStructureError preflightJsonText beg
- Community 37
- Community 38
- TrainingController construct modules
- LandingAndApproval setUp 01_landing_page
- Community 41
- Community 42
- Community 43
- Community 44
- Community 45
- Community 46
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- AppServiceProvider boot ensureEnvironmen
- UserFactory {closure#1} {closure#2}
- Altéa Cryptic Terminal Emulator Amadus T
- DashboardSubpagesAndTerminalHeader setUp
- ResponsiveNavigationAndSidebar setUp 01_
- Community 63
- Community 64
- Community 65
- Community 66
- Community 67
- Community 68
- Community 69
- Community 70
- Community 71
- Community 72
- Community 73
- Community 74
- Community 75
- Community 76
- Community 77
- Community 78
- Community 79
- Community 80
- Community 81
- Community 82
- Community 83
- PHPUnit\Framework\Case Example that_true
- mcp.json php laravel-boost
- forgot-password.blade layouts.app
- login.blade layouts.app
- pending-approval.blade layouts.app
- register.blade layouts.app
- reset-password.blade layouts.app
- instructor.blade layouts.dashboard
- student.blade layouts.dashboard
- index.blade layouts.app
- app.blade partials.dynamic-popup
- dashboard.blade partials.dynamic-popup
- index.blade layouts.app

## God Nodes (most connected - your core abstractions)
1. `User` - 36 edges
2. `InstallerService` - 34 edges
3. `Amadeus Quick Reference Guide (Tourism School)` - 32 edges
4. `SecurityHardeningTest` - 30 edges
5. `TestCase` - 29 edges
6. `InstallerTest` - 24 edges
7. `DashboardController` - 22 edges
8. `AmadeusCmdReferenceTest` - 21 edges
9. `Amadeus Basic Reservation Guide` - 20 edges
10. `StudentInstructorAccountSeparationTest` - 19 edges

## Surprising Connections (you probably didn't know these)
- `Phone Field (AP)` --semantically_similar_to--> `Mandatory PNR Elements (NM, AP, TK)`  [INFERRED] [semantically similar]
  amadeus-cmd-1.md → amadeus-cmd-2.md
- `Ticketing Arrangement (TK)` --semantically_similar_to--> `Mandatory PNR Elements (NM, AP, TK)`  [INFERRED] [semantically similar]
  amadeus-cmd-1.md → amadeus-cmd-2.md
- `InstallerTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/InstallerTest.php → app/Services/Installer/InstallerService.php
- `UserRequirementsVerificationTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/UserRequirementsVerificationTest.php → app/Services/Installer/InstallerService.php
- `Sign-In & Sign-Out (JI/JO)` --semantically_similar_to--> `Sign In & Sign Out (HE SIGN, JI, JO, JXC, JMB)`  [INFERRED] [semantically similar]
  amadeus-cmd-1.md → amadeus-cmd-2.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Amadus Simulator Core Architecture** — readme_amadus_training_simulator, readme_altea_cryptic_terminal, readme_pnr_lifecycle_engine, readme_flight_instructor_hub [EXTRACTED 1.00]
- **Core GDS Subsystems** — features_trainee_student_dashboard, features_instructor_operations_hub, features_cryptic_terminal_simulator, features_altea_cryptic_command_engine [INFERRED 0.85]
- **PNR Creation and Booking Lifecycle** — amadeus_cmd_1_name_element, amadeus_cmd_1_segment_sell, amadeus_cmd_1_phone_field, amadeus_cmd_1_ticketing_arrangement, amadeus_cmd_1_received_from, amadeus_cmd_1_end_transaction [EXTRACTED 1.00]
- **Ticketless Booking and Pricing Pipeline** — amadeus_cmd_2_availability_display, amadeus_cmd_2_segment_sell, amadeus_cmd_2_mandatory_pnr_elements, amadeus_cmd_2_pricing_entries, amadeus_cmd_2_ticketless_booking_workflow [EXTRACTED 1.00]
- **PNR Servicing, Split, and Queue Management Flow** — amadeus_cmd_2_pnr_modifications, amadeus_cmd_2_split_pnr, amadeus_cmd_2_non_homogeneous_pnr, amadeus_cmd_2_queue_system, amadeus_cmd_2_pnr_history [INFERRED 0.85]

## Communities (111 total, 41 thin omitted)

### Community 0 - "Application Installer & Setup Commands"
Cohesion: 0.05
Nodes (7): InstallAppCommand, RemoveInstallerCommand, {closure#1}(), InstallerController, InstallerService, InstallerTest, UserRequirementsVerificationTest

### Community 1 - "Terminal Simulator & Parser Engine"
Cohesion: 0.06
Nodes (13): TerminalController, AmadeusParser, CommandExecutionResult, AvailabilityService, DecodeResult, DecodeService, PnrService, PnrValidationResult (+5 more)

### Community 2 - "Amadeus Reservation Command Catalog"
Cohesion: 0.06
Nodes (52): Amadeus Basic Reservation Guide, Flight Availability Display (AN/SN), Change Booking Class & Date (SB), End Transaction (ET/ER), Frequent Flyer Number (FFN/SR FQTV), Manual Ticket Number Entry (FH), Name Elements (NM), Other Services Information (OS) (+44 more)

### Community 3 - "PHP Dependencies & Composer Config"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 4 - "Aviation Flight Domain Models"
Cohesion: 0.06
Nodes (10): Aircraft, Airline, Airport, {closure#1}(), {closure#2}(), QueueItem, {closure#1}(), {closure#1}() (+2 more)

### Community 5 - "Authentication & Password Management"
Cohesion: 0.08
Nodes (6): {closure#2}(), {closure#1}(), User, AmadeusGdsIntegrationTest, ExampleTest, TestCase

### Community 6 - "Audit Finding Schema Validation"
Cohesion: 0.10
Nodes (38): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+30 more)

### Community 7 - "Core Database Migration Schemas"
Cohesion: 0.09
Nodes (19): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+11 more)

### Community 9 - "Laravel Cloud Infrastructure Checklists"
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 10 - "Frontend TypeScript GDS Types"
Cohesion: 0.07
Nodes (29): AutoGradeResult, ClassroomStudentStatus, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck (+21 more)

### Community 12 - "Agent Guidelines & Laravel Standards"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 13 - "Claude Architecture Rules"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 14 - "Audit Findings Validation Tests"
Cohesion: 0.11
Nodes (18): assert, confirmed(), errorsFor(), evidence(), fs, {
  LIMITS,
  collect,
  collectSchemaErrors,
  validateDocument,
}, needsValidation(), os (+10 more)

### Community 15 - "Security Coverage Ledger Validator"
Cohesion: 0.12
Nodes (24): ATTEMPT_FIELDS, ATTEMPT_STATUSES, createErrorList(), escapeUnsafeDiagnosticCharacters(), fs, hasValidUnicodeScalarValues(), hasVisibleProse(), path (+16 more)

### Community 16 - "Authentication Controller Actions"
Cohesion: 0.08
Nodes (4): AuthController, Controller, LandingController, PasswordResetController

### Community 17 - "Dashboard & Training Controllers"
Cohesion: 0.11
Nodes (6): {closure#1}(), {closure#4}(), {closure#5}(), LessonProgress, Pnr, TerminalActivityLog

### Community 18 - "Node NPM Build Dependencies"
Cohesion: 0.09
Nodes (22): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, name, optionalDependencies (+14 more)

### Community 19 - "Installation & Role Middleware"
Cohesion: 0.15
Nodes (6): EnsureAppIsInstalled, EnsureUserIsInstructor, SecurityHeadersMiddleware, {closure#1}(), {closure#3}(), {closure#4}()

### Community 20 - "Coverage Ledger Test Suite"
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

### Community 22 - "Security Audit Workflow Skills"
Cohesion: 0.11
Nodes (18): Anti-patterns, Core principles, Cost budget, Coverage and prior runs, Full audit planning, Full audit setup, Full audit workflow, Operating modes (+10 more)

### Community 23 - "Audit Ledger Canonical Formatting"
Cohesion: 0.27
Nodes (18): canonicalCoverageId(), collectUnitErrors(), encodeCanonicalRef(), hasOwn(), hasValidSemanticFields(), isCanonicalRef(), isObject(), isOwnedArtifactPath() (+10 more)

### Community 24 - "Training Scenarios & Submissions"
Cohesion: 0.17
Nodes (4): Scenario, Submission, {closure#1}(), ScenarioService

### Community 27 - "Platform Feature Specifications"
Cohesion: 0.17
Nodes (13): Amadeus Altéa Cryptic Command Engine, Automated Drill Grading & Submission, Complete REST API Reference, Core Domain Models & Schemas, Cryptic Terminal Simulator, Amadeus GDS Platform Features Specification, Instructor Operations Hub & Classroom Management, Live Classroom Student Grid (+5 more)

### Community 28 - "Codebase Conventions Checklists"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 29 - "Convention Inference Process Rules"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 30 - "Community 30"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 33 - "Community 33"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 34 - "Community 34"
Cohesion: 0.18
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 35 - "Community 35"
Cohesion: 0.18
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 36 - "JsonStructureError preflightJsonText beg"
Cohesion: 0.40
Nodes (11): JsonStructureError, preflightJsonText(), beginContainer(), beginString(), beginValue(), closeContainer(), countValue(), currentContainer() (+3 more)

### Community 37 - "Community 37"
Cohesion: 0.18
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 38 - "Community 38"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 41 - "Community 41"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 42 - "Community 42"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 43 - "Community 43"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 44 - "Community 44"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 45 - "Community 45"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 46 - "Community 46"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 47 - "Community 47"
Cohesion: 0.20
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 48 - "Community 48"
Cohesion: 0.20
Nodes (10): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+2 more)

### Community 49 - "Community 49"
Cohesion: 0.20
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 50 - "Community 50"
Cohesion: 0.20
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 51 - "Community 51"
Cohesion: 0.20
Nodes (9): Candidate-verifier prompt, `FINDINGS-DETAIL.md`, `NEEDS-VALIDATION.md`, Phase 3: Independently validate every candidate, Phase 4: Write and validate `findings.json`, Phase 5: Verify the final records with fresh eyes, Phase 6: Produce target-neutral reports from final records, `REPORT.md` (+1 more)

### Community 52 - "Community 52"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 53 - "Community 53"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 54 - "Community 54"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 56 - "Community 56"
Cohesion: 0.22
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 57 - "Community 57"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 60 - "Altéa Cryptic Terminal Emulator Amadus T"
Cohesion: 0.22
Nodes (8): Altéa Cryptic Terminal Emulator, Amadus Training Simulator (1A), Automated Installers (Web & CLI), Cadet Student Cockpit, Flight Instructor Operations Hub, Global Aviation Datasets, Realistic PNR Lifecycle Engine, Role-Based Training Ecosystem

### Community 63 - "Community 63"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 64 - "Community 64"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 65 - "Community 65"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 66 - "Community 66"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 67 - "Community 67"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 68 - "Community 68"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 69 - "Community 69"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 70 - "Community 70"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 71 - "Community 71"
Cohesion: 0.29
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 72 - "Community 72"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 73 - "Community 73"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 74 - "Community 74"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 75 - "Community 75"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 76 - "Community 76"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 77 - "Community 77"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 78 - "Community 78"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 79 - "Community 79"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 80 - "Community 80"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 81 - "Community 81"
Cohesion: 0.40
Nodes (5): Architecture summary and companion selection, Deterministic coverage ledger, Phase 1: Map the source and plan coverage, Prior-run input, Reconnaissance

### Community 82 - "Community 82"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 83 - "Community 83"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

## Knowledge Gaps
- **416 isolated node(s):** `php`, `concurrently`, `tailwindcss`, `@laravel/multiplex`, `layouts.app` (+411 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 789 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **41 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Authentication & Password Management` to `Application Installer & Setup Commands`, `Terminal Simulator & Parser Engine`, `Instructor Dashboard Operations`, `Dashboard & Training Controllers`, `Installation & Role Middleware`, `Training Scenarios & Submissions`, `UserFactory {closure#1} {closure#2}`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **What connects `php`, `concurrently`, `tailwindcss` to the rest of the system?**
  _416 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Application Installer & Setup Commands` be split into smaller, more focused modules?**
  _Cohesion score 0.05098934550989345 - nodes in this community are weakly interconnected._
- **Why does `InstallerService` connect `Application Installer & Setup Commands` to `Instructor Dashboard Operations`, `Dashboard & Training Controllers`, `Installation & Role Middleware`, `Authentication & Password Management`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Should `Terminal Simulator & Parser Engine` be split into smaller, more focused modules?**
  _Cohesion score 0.061367621274108705 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `Authentication & Password Management` to `Application Installer & Setup Commands`, `PasswordReset api_password_reset_flow fo`, `LandingAndApproval setUp 01_landing_page`, `Security Hardening Feature Tests`, `Dashboard & Training Controllers`, `Amadeus Command Reference Tests`, `Training Scenarios & Submissions`, `User Role Separation Tests`, `Dual Role Dashboard Tests`, `DashboardSubpagesAndTerminalHeader setUp`, `ResponsiveNavigationAndSidebar setUp 01_`, `InstructorStudentManagement setUp instru`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **Should `Amadeus Reservation Command Catalog` be split into smaller, more focused modules?**
  _Cohesion score 0.0593990216631726 - nodes in this community are weakly interconnected._