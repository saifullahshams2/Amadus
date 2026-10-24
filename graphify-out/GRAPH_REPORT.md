# Graph Report - gds_amadeus  (2026-10-24)

## Corpus Check
- 45 files · ~159,271 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 7 file(s) not represented in the graph (top: (none) 2, .example 1, .db 1)

## Summary
- 263 nodes · 513 edges · 20 communities (11 shown, 9 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 2 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Core Engine & Availability
- UI Components & Dashboard
- JS Test Engine & Seeding
- Amadeus Training Architecture
- TypeScript Configuration
- Development Dependencies
- Production Dependencies
- Aviation Dataset Pipeline
- Package Metadata
- API Integration Tests
- NPM Lifecycle Scripts
- Auth & DB Drivers
- Database Client Interface
- Next.js Build Config
- Autoprefixer CSS Tooling
- Tailwind CSS Dependency
- TypeScript Compiler

## God Nodes (most connected - your core abstractions)
1. `executeCrypticCommand()` - 21 edges
2. `getDb()` - 19 edges
3. `initializeDatabase()` - 19 edges
4. `compilerOptions` - 16 edges
5. `next` - 13 edges
6. `useTerminalStore` - 12 edges
7. `react` - 10 edges
8. `verifyToken()` - 10 edges
9. `lucide-react` - 9 edges
10. `PNR` - 9 edges

## Surprising Connections (you probably didn't know these)
- `runTestSuite()` --calls--> `executeCrypticCommand()`  [EXTRACTED]
  scripts/test_engine.js → src/lib/amadeus/parser.ts
- `runTestSuite()` --calls--> `initializeDatabase()`  [EXTRACTED]
  scripts/test_engine.mts → src/lib/db/index.ts
- `runTestSuite()` --calls--> `initializeDatabase()`  [EXTRACTED]
  scripts/test_engine.js → src/lib/db/index.ts
- `runTestSuite()` --calls--> `executeCrypticCommand()`  [EXTRACTED]
  scripts/test_engine.mts → src/lib/amadeus/parser.ts
- `POST()` --calls--> `executeCrypticCommand()`  [EXTRACTED]
  src/app/api/terminal/execute/route.ts → src/lib/amadeus/parser.ts

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Amadeus Training Curriculum Progression** — readme_module_1_iata_encoding, readme_module_2_schedules_availability, readme_module_3_print_elements, readme_module_4_pricing_tst, readme_module_5_payment_ticketing [EXTRACTED 1.00]
- **Mandatory PRINT Reservation Validation Flow** — readme_print_elements, readme_pnr_lifecycle, readme_cryptic_command_parser [EXTRACTED 1.00]

## Communities (20 total, 9 thin omitted)

### Community 0 - "Core Engine & Availability"
Cohesion: 0.07
Nodes (45): createFreshState(), runTestSuite(), AIRLINE_HUBS, AvailabilityResult, DAY_NAMES, generateSchedules(), MONTH_MAP, parseDateString() (+37 more)

### Community 1 - "UI Components & Dashboard"
Cohesion: 0.11
Nodes (23): lucide-react, react, metadata, RootLayout(), HomePage(), PnrVisualizer(), TrainingCompanion(), Navbar() (+15 more)

### Community 2 - "JS Test Engine & Seeding"
Cohesion: 0.16
Nodes (22): next, createFreshState(), runTestSuite(), POST(), GET(), POST(), GET(), POST() (+14 more)

### Community 3 - "Amadeus Training Architecture"
Cohesion: 0.11
Nodes (25): Amadeus GDS Training Simulator, Amadeus Selling Platform Connect, CRT and Modern Visual Themes, Amadeus Cryptic Command Parser, Authentic Cryptic Terminal Screen, Training Curriculum Modules, Database Parameter Mapping Abstraction, Dual-Database Support (+17 more)

### Community 4 - "TypeScript Configuration"
Cohesion: 0.11
Nodes (18): compilerOptions, allowJs, baseUrl, esModuleInterop, incremental, isolatedModules, jsx, lib (+10 more)

### Community 5 - "Development Dependencies"
Cohesion: 0.13
Nodes (15): devDependencies, @types/bcryptjs@^2.4.6, @types/better-sqlite3@^9.6.0, @types/jsonwebtoken@^9.0.10, @types/node@^26.6.5, @types/pg@^8.23.1, @types/react@^18.3.1, @types/react-dom@^18.3.1 (+7 more)

### Community 6 - "Production Dependencies"
Cohesion: 0.14
Nodes (14): dependencies, jsonwebtoken@^9.0.3, lucide-react@^1.61.0, next@^14.2.15, pg@^8.23.1, postcss@^8.5.31, react@^18.3.1, react-dom@^18.3.1 (+6 more)

### Community 8 - "Package Metadata"
Cohesion: 0.22
Nodes (8): author, description, keywords, license, main, name, type, version

### Community 9 - "API Integration Tests"
Cohesion: 0.36
Nodes (4): getJson(), postJson(), run(), sleep()

### Community 10 - "NPM Lifecycle Scripts"
Cohesion: 0.33
Nodes (6): scripts, build, dev, seed, start, test

### Community 11 - "Auth & DB Drivers"
Cohesion: 0.40
Nodes (5): bcryptjs@^3.0.3, better-sqlite3@^13.0.3, bcryptjs, better-sqlite3, seed()

## Knowledge Gaps
- **7 isolated node(s):** `Amadeus Selling Platform Connect`, `Multi-Work Area Support`, `CRT and Modern Visual Themes`, `Worldwide Aviation Datasets`, `Database Parameter Mapping Abstraction` (+2 more)
  These have ≤1 connection - possible missing edges. (Counts symbols only; 74 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **9 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `next` connect `JS Test Engine & Seeding` to `Package Metadata`, `UI Components & Dashboard`, `Production Dependencies`?**
  _High betweenness centrality (0.123) - this node is a cross-community bridge._
- **What connects `Amadeus Selling Platform Connect`, `Multi-Work Area Support`, `CRT and Modern Visual Themes` to the rest of the system?**
  _7 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Core Engine & Availability` be split into smaller, more focused modules?**
  _Cohesion score 0.07364114552893045 - nodes in this community are weakly interconnected._
- **Why does `getDb()` connect `JS Test Engine & Seeding` to `Core Engine & Availability`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Should `UI Components & Dashboard` be split into smaller, more focused modules?**
  _Cohesion score 0.11470985155195682 - nodes in this community are weakly interconnected._
- **Why does `react` connect `UI Components & Dashboard` to `Package Metadata`, `Production Dependencies`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **Should `Amadeus Training Architecture` be split into smaller, more focused modules?**
  _Cohesion score 0.11333333333333333 - nodes in this community are weakly interconnected._