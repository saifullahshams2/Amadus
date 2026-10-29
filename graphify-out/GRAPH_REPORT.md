# Graph Report - gds_amadeus  (2026-10-29)

## Corpus Check
- 159 files · ~264,806 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 18, .example 1, .xml 1)

## Summary
- 1305 nodes · 1874 edges · 114 communities (61 shown, 53 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 79 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Security Audit Coverage Ledger
- Security Audit Findings Validation
- PHP Package Dependencies & Autoloading
- Agents Skills Security Module
- Cryptic Terminal Controller & Engine
- Database Migrations & Schema
- Laravel Cloud Deployment Guides
- GDS TypeScript Definitions & Types
- Agent Guidelines & Directives
- App Installer & Setup Wizard
- Claude Assistant Guidelines
- Dual-Role Dashboard & Analytics
- Authentication & User Management
- Cryptic Terminal Controller & Engine
- Frontend Tooling & Bundling
- Dual-Role Dashboard & Analytics
- App Installer & Setup Wizard
- Application HTTP Middleware
- App Installer & Setup Wizard
- Automated Test Suites
- App Installer & Setup Wizard
- Dual-Role Dashboard & Analytics
- Automated Test Suites
- Application HTTP Middleware
- Automated Test Suites
- Agents Skills Infer Module
- Curriculum & Drill Scenarios
- Automated Test Suites
- Agents Skills Laravel Module
- Agents Skills Laravel Module
- Agents Skills Security Module
- Agents Skills Security Module
- Frontend Tooling & Bundling
- Automated Test Suites
- Automated Test Suites
- Agents Skills Laravel Module
- Agents Skills Laravel Module
- Agents Skills Laravel Module
- Agents Skills Laravel Module
- Agents Skills Security Module
- Agents Skills Security Module
- Agents Skills Security Module
- Agents Skills Security Module
- Agents Skills Security Module
- Agents Skills Security Module
- Submission Php Module
- Automated Test Suites
- Agents Skills Laravel Module
- Blade UI Views & Templates
- Agents Skills Laravel Module
- Agents Skills Security Module
- Agents Skills Security Module
- Queueitem Php Module
- Automated Test Suites
- Application HTTP Middleware
- Userfactory Php Module
- Automated Test Suites
- Agents Skills Laravel Module
- Blade UI Views & Templates
- Agents Skills Laravel Module
- Agents Skills Laravel Module
- Automated Test Suites
- IATA Decoding & Lookup Services
- Electronic Ticketing Service
- Agents Skills Laravel Module
- Automated Test Suites
- Automated Test Suites
- Agents Skills Laravel Module
- Agents Skills Laravel Module
- Agents Skills Laravel Module
- Agents Skills Security Module
- Automated Test Suites
- Automated Test Suites
- Automated Test Suites
- Automated Test Suites
- Automated Test Suites
- Automated Test Suites
- Agents Skills Laravel Module
- Automated Test Suites
- App Installer & Setup Wizard
- App Installer & Setup Wizard
- Fare Pricing & TST Calculation
- Databaseseeder Php Module
- Automated Test Suites
- Automated Test Suites
- Automated Test Suites
- Mcp Json Module
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates
- Blade UI Views & Templates

## God Nodes (most connected - your core abstractions)
1. `User` - 32 edges
2. `InstallerService` - 31 edges
3. `TestCase` - 27 edges
4. `SecurityHardeningTest` - 23 edges
5. `DashboardController` - 22 edges
6. `StudentInstructorAccountSeparationTest` - 19 edges
7. `UserRequirementsVerificationTest` - 18 edges
8. `collectUnitErrors()` - 16 edges
9. `DualRoleDashboardTest` - 16 edges
10. `InstallerTest` - 16 edges

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

## Communities (114 total, 53 thin omitted)

### Community 0 - "Security Audit Coverage Ledger"
Cohesion: 0.06
Nodes (66): ATTEMPT_FIELDS, ATTEMPT_STATUSES, canonicalCoverageId(), collectUnitErrors(), createErrorList(), encodeCanonicalRef(), escapeUnsafeDiagnosticCharacters(), fs (+58 more)

### Community 1 - "Security Audit Findings Validation"
Cohesion: 0.06
Nodes (56): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+48 more)

### Community 2 - "PHP Package Dependencies & Autoloading"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 3 - "Agents Skills Security Module"
Cohesion: 0.04
Nodes (42): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+34 more)

### Community 4 - "Cryptic Terminal Controller & Engine"
Cohesion: 0.07
Nodes (13): TerminalController, AmadeusParser, CommandExecutionResult, AvailabilityResult, AvailabilityService, DecodeResult, DecodeService, PnrService (+5 more)

### Community 5 - "Database Migrations & Schema"
Cohesion: 0.09
Nodes (19): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+11 more)

### Community 6 - "Laravel Cloud Deployment Guides"
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 7 - "GDS TypeScript Definitions & Types"
Cohesion: 0.07
Nodes (29): AutoGradeResult, ClassroomStudentStatus, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck (+21 more)

### Community 8 - "Agent Guidelines & Directives"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 10 - "Claude Assistant Guidelines"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 11 - "Dual-Role Dashboard & Analytics"
Cohesion: 0.11
Nodes (7): {closure#1}(), {closure#3}(), LessonProgress, Pnr, Submission, TerminalActivityLog, ScenarioService

### Community 12 - "Authentication & User Management"
Cohesion: 0.09
Nodes (3): AuthController, Controller, PasswordResetController

### Community 13 - "Cryptic Terminal Controller & Engine"
Cohesion: 0.10
Nodes (21): Amadeus Altéa Cryptic Command Engine, Automated Drill Grading & Submission, Complete REST API Reference, Core Domain Models & Schemas, Cryptic Terminal Simulator, Amadeus GDS Platform Features Specification, Instructor Operations Hub & Classroom Management, Live Classroom Student Grid (+13 more)

### Community 14 - "Frontend Tooling & Bundling"
Cohesion: 0.10
Nodes (20): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, optionalDependencies, @laravel/multiplex (+12 more)

### Community 15 - "Dual-Role Dashboard & Analytics"
Cohesion: 0.16
Nodes (3): {closure#2}(), {closure#1}(), User

### Community 23 - "Application HTTP Middleware"
Cohesion: 0.26
Nodes (3): EnsureAppIsInstalled, EnsureUserIsInstructor, SecurityHeadersMiddleware

### Community 24 - "Automated Test Suites"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 25 - "Agents Skills Infer Module"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 28 - "Agents Skills Laravel Module"
Cohesion: 0.18
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 29 - "Agents Skills Laravel Module"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 30 - "Agents Skills Security Module"
Cohesion: 0.18
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 31 - "Agents Skills Security Module"
Cohesion: 0.18
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 32 - "Frontend Tooling & Bundling"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 35 - "Agents Skills Laravel Module"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 36 - "Agents Skills Laravel Module"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 37 - "Agents Skills Laravel Module"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 38 - "Agents Skills Laravel Module"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 39 - "Agents Skills Security Module"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 40 - "Agents Skills Security Module"
Cohesion: 0.20
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 41 - "Agents Skills Security Module"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 42 - "Agents Skills Security Module"
Cohesion: 0.20
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 43 - "Agents Skills Security Module"
Cohesion: 0.20
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 44 - "Agents Skills Security Module"
Cohesion: 0.20
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 47 - "Agents Skills Laravel Module"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 48 - "Blade UI Views & Templates"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 49 - "Agents Skills Laravel Module"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 50 - "Agents Skills Security Module"
Cohesion: 0.22
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 51 - "Agents Skills Security Module"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 52 - "Queueitem Php Module"
Cohesion: 0.31
Nodes (3): {closure#1}(), {closure#2}(), QueueItem

### Community 54 - "Application HTTP Middleware"
Cohesion: 0.28
Nodes (3): {closure#1}(), {closure#3}(), {closure#4}()

### Community 57 - "Agents Skills Laravel Module"
Cohesion: 0.25
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 58 - "Blade UI Views & Templates"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 59 - "Agents Skills Laravel Module"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 60 - "Agents Skills Laravel Module"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 61 - "Automated Test Suites"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 62 - "IATA Decoding & Lookup Services"
Cohesion: 0.43
Nodes (3): Aircraft, Airline, Airport

### Community 64 - "Agents Skills Laravel Module"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 65 - "Automated Test Suites"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 66 - "Automated Test Suites"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 67 - "Agents Skills Laravel Module"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 68 - "Agents Skills Laravel Module"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 69 - "Agents Skills Laravel Module"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 71 - "Automated Test Suites"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 72 - "Automated Test Suites"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 73 - "Automated Test Suites"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 74 - "Automated Test Suites"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 75 - "Automated Test Suites"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 79 - "Agents Skills Laravel Module"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 80 - "Automated Test Suites"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 85 - "Automated Test Suites"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 86 - "Automated Test Suites"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

## Knowledge Gaps
- **406 isolated node(s):** `php`, `concurrently`, `tailwindcss`, `@laravel/multiplex`, `layouts.app` (+401 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 759 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **53 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `InstallerService` connect `App Installer & Setup Wizard` to `App Installer & Setup Wizard`, `Application HTTP Middleware`, `App Installer & Setup Wizard`, `App Installer & Setup Wizard`, `App Installer & Setup Wizard`, `App Installer & Setup Wizard`, `Application HTTP Middleware`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **What connects `php`, `concurrently`, `tailwindcss` to the rest of the system?**
  _406 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Security Audit Coverage Ledger` be split into smaller, more focused modules?**
  _Cohesion score 0.06054054054054054 - nodes in this community are weakly interconnected._
- **Why does `User` connect `Dual-Role Dashboard & Analytics` to `Cryptic Terminal Controller & Engine`, `Dual-Role Dashboard & Analytics`, `Application HTTP Middleware`, `Automated Test Suites`, `Automated Test Suites`, `App Installer & Setup Wizard`, `App Installer & Setup Wizard`, `Dual-Role Dashboard & Analytics`, `Application HTTP Middleware`, `Userfactory Php Module`, `Automated Test Suites`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **Should `Security Audit Findings Validation` be split into smaller, more focused modules?**
  _Cohesion score 0.05662862159789289 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `Automated Test Suites` to `Automated Test Suites`, `Automated Test Suites`, `Dual-Role Dashboard & Analytics`, `Application HTTP Middleware`, `Automated Test Suites`, `Dual-Role Dashboard & Analytics`, `App Installer & Setup Wizard`, `Application HTTP Middleware`, `App Installer & Setup Wizard`, `Automated Test Suites`, `Automated Test Suites`, `Automated Test Suites`, `Application HTTP Middleware`, `Automated Test Suites`, `Automated Test Suites`, `Electronic Ticketing Service`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **Should `PHP Package Dependencies & Autoloading` be split into smaller, more focused modules?**
  _Cohesion score 0.04081632653061224 - nodes in this community are weakly interconnected._