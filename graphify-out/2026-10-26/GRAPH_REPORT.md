# Graph Report - gds-amadeus  (2026-10-26)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 625 nodes · 1098 edges · 41 communities (13 shown, 28 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 61 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `52af19c4`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- HTTP Controllers & Routing
- Amadeus Engine Services
- PHP Package Manifest
- Database Schema Migrations
- User Authentication & State Config
- Node Dependencies & Vite
- Domain Models & Aviation Data
- Feature Integration Tests
- Project Architecture & Features
- Application Bootstrap & Entry
- Dev Dependencies & Tooling
- Composer Configuration
- Service Providers
- Logging Configuration
- Artisan Console Commands
- Unit Test Suite
- Artisan CLI Runner
- Agent & Developer Guidelines
- Authentication Login View
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

## God Nodes (most connected - your core abstractions)
1. `User` - 24 edges
2. `DashboardController` - 21 edges
3. `StudentInstructorAccountSeparationTest` - 19 edges
4. `InstallerService` - 18 edges
5. `DualRoleDashboardTest` - 16 edges
6. `collectUnitErrors()` - 16 edges
7. `SecurityHardeningTest` - 13 edges
8. `InstallerTest` - 13 edges
9. `validate()` - 13 edges
10. `Submission` - 12 edges

## Surprising Connections (you probably didn't know these)
- `InstallerTest` --references--> `InstallerService`  [EXTRACTED]
  tests/Feature/InstallerTest.php → app/Services/Installer/InstallerService.php
- `Laravel Application Guidelines (AGENTS)` --semantically_similar_to--> `Claude Laravel Guidelines`  [INFERRED] [semantically similar]
  AGENTS.md → CLAUDE.md
- `InstallerController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/InstallerController.php → app/Http/Controllers/Controller.php
- `TerminalController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/TerminalController.php → app/Http/Controllers/Controller.php
- `TrainingController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/TrainingController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Amadeus Simulator Core Architecture** — readme_amadeus_gds_training_simulator, readme_work_area_terminal, readme_interactive_curriculum, readme_instructor_hub [EXTRACTED 1.00]

## Communities (41 total, 28 thin omitted)

### Community 0 - "HTTP Controllers & Routing"
Cohesion: 0.06
Nodes (10): AuthController, Controller, DashboardController, InstructorController, EnsureUserIsInstructor, SecurityHeadersMiddleware, ScenarioService, {closure#1}() (+2 more)

### Community 1 - "Amadeus Engine Services"
Cohesion: 0.05
Nodes (17): {closure#1}(), {closure#3}(), Aircraft, Airline, Airport, LessonProgress, Pnr, {closure#1}() (+9 more)

### Community 2 - "PHP Package Manifest"
Cohesion: 0.07
Nodes (45): canonicalKey(), append(), encode(), codePointLength(), collect(), collectDataLimitErrors(), collectFindingSemanticErrors(), collectSchemaErrors() (+37 more)

### Community 3 - "Database Schema Migrations"
Cohesion: 0.10
Nodes (45): ATTEMPT_FIELDS, ATTEMPT_STATUSES, canonicalCoverageId(), collectUnitErrors(), createErrorList(), encodeCanonicalRef(), escapeUnsafeDiagnosticCharacters(), hasOwn() (+37 more)

### Community 4 - "User Authentication & State Config"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 5 - "Node Dependencies & Vite"
Cohesion: 0.07
Nodes (14): TerminalController, AmadeusParser, CommandExecutionResult, AvailabilityResult, AvailabilityService, DecodeResult, DecodeService, PnrService (+6 more)

### Community 6 - "Domain Models & Aviation Data"
Cohesion: 0.11
Nodes (5): InstallAppCommand, InstallerController, EnsureAppIsInstalled, InstallerService, InstallerTest

### Community 7 - "Feature Integration Tests"
Cohesion: 0.10
Nodes (17): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+9 more)

### Community 8 - "Project Architecture & Features"
Cohesion: 0.07
Nodes (29): AutoGradeResult, ClassroomStudentStatus, CommandDefinition, CommandType, ContactElement, FareElement, FlightSegment, GradingElementCheck (+21 more)

### Community 9 - "Application Bootstrap & Entry"
Cohesion: 0.13
Nodes (20): devDependencies, concurrently@^10.0.3, laravel-vite-plugin@^3.1, tailwindcss@^4.0.0, @tailwindcss/vite@^4.0.0, vite@^8.0.0, optionalDependencies, @laravel/multiplex@^0.4.1 (+12 more)

### Community 10 - "Dev Dependencies & Tooling"
Cohesion: 0.14
Nodes (3): {closure#2}(), User, UserFactory

### Community 14 - "Artisan Console Commands"
Cohesion: 0.40
Nodes (11): JsonStructureError, preflightJsonText(), beginContainer(), beginString(), beginValue(), closeContainer(), countValue(), currentContainer() (+3 more)

### Community 17 - "Agent & Developer Guidelines"
Cohesion: 0.22
Nodes (9): Amadeus GDS Training Simulator, CRT & Modern Terminal Themes, Cryptic Command Cheat Sheet, Database Flexibility (SQLite, MySQL, MariaDB), Instructor Operations Hub & PNR Audit, Interactive Training Curriculum & Hints, Amadeus Selling Platform Connect, Multi-Work Area Terminal (A-F) (+1 more)

## Knowledge Gaps
- **17 isolated node(s):** `php`, `layouts.app`, `layouts.app`, `layouts.dashboard`, `layouts.dashboard` (+12 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 249 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **28 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Dev Dependencies & Tooling` to `HTTP Controllers & Routing`, `Amadeus Engine Services`, `Node Dependencies & Vite`, `Domain Models & Aviation Data`, `Artisan CLI Runner`, `Authentication Registration View`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **What connects `php`, `layouts.app`, `layouts.app` to the rest of the system?**
  _17 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `HTTP Controllers & Routing` be split into smaller, more focused modules?**
  _Cohesion score 0.06095481670929241 - nodes in this community are weakly interconnected._
- **Why does `StudentInstructorAccountSeparationTest` connect `Composer Configuration` to `Artisan CLI Runner`, `Authentication Login View`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Should `Amadeus Engine Services` be split into smaller, more focused modules?**
  _Cohesion score 0.05081585081585081 - nodes in this community are weakly interconnected._
- **Should `PHP Package Manifest` be split into smaller, more focused modules?**
  _Cohesion score 0.07199032062915911 - nodes in this community are weakly interconnected._
- **Should `Database Schema Migrations` be split into smaller, more focused modules?**
  _Cohesion score 0.09579100145137881 - nodes in this community are weakly interconnected._