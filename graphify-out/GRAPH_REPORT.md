# Graph Report - gds-amadeus  (2026-10-27)

## Corpus Check
- 147 files · ~243,394 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 18, .example 1, .xml 1)

## Summary
- 1136 nodes · 1697 edges · 97 communities (63 shown, 34 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 110 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d6b9cb47`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- bootstrap/app.php
- Submission
- PHP Package Manifest
- Database Schema Migrations
- User Authentication & State Config
- .execute
- InstallerService
- Feature Integration Tests
- Project Architecture & Features
- Application Bootstrap & Entry
- Dev Dependencies & Tooling
- Composer Configuration
- Service Providers
- Detection Checklist
- Unit Test Suite
- Agent & Developer Guidelines
- TestCase
- Instructor Dashboard View
- Application Core Config
- Mail Configuration
- Queue Configuration
- Third-Party Services Config
- Frontend Client Script
- Blade App Layout
- Landing Welcome View
- instructor/index.blade.php
- terminal/index.blade.php
- Cloud CLI
- Illuminate\Http\Request
- confirmed
- ATTACK-CLASSES.md
- Illuminate\Database\Eloquent\Model
- Security Audit
- AmadeusParser
- Architecture Best Practices
- Security Best Practices
- HTTP-Protocol and Authentication Hunting
- Tailwind CSS Development
- Advanced Query Best Practices
- Events and Notifications Best Practices
- Migration Best Practices
- Queue and Job Best Practices
- AI, LLM, and Agent Hunting
- Client-Side and Browser Hunting
- Cloud and Deployment Hunting
- Data Isolation and Lifecycle Hunting
- Desktop, Mobile, and Local IPC Hunting
- Memory Safety, Binary, and Kernel Hunting
- Protocols, RPC, and Messaging Hunting
- Resource Exhaustion and Availability Hunting
- DashboardController.php
- LandingAndApprovalTest
- Caching Best Practices
- Database Performance Best Practices
- Vulnerability Hunting
- Supply Chain and Release Hunting
- QueueItem
- UserFactory.php
- laravel-best-practices/SKILL.md
- Blade and View Best Practices
- Error Handling Best Practices
- Task Scheduling Best Practices
- Endpoint Tests
- Scenario
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- Routing and Controller Best Practices
- Convention and Style Best Practices
- Validation and Forms Best Practices
- Assertions
- testing-best-practices/SKILL.md
- Fakes, Mocks, and Determinism
- Test Suite Performance
- Reviewing Tests
- Configuration Best Practices
- Naming and Structure
- PricingService
- TicketingService
- Factories and Test Data
- Testing Best Practices
- Phase 6: Produce target-neutral reports from final records
- pending-approval.blade.php

## God Nodes (most connected - your core abstractions)
1. `InstallerService` - 29 edges
2. `confirmed()` - 27 edges
3. `User` - 26 edges
4. `TestCase` - 22 edges
5. `DashboardController` - 21 edges
6. `StudentInstructorAccountSeparationTest` - 19 edges
7. `UserRequirementsVerificationTest` - 18 edges
8. `collectUnitErrors()` - 16 edges
9. `DualRoleDashboardTest` - 16 edges
10. `InstallerTest` - 15 edges

## Surprising Connections (you probably didn't know these)
- `Test Class and Methods` --references--> `TestCase`  [INFERRED]
  .agents/skills/testing-best-practices/rules/naming.md → tests/TestCase.php
- `Global Fakes` --references--> `TestCase`  [INFERRED]
  .agents/skills/testing-best-practices/rules/performance.md → tests/TestCase.php
- `Names and Structure` --references--> `TestCase`  [INFERRED]
  .agents/skills/testing-best-practices/rules/review.md → tests/TestCase.php
- `Validation rules (apply before reporting ANY finding here)` --references--> `confirmed()`  [INFERRED]
  .agents/skills/security-audit/AI-AND-LLM.md → .agents/skills/security-audit/validate-findings.test.cjs
- `Attack classes — choose and split based on Phase 1` --references--> `confirmed()`  [INFERRED]
  .agents/skills/security-audit/ATTACK-CLASSES.md → .agents/skills/security-audit/validate-findings.test.cjs

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Amadeus Simulator Core Architecture** — readme_amadeus_gds_training_simulator, readme_work_area_terminal, readme_interactive_curriculum, readme_instructor_hub [EXTRACTED 1.00]

## Communities (97 total, 34 thin omitted)

### Community 0 - "bootstrap/app.php"
Cohesion: 0.16
Nodes (6): EnsureAppIsInstalled, EnsureUserIsInstructor, SecurityHeadersMiddleware, {closure#1}(), {closure#3}(), {closure#4}()

### Community 1 - "Submission"
Cohesion: 0.14
Nodes (3): {closure#1}(), Submission, {closure#1}()

### Community 2 - "PHP Package Manifest"
Cohesion: 0.08
Nodes (39): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+31 more)

### Community 3 - "Database Schema Migrations"
Cohesion: 0.08
Nodes (56): ATTEMPT_FIELDS, ATTEMPT_STATUSES, canonicalCoverageId(), collectUnitErrors(), createErrorList(), encodeCanonicalRef(), escapeUnsafeDiagnosticCharacters(), hasOwn() (+48 more)

### Community 4 - "User Authentication & State Config"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 5 - ".execute"
Cohesion: 0.20
Nodes (4): DecodeResult, DecodeService, PnrService, PnrValidationResult

### Community 6 - "InstallerService"
Cohesion: 0.07
Nodes (6): InstallAppCommand, RemoveInstallerCommand, {closure#1}(), InstallerService, InstallerTest, UserRequirementsVerificationTest

### Community 7 - "Feature Integration Tests"
Cohesion: 0.09
Nodes (19): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+11 more)

### Community 8 - "Project Architecture & Features"
Cohesion: 0.07
Nodes (29): AutoGradeResult, ClassroomStudentStatus, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck (+21 more)

### Community 9 - "Application Bootstrap & Entry"
Cohesion: 0.13
Nodes (20): devDependencies, concurrently@^10.0.3, laravel-vite-plugin@^3.1, tailwindcss@^4.0.0, @tailwindcss/vite@^4.0.0, vite@^8.0.0, optionalDependencies, @laravel/multiplex@^0.4.1 (+12 more)

### Community 14 - "Detection Checklist"
Cohesion: 0.06
Nodes (30): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+22 more)

### Community 17 - "Agent & Developer Guidelines"
Cohesion: 0.22
Nodes (9): Amadeus GDS Training Simulator, CRT & Modern Terminal Themes, Cryptic Command Cheat Sheet, Database Flexibility (SQLite, MySQL, MariaDB), Instructor Operations Hub & PNR Audit, Interactive Training Curriculum & Hints, Amadeus Selling Platform Connect, Multi-Work Area Terminal (A-F) (+1 more)

### Community 18 - "TestCase"
Cohesion: 0.18
Nodes (3): AmadeusGdsIntegrationTest, ExampleTest, TestCase

### Community 41 - "Cloud CLI"
Cohesion: 0.06
Nodes (30): Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup, Full environment setup (app + database + cache + domain), New app from scratch, Application Setup, Billing and Usage (+22 more)

### Community 42 - "Illuminate\Http\Request"
Cohesion: 0.06
Nodes (11): AuthController, Controller, {closure#1}(), DashboardController, InstallerController, InstructorController, LandingController, TrainingController (+3 more)

### Community 43 - "confirmed"
Cohesion: 0.24
Nodes (16): Structured hunter result, Cost budget, Coverage and prior runs, Full audit planning, Run profiles and scope, confirmed(), evidence(), needsValidation() (+8 more)

### Community 45 - "ATTACK-CLASSES.md"
Cohesion: 0.14
Nodes (7): Attack Classes, Attack classes — choose and split based on Phase 1, Architecture summary and companion selection, Deterministic coverage ledger, Phase 1: Map the source and plan coverage, Prior-run input, Reconnaissance

### Community 46 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.13
Nodes (7): Aircraft, Airline, Airport, LessonProgress, AvailabilityResult, AvailabilityService, DatabaseSeeder

### Community 47 - "Security Audit"
Cohesion: 0.14
Nodes (14): Anti-patterns, Core principles, Full audit setup, Full audit workflow, Operating modes, Platform terminology, Recommend the smallest effective source fix, Require a boundary and result (+6 more)

### Community 48 - "AmadeusParser"
Cohesion: 0.28
Nodes (3): TerminalController, AmadeusParser, CommandExecutionResult

### Community 49 - "Architecture Best Practices"
Cohesion: 0.18
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 50 - "Security Best Practices"
Cohesion: 0.18
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 51 - "HTTP-Protocol and Authentication Hunting"
Cohesion: 0.20
Nodes (10): API-key and mTLS attack classes (subagent_type: `general`), Browser-session attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Federated-identity attack classes (subagent_type: `general`), HTTP framing and cache attack classes (subagent_type: `general`), HTTP-Protocol and Authentication Hunting, MFA, passkey, and account-transition attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 52 - "Tailwind CSS Development"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 53 - "Advanced Query Best Practices"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 54 - "Events and Notifications Best Practices"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 55 - "Migration Best Practices"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 56 - "Queue and Job Best Practices"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 57 - "AI, LLM, and Agent Hunting"
Cohesion: 0.20
Nodes (9): AI, LLM, and Agent Hunting, Context, retrieval, and memory attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), MCP and sub-agent trust classes (subagent_type: `general`), Output and disclosure attack classes (subagent_type: `general`), Tool and action attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 58 - "Client-Side and Browser Hunting"
Cohesion: 0.18
Nodes (10): Client-Side and Browser Hunting, Core discipline (include in every agent prompt for this domain), Cross-origin messaging and network attack classes (subagent_type: `general`), Cross-site information leak classes (subagent_type: `general`), DOM and object-state attack classes (subagent_type: `general`), Service-worker and browser-storage attack classes (subagent_type: `general`), UI-redress and navigation attack classes (subagent_type: `general`), Universal moves (apply across the above) (+2 more)

### Community 59 - "Cloud and Deployment Hunting"
Cohesion: 0.20
Nodes (10): Cloud and Deployment Hunting, Configuration and secret lifecycle attack classes (subagent_type: `general`), Container and orchestration attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Ingress, network, and control-plane attack classes (subagent_type: `general`), Managed storage, events, and edge attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 60 - "Data Isolation and Lifecycle Hunting"
Cohesion: 0.20
Nodes (9): Core discipline (include in every agent prompt for this domain), Data Isolation and Lifecycle Hunting, Deletion, revocation, and lifecycle attack classes (subagent_type: `general`), Derived-data and disclosure attack classes (subagent_type: `general`), Export, backup, restore, and migration attack classes (subagent_type: `general`), Tenant and object-isolation attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 61 - "Desktop, Mobile, and Local IPC Hunting"
Cohesion: 0.20
Nodes (10): Application-state and device-lifecycle attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Deep-link, callback, and navigation attack classes (subagent_type: `general`), Desktop, Mobile, and Local IPC Hunting, Local IPC and exported-component attack classes (subagent_type: `general`), Privileged-helper and local-file attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+2 more)

### Community 62 - "Memory Safety, Binary, and Kernel Hunting"
Cohesion: 0.18
Nodes (10): Binary loading and runtime attack classes (subagent_type: `general`), Bounds, integer, and representation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), FFI and ABI attack classes (subagent_type: `general`), Kernel and privileged-interface attack classes (subagent_type: `general`), Lifetime, type, and concurrency attack classes (subagent_type: `general`), Memory Safety, Binary, and Kernel Hunting, Universal moves (apply across the above) (+2 more)

### Community 63 - "Protocols, RPC, and Messaging Hunting"
Cohesion: 0.20
Nodes (9): Broker and queue isolation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Framing, schema, and interpretation attack classes (subagent_type: `general`), Protocols, RPC, and Messaging Hunting, Replay, ordering, and transaction attack classes (subagent_type: `general`), RPC identity and authorization attack classes (subagent_type: `general`), Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 64 - "Resource Exhaustion and Availability Hunting"
Cohesion: 0.22
Nodes (9): Computational amplification attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Failure and recovery attack classes (subagent_type: `general`), Quota and scheduling attack classes (subagent_type: `general`), Resource accumulation attack classes (subagent_type: `general`), Resource Exhaustion and Availability Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here) (+1 more)

### Community 65 - "DashboardController.php"
Cohesion: 0.20
Nodes (3): {closure#3}(), Pnr, TerminalActivityLog

### Community 67 - "Caching Best Practices"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 68 - "Database Performance Best Practices"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 69 - "Vulnerability Hunting"
Cohesion: 0.22
Nodes (9): Core hunting method — include in every hunter prompt, Core validation rules — include in every hunter prompt, Coverage-critic waves, Local validation boundaries, Parent consolidation and ledger update, Phase 2: Run coverage-led hunting waves, Promotion procedure — copy this promotion procedure verbatim into every hunter prompt, Required hunter prompt (+1 more)

### Community 70 - "Supply Chain and Release Hunting"
Cohesion: 0.22
Nodes (8): CI and automation attack classes (subagent_type: `general`), Core discipline (include in every agent prompt for this domain), Dependency and build-input attack classes (subagent_type: `general`), Release and update attack classes (subagent_type: `general`), Supply Chain and Release Hunting, Universal moves (apply across the above), Validation rules (apply before reporting ANY finding here), When to use this file

### Community 71 - "QueueItem"
Cohesion: 0.31
Nodes (3): {closure#1}(), {closure#2}(), QueueItem

### Community 74 - "laravel-best-practices/SKILL.md"
Cohesion: 0.25
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 75 - "Blade and View Best Practices"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 76 - "Error Handling Best Practices"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 77 - "Task Scheduling Best Practices"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 78 - "Endpoint Tests"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 82 - "Collection Best Practices"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 83 - "HTTP Client Best Practices"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 84 - "Mail Best Practices"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 85 - "Routing and Controller Best Practices"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 86 - "Convention and Style Best Practices"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 87 - "Validation and Forms Best Practices"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 88 - "Assertions"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 89 - "testing-best-practices/SKILL.md"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 90 - "Fakes, Mocks, and Determinism"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 91 - "Test Suite Performance"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 92 - "Reviewing Tests"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 95 - "Configuration Best Practices"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 96 - "Naming and Structure"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 99 - "Factories and Test Data"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 100 - "Testing Best Practices"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

### Community 101 - "Phase 6: Produce target-neutral reports from final records"
Cohesion: 0.50
Nodes (4): `FINDINGS-DETAIL.md`, `NEEDS-VALIDATION.md`, Phase 6: Produce target-neutral reports from final records, `REPORT.md`

## Knowledge Gaps
- **18 isolated node(s):** `php`, `layouts.app`, `layouts.app`, `layouts.app`, `layouts.dashboard` (+13 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 594 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **34 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Dev Dependencies & Tooling` to `DashboardController.php`, `InstallerService`, `UserFactory.php`, `Illuminate\Http\Request`, `Detection Checklist`, `AmadeusParser`, `Illuminate\Foundation\Testing\RefreshDatabase`, `TestCase`, `Authentication Registration View`?**
  _High betweenness centrality (0.159) - this node is a cross-community bridge._
- **Are the 23 inferred relationships involving `confirmed()` (e.g. with `Validation rules (apply before reporting ANY finding here)` and `Attack classes — choose and split based on Phase 1`) actually correct?**
  _`confirmed()` has 23 INFERRED edges - model-reasoned connections that need verification._
- **What connects `php`, `layouts.app`, `layouts.app` to the rest of the system?**
  _18 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Submission` be split into smaller, more focused modules?**
  _Cohesion score 0.13970588235294118 - nodes in this community are weakly interconnected._
- **Should `PHP Package Manifest` be split into smaller, more focused modules?**
  _Cohesion score 0.08078431372549019 - nodes in this community are weakly interconnected._
- **Should `Database Schema Migrations` be split into smaller, more focused modules?**
  _Cohesion score 0.07596153846153846 - nodes in this community are weakly interconnected._
- **Should `User Authentication & State Config` be split into smaller, more focused modules?**
  _Cohesion score 0.04081632653061224 - nodes in this community are weakly interconnected._