# Graph Report - gds_amadeus  (2026-10-27)

## Corpus Check
- 154 files · ~245,139 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 23 file(s) not represented in the graph (top: (none) 19, .example 1, .xml 1)

## Summary
- 1222 nodes · 1757 edges · 107 communities (62 shown, 45 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 71 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Coverage Ledger Validation
- Application Installer Commands
- Security Findings Validation
- Cryptic Terminal Controller
- PHP Package Dependencies
- Security Audit Threat Hunting
- User & Session Database Migrations
- Cloud Deployment Checklists
- GDS TypeScript Definitions
- Agent System Conventions
- Claude Coding Standards
- Node & Vite Configuration
- Application Access Middleware
- Auth & Password Reset
- Role Separation Feature Tests
- Dual-Role Dashboard Operations
- Dual-Role Dashboard Operations
- Auth & Password Reset
- Application Installer Commands
- Test Suite & Verification (19)
- Test Suite & Verification (20)
- Agent System Conventions
- Agent System Conventions
- Aviation Curriculum & Scenarios
- Aircraft Php
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Submission Php
- Test Suite & Verification (29)
- Test Suite & Verification (30)
- Test Suite & Verification (31)
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Security Audit Threat Hunting
- Security Audit Threat Hunting
- Security Audit Threat Hunting
- Security Audit Threat Hunting
- Security Audit Threat Hunting
- Security Audit Threat Hunting
- Security Audit Threat Hunting
- Security Audit Threat Hunting
- Security Audit Threat Hunting
- Cryptic Terminal Controller
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Security Audit Threat Hunting
- Dual-Role Dashboard Operations
- Queueitem Php
- Userfactory Php
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Terminalactivitylog Php
- Test Suite & Verification (60)
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Agent System Conventions
- Test Suite & Verification (73)
- Test Suite & Verification (75)
- Agent System Conventions
- Agent System Conventions
- Appserviceprovider Php
- Agent System Conventions
- Agent System Conventions
- Test Suite & Verification (82)
- Laravel Boost
- Forgot Password Blade
- Login Blade Php
- Pending Approval Blade
- Register Blade Php
- Reset Password Blade
- Instructor Blade Php
- Student Blade Php
- Index Blade Php
- Index Blade Php

## God Nodes (most connected - your core abstractions)
1. `User` - 29 edges
2. `InstallerService` - 29 edges
3. `DashboardController` - 21 edges
4. `TestCase` - 21 edges
5. `StudentInstructorAccountSeparationTest` - 19 edges
6. `UserRequirementsVerificationTest` - 18 edges
7. `collectUnitErrors()` - 16 edges
8. `DualRoleDashboardTest` - 16 edges
9. `InstallerTest` - 16 edges
10. `validate()` - 13 edges

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

## Communities (107 total, 45 thin omitted)

### Community 0 - "Coverage Ledger Validation"
Cohesion: 0.08
Nodes (56): ATTEMPT_FIELDS, ATTEMPT_STATUSES, canonicalCoverageId(), collectUnitErrors(), createErrorList(), encodeCanonicalRef(), escapeUnsafeDiagnosticCharacters(), hasOwn() (+48 more)

### Community 1 - "Application Installer Commands"
Cohesion: 0.05
Nodes (6): InstallAppCommand, RemoveInstallerCommand, {closure#1}(), InstallerService, InstallerTest, UserRequirementsVerificationTest

### Community 2 - "Security Findings Validation"
Cohesion: 0.07
Nodes (45): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+37 more)

### Community 3 - "Cryptic Terminal Controller"
Cohesion: 0.07
Nodes (14): TerminalController, AmadeusParser, CommandExecutionResult, AvailabilityResult, AvailabilityService, DecodeResult, DecodeService, PnrService (+6 more)

### Community 4 - "PHP Package Dependencies"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 5 - "Security Audit Threat Hunting"
Cohesion: 0.04
Nodes (42): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+34 more)

### Community 6 - "User & Session Database Migrations"
Cohesion: 0.09
Nodes (19): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+11 more)

### Community 7 - "Cloud Deployment Checklists"
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 8 - "GDS TypeScript Definitions"
Cohesion: 0.07
Nodes (29): AutoGradeResult, ClassroomStudentStatus, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck (+21 more)

### Community 9 - "Agent System Conventions"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 10 - "Claude Coding Standards"
Cohesion: 0.07
Nodes (26): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+18 more)

### Community 11 - "Node & Vite Configuration"
Cohesion: 0.13
Nodes (20): devDependencies, concurrently@^10.0.3, laravel-vite-plugin@^3.1, tailwindcss@^4.0.0, @tailwindcss/vite@^4.0.0, vite@^8.0.0, optionalDependencies, @laravel/multiplex@^0.4.1 (+12 more)

### Community 12 - "Application Access Middleware"
Cohesion: 0.15
Nodes (6): EnsureAppIsInstalled, EnsureUserIsInstructor, SecurityHeadersMiddleware, {closure#1}(), {closure#3}(), {closure#4}()

### Community 15 - "Dual-Role Dashboard Operations"
Cohesion: 0.23
Nodes (3): {closure#1}(), {closure#2}(), DashboardController

### Community 16 - "Dual-Role Dashboard Operations"
Cohesion: 0.17
Nodes (3): Scenario, {closure#1}(), ScenarioService

### Community 18 - "Application Installer Commands"
Cohesion: 0.20
Nodes (3): Controller, InstallerController, LandingController

### Community 20 - "Test Suite & Verification (20)"
Cohesion: 0.15
Nodes (12): 1. Initialize Database & Seed Datasets, 2. Start the Local Server, 3. Run the Automated Test Suite, 📜 Amadeus Cryptic Command Cheat Sheet, ✈️ Amadeus GDS Training Simulator (1A), 🗄️ Database Configuration (SQLite, MySQL, or MariaDB), 🔑 Demo Accounts, Option 1: SQLite (Default - Recommended for Local Dev & Testing) (+4 more)

### Community 21 - "Agent System Conventions"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 22 - "Agent System Conventions"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 24 - "Aircraft Php"
Cohesion: 0.26
Nodes (4): Aircraft, Airline, Airport, LessonProgress

### Community 25 - "Agent System Conventions"
Cohesion: 0.18
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 26 - "Agent System Conventions"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 27 - "Agent System Conventions"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 32 - "Agent System Conventions"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 33 - "Agent System Conventions"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 34 - "Agent System Conventions"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 35 - "Agent System Conventions"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 36 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 37 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 38 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 39 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 40 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 41 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 42 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 43 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 44 - "Security Audit Threat Hunting"
Cohesion: 0.20
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 45 - "Cryptic Terminal Controller"
Cohesion: 0.22
Nodes (3): {closure#3}(), Pnr, TerminalActivityLog

### Community 46 - "Agent System Conventions"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 47 - "Agent System Conventions"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 48 - "Agent System Conventions"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 49 - "Security Audit Threat Hunting"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 51 - "Queueitem Php"
Cohesion: 0.31
Nodes (3): {closure#1}(), {closure#2}(), QueueItem

### Community 53 - "Agent System Conventions"
Cohesion: 0.25
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 54 - "Agent System Conventions"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 55 - "Agent System Conventions"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 56 - "Agent System Conventions"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 58 - "Agent System Conventions"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 62 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 63 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 64 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 65 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 66 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 67 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 68 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 69 - "Agent System Conventions"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 70 - "Agent System Conventions"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 71 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 72 - "Agent System Conventions"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 76 - "Agent System Conventions"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 77 - "Agent System Conventions"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 79 - "Agent System Conventions"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 80 - "Agent System Conventions"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

## Knowledge Gaps
- **397 isolated node(s):** `php`, `layouts.app`, `layouts.app`, `layouts.app`, `layouts.app` (+392 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 683 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **45 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `Test Suite & Verification (75)` to `Application Installer Commands`, `Test Suite & Verification (73)`, `Test Suite & Verification (74)`, `Auth & Password Reset`, `Cryptic Terminal Controller`, `Role Separation Feature Tests`, `Dual-Role Dashboard Operations`, `Test Suite & Verification (19)`, `Test Suite & Verification (60)`, `Test Suite & Verification (29)`, `Test Suite & Verification (30)`, `Test Suite & Verification (31)`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **What connects `php`, `layouts.app`, `layouts.app` to the rest of the system?**
  _397 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Coverage Ledger Validation` be split into smaller, more focused modules?**
  _Cohesion score 0.07837301587301587 - nodes in this community are weakly interconnected._
- **Why does `User` connect `Auth & Password Reset` to `Application Installer Commands`, `Cryptic Terminal Controller`, `Test Suite & Verification (73)`, `Test Suite & Verification (74)`, `Test Suite & Verification (75)`, `Cryptic Terminal Controller`, `Dual-Role Dashboard Operations`, `Dual-Role Dashboard Operations`, `Application Installer Commands`, `Userfactory Php`?**
  _High betweenness centrality (0.016) - this node is a cross-community bridge._
- **Should `Application Installer Commands` be split into smaller, more focused modules?**
  _Cohesion score 0.0546448087431694 - nodes in this community are weakly interconnected._
- **Why does `InstallerService` connect `Application Installer Commands` to `Test Suite & Verification (73)`, `Application Installer Commands`, `Application Access Middleware`?**
  _High betweenness centrality (0.013) - this node is a cross-community bridge._
- **Should `Security Findings Validation` be split into smaller, more focused modules?**
  _Cohesion score 0.07199032062915911 - nodes in this community are weakly interconnected._