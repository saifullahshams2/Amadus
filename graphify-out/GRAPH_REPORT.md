# Graph Report - gds_amadeus  (2026-10-30)

## Corpus Check
- 159 files · ~264,725 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 18, .example 1, .xml 1)

## Summary
- 1333 nodes · 1931 edges · 105 communities (63 shown, 42 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 87 edges (avg confidence: 0.86)
- Token cost: 1,200 input · 600 output

## Community Hubs (Navigation)
- Audit Coverage Ledger
- Security Findings Validator
- CLI Application Installer
- Installer Core Service
- PHP Dependencies & Config
- Audit Coverage Ledger
- Database Schema Migrations
- Cloud Deployment Checklists
- Cadet Dashboard Controller
- Frontend GDS Types
- Web Installer Controller
- Security Hardening Tests
- Agent Guidelines & Rules
- Claude Guidelines & Rules
- User & Instructor Model
- Training Drill Scenarios
- Tailwind CSS Development
- Authentication & Sessions
- Flight Instructor Operations
- Simulated Queue Bank
- User & Instructor Model
- Installer Feature Tests
- Dual-Role Dashboard Tests
- Trainingcontroller Subsystem
- Cadet Progress Tracking
- Agent Guidelines & Rules
- Agent Guidelines & Rules
- Feature Instructorstudentmanagementtest Instructorstudentmanagementtest
- User & Instructor Model
- Laravel Best Practices
- Laravel Best Practices
- Agent Guidelines & Rules
- Agent Guidelines & Rules
- Tailwind CSS Development
- User & Instructor Model
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Agent Guidelines & Rules
- Agent Guidelines & Rules
- Agent Guidelines & Rules
- Agent Guidelines & Rules
- Agent Guidelines & Rules
- Agent Guidelines & Rules
- Feature Dashboardsubpagesandterminalheadertest
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Agent Guidelines & Rules
- Agent Guidelines & Rules
- Installer Feature Tests
- Amadus Project Overview
- Feature Responsivenavigationandsidebartest Responsivenavigationandsidebartest
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Testing Best Practices
- Cadet Dashboard Controller
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Laravel Best Practices
- Agent Guidelines & Rules
- Testing Best Practices
- Testing Best Practices
- Testing Best Practices
- Testing Best Practices
- Testing Best Practices
- User & Instructor Model
- Laravel Best Practices
- Testing Best Practices
- Testing Best Practices
- Testing Best Practices
- Phpunit Framework Testcase
- Mcp Subsystem
- Resources Views Auth
- Resources Views Auth
- Resources Views Auth
- Resources Views Auth
- Resources Views Auth
- Resources Views Dashboard
- Resources Views Dashboard
- Resources Views Instructor
- Resources Views Layouts
- Resources Views Layouts
- Resources Views Terminal

## God Nodes (most connected - your core abstractions)
1. `User` - 34 edges
2. `InstallerService` - 34 edges
3. `SecurityHardeningTest` - 30 edges
4. `TestCase` - 27 edges
5. `DashboardController` - 22 edges
6. `InstallerTest` - 22 edges
7. `StudentInstructorAccountSeparationTest` - 19 edges
8. `UserRequirementsVerificationTest` - 18 edges
9. `DualRoleDashboardTest` - 17 edges
10. `collectUnitErrors()` - 16 edges

## Surprising Connections (you probably didn't know these)
- `{closure#2}()` --references--> `User`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Models/User.php
- `InstallerTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/InstallerTest.php → app/Services/Installer/InstallerService.php
- `UserRequirementsVerificationTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/UserRequirementsVerificationTest.php → app/Services/Installer/InstallerService.php
- `unit()` --calls--> `canonicalCoverageId()`  [EXTRACTED]
  .agents/skills/security-audit/validate-coverage-ledger.test.cjs → .agents/skills/security-audit/validate-coverage-ledger.cjs
- `errorsFor()` --calls--> `validateDocument()`  [EXTRACTED]
  .agents/skills/security-audit/validate-coverage-ledger.test.cjs → .agents/skills/security-audit/validate-coverage-ledger.cjs

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core GDS Subsystems** — features_trainee_student_dashboard, features_instructor_operations_hub, features_cryptic_terminal_simulator, features_altea_cryptic_command_engine [INFERRED 0.85]
- **Amadus Simulator Core Architecture** — readme_amadus_training_simulator, readme_altea_cryptic_terminal, readme_pnr_lifecycle_engine, readme_flight_instructor_hub [EXTRACTED 1.00]

## Communities (105 total, 42 thin omitted)

### Community 0 - "Audit Coverage Ledger"
Cohesion: 0.06
Nodes (66): ATTEMPT_FIELDS, ATTEMPT_STATUSES, canonicalCoverageId(), collectUnitErrors(), createErrorList(), encodeCanonicalRef(), escapeUnsafeDiagnosticCharacters(), fs (+58 more)

### Community 1 - "Security Findings Validator"
Cohesion: 0.06
Nodes (56): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+48 more)

### Community 2 - "CLI Application Installer"
Cohesion: 0.06
Nodes (7): InstallAppCommand, RemoveInstallerCommand, {closure#1}(), InstallerController, InstallerService, InstallerTest, UserRequirementsVerificationTest

### Community 3 - "Installer Core Service"
Cohesion: 0.06
Nodes (16): TerminalController, Aircraft, Airline, Airport, AmadeusParser, CommandExecutionResult, AvailabilityResult, AvailabilityService (+8 more)

### Community 4 - "PHP Dependencies & Config"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 5 - "Audit Coverage Ledger"
Cohesion: 0.04
Nodes (42): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+34 more)

### Community 6 - "Database Schema Migrations"
Cohesion: 0.09
Nodes (19): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+11 more)

### Community 7 - "Cloud Deployment Checklists"
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 8 - "Cadet Dashboard Controller"
Cohesion: 0.09
Nodes (9): {closure#1}(), {closure#2}(), {closure#4}(), {closure#5}(), LessonProgress, Pnr, Submission, TerminalActivityLog (+1 more)

### Community 9 - "Frontend GDS Types"
Cohesion: 0.07
Nodes (29): AutoGradeResult, ClassroomStudentStatus, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck (+21 more)

### Community 12 - "Agent Guidelines & Rules"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 13 - "Claude Guidelines & Rules"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 14 - "User & Instructor Model"
Cohesion: 0.09
Nodes (3): {closure#1}(), {closure#1}(), UserFactory

### Community 15 - "Training Drill Scenarios"
Cohesion: 0.08
Nodes (3): Scenario, {closure#1}(), StudentInstructorAccountSeparationTest

### Community 16 - "Tailwind CSS Development"
Cohesion: 0.09
Nodes (22): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, name, optionalDependencies (+14 more)

### Community 17 - "Authentication & Sessions"
Cohesion: 0.09
Nodes (3): AuthController, Controller, PasswordResetController

### Community 19 - "Simulated Queue Bank"
Cohesion: 0.13
Nodes (4): {closure#1}(), {closure#2}(), QueueItem, QueueService

### Community 20 - "User & Instructor Model"
Cohesion: 0.16
Nodes (5): EnsureUserIsInstructor, SecurityHeadersMiddleware, {closure#1}(), {closure#3}(), {closure#4}()

### Community 21 - "Installer Feature Tests"
Cohesion: 0.16
Nodes (3): EnsureAppIsInstalled, DatabaseSeeder, AmadeusGdsIntegrationTest

### Community 24 - "Cadet Progress Tracking"
Cohesion: 0.17
Nodes (13): Amadeus Altéa Cryptic Command Engine, Automated Drill Grading & Submission, Complete REST API Reference, Core Domain Models & Schemas, Cryptic Terminal Simulator, Amadeus GDS Platform Features Specification, Instructor Operations Hub & Classroom Management, Live Classroom Student Grid (+5 more)

### Community 25 - "Agent Guidelines & Rules"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 26 - "Agent Guidelines & Rules"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 29 - "Laravel Best Practices"
Cohesion: 0.18
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 30 - "Laravel Best Practices"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 31 - "Agent Guidelines & Rules"
Cohesion: 0.18
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 32 - "Agent Guidelines & Rules"
Cohesion: 0.18
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 33 - "Tailwind CSS Development"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 35 - "Laravel Best Practices"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 36 - "Laravel Best Practices"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 37 - "Laravel Best Practices"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 38 - "Laravel Best Practices"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 39 - "Agent Guidelines & Rules"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 40 - "Agent Guidelines & Rules"
Cohesion: 0.20
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 41 - "Agent Guidelines & Rules"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 42 - "Agent Guidelines & Rules"
Cohesion: 0.20
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 43 - "Agent Guidelines & Rules"
Cohesion: 0.20
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 44 - "Agent Guidelines & Rules"
Cohesion: 0.20
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 46 - "Laravel Best Practices"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 47 - "Laravel Best Practices"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 48 - "Laravel Best Practices"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 49 - "Agent Guidelines & Rules"
Cohesion: 0.22
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 50 - "Agent Guidelines & Rules"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 52 - "Amadus Project Overview"
Cohesion: 0.22
Nodes (8): Altéa Cryptic Terminal Emulator, Amadus Training Simulator (1A), Automated Installers (Web & CLI), Cadet Student Cockpit, Flight Instructor Operations Hub, Global Aviation Datasets, Realistic PNR Lifecycle Engine, Role-Based Training Ecosystem

### Community 54 - "Laravel Best Practices"
Cohesion: 0.25
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 55 - "Laravel Best Practices"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 56 - "Laravel Best Practices"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 57 - "Laravel Best Practices"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 58 - "Testing Best Practices"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 60 - "Laravel Best Practices"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 61 - "Laravel Best Practices"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 62 - "Laravel Best Practices"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 63 - "Laravel Best Practices"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 64 - "Laravel Best Practices"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 65 - "Laravel Best Practices"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 67 - "Testing Best Practices"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 68 - "Testing Best Practices"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 69 - "Testing Best Practices"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 70 - "Testing Best Practices"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 71 - "Testing Best Practices"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 73 - "Laravel Best Practices"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 74 - "Testing Best Practices"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 75 - "Testing Best Practices"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 76 - "Testing Best Practices"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

## Knowledge Gaps
- **407 isolated node(s):** `php`, `concurrently`, `tailwindcss`, `@laravel/multiplex`, `layouts.app` (+402 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 776 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **42 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Flight Instructor Operations` to `CLI Application Installer`, `Installer Core Service`, `Cadet Dashboard Controller`, `User & Instructor Model`, `Web Installer Controller`, `Feature Dashboardsubpagesandterminalheadertest`, `User & Instructor Model`, `Training Drill Scenarios`, `Simulated Queue Bank`, `Installer Feature Tests`, `Cadet Dashboard Controller`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **What connects `php`, `concurrently`, `tailwindcss` to the rest of the system?**
  _407 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Audit Coverage Ledger` be split into smaller, more focused modules?**
  _Cohesion score 0.06219918548685672 - nodes in this community are weakly interconnected._
- **Why does `InstallerService` connect `CLI Application Installer` to `Installer Core Service`, `Cadet Dashboard Controller`, `Web Installer Controller`, `User & Instructor Model`, `Installer Feature Tests`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Should `Security Findings Validator` be split into smaller, more focused modules?**
  _Cohesion score 0.05541346973572037 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `User & Instructor Model` to `CLI Application Installer`, `User & Instructor Model`, `Cadet Dashboard Controller`, `Security Hardening Tests`, `Feature Dashboardsubpagesandterminalheadertest`, `Training Drill Scenarios`, `Flight Instructor Operations`, `Installer Feature Tests`, `Dual-Role Dashboard Tests`, `Feature Responsivenavigationandsidebartest Responsivenavigationandsidebartest`, `Feature Instructorstudentmanagementtest Instructorstudentmanagementtest`, `User & Instructor Model`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **Should `CLI Application Installer` be split into smaller, more focused modules?**
  _Cohesion score 0.05641025641025641 - nodes in this community are weakly interconnected._