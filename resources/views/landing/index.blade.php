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
                <span class="font-extrabold text-base sm:text-lg tracking-wider bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">
                    AMADEUS GDS SIM
                </span>
            </a>
        </div>

        <!-- Right Header Actions: Login / Dashboard -->
        <div class="flex items-center space-x-2.5 sm:space-x-3">
            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition-colors shadow-sm flex items-center gap-1.5">
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

                <!-- Action CTA -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                    <a href="{{ route('access-dashboard') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm tracking-wide transition-all shadow-md shadow-blue-600/20 flex items-center justify-center gap-2 group">
                        <span>Access Dashboard</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1: Altéa Cryptic Engine -->
                <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-6">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 font-mono font-bold text-lg select-none">
                            &gt;_
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-5 mb-2.5 tracking-tight">Altéa Cryptic Engine</h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                            True-to-life emulation of AN availability, SS segment sell, XE cancellation, SSR special meal assistance (VGML/WCHR), and IATA decoding (DAN/DAC/DNA).
                        </p>
                    </div>
                    <div class="space-y-1.5 pt-4 text-xs font-mono font-medium text-sky-600 border-t border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span>✓</span>
                            <span>AN, SS, NM, AP, TK, RF, ET</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span>✓</span>
                            <span>FXP Pricing &amp; TTP E-Tickets</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Cadet Learning Suite -->
                <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-6">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 select-none">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 13.5L12 18.16l8.55-4.66v3.84L12 22l-8.55-4.66v-3.84z"/></svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-5 mb-2.5 tracking-tight">Cadet Learning Suite</h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                            Drill scenarios covering multi-city, infant-in-lap, and schedule disruptions. Includes live syntax corrections, PNR quick inspect, and mistake audit history.
                        </p>
                    </div>
                    <div class="space-y-1.5 pt-4 text-xs font-mono font-medium text-indigo-600 border-t border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span>✓</span>
                            <span>Auto-Graded PNR Submissions</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span>✓</span>
                            <span>KPI Accuracy &amp; Efficiency Rings</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Instructor Control Hub -->
                <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-6">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 select-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2zM12 7a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-5 mb-2.5 tracking-tight">Instructor Control Hub</h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                            Supervise active cadets with live keystroke spectator mode, queue bank broadcasts (Q1, Q7, Q8, Q87), manual score overrides, and 1-click sandbox reset.
                        </p>
                    </div>
                    <div class="space-y-1.5 pt-4 text-xs font-mono font-medium text-emerald-600 border-t border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span>✓</span>
                            <span>Live Keystroke Spectator</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span>✓</span>
                            <span>Queue Simulation (Q1, 7, 87)</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Secure Architecture -->
                <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-6">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 select-none">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.5 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm-1.03 9.06a.75.75 0 011.06 0l2.25 2.25a.75.75 0 11-1.06 1.06L12 12.81l-1.72 1.72a.75.75 0 01-1.06-1.06l2.25-2.25z" clip-rule="evenodd"/></svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-5 mb-2.5 tracking-tight">Secure Architecture</h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                            Fast 4-step web setup wizard (/install), student approval gateway (/pending-approval), rate limiting (10/min auth, 60/min terminal), and batch provisioning of up to 500 cadets.
                        </p>
                    </div>
                    <div class="space-y-1.5 pt-4 text-xs font-mono font-medium text-amber-700 border-t border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span>✓</span>
                            <span>Web Installer (/install)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span>✓</span>
                            <span>Strict CSRF &amp; Rate Limits</span>
                        </div>
                    </div>
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
                <span class="font-bold tracking-wide bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">AMADEUS GDS SIM</span>
            </div>

            <!-- Center: Legal & Purpose -->
            <div class="text-[11px] text-slate-500 text-center">
                For educational & airline reservation training purposes only.
            </div>
        </div>
    </footer>

</body>
</html>
