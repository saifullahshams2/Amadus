<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AMADEUS GDS SIM')</title>
    <link rel="icon" type="image/png" href="{{ asset('airplane-ticket.png') }}">
    <link rel="shortcut icon" href="{{ asset('airplane-ticket.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --background: #f8fafc;
            --foreground: #0f172a;
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
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-full flex bg-[#f8fafc] text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- Persistent Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col shrink-0 z-30 select-none shadow-sm">
        <!-- Brand Header -->
        <div class="h-14 px-4 flex items-center justify-between border-b border-slate-200 bg-white">
            <a href="/dashboard" class="flex items-center space-x-2.5">
                <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                <div>
                    <div class="text-xs font-black tracking-wider uppercase flex items-center gap-1.5">
                        <span class="bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">AMADEUS GDS SIM</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Role Status & Account Badges -->
        @if(Auth::check() && Auth::user()->role === 'student')
            <!-- Student Account Status (Strict isolation - No switching to instructor) -->
            <div class="p-3 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2">
                    <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-mono font-bold">STUDENT</span>
                </div>
                <div class="px-2.5 py-2 bg-white rounded-lg border border-slate-200 text-[11px] text-slate-700 flex items-center justify-between font-mono shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Trainee Portal</span>
                    </div>
                    <span class="text-[10px] text-slate-400">SECURE</span>
                </div>
            </div>
        @elseif(Auth::check() && Auth::user()->role === 'instructor')
            <!-- Instructor Account Status (Dedicated Operations Hub - No student switcher) -->
            <div class="p-3 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2">
                    <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono font-bold">INSTRUCTOR</span>
                </div>
                <div class="px-2.5 py-2 bg-white rounded-lg border border-slate-200 text-[11px] text-amber-800 flex items-center justify-between font-mono shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Operations Hub</span>
                    </div>
                    <span class="text-[10px] text-slate-400">ADMIN</span>
                </div>
            </div>
        @endif

        <!-- Navigation Menu -->
        <nav class="flex-1 p-3 overflow-y-auto space-y-1 font-medium text-xs">
            @if(($activeRole ?? 'student') === 'student')
                <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-400 uppercase tracking-wider">TRAINEE DESK</div>
                <a href="#workspace" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Overview & Active PNR</span>
                </a>
                <a href="#drills" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Assignments & Drills</span>
                </a>
                <a href="#validator" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Cryptic Syntax Validator</span>
                </a>
                <a href="#analytics" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Learning Analytics</span>
                </a>
            @else
                <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-400 uppercase tracking-wider">COMMAND CENTER</div>
                <a href="#classroom" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Classroom & Spectate</span>
                </a>
                <a href="#students-enrollment" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    <span>Enroll Students</span>
                </a>
                <a href="#builder" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Scenario & Drill Builder</span>
                </a>
                <a href="#gradebook" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span>Submissions & Gradebook</span>
                </a>
                <a href="#queues" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Queue & Sandbox Manager</span>
                </a>
            @endif
        </nav>

        <!-- Active Terminal Status Widget -->
        <div class="p-3 border-t border-slate-200 bg-slate-50 font-mono text-[11px] space-y-1.5">
            <div class="flex items-center justify-between text-slate-600">
                <span class="text-[10px] uppercase">GDS SESSION:</span>
                <span class="text-emerald-600 font-bold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    ACTIVE
                </span>
            </div>
            <div class="flex items-center justify-between text-slate-600">
                <span>OFFICE:</span>
                <span class="text-slate-900 font-bold">{{ $user->office_id ?? 'LON1A0980' }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-600">
                <span>AGENT ID:</span>
                <span class="text-slate-900 font-bold">{{ $user->agent_id ?? '1234AB' }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-600">
                <span>WORK AREA:</span>
                <span class="text-blue-600 font-bold">AREA A</span>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="h-14 px-3.5 border-t border-slate-200 flex items-center justify-between bg-white">
            <div class="flex items-center space-x-2.5 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 uppercase">
                    {{ substr($user->username ?? 'U', 0, 2) }}
                </div>
                <div class="truncate">
                    <div class="text-xs font-bold text-slate-800 truncate">{{ $user->name ?? ($user->username ?? 'Guest') }}</div>
                    <div class="text-[10px] font-mono text-slate-500 truncate uppercase">{{ $user->role ?? 'student' }}</div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors" title="Sign Out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Workspace Area -->
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden bg-[#f8fafc]">
        @if(session('impersonated_by_instructor'))
            <div class="bg-amber-500 text-white px-6 py-2.5 text-xs flex items-center justify-between shadow-md shrink-0 z-30">
                <div class="flex items-center space-x-2 font-mono">
                    <svg class="w-4 h-4 text-white shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><strong>INSTRUCTOR MODE:</strong> Viewing cadet portal as <strong>{{ $user->username ?? Auth::user()->username }}</strong> ({{ $user->name ?? '' }}).</span>
                </div>
                <form action="{{ route('impersonation.exit') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-white text-amber-900 hover:bg-amber-100 rounded-md text-xs font-bold font-sans transition-colors shadow-xs flex items-center gap-1">
                        <span>Exit to Instructor Hub</span>
                        <span>&rarr;</span>
                    </button>
                </form>
            </div>
        @endif

        <!-- Top Application Header -->
        <header class="h-14 px-6 border-b border-slate-200 bg-white flex items-center justify-between shrink-0 shadow-sm">
            <!-- Left: View Title & Breadcrumb -->
            <div class="flex items-center space-x-3">
                <span class="px-2.5 py-1 rounded text-[11px] font-bold font-mono tracking-wider uppercase {{ ($activeRole ?? 'student') === 'instructor' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                    {{ ($activeRole ?? 'student') === 'instructor' ? 'INSTRUCTOR OPERATIONS HUB' : 'TRAINEE AGENT DESK' }}
                </span>
                <span class="text-slate-300 hidden sm:inline">•</span>
                <span class="text-xs text-slate-500 hidden sm:inline">
                    {{ ($activeRole ?? 'student') === 'instructor' ? 'Real-time Classroom Monitoring & Scenario Grading' : 'Cryptic Practice Terminal & PNR Management' }}
                </span>
            </div>

            <!-- Right: Quick Status Pills & Actions -->
            <div class="flex items-center space-x-3 text-xs">
                @if(($activeRole ?? 'student') === 'student' && !empty($activePnr))
                    <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-md font-mono text-[11px]">
                        <span class="text-slate-500">ACTIVE PNR:</span>
                        <span class="font-bold text-blue-600">{{ $activePnr->record_locator }}</span>
                    </div>
                @endif

                <a href="{{ route('terminal.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs transition-colors shadow-sm">
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
