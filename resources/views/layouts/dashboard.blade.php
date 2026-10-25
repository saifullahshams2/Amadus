<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Amadeus GDS Training Simulator')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --background: #070b14;
            --foreground: #e2e8f0;
        }
        html, body {
            height: 100%;
            color: var(--foreground);
            background: var(--background);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            overflow: hidden;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #080d1a;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
</head>
<body class="h-full flex bg-[#070b14] text-slate-200 antialiased selection:bg-blue-600 selection:text-white">

    <!-- Persistent Sidebar -->
    <aside class="w-64 bg-[#0a0f1d] border-r border-slate-800/80 flex flex-col shrink-0 z-30 select-none">
        <!-- Brand Header -->
        <div class="h-14 px-4 flex items-center justify-between border-b border-slate-800/80 bg-[#080d19]">
            <a href="/dashboard" class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-700 to-sky-500 flex items-center justify-center shadow-lg shadow-blue-500/20 text-white font-black text-sm tracking-tighter">
                    1A
                </div>
                <div>
                    <div class="text-xs font-black tracking-wider text-white uppercase flex items-center gap-1.5">
                        <span>AMADEUS GDS</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <div class="text-[10px] font-mono text-slate-400">TRAINING CONNECT</div>
                </div>
            </a>
        </div>

        <!-- Role Status & Account Badges -->
        @if(Auth::check() && Auth::user()->role === 'student')
            <!-- Student Account Status (Strict isolation - No switching to instructor) -->
            <div class="p-3 border-b border-slate-800/80 bg-[#0c1222]/60">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-400 mb-2">
                    <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-800/60 font-mono font-bold">STUDENT</span>
                </div>
                <div class="px-2.5 py-2 bg-[#070b14] rounded-lg border border-slate-800/80 text-[11px] text-slate-300 flex items-center justify-between font-mono">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Trainee Portal</span>
                    </div>
                    <span class="text-[10px] text-slate-500">SECURE</span>
                </div>
            </div>
        @elseif(Auth::check() && Auth::user()->role === 'instructor')
            <!-- Instructor Account Status (Dedicated Operations Hub - No student switcher) -->
            <div class="p-3 border-b border-slate-800/80 bg-[#0c1222]/60">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-400 mb-2">
                    <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-amber-950 text-amber-400 border border-amber-800/60 font-mono font-bold">INSTRUCTOR</span>
                </div>
                <div class="px-2.5 py-2 bg-[#070b14] rounded-lg border border-slate-800/80 text-[11px] text-amber-300 flex items-center justify-between font-mono">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        <span>Operations Hub</span>
                    </div>
                    <span class="text-[10px] text-slate-500">ADMIN</span>
                </div>
            </div>
        @elseif(!Auth::check())
            <!-- Active Perspective / Role Switcher for Guest/Dev Demo ONLY -->
            <div class="p-3 border-b border-slate-800/80 bg-[#0c1222]/60">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-400 mb-2">
                    <span class="uppercase tracking-wider">ACTIVE PERSPECTIVE</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-blue-950 text-blue-400 border border-blue-800/60 font-mono">DEV/DEMO</span>
                </div>
                <div class="grid grid-cols-2 gap-1.5 bg-[#070b14] p-1 rounded-lg border border-slate-800">
                    <form action="{{ route('dashboard.switch-role') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="role" value="student">
                        <button type="submit"
                                class="w-full py-1.5 px-2 rounded-md text-xs font-bold transition-all flex items-center justify-center gap-1.5 {{ ($activeRole ?? 'student') === 'student' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                            <span>Student</span>
                        </button>
                    </form>
                    <form action="{{ route('dashboard.switch-role') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="role" value="instructor">
                        <button type="submit"
                                class="w-full py-1.5 px-2 rounded-md text-xs font-bold transition-all flex items-center justify-center gap-1.5 {{ ($activeRole ?? 'student') === 'instructor' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span>Instructor</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Navigation Menu -->
        <nav class="flex-1 p-3 overflow-y-auto space-y-1 font-medium text-xs">
            @if(($activeRole ?? 'student') === 'student')
                <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-500 uppercase tracking-wider">TRAINEE DESK</div>
                <a href="#workspace" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Overview & Active PNR</span>
                </a>
                <a href="#drills" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Assignments & Drills</span>
                </a>
                <a href="#validator" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Cryptic Syntax Validator</span>
                </a>
                <a href="#analytics" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Learning Analytics</span>
                </a>
            @else
                <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-500 uppercase tracking-wider">COMMAND CENTER</div>
                <a href="#classroom" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Classroom & Spectate</span>
                </a>
                <a href="#students-enrollment" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    <span>Enroll Students</span>
                </a>
                <a href="#builder" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Scenario & Drill Builder</span>
                </a>
                <a href="#gradebook" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span>Submissions & Gradebook</span>
                </a>
                <a href="#queues" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Queue & Sandbox Manager</span>
                </a>
            @endif

            <div class="pt-4 px-2 pb-1 text-[10px] font-mono text-slate-500 uppercase tracking-wider">TERMINAL EMULATOR</div>
            <a href="/" class="flex items-center justify-between px-3 py-2 rounded-lg bg-blue-950/40 border border-blue-800/50 text-blue-300 hover:bg-blue-900/60 transition-colors group">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="font-bold">Cryptic Terminal</span>
                </div>
                <span class="text-[10px] bg-blue-600/40 px-1.5 py-0.5 rounded text-blue-200 font-mono">1A</span>
            </a>
        </nav>

        <!-- Active Terminal Status Widget -->
        <div class="p-3 border-t border-slate-800/80 bg-[#060a14] font-mono text-[11px] space-y-1.5">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[10px] uppercase">GDS SESSION:</span>
                <span class="text-emerald-400 font-bold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    ACTIVE
                </span>
            </div>
            <div class="flex items-center justify-between text-slate-400">
                <span>OFFICE:</span>
                <span class="text-white font-bold">{{ $user->office_id ?? 'LON1A0980' }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-400">
                <span>AGENT ID:</span>
                <span class="text-white font-bold">{{ $user->agent_id ?? '1234AB' }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-400">
                <span>WORK AREA:</span>
                <span class="text-sky-400 font-bold">AREA A</span>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="h-14 px-3.5 border-t border-slate-800/80 flex items-center justify-between bg-[#080d19]">
            <div class="flex items-center space-x-2.5 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-white uppercase">
                    {{ substr($user->username ?? 'U', 0, 2) }}
                </div>
                <div class="truncate">
                    <div class="text-xs font-bold text-white truncate">{{ $user->name ?? ($user->username ?? 'Guest') }}</div>
                    <div class="text-[10px] font-mono text-slate-400 truncate uppercase">{{ $user->role ?? 'student' }}</div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-950/40 rounded transition-colors" title="Sign Out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Workspace Area -->
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden bg-[#070b14]">
        <!-- Top Application Header -->
        <header class="h-14 px-6 border-b border-slate-800/80 bg-[#090e1c] flex items-center justify-between shrink-0">
            <!-- Left: View Title & Breadcrumb -->
            <div class="flex items-center space-x-3">
                <span class="px-2.5 py-1 rounded text-[11px] font-bold font-mono tracking-wider uppercase {{ ($activeRole ?? 'student') === 'instructor' ? 'bg-amber-950 text-amber-400 border border-amber-800/60' : 'bg-blue-950 text-blue-400 border border-blue-800/60' }}">
                    {{ ($activeRole ?? 'student') === 'instructor' ? 'INSTRUCTOR OPERATIONS HUB' : 'TRAINEE AGENT DESK' }}
                </span>
                <span class="text-slate-600 hidden sm:inline">•</span>
                <span class="text-xs text-slate-400 hidden sm:inline">
                    {{ ($activeRole ?? 'student') === 'instructor' ? 'Real-time Classroom Monitoring & Scenario Grading' : 'Cryptic Practice Terminal & PNR Management' }}
                </span>
            </div>

            <!-- Right: Quick Status Pills & Actions -->
            <div class="flex items-center space-x-3 text-xs">
                @if(($activeRole ?? 'student') === 'student' && !empty($activePnr))
                    <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 bg-slate-900 border border-slate-700/80 rounded-md font-mono text-[11px]">
                        <span class="text-slate-400">ACTIVE PNR:</span>
                        <span class="font-bold text-sky-400">{{ $activePnr->record_locator }}</span>
                    </div>
                @endif

                <a href="/" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-xs transition-colors shadow-md shadow-blue-600/20">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Launch Terminal</span>
                </a>
            </div>
        </header>

        <!-- View Content Wrapper -->
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
