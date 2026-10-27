# Graph Report - gds_amadeus  (2026-10-27)

## Corpus Check
- 156 files · ~249,480 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 23 file(s) not represented in the graph (top: (none) 19, .example 1, .xml 1)

## Summary
- 1251 nodes · 1807 edges · 116 communities (61 shown, 55 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 77 edges (avg confidence: 0.86)
- Token cost: 1,200 input · 800 output

## Community Hubs (Navigation)
- Coverage Ledger Validation
- Security Audit Findings Schema
- Cryptic Terminal Controller & Execution
- Composer Dependencies & Autoload
- Security Audit Threat Hunting
- Core Database Migrations & Users
- Laravel Cloud Deployment Workflows
- Amadeus GDS Data Models & Types
- Laravel Boost Guidelines
- Claude Code Agent Guidelines
- Dual-Role Dashboard Controller & APIs
- HTTP Routing & Controller Gateway
- Frontend Bundling & Tailwind Tooling
- Security & Install Middleware Pipeline
- Platform Installer Service
- Amadeus Altéa Syntax & PNR Parsing
- Queue Management & Broadcasting Engine
- Scenario Management & Auto-Grading
- Amadeus Cryptic Engine
- Database Start The
- Validation Http Input
- Step Edge Cases
- Trainingcontroller Curriculumservice Modules
- Aircraft Lessonprogress Pnr
- Instructor Student Can
- Use For Architecture
- Sensitive Apply Cross
- Attack Classes Subagent
- Css Tailwind Basic
- Illuminate Installappcommand Removeinstallerc
- And Redirects Dashboard
- Password Reset User
- Cannot Csrf Instructor
- Requirement Page Demo
- With Query Subquery
- Notifications Event Discovery
- Migrations Define Foreign
- For Time Jobs
- And Classes Subagent
- And Classes Subagent
- And Attack Classes
- And Attack Classes
- And Attack Classes
- And Type Attack
- And Attack Classes
- Attack Classes Subagent
- Controller Authcontroller Login
- Handle Ensureuserisinstructor Securityheaders
- Submission User Self
- Cache Use For
- Relationships Loading Add
- Queries Scopes Define
- And Attack Classes
- Queueitem Self Booted
- Scenario Submissions Scenarioservice
- Illuminate Foundation Configuration
- Userfactory Definition Unapproved
- Architecture Security Consistency
- View For Component
- Exception Report Exceptions
- Task The Run
- Endpoint The Which
- Index Landingcontroller View
- Amadeusgdsintegrationtest Illuminate Foundati
- Use For Collection
- Http Requests Fake
- Delivery Mail Assert
- Controllers Use Keep
- Convention And Style
- Validation Rules Add
- And Attack Classes
- Assert The Assertions
- Built Laravel Assertion
- Fakes And Database
- How Suite Common
- And Assertions Coverage
- Testcase Exampletest Illuminate
- Configuration Environment Best
- Naming And File
- Terminalactivitylog Self Booted
- Scenarioservice Autograde Deletescenario
- Installercontroller Install Requirements
- Appserviceprovider Boot Register
- Data Providers Each
- Consistency First How
- Exampletest True Phpunit
- Mcp Json Laravel
- Forgot Password Blade
- Login Blade Layouts
- Pending Approval Blade
- Register Blade Layouts
- Reset Password Blade
- Instructor Blade Layouts
- Student Blade Layouts
- Index Blade Layouts
- Index Blade Layouts

## God Nodes (most connected - your core abstractions)
1. `User` - 30 edges
2. `InstallerService` - 29 edges
3. `TestCase` - 23 edges
4. `DashboardController` - 21 edges
5. `StudentInstructorAccountSeparationTest` - 19 edges
6. `UserRequirementsVerificationTest` - 18 edges
7. `collectUnitErrors()` - 16 edges
8. `DualRoleDashboardTest` - 16 edges
9. `InstallerTest` - 16 edges
10. `Submission` - 14 edges

## Surprising Connections (you probably didn't know these)
- `InstallerTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/InstallerTest.php → app/Services/Installer/InstallerService.php
- `UserRequirementsVerificationTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/UserRequirementsVerificationTest.php → app/Services/Installer/InstallerService.php
- `unit()` --calls--> `canonicalCoverageId()`  [EXTRACTED]
  .agents/skills/security-audit/validate-coverage-ledger.test.cjs → .agents/skills/security-audit/validate-coverage-ledger.cjs
- `errorsFor()` --calls--> `validateDocument()`  [EXTRACTED]
  .agents/skills/security-audit/validate-coverage-ledger.test.cjs → .agents/skills/security-audit/validate-coverage-ledger.cjs
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core GDS Subsystems** — features_trainee_student_dashboard, features_instructor_operations_hub, features_cryptic_terminal_simulator, features_altea_cryptic_command_engine [INFERRED 0.85]

## Communities (116 total, 55 thin omitted)

### Community 0 - "Coverage Ledger Validation"
Cohesion: 0.08
Nodes (56): ATTEMPT_FIELDS, ATTEMPT_STATUSES, canonicalCoverageId(), collectUnitErrors(), createErrorList(), encodeCanonicalRef(), escapeUnsafeDiagnosticCharacters(), hasOwn() (+48 more)

### Community 1 - "Security Audit Findings Schema"
Cohesion: 0.07
Nodes (45): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+37 more)

### Community 2 - "Cryptic Terminal Controller & Execution"
Cohesion: 0.06
Nodes (16): TerminalController, Airline, Airport, AmadeusParser, CommandExecutionResult, AvailabilityResult, AvailabilityService, DecodeResult (+8 more)

### Community 3 - "Composer Dependencies & Autoload"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 4 - "Security Audit Threat Hunting"
Cohesion: 0.04
Nodes (42): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+34 more)

### Community 5 - "Core Database Migrations & Users"
Cohesion: 0.09
Nodes (19): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+11 more)

### Community 6 - "Laravel Cloud Deployment Workflows"
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 7 - "Amadeus GDS Data Models & Types"
Cohesion: 0.07
Nodes (29): AutoGradeResult, ClassroomStudentStatus, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck (+21 more)

### Community 8 - "Laravel Boost Guidelines"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 9 - "Claude Code Agent Guidelines"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 11 - "HTTP Routing & Controller Gateway"
Cohesion: 0.15
Nodes (4): {closure#1}(), {closure#2}(), {closure#1}(), User

### Community 12 - "Frontend Bundling & Tailwind Tooling"
Cohesion: 0.13
Nodes (20): devDependencies, concurrently@^10.0.3, laravel-vite-plugin@^3.1, tailwindcss@^4.0.0, @tailwindcss/vite@^4.0.0, vite@^8.0.0, optionalDependencies, @laravel/multiplex@^0.4.1 (+12 more)

### Community 13 - "Security & Install Middleware Pipeline"
Cohesion: 0.15
Nodes (4): {closure#1}(), EnsureAppIsInstalled, InstallerService, DatabaseSeeder

### Community 18 - "Amadeus Cryptic Engine"
Cohesion: 0.17
Nodes (13): Amadeus Altéa Cryptic Command Engine, Automated Drill Grading & Submission, Complete REST API Reference, Core Domain Models & Schemas, Cryptic Terminal Simulator, Amadeus GDS Platform Features Specification, Instructor Operations Hub & Classroom Management, Live Classroom Student Grid (+5 more)

### Community 19 - "Database Start The"
Cohesion: 0.15
Nodes (12): 1. Initialize Database & Seed Datasets, 2. Start the Local Server, 3. Run the Automated Test Suite, 📜 Amadeus Cryptic Command Cheat Sheet, ✈️ Amadeus GDS Training Simulator (1A), 🗄️ Database Configuration (SQLite, MySQL, or MariaDB), 🔑 Demo Accounts, Option 1: SQLite (Default - Recommended for Local Dev & Testing) (+4 more)

### Community 20 - "Validation Http Input"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 21 - "Step Edge Cases"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 23 - "Aircraft Lessonprogress Pnr"
Cohesion: 0.23
Nodes (3): Aircraft, LessonProgress, Pnr

### Community 25 - "Use For Architecture"
Cohesion: 0.18
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 26 - "Sensitive Apply Cross"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 27 - "Attack Classes Subagent"
Cohesion: 0.18
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 28 - "Css Tailwind Basic"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 34 - "With Query Subquery"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 35 - "Notifications Event Discovery"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 36 - "Migrations Define Foreign"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 37 - "For Time Jobs"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 38 - "And Classes Subagent"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 39 - "And Classes Subagent"
Cohesion: 0.20
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 40 - "And Attack Classes"
Cohesion: 0.20
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 41 - "And Attack Classes"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 42 - "And Attack Classes"
Cohesion: 0.20
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 43 - "And Type Attack"
Cohesion: 0.20
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 44 - "And Attack Classes"
Cohesion: 0.20
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 45 - "Attack Classes Subagent"
Cohesion: 0.20
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 49 - "Cache Use For"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 50 - "Relationships Loading Add"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 51 - "Queries Scopes Define"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 52 - "And Attack Classes"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 53 - "Queueitem Self Booted"
Cohesion: 0.31
Nodes (3): {closure#1}(), {closure#2}(), QueueItem

### Community 55 - "Illuminate Foundation Configuration"
Cohesion: 0.28
Nodes (3): {closure#1}(), {closure#3}(), {closure#4}()

### Community 57 - "Architecture Security Consistency"
Cohesion: 0.25
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 58 - "View For Component"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 59 - "Exception Report Exceptions"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 60 - "Task The Run"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 61 - "Endpoint The Which"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 64 - "Use For Collection"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 65 - "Http Requests Fake"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 66 - "Delivery Mail Assert"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 67 - "Controllers Use Keep"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 68 - "Convention And Style"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 69 - "Validation Rules Add"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 71 - "Assert The Assertions"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 72 - "Built Laravel Assertion"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 73 - "Fakes And Database"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 74 - "How Suite Common"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 75 - "And Assertions Coverage"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 78 - "Configuration Environment Best"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 79 - "Naming And File"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 80 - "Terminalactivitylog Self Booted"
Cohesion: 0.33
Nodes (3): {closure#3}(), {closure#1}(), TerminalActivityLog

### Community 84 - "Data Providers Each"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 85 - "Consistency First How"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

## Knowledge Gaps
- **405 isolated node(s):** `php`, `layouts.app`, `layouts.app`, `layouts.app`, `layouts.app` (+400 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 702 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **55 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `DualRoleDashboardTest` connect `Queue Management & Broadcasting Engine` to `Testcase Exampletest Illuminate`, `Amadeusgdsintegrationtest Illuminate Foundati`, `Aircraft Lessonprogress Pnr`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **What connects `php`, `layouts.app`, `layouts.app` to the rest of the system?**
  _405 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Coverage Ledger Validation` be split into smaller, more focused modules?**
  _Cohesion score 0.07837301587301587 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `Testcase Exampletest Illuminate` to `Cannot Csrf Instructor`, `Requirement Page Demo`, `HTTP Routing & Controller Gateway`, `Illuminate Contracts Encryption`, `Security & Install Middleware Pipeline`, `Platform Installer Service`, `Amadeus Altéa Syntax & PNR Parsing`, `Queue Management & Broadcasting Engine`, `Scenario Submissions Scenarioservice`, `Aircraft Lessonprogress Pnr`, `Instructor Student Can`, `Password Reset User`, `And Redirects Dashboard`, `Amadeusgdsintegrationtest Illuminate Foundati`?**
  _High betweenness centrality (0.016) - this node is a cross-community bridge._
- **Should `Security Audit Findings Schema` be split into smaller, more focused modules?**
  _Cohesion score 0.07199032062915911 - nodes in this community are weakly interconnected._
- **Why does `User` connect `HTTP Routing & Controller Gateway` to `Cryptic Terminal Controller & Execution`, `Illuminate Contracts Encryption`, `Security & Install Middleware Pipeline`, `Testcase Exampletest Illuminate`, `Scenario Submissions Scenarioservice`, `Aircraft Lessonprogress Pnr`, `Userfactory Definition Unapproved`, `Illuminate Installappcommand Removeinstallerc`, `Index Landingcontroller View`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._
- **Should `Cryptic Terminal Controller & Execution` be split into smaller, more focused modules?**
  _Cohesion score 0.0649895178197065 - nodes in this community are weakly interconnected._