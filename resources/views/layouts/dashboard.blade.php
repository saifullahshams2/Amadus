<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Amadus')</title>
    <link rel="icon" type="image/png" href="{{ asset('airplane-ticket.png') }}">
    <link rel="shortcut icon" href="{{ asset('airplane-ticket.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --background: #f8fafc;
            --foreground: #0f172a;
        }
        [x-cloak] { display: none !important; }
        html, body {
            height: 100%;
            color: var(--foreground);
            background: var(--background);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
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
<body x-data="{ mobileSidebarOpen: false }"
      @keydown.window.escape="mobileSidebarOpen = false"
      class="h-full flex bg-[#f8fafc] text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- Persistent Sidebar (Desktop >= xl: 1280px) -->
    <aside class="hidden xl:flex xl:flex-col w-64 bg-white border-r border-slate-200 shrink-0 z-30 select-none shadow-sm">
        <!-- Brand Header -->
        <div class="h-14 px-4 flex items-center justify-between border-b border-slate-200 bg-white">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                <div>
                    <div class="text-xs font-black tracking-wider uppercase flex items-center gap-1.5">
                        <span class="bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">Amadus</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Account Status -->
        <div class="p-3 border-b border-slate-200 bg-slate-50">
            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2">
                <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-mono font-bold">USER</span>
            </div>
            <div class="px-2.5 py-2 bg-white rounded-lg border border-slate-200 text-[11px] text-slate-700 flex items-center justify-between font-mono shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Agent Workspace</span>
                </div>
                <span class="text-[10px] text-slate-400">SECURE</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-3 overflow-y-auto space-y-1 font-medium text-xs">
            <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-400 uppercase tracking-wider">AGENT DESK</div>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ ($currentSection ?? 'dashboard') === 'dashboard' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dashboard.section', 'workspace') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ ($currentSection ?? '') === 'workspace' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Overview & Active PNR</span>
            </a>

            <a href="{{ route('dashboard.section', 'drills') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ ($currentSection ?? '') === 'drills' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <span>Assignments & Drills</span>
            </a>

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
                    <div class="text-[10px] font-mono text-slate-500 truncate uppercase">{{ $user->username ?? 'user' }}</div>
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

    <!-- Mobile & Tablet Sidebar Drawer -->
    <div x-show="mobileSidebarOpen"
         x-cloak
         class="fixed inset-0 z-50 xl:hidden"
         role="dialog"
         aria-modal="true">

        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileSidebarOpen = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
             aria-hidden="true"></div>

        <div class="fixed inset-y-0 right-0 flex max-w-full">
            <div x-show="mobileSidebarOpen"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 @click.stop
                 class="w-[80vw] max-w-[80vw] bg-white h-full shadow-2xl flex flex-col justify-between overflow-hidden border-l border-slate-200 select-none relative">

                <div class="h-14 px-4 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
                    <a href="{{ route('dashboard') }}" @click="mobileSidebarOpen = false" class="flex items-center space-x-2">
                        <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-7 h-7 object-contain">
                        <div class="text-xs font-black tracking-wider uppercase flex items-center gap-1">
                            <span class="bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">Amadus</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                    </a>

                    <button type="button"
                            @click="mobileSidebarOpen = false"
                            class="p-2 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                            aria-label="Close sidebar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto flex flex-col">
                    <div class="p-3 border-b border-slate-200 bg-slate-50">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                            <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-mono font-bold">USER</span>
                        </div>
                        <div class="px-2.5 py-2 bg-white rounded-lg border border-slate-200 text-[11px] text-slate-700 flex items-center justify-between font-mono shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Agent Workspace</span>
                            </div>
                            <span class="text-[10px] text-slate-400">ONLINE</span>
                        </div>
                    </div>

                    <nav class="p-3 space-y-1 font-medium text-xs">
                        <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-400 uppercase tracking-wider">AGENT DESK</div>

                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ ($currentSection ?? 'dashboard') === 'dashboard' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('dashboard.section', 'workspace') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ ($currentSection ?? '') === 'workspace' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            <span>Overview & Active PNR</span>
                        </a>

                        <a href="{{ route('dashboard.section', 'drills') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ ($currentSection ?? '') === 'drills' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            <span>Assignments & Drills</span>
                        </a>

                        <div class="pt-2 border-t border-slate-200 mt-2">
                            <a href="{{ route('terminal.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-blue-600 text-white font-bold shadow-sm hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Launch Practice Terminal</span>
                            </a>
                        </div>
                    </nav>

                    <div class="p-3 border-t border-slate-200 bg-slate-50 font-mono text-[11px] space-y-1.5 mt-auto">
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
                    </div>
                </div>

                <div class="h-14 px-3.5 border-t border-slate-200 flex items-center justify-between bg-white shrink-0">
                    <div class="flex items-center space-x-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 uppercase">
                            {{ substr($user->username ?? 'U', 0, 2) }}
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-800 truncate">{{ $user->name ?? ($user->username ?? 'Guest') }}</div>
                            <div class="text-[10px] font-mono text-slate-500 truncate uppercase">{{ $user->username ?? 'user' }}</div>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors" title="Sign Out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace Area -->
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden bg-[#f8fafc]">
        <!-- Top Application Header -->
        <header class="h-14 px-3 sm:px-6 border-b border-slate-200 bg-white flex items-center justify-between shrink-0 shadow-sm z-20">
            <div class="flex items-center space-x-2.5 xl:hidden">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs sm:text-sm font-black tracking-wider uppercase bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">
                            Amadus
                        </span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                </a>
            </div>

            <div class="hidden xl:flex items-center space-x-3">
                <span class="px-2.5 py-1 rounded text-[11px] font-bold font-mono tracking-wider uppercase bg-blue-50 text-blue-700 border border-blue-200">
                    AGENT DESK
                </span>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-slate-500">
                    Cryptic Practice Terminal & PNR Management
                </span>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-3 text-xs">
                @if(!empty($activePnr))
                    <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-md font-mono text-[11px]">
                        <span class="text-slate-500">ACTIVE PNR:</span>
                        <span class="font-bold text-blue-600">{{ $activePnr->record_locator }}</span>
                    </div>
                @endif

                <a href="{{ route('terminal.index') }}" class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs transition-colors shadow-sm" title="Launch Terminal">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="hidden sm:inline">Launch Terminal</span>
                </a>

                <button type="button"
                        @click="mobileSidebarOpen = true"
                        class="xl:hidden p-2 rounded-lg text-slate-700 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="Open navigation menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-3 sm:p-5 lg:p-6">
            @yield('content')
        </main>
    </div>

    @include('partials.dynamic-popup')
</body>
</html>
