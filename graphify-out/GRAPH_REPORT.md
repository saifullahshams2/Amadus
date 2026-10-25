# Graph Report - gds_amadeus  (2026-10-25)

## Corpus Check
- 61 files · ~156,841 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 18, .example 1, .xml 1)

## Summary
- 285 nodes · 391 edges · 30 communities (11 shown, 19 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 18 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

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
- Unit Test Suite
- Agent & Developer Guidelines
- Authentication Login View
- Authentication Registration View
- Instructor Dashboard View
- GDS Terminal View

## God Nodes (most connected - your core abstractions)
1. `User` - 11 edges
2. `AmadeusParser` - 11 edges
3. `CurriculumService` - 10 edges
4. `AuthController` - 9 edges
5. `scripts` - 9 edges
6. `TerminalController` - 8 edges
7. `TrainingController` - 8 edges
8. `require-dev` - 8 edges
9. `AmadeusGdsIntegrationTest` - 8 edges
10. `Amadeus GDS Training Simulator` - 8 edges

## Surprising Connections (you probably didn't know these)
- `Laravel Application Guidelines (AGENTS)` --semantically_similar_to--> `Claude Laravel Guidelines`  [INFERRED] [semantically similar]
  AGENTS.md → CLAUDE.md
- `AmadeusParser` --references--> `AvailabilityService`  [EXTRACTED]
  app/Services/Amadeus/AmadeusParser.php → app/Services/Amadeus/AvailabilityService.php
- `AmadeusParser` --references--> `DecodeService`  [EXTRACTED]
  app/Services/Amadeus/AmadeusParser.php → app/Services/Amadeus/DecodeService.php
- `AmadeusParser` --references--> `PnrService`  [EXTRACTED]
  app/Services/Amadeus/AmadeusParser.php → app/Services/Amadeus/PnrService.php
- `AmadeusParser` --references--> `PricingService`  [EXTRACTED]
  app/Services/Amadeus/AmadeusParser.php → app/Services/Amadeus/PricingService.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Amadeus Simulator Core Architecture** — readme_amadeus_gds_training_simulator, readme_work_area_terminal, readme_interactive_curriculum, readme_instructor_hub [EXTRACTED 1.00]

## Communities (30 total, 19 thin omitted)

### Community 0 - "HTTP Controllers & Routing"
Cohesion: 0.08
Nodes (9): AuthController, Controller, InstructorController, TerminalController, TrainingController, LessonProgress, AmadeusParser, CommandExecutionResult (+1 more)

### Community 1 - "Amadeus Engine Services"
Cohesion: 0.10
Nodes (10): AvailabilityResult, AvailabilityService, DecodeResult, DecodeService, PnrService, PnrValidationResult, PricingResult, PricingService (+2 more)

### Community 2 - "PHP Package Manifest"
Cohesion: 0.06
Nodes (32): autoload, autoload-dev, psr-4, psr-4, description, extra, laravel, keywords (+24 more)

### Community 3 - "Database Schema Migrations"
Cohesion: 0.12
Nodes (13): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+5 more)

### Community 5 - "Node Dependencies & Vite"
Cohesion: 0.13
Nodes (20): devDependencies, concurrently@^10.0.3, laravel-vite-plugin@^3.1, tailwindcss@^4.0.0, @tailwindcss/vite@^4.0.0, vite@^8.0.0, optionalDependencies, @laravel/multiplex@^0.4.1 (+12 more)

### Community 6 - "Domain Models & Aviation Data"
Cohesion: 0.20
Nodes (5): Aircraft, Airline, Airport, Pnr, DatabaseSeeder

### Community 7 - "Feature Integration Tests"
Cohesion: 0.19
Nodes (3): AmadeusGdsIntegrationTest, ExampleTest, TestCase

### Community 8 - "Project Architecture & Features"
Cohesion: 0.22
Nodes (9): Amadeus GDS Training Simulator, CRT & Modern Terminal Themes, Cryptic Command Cheat Sheet, Database Flexibility (SQLite, MySQL, MariaDB), Instructor Operations Hub & PNR Audit, Interactive Training Curriculum & Hints, Amadeus Selling Platform Connect, Multi-Work Area Terminal (A-F) (+1 more)

### Community 9 - "Application Bootstrap & Entry"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 10 - "Dev Dependencies & Tooling"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 11 - "Composer Configuration"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

## Knowledge Gaps
- **14 isolated node(s):** `layouts.app`, `layouts.app`, `layouts.app`, `layouts.app`, `Amadeus Selling Platform Connect` (+9 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 119 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **19 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Authentication & State Config` to `HTTP Controllers & Routing`, `Domain Models & Aviation Data`, `Feature Integration Tests`?**
  _High betweenness centrality (0.085) - this node is a cross-community bridge._
- **What connects `layouts.app`, `layouts.app`, `layouts.app` to the rest of the system?**
  _14 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `HTTP Controllers & Routing` be split into smaller, more focused modules?**
  _Cohesion score 0.08478513356562137 - nodes in this community are weakly interconnected._
- **Why does `AmadeusParser` connect `HTTP Controllers & Routing` to `Amadeus Engine Services`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Should `Amadeus Engine Services` be split into smaller, more focused modules?**
  _Cohesion score 0.09803921568627451 - nodes in this community are weakly interconnected._
- **Why does `Pnr` connect `Domain Models & Aviation Data` to `HTTP Controllers & Routing`, `Amadeus Engine Services`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Should `PHP Package Manifest` be split into smaller, more focused modules?**
  _Cohesion score 0.06060606060606061 - nodes in this community are weakly interconnected._