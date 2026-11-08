# Security Penetration Test Report

**Generated:** 2026-11-08 17:24:27 UTC

# Executive Summary

An authorized white-box security assessment of the **Amadus Training Simulator** (Laravel 13 / PHP 8.4 GDS training platform for aviation academies) covering application code, authentication, authorization, the cryptic-terminal engine, and the deployment installer.

**Overall risk posture: Moderate, with one high-severity exposure.** Eight issues were confirmed and documented; every finding ships with a concrete, verified remediation.

**Key findings**
- **HIGH — Password-reset link poisoning → account takeover (vuln-0004).** The application trusts the client-supplied `Host`/`X-Forwarded-Host` header when building emailed password-reset links. An unauthenticated attacker can poison a reset request so the victim receives a link on the attacker's domain carrying a valid token; the token was redeemed on the real origin and the account (up to and including instructor accounts) was taken over end-to-end, verified at the database-hash level. A fix was derived, applied, and regression-tested during the assessment.
- **MEDIUM — Unauthenticated PNR disclosure (vuln-0005).** The terminal API accepts guest sessions, and the ownership check on record-locator retrieval explicitly exempts guests — anyone without credentials can read any stored booking by its 6-character locator or a passenger-surname search, and inject queue rows into other offices.
- **MEDIUM — Cross-cadet PNR disclosure (vuln-0006).** The single-letter PNR index (`RT/A`…`RT/Z`) lists the 50 most recent bookings with no user or office scoping, leaking classmates' passenger names and locators to any cadet.
- **MEDIUM — Installer SSRF/port-scan + denial of service (vuln-0008).** Pre-install, the unauthenticated database-test endpoint connects to attacker-chosen hosts; a timing oracle distinguishes open/closed ports, the cloud-metadata blocklist is bypassable with hex/octal/trailing-dot IP forms (verified with a live link-local listener), and a single request to a silent-open port pins the PHP worker indefinitely.
- **LOW — Cross-instructor scenario deletion (vuln-0007).** Any instructor can delete any office's drill scenario, cascading to all students' graded submissions.
- Three transitive dependency CVEs (symfony packages via laravel/framework) were reported at informational severity with verified non-reachability in this codebase.

**Business impact.** For an academy deployment: full instructor-account takeover destroys the platform's privilege model (impersonation, cohort provisioning, grading); unauthenticated booking disclosure exposes every trainee's practice data to the internet; and the installer window on fresh deployments is exposed to internal network probing and outright DoS. Remediation is low effort across the board.

**What held up well.** Injection resistance is genuinely strong: the entire data layer is Eloquent with bound parameters (zero raw query fragments), every render path escapes output, the parser contains no code-execution or file sinks, vertical privilege escalation paths (role switching, impersonation) are properly locked, and the installer's lock/env-writer logic is correctly hardened. The confirmed issues cluster in **object-level authorization scoping and trust of request-derived identity** — not in injection or transport.

# Methodology

**Framework:** OWASP Web Security Testing Guide (WSTG) categories for authentication, session management, access control, input handling, and configuration; combined static + dynamic (white-box) methodology per PTES phases.

**Engagement type:** White-box source-assisted assessment of the repository with a live locally-deployed instance (Laravel 13, PHP 8.4, SQLite) used for dynamic validation of every candidate.

**Scope:** All application code (`app/`, `routes/`, `resources/views/`, `config/`), dependency manifests (`composer.lock`, `package-lock.json`), database schema and seeders, and the runtime behavior of all 40+ routes (web + `/api/*`), the cryptic-terminal parser, and the installation wizard.

**Activities performed**
- Static breadth: Semgrep (p/default + p/php + p/secrets), ast-grep structural mapping, gitleaks + trufflehog secret detection, trivy filesystem dependency/misconfiguration scanning; full route→controller→sink inventory.
- Manual source review: authentication and session flows, role/impersonation architecture, object-ownership enforcement across all seven data models, the 2,568-line cryptic-command parser, installer state machine and env-writer, output encoding across all Blade views.
- Dynamic validation: every reported finding reproduced end-to-end on the live instance with captured request/response evidence (multi-user sessions: instructor + two cadets across two offices, plus unauthenticated callers); negative controls (blocked-request counterparts) captured alongside each proof.
- Dependency analysis: advisory verification and per-CVE symbol-level reachability analysis against application code.
- Fix derivation: each dynamic finding carries an inline, code-anchored remediation; the high-severity and installer fixes were applied and regression-tested during the engagement.
- Counter-evidence discipline: for every reported finding the alternative explanations and potential guards were actively investigated and documented; for every closed surface, the specific control that makes it safe was named.

# Technical Analysis

**Severity model:** findings rated by demonstrated exploitability × business impact, CVSS-scored on the proven attack path only.

**Confirmed findings**
1. **Password-reset link poisoning (HIGH, 7.5, CWE-346).** `bootstrap/app.php` sets `trustProxies(at: '*')`, so the framework derives the request root from attacker-controlled `Host`/`X-Forwarded-Host`. The reset email URL is built from that root; the embedded broker token remains valid on the real origin. Full takeover of any account, including instructors. Fix: pin reset-link generation to the configured application URL (applied + verified).
2. **Unauthenticated PNR disclosure (MEDIUM, 5.3, CWE-862).** `POST /api/terminal/execute` sits outside the auth middleware and the parser runs guests under a `'guest'` identity; the record-locator ownership check and surname-search scoping both skip enforcement for guests. Any stored booking readable without credentials; guests can also write queue rows into other offices.
3. **Cross-cadet PNR disclosure (MEDIUM, 4.3, CWE-639).** The single-letter index path queries the 50 most recent PNRs unscoped; the follow-up line-retrieve command then returns the full foreign record from the leaked list. The adjacent surname branch scopes students correctly — the index path simply omitted the same constraint.
4. **Installer test-db trio (MEDIUM, 6.5).** Unauthenticated pre-install endpoint: (a) timing differential yields an internal port-scan oracle; (b) the link-local metadata blocklist matches only dotted-quad/IPv6-mapped forms, bypassable via hex/octal/trailing-dot notation — proven by a server-initiated connection to a link-local listener; (c) PDO's connect timeout does not bound the handshake read, so one request to a silent-open port wedges the single-worker deployment (DoS). Fix: fail-closed host resolution with pinned resolved IP plus bounded TCP pre-flight (applied + verified, 169/169 tests).
5. **Cross-instructor scenario deletion (LOW, 2.7, CWE-639).** Scenario delete checks role only; the model has no office/creator column, unlike every sibling instructor operation which is office-scoped. Deletion cascades all students' submissions.
6–8. **Dependency CVEs (INFO).** Three transitive symfony advisories (email header injection; HEAD-bypass of method-scoped matchers; host-placeholder path desync) verified **not reachable**: the affected classes are never used and no host-constrained routes exist. Reported for manifest hygiene.

**Systemic themes**
- **Client-trusted identity is the dominant root cause:** request-derived `Host` (finding 1), a guest identity allowed into business logic (finding 2), and client-supplied office state in queue commands. The platform's vertical privilege architecture is solid; its horizontal/unauthenticated boundaries are not consistently enforced.
- **Authorization checks are per-branch, not systemic:** the parser re-implements scoping in each retrieval branch, so one branch (single-letter index) silently omitted what its sibling enforced. Centralizing user/office scoping in the query layer would eliminate the class.
- **Strong injection posture is the standout positive:** parameterized queries everywhere, escaped output everywhere, no dangerous sinks in the parser — confirmed both statically and dynamically with payload probes.

**Verified-clean areas (with named controls):** SQL injection (bound parameters only), XSS/output injection (no unescaped sinks; `@json` hex-escaping verified), command/file sinks (none reachable), role switching (hard-locked), impersonation abuse (single gated session-key writer, inert exit path), mass assignment (explicit field lists verified in DB), user enumeration (uniform responses), CSRF (enforced, POST-only logout), secrets (all 100+ scanner hits were false positives), installer lock/env-writer (flock + re-checked on every route).

# Recommendations

**Immediate**
1. Ship the applied **vuln-0004 fix** (reset links pinned to configured application URL — already in the working tree, tests green) and deploy; also constrain trusted proxies from `*` to the real load-balancer range, which hardens every request-derived URL at the root.
2. Fix **vuln-0005**: require authentication on the terminal API (`401` for guests) and remove the two guest exemptions in the PNR retrieval paths; derive office identity from the session user instead of client-supplied state on queue commands.
3. Fix **vuln-0006**: apply the same user/office scoping to the single-letter PNR index that the surname branch already enforces.
4. Ship the applied **vuln-0008 installer fix** (fail-closed host resolution + bounded connect/first-byte timeouts) — pre-fix instances remain exposed during their install window.

**Short-term**
5. Add an ownership/creator check to scenario deletion (vuln-0007).
6. Add the regression tests suggested during the assessment: guest terminal call → 401; cadet cross-user index must not surface other users' records; cross-office scenario delete → 404; poisoned-Host reset request must yield a correct-origin link.
7. Remove `role` and `is_approved` from `User::$fillable` (currently unreachable from requests, but a latent mass-assignment escalation risk).
8. Revisit the password-reset notification path once a production mailer is configured: the sandbox default (`log` mailer) masked mail-delivery behavior; verify sent links against the configured application URL.

**Medium-term**
9. Centralize object-authorization scoping (user + office) into the PNR/queue query layer so new retrieval commands inherit it by default, rather than per-command checks.
10. Tighten the CSP (currently `unsafe-inline`/`unsafe-eval` in script-src) to restore defense-in-depth behind the (currently clean) output-encoding layer.
11. Upgrade the three flagged transitive symfony components with the next `composer update`; informational today, but cheap to clear.

**Retest & validation:** after deploying items 1–4, re-run the captured PoCs (all documented with request/response evidence in the individual reports): poisoned-Host reset request, unauthenticated `RT` retrieval, cadet single-letter index + list-line retrieval, and the installer bypass matrix (hex/octal/trailing-dot metadata forms, silent-open port timing). Each should now fail closed while legitimate flows (real MariaDB install, honest-usage terminal commands) continue to pass; the applied fixes already have green regression suites to build on.

