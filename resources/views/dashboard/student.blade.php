@extends('layouts.dashboard')

@section('title', 'Trainee Dashboard - Amadeus GDS Training Platform')

@section('content')
<div x-data="studentDashboard()" x-init="init()" class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- Section 1: Top Metrics & KPI Overview -->
    <section id="analytics" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Accuracy Rate Gauge Card -->
        <div class="bg-[#0b101d] border border-slate-800 rounded-xl p-4 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-mono text-slate-400 uppercase tracking-wider">Syntax Accuracy</div>
                <div class="text-2xl font-black text-white mt-1" x-text="analytics.accuracyRate + '%'"></div>
                <div class="text-[11px] text-emerald-400 mt-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>High Precision</span>
                </div>
            </div>
            <div class="relative w-14 h-14 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="text-emerald-500" :stroke-dasharray="analytics.accuracyRate + ', 100'" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <span class="absolute font-mono text-xs font-bold text-white" x-text="analytics.accuracyRate + '%'"></span>
            </div>
        </div>

        <!-- Command Efficiency Score -->
        <div class="bg-[#0b101d] border border-slate-800 rounded-xl p-4 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-mono text-slate-400 uppercase tracking-wider">Efficiency Score</div>
                <div class="text-2xl font-black text-sky-400 mt-1" x-text="analytics.efficiencyScore + ' / 100'"></div>
                <div class="text-[11px] text-slate-400 mt-1">
                    <span class="text-sky-300 font-bold" x-text="analytics.totalCommands"></span> commands executed
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-950/60 border border-sky-800/60 flex items-center justify-center text-sky-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        </div>

        <!-- Drills Completion Rate -->
        <div class="bg-[#0b101d] border border-slate-800 rounded-xl p-4 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-mono text-slate-400 uppercase tracking-wider">Scenario Progress</div>
                <div class="text-2xl font-black text-purple-400 mt-1" x-text="analytics.completedCount + ' / ' + analytics.totalScenarios"></div>
                <div class="text-[11px] text-slate-400 mt-1">
                    <span class="text-purple-300 font-bold" x-text="analytics.completionRate + '%'"></span> completed
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-950/60 border border-purple-800/60 flex items-center justify-center text-purple-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Active Queue Bank Summary -->
        <div class="bg-[#0b101d] border border-slate-800 rounded-xl p-4 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-mono text-slate-400 uppercase tracking-wider">Queues on File</div>
                <div class="text-2xl font-black text-amber-400 mt-1">{{ array_sum(array_column($queueSummary, 'count')) }} PNRs</div>
                <div class="text-[11px] text-amber-300/80 mt-1">Q1 (Ticketing) & Q7 (Sched)</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-950/60 border border-amber-800/60 flex items-center justify-center text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
        </div>
    </section>

    <!-- Section 2: Workspace & Current Active PNR Status Widget -->
    <section id="workspace" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Current Active PNR Inspector Card (2 columns) -->
        <div class="lg:col-span-2 bg-[#0b101d] border border-slate-800 rounded-xl overflow-hidden shadow-xl flex flex-col">
            <div class="px-5 py-3.5 border-b border-slate-800 bg-[#0d1322] flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
                    <h2 class="text-sm font-bold text-white tracking-wide">Current Active PNR Status</h2>
                    <template x-if="activePnr">
                        <span class="px-2 py-0.5 rounded bg-blue-950 border border-blue-800 text-sky-400 font-mono text-xs font-bold" x-text="activePnr.recordLocator"></span>
                    </template>
                </div>

                <div class="flex items-center space-x-2">
                    <button @click="openDrawer = true" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg border border-slate-700 flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <span>Quick Inspect Drawer</span>
                    </button>
                    <a href="/" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg shadow transition-colors flex items-center gap-1">
                        <span>Work on PNR</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            <!-- Card Body: Structured PNR Elements Inspection -->
            <div class="p-5 flex-1">
                <template x-if="!activePnr">
                    <div class="h-48 flex flex-col items-center justify-center text-center p-6 border border-dashed border-slate-800 rounded-lg">
                        <svg class="w-10 h-10 text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <h3 class="text-sm font-semibold text-slate-300">No Active PNR in Workspace</h3>
                        <p class="text-xs text-slate-500 max-w-sm mt-1">Start a practice drill or launch the terminal to sell flight segments and build a reservation.</p>
                        <a href="/" class="mt-3 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-lg">Launch Practice Terminal</a>
                    </div>
                </template>

                <template x-if="activePnr">
                    <div class="space-y-4 font-mono text-xs">
                        <!-- Mandatory PRINT Elements Readiness Bar -->
                        <div class="bg-[#080d19] p-3 rounded-lg border border-slate-800/80">
                            <div class="text-[10px] font-sans font-bold uppercase text-slate-400 mb-2 flex items-center justify-between">
                                <span>Mandatory P.R.I.N.T. Elements Readiness</span>
                                <span class="text-emerald-400 font-bold" x-text="printScoreText()"></span>
                            </div>
                            <div class="grid grid-cols-5 gap-2 text-center text-[10px] font-bold">
                                <div :class="activePnr.contacts?.length ? 'bg-emerald-950 text-emerald-400 border-emerald-800' : 'bg-rose-950/40 text-rose-400 border-rose-900/50'" class="py-1 rounded border">
                                    P - Phone
                                </div>
                                <div :class="activePnr.receivedFrom ? 'bg-emerald-950 text-emerald-400 border-emerald-800' : 'bg-rose-950/40 text-rose-400 border-rose-900/50'" class="py-1 rounded border">
                                    R - Received
                                </div>
                                <div :class="activePnr.segments?.length ? 'bg-emerald-950 text-emerald-400 border-emerald-800' : 'bg-rose-950/40 text-rose-400 border-rose-900/50'" class="py-1 rounded border">
                                    I - Itinerary
                                </div>
                                <div :class="activePnr.passengers?.length ? 'bg-emerald-950 text-emerald-400 border-emerald-800' : 'bg-rose-950/40 text-rose-400 border-rose-900/50'" class="py-1 rounded border">
                                    N - Name
                                </div>
                                <div :class="activePnr.ticketing ? 'bg-emerald-950 text-emerald-400 border-emerald-800' : 'bg-rose-950/40 text-rose-400 border-rose-900/50'" class="py-1 rounded border">
                                    T - Ticketing
                                </div>
                            </div>
                        </div>

                        <!-- 2-Column Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Passengers & Contacts -->
                            <div class="bg-[#080d19] p-3 rounded-lg border border-slate-800/80 space-y-2">
                                <div class="text-[10px] font-sans font-bold uppercase text-slate-400 border-b border-slate-800 pb-1 flex items-center justify-between">
                                    <span>Passengers (NM)</span>
                                    <span class="text-sky-400" x-text="activePnr.passengers?.length + ' registered'"></span>
                                </div>
                                <div class="space-y-1">
                                    <template x-for="(pax, idx) in (activePnr.passengers || [])" :key="idx">
                                        <div class="text-slate-200 flex items-center justify-between">
                                            <span x-text="(idx + 1) + '. ' + pax.lastName + '/' + pax.firstName + ' ' + (pax.title || '')"></span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 font-sans" x-text="pax.type || 'ADT'"></span>
                                        </div>
                                    </template>
                                </div>

                                <div class="text-[10px] font-sans font-bold uppercase text-slate-400 border-b border-slate-800 pt-2 pb-1">
                                    Contacts (AP/APE)
                                </div>
                                <div class="space-y-1">
                                    <template x-for="(c, idx) in (activePnr.contacts || [])" :key="idx">
                                        <div class="text-slate-300 truncate" x-text="'AP ' + c.detail"></div>
                                    </template>
                                    <template x-if="!activePnr.contacts?.length">
                                        <div class="text-rose-400 text-[11px] italic">No contact details on file (Execute AP)</div>
                                    </template>
                                </div>
                            </div>

                            <!-- Segments & Pricing -->
                            <div class="bg-[#080d19] p-3 rounded-lg border border-slate-800/80 space-y-2">
                                <div class="text-[10px] font-sans font-bold uppercase text-slate-400 border-b border-slate-800 pb-1 flex items-center justify-between">
                                    <span>Itinerary Segments (SS)</span>
                                    <span class="text-sky-400" x-text="activePnr.segments?.length + ' segments'"></span>
                                </div>
                                <div class="space-y-1">
                                    <template x-for="seg in (activePnr.segments || [])" :key="seg.segmentNumber">
                                        <div class="text-slate-200 flex items-center justify-between">
                                            <span class="text-sky-300 font-bold" x-text="seg.airlineCode + ' ' + seg.flightNumber"></span>
                                            <span x-text="seg.departureAirport + '-' + seg.arrivalAirport"></span>
                                            <span class="text-slate-400" x-text="seg.departureDate"></span>
                                            <span class="text-emerald-400" x-text="seg.statusCode || 'HK1'"></span>
                                        </div>
                                    </template>
                                    <template x-if="!activePnr.segments?.length">
                                        <div class="text-rose-400 text-[11px] italic">No booked segments</div>
                                    </template>
                                </div>

                                <div class="text-[10px] font-sans font-bold uppercase text-slate-400 border-b border-slate-800 pt-2 pb-1 flex items-center justify-between">
                                    <span>Pricing & Ticketing (FXP / TTP)</span>
                                    <span :class="activePnr.tst ? 'text-emerald-400' : 'text-slate-500'" x-text="activePnr.tst ? 'TST STORED' : 'NO TST'"></span>
                                </div>
                                <div class="text-slate-300">
                                    <template x-if="activePnr.tst">
                                        <div class="flex items-center justify-between">
                                            <span>FARE QUOTE:</span>
                                            <span class="font-bold text-emerald-400" x-text="activePnr.tst.currency + ' ' + activePnr.tst.totalAmount"></span>
                                        </div>
                                    </template>
                                    <template x-if="!activePnr.tst">
                                        <div class="text-slate-500 text-[11px]">Unpriced - Run FXP to store TST</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Quick Terminal Launcher & Command Shortcut Widget (1 column) -->
        <div class="bg-[#0b101d] border border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-xl">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white tracking-wide">Amadeus Cryptic Terminal</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-950 text-emerald-400 border border-emerald-800">READY</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Access the high-fidelity green-screen command console with multi-work-area support (A through F), live IATA databases, and cryptic entry simulation.
                </p>

                <!-- Mini Cryptic Terminal Preview -->
                <div class="mt-4 bg-[#050811] p-3 rounded-lg border border-slate-800 font-mono text-[11px] text-emerald-400 space-y-1">
                    <div class="text-slate-500">// Terminal Area A: LON1A0980</div>
                    <div>&gt; AN15NOVLHRJFK/ABA</div>
                    <div class="text-slate-400">1 BA 117 Y9 B9 H9 K9 M9 LHRJFK 1030 1345</div>
                    <div>&gt; SS1Y1</div>
                    <div class="text-sky-300">1 BA 117 Y 15NOV LHRJFK HK1 1030 1345</div>
                </div>
            </div>

            <div class="mt-5 space-y-2">
                <a href="/" class="w-full py-2.5 px-4 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 text-white font-bold text-xs rounded-lg shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    <span>Open Full Practice Terminal</span>
                </a>
                <a href="#validator" class="w-full py-2 px-4 bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 font-semibold text-xs rounded-lg border border-slate-700 flex items-center justify-center gap-1.5 transition-colors">
                    <span>Open Syntax Validator</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Section 3: Assigned Scenarios & Practice Drills -->
    <section id="drills" class="bg-[#0b101d] border border-slate-800 rounded-xl overflow-hidden shadow-xl">
        <div class="px-6 py-4 border-b border-slate-800 bg-[#0d1322] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-white tracking-wide">Assigned Booking Scenarios & Drills</h2>
                <p class="text-xs text-slate-400">Practice core flight reservations, passenger profiles, and queue notifications</p>
            </div>

            <!-- Category Filter Tabs -->
            <div class="flex items-center space-x-1 bg-[#060a14] p-1 rounded-lg border border-slate-800 text-xs font-semibold">
                <button @click="selectedCategory = 'ALL'" :class="selectedCategory === 'ALL' ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded transition-colors">All</button>
                <button @click="selectedCategory = 'Round-trip'" :class="selectedCategory === 'Round-trip' ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded transition-colors">Round-trip</button>
                <button @click="selectedCategory = 'Infant-in-lap'" :class="selectedCategory === 'Infant-in-lap' ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded transition-colors">Infant-in-lap</button>
                <button @click="selectedCategory = 'Schedule Exchange'" :class="selectedCategory === 'Schedule Exchange' ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded transition-colors">Queues</button>
            </div>
        </div>

        <div class="divide-y divide-slate-800/80">
            <template x-for="sc in filteredScenarios" :key="sc.id">
                <div class="p-5 hover:bg-[#0e1628]/50 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Left: Scenario Overview -->
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center space-x-2.5">
                            <span class="font-mono text-xs font-bold text-sky-400" x-text="sc.id"></span>
                            <h3 class="text-sm font-bold text-white" x-text="sc.title"></h3>
                            <!-- Status Badges -->
                            <span :class="statusBadgeClass(sc.status)" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono tracking-wider" x-text="formatStatus(sc.status)"></span>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono" x-text="sc.difficulty"></span>
                        </div>
                        <p class="text-xs text-slate-400 max-w-2xl" x-text="sc.description"></p>

                        <!-- Route segments tags -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px] font-mono">
                            <span class="text-slate-500 font-sans text-[10px] uppercase font-bold">Target Route:</span>
                            <template x-for="(seg, idx) in (sc.flight_segments || [])" :key="idx">
                                <span class="px-1.5 py-0.5 rounded bg-[#131c30] text-slate-300 border border-slate-700/60" x-text="seg.origin + ' → ' + seg.destination + ' (' + (seg.airline || 'Any') + ')'"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Right: Score, Time & Action -->
                    <div class="flex items-center space-x-4 shrink-0">
                        <template x-if="sc.status === 'reviewed' || sc.status === 'submitted'">
                            <div class="text-right">
                                <div class="text-xs font-mono text-slate-400">Score Awarded</div>
                                <div class="text-lg font-black" :class="sc.score >= 70 ? 'text-emerald-400' : 'text-amber-400'" x-text="sc.score + ' / 100'"></div>
                            </div>
                        </template>

                        <button @click="openScenarioModal(sc)" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-lg border border-slate-700 transition-colors flex items-center gap-1.5">
                            <span>Briefing & Submit</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <!-- Section 4: Live Syntax Validator & Error Log -->
    <section id="validator" class="bg-[#0b101d] border border-slate-800 rounded-xl p-5 shadow-xl space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column: Interactive Cryptic Entry Validator -->
            <div class="flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                        <div>
                            <h2 class="text-sm font-bold text-white tracking-wide">Interactive Cryptic Entry Validator</h2>
                            <p class="text-xs text-slate-400">Test and validate any Amadeus cryptic entry in real-time</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-sky-950 text-sky-400 border border-sky-800">SANDBOX TESTER</span>
                    </div>

                    <!-- Input Field -->
                    <div class="space-y-2">
                        <label class="block text-xs font-mono text-slate-300">ENTER AMADEUS CRYPTIC COMMAND:</label>
                        <div class="relative">
                            <input type="text"
                                   x-model="validatorInput"
                                   @input.debounce.300ms="validateCommand()"
                                   placeholder="e.g. AN15NOVLHRJFK, NM1SMITH/JOHN MR, TKTL15NOV..."
                                   class="w-full bg-[#060a14] border border-slate-700 text-emerald-400 font-mono text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors uppercase">
                        </div>
                    </div>

                    <!-- Live Validation Feedback Box -->
                    <div class="mt-4 p-3.5 rounded-lg border text-xs font-mono transition-all"
                         :class="validationResult.isValid ? 'bg-emerald-950/30 border-emerald-800/80 text-emerald-300' : (validatorInput.trim() ? 'bg-rose-950/30 border-rose-800/80 text-rose-300' : 'bg-[#080d19] border-slate-800 text-slate-400')">
                        <div class="flex items-center justify-between mb-1.5 font-bold">
                            <div class="flex items-center gap-1.5">
                                <span :class="validationResult.isValid ? 'bg-emerald-500' : (validatorInput.trim() ? 'bg-rose-500' : 'bg-slate-600')" class="w-2 h-2 rounded-full"></span>
                                <span x-text="validationResult.isValid ? 'VALID AMADEUS SYNTAX' : (validatorInput.trim() ? 'SYNTAX ERROR / INCOMPLETE' : 'AWAITING INPUT')"></span>
                            </div>
                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-black/40 text-slate-400 font-sans uppercase" x-text="validationResult.category || 'GENERAL'"></span>
                        </div>
                        <p class="font-sans text-[11px] leading-relaxed text-slate-300" x-text="validationResult.explanation"></p>
                        <template x-if="validationResult.correctionHint">
                            <div class="mt-2 pt-2 border-t border-slate-800/60 text-amber-300 text-[11px] font-sans">
                                <strong class="text-amber-400 font-mono">CORRECTION HINT:</strong> <span x-text="validationResult.correctionHint"></span>
                            </div>
                        </template>
                        <template x-if="validationResult.example">
                            <div class="mt-1 text-slate-400 text-[10px]">
                                Example: <span class="text-sky-300 font-bold" x-text="validationResult.example"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Column: Recent Syntax Errors & Hints Summary -->
            <div class="flex flex-col justify-between border-t lg:border-t-0 lg:border-l border-slate-800 pt-4 lg:pt-0 lg:pl-6">
                <div>
                    <h3 class="text-xs font-bold text-slate-300 mb-2 flex items-center justify-between pb-3 border-b border-slate-800">
                        <span>Recent Syntax Mistakes & Correction Hints</span>
                        <span class="text-[10px] font-mono text-slate-500">AUDIT LOG</span>
                    </h3>
                    <div class="space-y-2 mt-3">
                        @forelse($analytics['recentErrors'] as $err)
                            <div class="p-2.5 rounded bg-[#070b14] border border-slate-800/80 text-[11px]">
                                <div class="flex items-center justify-between font-mono">
                                    <span class="text-rose-400 font-bold">&gt; {{ $err['command'] }}</span>
                                    <span class="text-slate-500 text-[10px]">{{ $err['time'] }}</span>
                                </div>
                                <div class="text-slate-400 text-[10px] mt-1">
                                    <strong class="text-amber-400">Action:</strong> {{ $err['hint'] }}
                                </div>
                            </div>
                        @empty
                            <div class="text-slate-500 text-[11px] italic py-4 text-center">No recent syntax errors logged. Excellent accuracy!</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Inspect Drawer Modal (Slide-out) -->
    <div x-show="openDrawer"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/80 flex justify-end"
         style="display: none;">
        <div @click.away="openDrawer = false" class="w-full max-w-lg bg-[#0a0f1d] border-l border-slate-800 h-full flex flex-col shadow-2xl overflow-hidden">
            <!-- Drawer Header -->
            <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-[#080d19]">
                <div>
                    <h3 class="text-sm font-bold text-white">PNR Quick Inspect Drawer</h3>
                    <div class="text-[11px] font-mono text-slate-400" x-text="'Record Locator: ' + (activePnr?.recordLocator || 'NONE')"></div>
                </div>
                <button @click="openDrawer = false" class="text-slate-400 hover:text-white p-1">✕</button>
            </div>

            <!-- Drawer Content -->
            <div class="p-4 overflow-y-auto flex-1 space-y-4 font-mono text-xs">
                <!-- RLR Raw Format -->
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Amadeus Cryptic Screen Image</span>
                    <pre class="bg-black p-3 rounded border border-slate-800 text-emerald-400 leading-relaxed overflow-x-auto whitespace-pre-wrap" x-text="(activePnr?.rawLines || ['NO PNR LOADED']).join('\n')"></pre>
                </div>

                <!-- Structured Elements breakdown -->
                <div class="space-y-3 font-sans text-xs">
                    <div class="bg-[#0e1628] p-3 rounded border border-slate-800">
                        <span class="font-bold text-white block mb-1">Passenger Profile & Segments</span>
                        <div class="text-slate-300 font-mono text-[11px]" x-text="(activePnr?.passengers?.length || 0) + ' passenger(s) • ' + (activePnr?.segments?.length || 0) + ' segment(s)'"></div>
                    </div>
                    <div class="bg-[#0e1628] p-3 rounded border border-slate-800">
                        <span class="font-bold text-white block mb-1">Ticketing & Stored Fares</span>
                        <div class="text-slate-300 font-mono text-[11px]" x-text="'Ticketing: ' + (activePnr?.ticketing?.raw || 'Not Set') + ' • Total: ' + (activePnr?.tst?.currency || 'GBP') + ' ' + (activePnr?.tst?.totalAmount || '0.00')"></div>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="p-3 border-t border-slate-800 bg-[#080d19] flex justify-end">
                <button @click="openDrawer = false" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded text-xs">Close Drawer</button>
            </div>
        </div>
    </div>

    <!-- Scenario Briefing & Submission Modal -->
    <div x-show="selectedScenarioModal" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="selectedScenarioModal = null" class="bg-[#0b101d] border border-slate-800 rounded-xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden shadow-2xl">
            <div class="p-4 border-b border-slate-800 bg-[#0d1322] flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-mono text-sky-400 font-bold uppercase" x-text="selectedScenarioModal?.id"></span>
                    <h3 class="text-base font-bold text-white" x-text="selectedScenarioModal?.title"></h3>
                </div>
                <button @click="selectedScenarioModal = null" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <div class="p-5 overflow-y-auto space-y-4 text-xs">
                <!-- Briefing Story -->
                <div>
                    <h4 class="text-xs font-bold uppercase font-mono text-slate-400 mb-1">Scenario Briefing</h4>
                    <p class="text-slate-200 leading-relaxed" x-text="selectedScenarioModal?.description"></p>
                </div>

                <!-- Target Passengers & Flight Requirements -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-[#070b14] p-3 rounded-lg border border-slate-800">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Passenger Profile</span>
                        <template x-for="(p, idx) in (selectedScenarioModal?.passenger_profile?.names || [])" :key="idx">
                            <div class="font-mono text-slate-200" x-text="p.title + ' ' + p.firstName + ' ' + p.lastName + (p.infantName ? ' (INF: ' + p.infantName + ')' : '')"></div>
                        </template>
                        <div class="text-slate-400 text-[11px] mt-1" x-text="'Contact: ' + (selectedScenarioModal?.passenger_profile?.contactPhone || 'Local Agency')"></div>
                    </div>

                    <div class="bg-[#070b14] p-3 rounded-lg border border-slate-800">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Target Flight Segments</span>
                        <template x-for="(seg, idx) in (selectedScenarioModal?.flight_segments || [])" :key="idx">
                            <div class="font-mono text-slate-200" x-text="seg.origin + ' → ' + seg.destination + ' on ' + seg.date + ' (' + (seg.airline || 'Any') + ')'"></div>
                        </template>
                        <div class="text-slate-400 text-[11px] mt-1" x-text="'Time limit: ' + selectedScenarioModal?.time_limit_minutes + ' minutes'"></div>
                    </div>
                </div>

                <!-- Submission Results (if already graded) -->
                <template x-if="selectedScenarioModal?.auto_grade_results">
                    <div class="bg-[#070b14] p-3.5 rounded-lg border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white uppercase tracking-wider font-mono">Automated Grading Breakdown</span>
                            <span class="text-sm font-black" :class="selectedScenarioModal.score >= 70 ? 'text-emerald-400' : 'text-amber-400'" x-text="selectedScenarioModal.score + ' / 100'"></span>
                        </div>
                        <div class="space-y-1.5 font-mono text-[11px]">
                            <template x-for="chk in (selectedScenarioModal.auto_grade_results?.checks || [])" :key="chk.code">
                                <div class="flex items-center justify-between p-1.5 rounded bg-black/40">
                                    <span class="flex items-center gap-1.5">
                                        <span :class="chk.passed ? 'text-emerald-400' : 'text-rose-400'" x-text="chk.passed ? '✓' : '✗'"></span>
                                        <span class="text-slate-200" x-text="chk.name"></span>
                                    </span>
                                    <span :class="chk.passed ? 'text-emerald-400' : 'text-rose-400'" x-text="chk.pointsAwarded + ' / ' + chk.maxPoints + ' pts'"></span>
                                </div>
                            </template>
                        </div>
                        <template x-if="selectedScenarioModal.instructor_feedback">
                            <div class="mt-2 pt-2 border-t border-slate-800 text-[11px]">
                                <span class="text-amber-400 font-bold block mb-0.5">Instructor Feedback:</span>
                                <p class="text-slate-300 italic" x-text="selectedScenarioModal.instructor_feedback"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Modal Footer Actions -->
            <div class="p-4 border-t border-slate-800 bg-[#0d1322] flex items-center justify-between">
                <a href="/" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs transition-colors">
                    Practice in Terminal
                </a>

                <button @click="submitScenarioNow(selectedScenarioModal)"
                        :disabled="isSubmitting"
                        class="px-4 py-1.5 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1.5">
                    <span x-text="isSubmitting ? 'Evaluating...' : 'Submit Active PNR for Grading'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function studentDashboard() {
    const scenariosData = @json($scenarios);
    const analyticsData = @json($analytics);
    const initialPnr = @json($activePnr);

    return {
        scenarios: scenariosData,
        analytics: analyticsData,
        activePnr: initialPnr ? {
            recordLocator: initialPnr.record_locator,
            status: initialPnr.status,
            passengers: initialPnr.data_json?.passengers || [],
            segments: initialPnr.data_json?.segments || [],
            contacts: initialPnr.data_json?.contacts || [],
            ticketing: initialPnr.data_json?.ticketing || null,
            fop: initialPnr.data_json?.fop || null,
            receivedFrom: initialPnr.data_json?.receivedFrom || null,
            tst: initialPnr.data_json?.tst || null,
            rawLines: []
        } : null,

        selectedCategory: 'ALL',
        validatorInput: '',
        validationResult: {
            isValid: false,
            category: '',
            explanation: 'Type any Amadeus command above to test its syntax.',
            correctionHint: '',
            example: 'AN15NOVLHRJFK'
        },

        openDrawer: false,
        selectedScenarioModal: null,
        isSubmitting: false,

        init() {
            this.fetchActivePnr();
        },

        get filteredScenarios() {
            if (this.selectedCategory === 'ALL') return this.scenarios;
            return this.scenarios.filter(s => s.category === this.selectedCategory);
        },

        statusBadgeClass(status) {
            switch (status) {
                case 'reviewed': return 'bg-emerald-950 text-emerald-400 border border-emerald-800';
                case 'submitted': return 'bg-amber-950 text-amber-400 border border-amber-800';
                case 'in_progress': return 'bg-blue-950 text-blue-400 border border-blue-800';
                default: return 'bg-slate-800 text-slate-400 border border-slate-700';
            }
        },

        formatStatus(status) {
            switch (status) {
                case 'reviewed': return 'Reviewed / Scored';
                case 'submitted': return 'Submitted';
                case 'in_progress': return 'In Progress';
                default: return 'Not Started';
            }
        },

        printScoreText() {
            if (!this.activePnr) return '0/5';
            let met = 0;
            if (this.activePnr.contacts?.length) met++;
            if (this.activePnr.receivedFrom) met++;
            if (this.activePnr.segments?.length) met++;
            if (this.activePnr.passengers?.length) met++;
            if (this.activePnr.ticketing) met++;
            return met + '/5 Elements Valid';
        },

        async fetchActivePnr() {
            try {
                const res = await fetch('/api/dashboard/active-pnr');
                const data = await res.json();
                if (data.hasActivePnr) {
                    this.activePnr = data;
                }
            } catch (e) {
                console.error('Error fetching active PNR:', e);
            }
        },

        async validateCommand() {
            if (!this.validatorInput.trim()) {
                this.validationResult = {
                    isValid: false,
                    category: '',
                    explanation: 'Type any Amadeus command above to test its syntax.',
                    correctionHint: '',
                    example: 'AN15NOVLHRJFK'
                };
                return;
            }

            try {
                const res = await fetch('/api/dashboard/validate-syntax', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ command: this.validatorInput })
                });
                this.validationResult = await res.json();
            } catch (e) {
                console.error('Syntax validation error:', e);
            }
        },

        openScenarioModal(sc) {
            this.selectedScenarioModal = sc;
        },

        async submitScenarioNow(sc) {
            if (!sc) return;
            this.isSubmitting = true;

            try {
                const res = await fetch(`/api/dashboard/scenarios/${sc.id}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        pnrData: this.activePnr,
                        timeElapsedSeconds: 300
                    })
                });

                const data = await res.json();
                if (data.success) {
                    sc.status = 'submitted';
                    sc.score = data.submission.score;
                    sc.auto_grade_results = data.grade;
                    alert(`Scenario submitted successfully!\nScore: ${data.submission.score}/100\n${data.grade?.summary || ''}`);
                }
            } catch (e) {
                alert('Submission failed. Check network or terminal session.');
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}
</script>
@endsection
