<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMADEUS GDS SIM</title>
    <link rel="icon" type="image/png" href="{{ asset('airplane-ticket.png') }}">
    <link rel="shortcut icon" href="{{ asset('airplane-ticket.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --bg-base: #f8fafc;
            --bg-surface: #ffffff;
        }
        body {
            background-color: var(--bg-base);
            color: #1e293b;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .crt-glow {
            text-shadow: 0 0 8px rgba(52, 211, 153, 0.6);
        }
        .crt-amber-glow {
            text-shadow: 0 0 8px rgba(251, 191, 36, 0.6);
        }
    </style>
</head>
<body class="min-h-screen w-full flex flex-col justify-between bg-[#f8fafc] text-slate-800 overflow-x-hidden selection:bg-blue-600 selection:text-white">

    <!-- Ambient Subtle Glows -->
    <div class="fixed inset-0 pointer-events-none opacity-40 overflow-hidden">
        <div class="absolute -top-32 -left-32 w-[32rem] h-[32rem] bg-blue-100 rounded-full blur-[128px]"></div>
        <div class="absolute top-1/2 -right-32 w-[28rem] h-[28rem] bg-indigo-100 rounded-full blur-[128px]"></div>
        <div class="absolute -bottom-32 left-1/3 w-[30rem] h-[30rem] bg-sky-100 rounded-full blur-[128px]"></div>
    </div>

    <!-- 1. Header (Sticky Top, Full-Width Responsive) -->
    <header class="w-full bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50 px-4 sm:px-8 py-3.5 flex items-center justify-between shrink-0 shadow-sm">
        <!-- Brand -->
        <div class="flex items-center space-x-3">
            <a href="/" class="flex items-center space-x-2.5 group">
                <img src="{{ asset('airplane-ticket.png') }}" alt="AMADEUS GDS SIM" class="w-8 h-8 object-contain group-hover:scale-105 transition-transform">
                <span class="font-extrabold text-base sm:text-lg tracking-wider text-slate-900">
                    AMADEUS GDS SIM
                </span>
            </a>
        </div>

        <!-- Right Header Actions: Login / Dashboard -->
        <div class="flex items-center space-x-2.5 sm:space-x-3">
            @auth
                <a href="/dashboard" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Dashboard</span>
                </a>
            @else
                <a href="/login" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>Login</span>
                </a>
            @endauth
        </div>
    </header>

    <!-- 2. Hero Section (100% Responsive, High-Impact) -->
    <main class="flex-1 flex flex-col justify-center px-4 sm:px-8 py-10 sm:py-16 relative z-10 max-w-7xl mx-auto w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Column: Copy & CTAs -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                    <span>Altéa GDS Cryptic Emulation & Classroom Simulator</span>
                </div>

                <!-- Main Title -->
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-[1.15]">
                    Master Global Distribution in <span class="bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">Cryptic Real-Time</span>.
                </h1>

                <!-- Subtitle -->
                <p class="text-sm sm:text-base text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    Designed for flight academies, travel agencies, and aviation training programs. Practice live Amadeus cryptic entries, enforce mandatory <strong class="text-slate-900">P.R.I.N.T.</strong> booking standards, handle simulated queue banks, and spectate cadet keystrokes in real-time.
                </p>

                <!-- Action CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                    <a href="/login" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm tracking-wide transition-all shadow-md shadow-blue-600/20 flex items-center justify-center gap-2 group">
                        <span>Access Terminal & Dashboard</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>

                    <a href="/register" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        <span>Register as Trainee Cadet</span>
                    </a>

                    <a href="/terminal" class="w-full sm:w-auto px-4 py-3 rounded-xl hover:bg-slate-100 text-slate-600 hover:text-slate-900 text-xs font-semibold transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>Cryptic Terminal Mode</span>
                    </a>
                </div>

                <!-- Live Metrics Ticker -->
                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-slate-200 max-w-md mx-auto lg:mx-0 text-left">
                    <div class="p-2.5 rounded-lg bg-white border border-slate-200 shadow-sm">
                        <div class="text-[10px] font-mono text-slate-500 uppercase">OFFICE ID</div>
                        <div class="text-xs sm:text-sm font-bold font-mono text-blue-600">LON1A0980</div>
                    </div>
                    <div class="p-2.5 rounded-lg bg-white border border-slate-200 shadow-sm">
                        <div class="text-[10px] font-mono text-slate-500 uppercase">SYNTAX ENGINE</div>
                        <div class="text-xs sm:text-sm font-bold font-mono text-emerald-600">1A ALTÉA</div>
                    </div>
                    <div class="p-2.5 rounded-lg bg-white border border-slate-200 shadow-sm">
                        <div class="text-[10px] font-mono text-slate-500 uppercase">ACCESS GATE</div>
                        <div class="text-xs sm:text-sm font-bold font-mono text-amber-600">INSTRUCTOR GATED</div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Terminal Preview Screen -->
            <div class="lg:col-span-5" x-data="{ screenMode: 'modern' }">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden relative">
                    <!-- Terminal Top Bar -->
                    <div class="bg-slate-100 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-rose-400 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>
                            <span class="ml-2 font-mono text-[11px] text-slate-600 font-semibold">AREA A &bull; 1A WORKSTATION</span>
                        </div>
                        <div class="flex items-center gap-1 font-mono text-[10px]">
                            <button @click="screenMode = 'modern'" :class="screenMode === 'modern' ? 'bg-white text-blue-700 font-bold border-slate-300 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="px-2 py-0.5 rounded border border-transparent transition-colors">MODERN</button>
                            <button @click="screenMode = 'green'" :class="screenMode === 'green' ? 'bg-emerald-950 text-emerald-400 border-emerald-800' : 'text-slate-600 hover:text-emerald-700'" class="px-2 py-0.5 rounded border border-transparent transition-colors">CRT GREEN</button>
                            <button @click="screenMode = 'amber'" :class="screenMode === 'amber' ? 'bg-amber-950 text-amber-400 border-amber-800' : 'text-slate-600 hover:text-amber-700'" class="px-2 py-0.5 rounded border border-transparent transition-colors">AMBER</button>
                        </div>
                    </div>

                    <!-- Monospaced Terminal Output Simulation -->
                    <div :class="{
                            'bg-black text-emerald-400 crt-glow': screenMode === 'green',
                            'bg-black text-amber-400 crt-amber-glow': screenMode === 'amber',
                            'bg-slate-50 text-slate-800': screenMode === 'modern'
                         }"
                         class="p-4 sm:p-5 font-mono text-xs leading-relaxed overflow-x-auto space-y-2 select-none h-80 flex flex-col justify-between">
                        
                        <div class="space-y-1.5">
                            <div><span :class="screenMode === 'modern' ? 'text-blue-600 font-bold' : 'text-sky-400 font-bold'">> AN15DECLHRJFK/ABA</span></div>
                            <div class="opacity-90">
                                15DEC-TH LHRJFK 1 BA 117 J9 C9 D9 I9 W9 E9 T9 Y9 B9 H9 K9 M9 L9 V9 S9 N9 Q9 O9 G9<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LHR 0825 JFK 1130 77W 0 /E<br>
                                2 BA 175 J9 C9 D9 I9 W9 E9 T9 Y9 B9 H9 K9 M9 L9 V9 S9 N9 Q9 O9 G9<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LHR 1020 JFK 1325 77W 0 /E
                            </div>
                            <div class="pt-1"><span :class="screenMode === 'modern' ? 'text-blue-600 font-bold' : 'text-sky-400 font-bold'">> SS1Y1</span></div>
                            <div class="opacity-90">1 BA 117 Y 15DEC LHRJFK HK1 0825 1130 /DCBA*X9876 /E</div>
                            <div class="pt-1"><span :class="screenMode === 'modern' ? 'text-blue-600 font-bold' : 'text-sky-400 font-bold'">> NM1SMITH/JOHN MR</span></div>
                            <div class="opacity-90">1.1SMITH/JOHN MR</div>
                            <div class="pt-1"><span :class="screenMode === 'modern' ? 'text-blue-600 font-bold' : 'text-sky-400 font-bold'">> FXP &amp;&amp; ET</span></div>
                            <div :class="screenMode === 'modern' ? 'text-emerald-700 font-bold' : 'text-emerald-300 font-bold'">--- TST 001 STORED &bull; FARE: GBP 420.00 &bull; RLOC: Z8P3X9 ---</div>
                        </div>

                        <!-- Active Command Prompt -->
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-[11px] opacity-80" :class="screenMode === 'modern' ? 'text-slate-600' : 'text-slate-400 border-slate-800'">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-blue-600">&gt;</span>
                                <span class="animate-pulse font-bold">QS_</span>
                            </div>
                            <span class="text-[10px]">QUEUE STATUS: Q1(3) Q7(2) Q8(0) Q87(1)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Core Feature Highlights Grid -->
        <section id="features" class="mt-16 sm:mt-24 space-y-8">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-xs font-mono uppercase text-blue-600 font-bold tracking-widest">ENTERPRISE AVIATION CAPABILITIES</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Everything Required for Airline & Agency Certification</h2>
                <p class="text-xs sm:text-sm text-slate-600">Complete Amadeus Altéa command processing integrated with instructor-guided learning.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Feature 1 -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-slate-300 hover:shadow-md transition-all space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Cryptic Command Engine</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Full fidelity parser for availability (AN), segment selling (SS), passenger names (NM), pricing (FXP), and ticketing (TTP) with authentic error codes.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-slate-300 hover:shadow-md transition-all space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Mandatory P.R.I.N.T. Compliance</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Interactive readiness checklist ensuring Phone (AP), Received From (RF), Itinerary (SS), Name (NM), and Ticketing (TK) are satisfied prior to booking close.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-slate-300 hover:shadow-md transition-all space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Simulated GDS Queues</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Simulate live airline operational messages on Queue 1 (TTL), Queue 7 (Schedule Changes), Queue 8 (Waitlists), and Queue 87 (Exchanges).
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-slate-300 hover:shadow-md transition-all space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Instructor Approval Gate</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Public registrations require instructor authorization before simulator access is activated. Real-time keystroke spectating and drill grading.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <!-- 4. Footer (Full-Width Responsive) -->
    <footer class="w-full bg-white border-t border-slate-200 px-4 sm:px-8 py-6 text-xs text-slate-500 relative z-10 shrink-0">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Left: Copyright and status -->
            <div class="flex items-center space-x-2 text-center md:text-left">
                <img src="{{ asset('airplane-ticket.png') }}" alt="AMADEUS GDS SIM" class="w-5 h-5 object-contain">
                <span class="font-bold text-slate-800 tracking-wide">AMADEUS GDS SIM</span>
            </div>

            <!-- Center: Legal & Purpose -->
            <div class="text-[11px] text-slate-500 text-center">
                For educational & airline reservation training purposes only.
            </div>
        </div>
    </footer>

</body>
</html>
