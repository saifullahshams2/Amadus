<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Profile - Amadus')</title>
    <link rel="icon" type="image/png" href="{{ asset('airplane-ticket.png') }}">
    <link rel="shortcut icon" href="{{ asset('airplane-ticket.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        html, body {
            min-height: 100%;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body class="h-full flex flex-col bg-[#f8fafc] text-slate-800 antialiased selection:bg-blue-600 selection:text-white">
<div x-data="{ mobileNavOpen: false }" class="flex flex-1 h-screen overflow-hidden">

    <!-- Desktop Sidebar -->
    <aside class="hidden xl:flex xl:flex-col w-64 bg-white border-r border-slate-200 shrink-0 z-30 select-none shadow-sm h-full">
        <!-- Brand Header -->
        <div class="h-14 px-4 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <div class="text-xs font-black tracking-wider uppercase">
                    <span class="bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">Amadus</span>
                </div>
            </a>
        </div>

        <!-- Profile Navigation -->
        <nav class="flex-1 p-3 overflow-y-auto space-y-1 font-medium text-xs">
            <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-400 uppercase tracking-wider">NAVIGATION</div>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors text-slate-700 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>

            <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-400 uppercase tracking-wider">MY PROFILE</div>

            <a href="{{ route('profile.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ ($profileSection ?? '') === 'index' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span>Profile Overview</span>
            </a>

            <a href="{{ route('profile.password') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ ($profileSection ?? '') === 'password' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span>Change Password</span>
            </a>

            @if ($isOwner ?? false)
            <a href="{{ route('profile.smtp') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ ($profileSection ?? '') === 'smtp' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span>SMTP Settings</span>
            </a>
            @endif
        </nav>

        <!-- Profile Button + Active Terminal Status Widget -->
        <div class="p-3 border-t border-slate-200 bg-slate-50 shrink-0 space-y-2">
            <a href="{{ route('profile.index') }}" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 text-xs font-bold transition-colors shadow-xs">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span>My Profile</span>
                <span class="ml-auto text-[10px] font-mono text-slate-400">EDIT</span>
            </a>

            <div class="font-mono text-[11px] space-y-1.5 pt-1">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-[10px] uppercase">GDS SESSION:</span>
                    <span class="text-emerald-600 font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        ACTIVE
                    </span>
                </div>
            </div>
        </div>

        <!-- User Footer -->
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
    </aside>

    <!-- Mobile Drawer -->
    <div x-show="mobileNavOpen" x-cloak class="fixed inset-0 z-50 xl:hidden" role="dialog" aria-modal="true">
        <div x-show="mobileNavOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileNavOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" aria-hidden="true"></div>

        <div class="fixed inset-y-0 right-0 flex max-w-full">
            <div x-show="mobileNavOpen" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" @click.stop class="w-[80vw] max-w-[80vw] bg-white h-full shadow-2xl flex flex-col justify-between overflow-hidden border-l border-slate-200 select-none relative">
                <div class="h-14 px-4 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-7 h-7 object-contain">
                        <span class="text-xs font-black tracking-wider uppercase bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Amadus</span>
                    </div>
                    <button type="button" @click="mobileNavOpen = false" class="p-2 text-slate-500 hover:text-slate-800" aria-label="Close menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <nav class="flex-1 p-3 overflow-y-auto space-y-1 font-medium text-xs">
            <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-400 uppercase tracking-wider">NAVIGATION</div>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors text-slate-700 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>

            <div class="px-2 pt-2 pb-1 text-[10px] font-mono text-slate-400 uppercase tracking-wider">MY PROFILE</div>

                    <a href="{{ route('profile.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ ($profileSection ?? '') === 'index' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                        <span>Profile Overview</span>
                    </a>
                    <a href="{{ route('profile.password') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ ($profileSection ?? '') === 'password' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                        <span>Change Password</span>
                    </a>
                    @if ($isOwner ?? false)
                    <a href="{{ route('profile.smtp') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ ($profileSection ?? '') === 'smtp' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200 shadow-xs' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                        <span>SMTP Settings</span>
                    </a>
                    @endif
                </nav>

                <div class="p-3 border-t border-slate-200 bg-slate-50 shrink-0 space-y-2">
                    <a href="{{ route('profile.index') }}" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-blue-50 hover:text-blue-700 text-xs font-bold transition-colors shadow-xs">
                        <span>My Profile</span>
                    </a>
                    <div class="font-mono text-[11px] space-y-1.5 pt-1">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="text-[10px] uppercase">GDS SESSION:</span>
                            <span class="text-emerald-600 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                ACTIVE
                            </span>
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

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden bg-[#f8fafc]">
        <header class="h-14 px-3 sm:px-6 border-b border-slate-200 bg-white flex items-center justify-between shrink-0 shadow-sm z-20">
            <div class="flex items-center space-x-3 min-w-0">
                <button type="button" @click="mobileNavOpen = true" class="xl:hidden p-2 rounded-lg text-slate-700 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition-colors" aria-label="Open profile menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-sm font-black text-slate-900 tracking-wide truncate">@yield('profile_title', 'My Profile')</h1>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 font-mono truncate">@yield('profile_subtitle', 'Manage your account settings')</p>
                </div>
            </div>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-lg font-bold text-xs transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
        </header>

        <main class="flex-1 overflow-y-auto p-3 sm:p-5 lg:p-6">
            @if (session('status'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $error }}
                        </div>
                    @endforeach
                </div>
            @endif

            @yield('profile_content')
        </main>
    </div>
</div>

@include('partials.dynamic-popup')
</body>
</html>
