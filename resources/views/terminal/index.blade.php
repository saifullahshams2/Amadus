@extends('layouts.app')

@section('title', 'AMADEUS GDS SIM')
@section('body_class', 'h-screen overflow-hidden')

@section('content')
<div x-data="amadeusApp()"
     x-init="init()"
     @keydown.window.escape="mobileSidebarOpen = false"
     class="flex-1 flex h-full overflow-hidden bg-[#f8fafc]">

    <!-- Persistent Desktop Sidebar (Desktop >= xl: 1280px) -->
    <aside class="hidden xl:flex xl:flex-col w-64 bg-white border-r border-slate-200 shrink-0 z-30 select-none shadow-sm h-full">
        <!-- Brand Header -->
        <div class="h-14 px-4 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                <div>
                    <div class="text-xs font-black tracking-wider uppercase flex items-center gap-1.5">
                        <span class="bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">AMADEUS SIM</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Role Status & Account Badges -->
        @if(Auth::check() && Auth::user()->role === 'student')
            <div class="p-3 border-b border-slate-200 bg-slate-50 shrink-0">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                    <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-mono font-bold">STUDENT</span>
                </div>
                <div class="px-2.5 py-1.5 bg-white rounded-lg border border-slate-200 text-[11px] text-slate-700 flex items-center justify-between font-mono shadow-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Trainee Terminal</span>
                    </div>
                    <span class="text-[10px] text-slate-400">ONLINE</span>
                </div>
            </div>
        @elseif(Auth::check() && Auth::user()->role === 'instructor')
            <div class="p-3 border-b border-slate-200 bg-slate-50 shrink-0">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                    <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono font-bold">INSTRUCTOR</span>
                </div>
                <div class="px-2.5 py-1.5 bg-white rounded-lg border border-slate-200 text-[11px] text-amber-800 flex items-center justify-between font-mono shadow-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Instructor Console</span>
                    </div>
                    <span class="text-[10px] text-slate-400">ADMIN</span>
                </div>
            </div>
        @endif

        <!-- Scrollable Navigation and Controls in Desktop Sidebar -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
            <!-- Dashboard Return Button (Top of Menu) -->
            <div class="p-3">
                <a href="{{ route('dashboard') }}"
                   class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- 1. All Terminal Type Section (Moved to sidebar menu) -->
            <div class="p-3 bg-slate-50/60">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2">
                    <span class="uppercase tracking-wider font-mono">TERMINAL TYPE</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-bold"
                          :class="{
                              'bg-blue-100 text-blue-700': theme === 'modern',
                              'bg-emerald-100 text-emerald-800': theme === 'crt-green',
                              'bg-amber-100 text-amber-800': theme === 'crt-amber'
                          }"
                          x-text="theme === 'modern' ? 'MODERN' : (theme === 'crt-green' ? 'GREEN CRT' : 'AMBER CRT')">
                    </span>
                </div>

                <div class="space-y-1.5">
                    <!-- Modern GUI -->
                    <button type="button"
                            @click="theme = 'modern'"
                            :class="theme === 'modern' ? 'bg-white border-blue-500 shadow-xs ring-1 ring-blue-500 text-blue-900 font-semibold' : 'bg-white/90 border-slate-200 text-slate-700 hover:bg-white hover:border-slate-300'"
                            class="w-full text-left p-2 rounded-lg border transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-slate-100 border border-slate-300 flex items-center justify-center text-blue-600 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold leading-tight">Modern Web</div>
                                <div class="text-[10px] text-slate-400 font-mono">High-contrast GUI</div>
                            </div>
                        </div>
                        <span x-show="theme === 'modern'" class="w-2 h-2 rounded-full bg-blue-600"></span>
                    </button>

                    <!-- Green CRT -->
                    <button type="button"
                            @click="theme = 'crt-green'"
                            :class="theme === 'crt-green' ? 'bg-emerald-950 border-emerald-500 shadow-xs ring-1 ring-emerald-500 text-emerald-300 font-semibold' : 'bg-white/90 border-slate-200 text-slate-700 hover:bg-white hover:border-slate-300'"
                            class="w-full text-left p-2 rounded-lg border transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-black border border-emerald-700 flex items-center justify-center text-emerald-400 shrink-0">
                                <span class="font-mono text-[10px] font-bold">&gt;_</span>
                            </div>
                            <div>
                                <div class="text-xs font-bold leading-tight" :class="theme === 'crt-green' ? 'text-emerald-300' : 'text-slate-800'">Classic CRT Green</div>
                                <div class="text-[10px] font-mono" :class="theme === 'crt-green' ? 'text-emerald-400/80' : 'text-slate-400'">Phosphor P1 80-col</div>
                            </div>
                        </div>
                        <span x-show="theme === 'crt-green'" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </button>

                    <!-- Amber CRT -->
                    <button type="button"
                            @click="theme = 'crt-amber'"
                            :class="theme === 'crt-amber' ? 'bg-amber-950 border-amber-500 shadow-xs ring-1 ring-amber-500 text-amber-300 font-semibold' : 'bg-white/90 border-slate-200 text-slate-700 hover:bg-white hover:border-slate-300'"
                            class="w-full text-left p-2 rounded-lg border transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-black border border-amber-700 flex items-center justify-center text-amber-400 shrink-0">
                                <span class="font-mono text-[10px] font-bold">&gt;_</span>
                            </div>
                            <div>
                                <div class="text-xs font-bold leading-tight" :class="theme === 'crt-amber' ? 'text-amber-300' : 'text-slate-800'">Classic CRT Amber</div>
                                <div class="text-[10px] font-mono" :class="theme === 'crt-amber' ? 'text-amber-400/80' : 'text-slate-400'">Phosphor P3 80-col</div>
                            </div>
                        </div>
                        <span x-show="theme === 'crt-amber'" class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    </button>
                </div>
            </div>

            <!-- 2. Work Area Switcher -->
            <div class="p-3">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2">
                    <span class="uppercase tracking-wider font-mono">WORK AREA</span>
                    <span class="text-[10px] font-mono font-bold text-blue-600" x-text="'ACTIVE: AREA ' + activeArea"></span>
                </div>
                <div class="grid grid-cols-6 gap-1">
                    <template x-for="a in ['A', 'B', 'C', 'D', 'E', 'F']" :key="a">
                        <button type="button"
                                @click="selectArea(a)"
                                :class="activeArea === a ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="h-7 rounded flex items-center justify-center font-mono text-xs transition-colors"
                                x-text="a">
                        </button>
                    </template>
                </div>
            </div>

            <!-- 3. Terminal Controls -->
            <div class="p-3 space-y-1.5">
                <div class="text-[10px] font-mono text-slate-400 uppercase tracking-wider mb-1">TERMINAL UTILITIES</div>

                <button type="button"
                        @click="showVisualInspector = !showVisualInspector"
                        :class="showVisualInspector ? 'bg-blue-50 border-blue-200 text-blue-700 font-semibold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg border text-xs transition-colors shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <span>PNR Visual Inspector</span>
                    </div>
                    <span class="text-[10px] font-mono font-bold" x-text="showVisualInspector ? 'ON' : 'OFF'"></span>
                </button>

                <button type="button"
                        @click="clearTerminal()"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 text-xs transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Clear Terminal Screen</span>
                </button>

                <button type="button"
                        @click="executeCommand('HE')"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 text-xs transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Display Help (HE)</span>
                </button>
            </div>
        </div>

        <!-- Active Terminal Status Widget -->
        <div class="p-3 border-t border-slate-200 bg-slate-50 font-mono text-[11px] space-y-1.5 shrink-0">
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
                <span class="text-blue-600 font-bold" x-text="'AREA ' + activeArea"></span>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="h-14 px-3.5 border-t border-slate-200 flex items-center justify-between bg-white shrink-0">
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

    <!-- Mobile & Tablet Sidebar Drawer (Opens 80% with 20% backdrop click-to-close) -->
    <div x-show="mobileSidebarOpen"
         x-cloak
         class="fixed inset-0 z-50 xl:hidden"
         role="dialog"
         aria-modal="true">

        <!-- 20% Dimmed Backdrop Overlay (Clicking here immediately closes the sidebar) -->
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

        <!-- 80% Drawer Container on Right -->
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

                <!-- Drawer Top Header: Logo on Left & Cross Icon on Right -->
                <div class="h-14 px-4 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
                    <a href="{{ route('dashboard') }}" @click="mobileSidebarOpen = false" class="flex items-center space-x-2">
                        <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-7 h-7 object-contain">
                        <div class="text-xs font-black tracking-wider uppercase flex items-center gap-1">
                            <span class="bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent">AMADEUS SIM</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                    </a>

                    <!-- Cross Icon to close the sidebar -->
                    <button type="button"
                            @click="mobileSidebarOpen = false"
                            class="p-2 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                            aria-label="Close sidebar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Scrollable Body for all moved menu items and terminal types -->
                <div class="flex-1 overflow-y-auto flex flex-col divide-y divide-slate-100">
                    <!-- Role Status & Account Badges -->
                    @if(Auth::check() && Auth::user()->role === 'student')
                        <div class="p-3 bg-slate-50">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                                <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-mono font-bold">STUDENT</span>
                            </div>
                            <div class="px-2.5 py-1.5 bg-white rounded-lg border border-slate-200 text-[11px] text-slate-700 flex items-center justify-between font-mono shadow-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Trainee Terminal</span>
                                </div>
                                <span class="text-[10px] text-slate-400">ONLINE</span>
                            </div>
                        </div>
                    @elseif(Auth::check() && Auth::user()->role === 'instructor')
                        <div class="p-3 bg-slate-50">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                                <span class="uppercase tracking-wider">ACCOUNT ACCESS</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono font-bold">INSTRUCTOR</span>
                            </div>
                            <div class="px-2.5 py-1.5 bg-white rounded-lg border border-slate-200 text-[11px] text-amber-800 flex items-center justify-between font-mono shadow-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Instructor Console</span>
                                </div>
                                <span class="text-[10px] text-slate-400">ADMIN</span>
                            </div>
                        </div>
                    @endif

                    <!-- Dashboard Return Button in Mobile Drawer -->
                    <div class="p-3">
                        <a href="{{ route('dashboard') }}"
                           class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition-colors shadow-xs">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <!-- All Terminal Type Section in Mobile Drawer -->
                    <div class="p-3 bg-slate-50/70">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2">
                            <span class="uppercase tracking-wider font-mono">TERMINAL TYPE</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-bold"
                                  :class="{
                                      'bg-blue-100 text-blue-700': theme === 'modern',
                                      'bg-emerald-100 text-emerald-800': theme === 'crt-green',
                                      'bg-amber-100 text-amber-800': theme === 'crt-amber'
                                  }"
                                  x-text="theme === 'modern' ? 'MODERN' : (theme === 'crt-green' ? 'GREEN CRT' : 'AMBER CRT')">
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            <!-- Modern GUI -->
                            <button type="button"
                                    @click="theme = 'modern'"
                                    :class="theme === 'modern' ? 'bg-white border-blue-500 shadow-xs ring-1 ring-blue-500 text-blue-900 font-semibold' : 'bg-white/90 border-slate-200 text-slate-700 hover:bg-white hover:border-slate-300'"
                                    class="w-full text-left p-2 rounded-lg border transition-all flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-slate-100 border border-slate-300 flex items-center justify-center text-blue-600 shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold leading-tight">Modern Web</div>
                                        <div class="text-[10px] text-slate-400 font-mono">High-contrast GUI</div>
                                    </div>
                                </div>
                                <span x-show="theme === 'modern'" class="w-2 h-2 rounded-full bg-blue-600"></span>
                            </button>

                            <!-- Green CRT -->
                            <button type="button"
                                    @click="theme = 'crt-green'"
                                    :class="theme === 'crt-green' ? 'bg-emerald-950 border-emerald-500 shadow-xs ring-1 ring-emerald-500 text-emerald-300 font-semibold' : 'bg-white/90 border-slate-200 text-slate-700 hover:bg-white hover:border-slate-300'"
                                    class="w-full text-left p-2 rounded-lg border transition-all flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-black border border-emerald-700 flex items-center justify-center text-emerald-400 shrink-0">
                                        <span class="font-mono text-[10px] font-bold">&gt;_</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold leading-tight" :class="theme === 'crt-green' ? 'text-emerald-300' : 'text-slate-800'">Classic CRT Green</div>
                                        <div class="text-[10px] font-mono" :class="theme === 'crt-green' ? 'text-emerald-400/80' : 'text-slate-400'">Phosphor P1 80-col</div>
                                    </div>
                                </div>
                                <span x-show="theme === 'crt-green'" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            </button>

                            <!-- Amber CRT -->
                            <button type="button"
                                    @click="theme = 'crt-amber'"
                                    :class="theme === 'crt-amber' ? 'bg-amber-950 border-amber-500 shadow-xs ring-1 ring-amber-500 text-amber-300 font-semibold' : 'bg-white/90 border-slate-200 text-slate-700 hover:bg-white hover:border-slate-300'"
                                    class="w-full text-left p-2 rounded-lg border transition-all flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-black border border-amber-700 flex items-center justify-center text-amber-400 shrink-0">
                                        <span class="font-mono text-[10px] font-bold">&gt;_</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold leading-tight" :class="theme === 'crt-amber' ? 'text-amber-300' : 'text-slate-800'">Classic CRT Amber</div>
                                        <div class="text-[10px] font-mono" :class="theme === 'crt-amber' ? 'text-amber-400/80' : 'text-slate-400'">Phosphor P3 80-col</div>
                                    </div>
                                </div>
                                <span x-show="theme === 'crt-amber'" class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Work Area Switcher -->
                    <div class="p-3">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2">
                            <span class="uppercase tracking-wider font-mono">WORK AREA</span>
                            <span class="text-[10px] font-mono font-bold text-blue-600" x-text="'ACTIVE: AREA ' + activeArea"></span>
                        </div>
                        <div class="grid grid-cols-6 gap-1">
                            <template x-for="a in ['A', 'B', 'C', 'D', 'E', 'F']" :key="a">
                                <button type="button"
                                        @click="selectArea(a)"
                                        :class="activeArea === a ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                        class="h-7 rounded flex items-center justify-center font-mono text-xs transition-colors"
                                        x-text="a">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Terminal Controls -->
                    <div class="p-3 space-y-1.5">
                        <div class="text-[10px] font-mono text-slate-400 uppercase tracking-wider mb-1">TERMINAL UTILITIES</div>

                        <button type="button"
                                @click="showVisualInspector = !showVisualInspector"
                                :class="showVisualInspector ? 'bg-blue-50 border-blue-200 text-blue-700 font-semibold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg border text-xs transition-colors shadow-xs">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <span>PNR Visual Inspector</span>
                            </div>
                            <span class="text-[10px] font-mono font-bold" x-text="showVisualInspector ? 'ON' : 'OFF'"></span>
                        </button>

                        <button type="button"
                                @click="clearTerminal()"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 text-xs transition-colors shadow-xs">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            <span>Clear Terminal Screen</span>
                        </button>

                        <button type="button"
                                @click="executeCommand('HE')"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 text-xs transition-colors shadow-xs">
                            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Display Help (HE)</span>
                        </button>
                    </div>

                    <!-- Active GDS Session in Drawer -->
                    <div class="p-3 bg-slate-50 font-mono text-[11px] space-y-1.5 mt-auto">
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
                            <span class="text-blue-600 font-bold" x-text="'AREA ' + activeArea"></span>
                        </div>
                    </div>
                </div>

                <!-- User Profile Footer in Drawer -->
                <div class="h-14 px-3.5 border-t border-slate-200 flex items-center justify-between bg-white shrink-0">
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
            </div>
        </div>
    </div>

    <!-- Main Terminal Workspace -->
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden bg-[#f8fafc]">
        @if(session('impersonated_by_instructor'))
            <div class="bg-amber-500 text-white px-3 sm:px-4 py-2 text-xs flex items-center justify-between shadow-md shrink-0 z-30">
                <div class="flex items-center space-x-2 font-mono">
                    <svg class="w-4 h-4 text-white shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><strong>INSTRUCTOR MODE:</strong> Viewing cadet terminal as <strong>{{ $user->username ?? Auth::user()->username }}</strong>.</span>
                </div>
                <form action="{{ route('impersonation.exit') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-2.5 py-1 bg-white text-amber-900 hover:bg-amber-100 rounded text-xs font-bold font-sans transition-colors shadow-xs">
                        Exit to Instructor Hub &rarr;
                    </button>
                </form>
            </div>
        @endif

        <!-- Top Header: Logo on left for mobile/tablet, 3-line menu icon on right -->
        <header class="h-14 px-3 sm:px-4 border-b border-slate-200 bg-white flex items-center justify-between shrink-0 shadow-sm z-20">
            <!-- Left on Tablet & Mobile (< xl: 1280px): Brand Logo -->
            <div class="flex items-center space-x-2 xl:hidden">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-7 h-7 object-contain">
                    <span class="font-extrabold bg-gradient-to-r from-blue-600 via-sky-600 to-indigo-600 bg-clip-text text-transparent text-xs sm:text-sm">
                        AMADEUS SIM
                    </span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                </a>
            </div>

            <!-- Left on Desktop (>= xl: 1280px): Title & Active Session Mode -->
            <div class="hidden xl:flex items-center space-x-3">
                <span class="px-2.5 py-1 rounded text-[11px] font-bold font-mono tracking-wider uppercase bg-blue-50 text-blue-700 border border-blue-200">
                    CRYPTIC TERMINAL SIMULATOR
                </span>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-slate-500 font-mono">
                    Host Connected • 80-Column Display
                </span>
            </div>

            <!-- Right Controls -->
            <div class="flex items-center space-x-1.5 sm:space-x-2 text-xs">
                <!-- Active Work Area pill (Tablet/Mobile) -->
                <span class="xl:hidden px-2 py-1 rounded bg-blue-50 border border-blue-200 text-blue-700 font-mono font-bold text-[11px]" x-text="'AREA ' + activeArea"></span>

                <!-- PNR Inspector Toggle (Desktop >= xl) -->
                <button type="button"
                        @click="showVisualInspector = !showVisualInspector"
                        :class="showVisualInspector ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-slate-200 text-slate-600 hover:text-slate-900'"
                        class="hidden xl:flex px-2.5 py-1.5 rounded-lg border text-xs items-center gap-1.5 transition-colors shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>PNR Inspector</span>
                </button>

                <!-- Dashboard Link (Desktop >= xl) -->
                <a href="{{ route('dashboard') }}" class="hidden xl:flex px-2.5 py-1.5 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-semibold items-center gap-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Dashboard</span>
                </a>

                <!-- User status on Desktop >= xl -->
                <div class="hidden xl:flex items-center space-x-2 pl-2 border-l border-slate-200">
                    <span class="text-slate-700 font-mono text-xs">{{ $user->username ?? Auth::user()->username }}</span>
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-rose-600 p-1" title="Sign Out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>

                <!-- 3-line menu icon on tablet and mobile devices (< xl: 1280px) -->
                <button type="button"
                        @click="mobileSidebarOpen = true"
                        class="xl:hidden p-2 rounded-lg text-slate-700 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="Open terminal menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Main Workspace Container -->
        <div :class="{ 'crt-screen relative': theme === 'crt-green' || theme === 'crt-amber' }" class="flex-1 flex flex-col p-2 sm:p-3 gap-2 min-h-0 overflow-hidden bg-[#f8fafc]">
            <!-- Upper Area: Cryptic Terminal Workspace -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden">
                <!-- Terminal Component -->
                <div class="flex-1 h-full min-h-0 flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md">
                    <!-- Terminal Header & Work Area Tabs -->
                    <div class="bg-slate-100 border-b border-slate-200 px-2 sm:px-3 py-1.5 sm:py-2 flex items-center justify-between shrink-0">
                        <div class="flex items-center space-x-1">
                            <span class="text-[10px] sm:text-[11px] font-mono text-slate-500 mr-1 sm:mr-2 uppercase tracking-wider hidden sm:inline">WORK AREA:</span>
                            <template x-for="a in ['A', 'B', 'C', 'D', 'E', 'F']" :key="a">
                                <button @click="selectArea(a)"
                                        :class="activeArea === a ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'"
                                        class="w-6 sm:w-7 h-6 rounded flex items-center justify-center font-mono text-xs transition-colors"
                                        x-text="a">
                                </button>
                            </template>
                        </div>

                        <div class="flex items-center space-x-2 sm:space-x-3 text-xs font-mono text-slate-500">
                            <span class="hidden md:inline">OFFICE: <strong class="text-slate-800">{{ $user->office_id ?? 'LON1A0980' }}</strong></span>
                            <span class="hidden md:inline">AGENT: <strong class="text-slate-800">{{ $user->agent_id ?? '1234AB' }}</strong></span>
                            <button @click="clearTerminal()" class="hover:text-rose-600 transition-colors p-1" title="Clear Terminal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <button @click="executeCommand('HE')" class="hover:text-blue-600 transition-colors p-1" title="Quick Help">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Monospaced Terminal Output Console -->
                    <div x-ref="terminalContainer"
                         @click="if (!window.getSelection()?.toString()) $refs.cmdInput?.focus()"
                         :class="{
                            'bg-black text-emerald-400': theme === 'crt-green',
                            'bg-black text-amber-400': theme === 'crt-amber',
                            'bg-[#f8fafc] text-slate-800': theme === 'modern'
                         }"
                         class="flex-1 p-3 overflow-y-auto font-mono text-[13px] sm:text-[14px] leading-relaxed whitespace-pre-wrap select-text cursor-text">
                        <template x-for="(line, idx) in currentArea.outputLines" :key="idx">
                            <div :class="{
                                'text-blue-600 font-bold': line.isInput && theme === 'modern',
                                'text-rose-600 font-semibold': line.isError && theme === 'modern',
                                'text-slate-500': line.isHeader && theme === 'modern',
                                'underline': line.isInput && (theme === 'crt-green' || theme === 'crt-amber')
                            }" x-text="line.text"></div>
                        </template>
                    </div>

                    <!-- Cryptic Command Input Line -->
                    <form @submit.prevent="submitCommand()"
                          :class="{
                            'bg-black border-emerald-900 text-emerald-400': theme === 'crt-green',
                            'bg-black border-amber-900 text-amber-400': theme === 'crt-amber',
                            'bg-white border-slate-200 text-slate-900': theme === 'modern'
                          }"
                          class="border-t px-3 py-2 flex items-center gap-2 shrink-0">
                        <span class="font-mono font-bold text-sm select-none" :class="theme === 'modern' ? 'text-blue-600' : ''">&gt;</span>
                        <input type="text"
                               x-ref="cmdInput"
                               x-model="inputVal"
                               @keydown.up.prevent="navigateHistory('up')"
                               @keydown.down.prevent="navigateHistory('down')"
                               :readonly="isExecuting"
                               placeholder="ENTER CRYPTIC COMMAND (E.G. AN15NOVLHRJFK, SS1Y1, NM1DOE/JOHN MR, FXP, ET)..."
                               autocomplete="off"
                               spellcheck="false"
                               class="flex-1 bg-transparent border-0 outline-none font-mono text-sm tracking-wider uppercase placeholder:text-slate-400 placeholder:normal-case">
                        <div x-show="isExecuting" class="animate-spin text-blue-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lower Area: Visual PNR Inspector -->
            <div x-show="showVisualInspector" class="shrink-0 max-h-[220px] sm:max-h-[190px] overflow-y-auto rounded-xl border border-slate-200 bg-white p-2.5 sm:p-3 text-xs shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pb-2 mb-2 border-b border-slate-200">
                    <div class="flex items-center space-x-2 flex-wrap gap-1">
                        <span class="font-bold text-slate-900 tracking-wide text-[11px] sm:text-xs">ACTIVE PNR INSPECTOR</span>
                        <span class="text-slate-500 font-mono text-[10px] sm:text-[11px]" x-text="'(Area ' + activeArea + ')'"></span>
                        <span x-show="activePnr?.recordLocator" class="px-1.5 py-0.5 bg-blue-50 border border-blue-200 text-blue-700 rounded font-mono font-bold text-[10px] sm:text-xs" x-text="'RLOC: ' + activePnr?.recordLocator"></span>
                    </div>

                    <!-- Mandatory PRINT Status Badges -->
                    <div class="flex items-center gap-1 text-[9px] sm:text-[10px] font-mono flex-wrap">
                        <span class="text-slate-500 mr-0.5 font-bold">PRINT:</span>
                        <span :class="pnrHasPhone ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'" class="px-1.5 py-0.5 rounded border">P: Phone</span>
                        <span :class="pnrHasReceivedFrom ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'" class="px-1.5 py-0.5 rounded border">R: RecvFrom</span>
                        <span :class="pnrHasItinerary ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'" class="px-1.5 py-0.5 rounded border">I: Itinerary</span>
                        <span :class="pnrHasName ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'" class="px-1.5 py-0.5 rounded border">N: Name</span>
                        <span :class="pnrHasTicketing ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'" class="px-1.5 py-0.5 rounded border">T: Ticket</span>
                    </div>
                </div>

                <!-- Segments, Passengers & TST Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <!-- Booked Segments -->
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">FLIGHT SEGMENTS (<span x-text="activePnr?.segments?.length || 0"></span>)</span>
                        <template x-if="!activePnr?.segments?.length">
                            <span class="text-slate-400 italic text-[11px]">No segments booked yet (use AN + SS1Y1)</span>
                        </template>
                        <div class="space-y-1">
                            <template x-for="s in activePnr?.segments || []" :key="s.segmentNumber">
                                <div class="bg-white p-1.5 rounded border border-slate-200 font-mono text-[11px] flex justify-between shadow-xs">
                                    <span class="text-blue-600 font-semibold" x-text="s.airlineCode + ' ' + s.flightNumber + ' (' + s.bookingClass + ')'"></span>
                                    <span class="text-slate-700" x-text="s.departureAirport + '-' + s.arrivalAirport + ' ' + s.departureDate"></span>
                                    <span class="text-emerald-600 font-bold" x-text="'HK' + s.seats"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Passengers -->
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">PASSENGERS (<span x-text="activePnr?.passengers?.length || 0"></span>)</span>
                        <template x-if="!activePnr?.passengers?.length">
                            <span class="text-slate-400 italic text-[11px]">No passengers added (use NM1SURNAME/NAME MR)</span>
                        </template>
                        <div class="space-y-1">
                            <template x-for="p in activePnr?.passengers || []" :key="p.id">
                                <div class="bg-white p-1.5 rounded border border-slate-200 font-mono text-[11px] flex justify-between shadow-xs">
                                    <span class="text-slate-800 font-medium" x-text="p.lastName + '/' + p.firstName + (p.title ? ' ' + p.title : '')"></span>
                                    <span class="text-slate-500 text-[10px]" x-text="'(' + p.type + ')'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- TST & Tickets -->
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">STORED FARES & TICKETS</span>
                        <template x-if="!activePnr?.tsts?.length && !activePnr?.tickets?.length">
                            <span class="text-slate-400 italic text-[11px]">Unpriced (use FXP to price & store TST)</span>
                        </template>
                        <div class="space-y-1">
                            <template x-for="t in activePnr?.tsts || []" :key="t.tstNumber">
                                <div class="bg-white p-1.5 rounded border border-slate-200 font-mono text-[11px] flex justify-between shadow-xs">
                                    <span class="text-amber-700 font-medium" x-text="'TST ' + t.tstNumber + ' (' + t.fareBasis + ')'"></span>
                                    <span class="text-emerald-700 font-bold" x-text="t.currency + ' ' + t.total.toFixed(2)"></span>
                                </div>
                            </template>
                            <template x-for="tkt in activePnr?.tickets || []" :key="tkt.ticketNumber">
                                <div class="bg-emerald-50 p-1.5 rounded border border-emerald-200 font-mono text-[11px] flex justify-between">
                                    <span class="text-emerald-700 font-bold" x-text="'ETKT: ' + tkt.ticketNumber"></span>
                                    <span class="text-emerald-600 font-semibold" x-text="tkt.coupons.length + ' CPN OPEN'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function amadeusApp() {
    const initialAreaState = (area) => ({
        area: area,
        isSignedIn: true,
        officeId: 'LON1A0980',
        agentId: '1234AB',
        activePnr: null,
        lastAvailabilityDisplay: null,
        history: [],
        historyIndex: -1,
        outputLines: [
            { text: `AMADEUS GDS SIM - WORK AREA ${area} READY`, isHeader: true },
            { text: `OFFICE: LON1A0980  AGENT: 1234AB  SYSTEM ACTIVE`, isHeader: true },
            { text: `ENTER "HE" OR "HELP" FOR COMMAND REFERENCE`, isHeader: true },
            { text: `------------------------------------------------------------` },
        ],
    });

    return {
        mobileSidebarOpen: false,
        theme: 'modern',
        showVisualInspector: true,
        activeArea: 'A',
        areas: {
            A: initialAreaState('A'),
            B: initialAreaState('B'),
            C: initialAreaState('C'),
            D: initialAreaState('D'),
            E: initialAreaState('E'),
            F: initialAreaState('F'),
        },

        inputVal: '',
        isExecuting: false,
        historyCursor: -1,

        get currentArea() {
            return this.areas[this.activeArea];
        },
        get activePnr() {
            return this.currentArea.activePnr;
        },

        // PRINT status helpers
        get pnrHasPhone() { return (this.activePnr?.contacts?.length || 0) > 0; },
        get pnrHasReceivedFrom() { return !!this.activePnr?.receivedFrom; },
        get pnrHasItinerary() { return (this.activePnr?.segments?.length || 0) > 0; },
        get pnrHasName() { return (this.activePnr?.passengers?.length || 0) > 0; },
        get pnrHasTicketing() { return !!this.activePnr?.ticketing; },

        init() {
            this.$nextTick(() => {
                this.scrollToBottom();
                this.$refs.cmdInput?.focus();
            });
        },

        selectArea(area) {
            this.activeArea = area;
            this.historyCursor = -1;
            this.$nextTick(() => {
                this.scrollToBottom();
                this.$refs.cmdInput?.focus();
            });
        },

        clearTerminal() {
            this.currentArea.outputLines = [];
        },

        navigateHistory(direction) {
            const hist = this.currentArea.history;
            if (hist.length === 0) return;

            if (direction === 'up') {
                if (this.historyCursor === -1) {
                    this.historyCursor = hist.length - 1;
                } else if (this.historyCursor > 0) {
                    this.historyCursor--;
                }
            } else if (direction === 'down') {
                if (this.historyCursor !== -1) {
                    if (this.historyCursor < hist.length - 1) {
                        this.historyCursor++;
                    } else {
                        this.historyCursor = -1;
                        this.inputVal = '';
                        return;
                    }
                }
            }

            if (this.historyCursor >= 0 && this.historyCursor < hist.length) {
                this.inputVal = hist[this.historyCursor];
            }
        },

        async submitCommand() {
            const cmd = this.inputVal.trim();
            if (!cmd || this.isExecuting) return;

            this.inputVal = '';
            this.historyCursor = -1;
            await this.executeCommand(cmd);
        },

        async executeCommand(command) {
            this.isExecuting = true;
            const area = this.activeArea;
            const state = this.areas[area];

            // Echo input
            state.outputLines.push({ text: `> ${command.toUpperCase()}`, isInput: true });
            state.history.push(command);
            this.scrollToBottom();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const res = await fetch('/terminal/execute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        command,
                        state,
                    }),
                });

                const data = await res.json();

                if (data.outputLines) {
                    for (const line of data.outputLines) {
                        state.outputLines.push({ text: line, isError: data.isError });
                    }
                }

                if (data.updatedState) {
                    state.activePnr = data.updatedState.activePnr;
                    state.lastAvailabilityDisplay = data.updatedState.lastAvailabilityDisplay;
                    state.isSignedIn = data.updatedState.isSignedIn;
                }
            } catch (err) {
                state.outputLines.push({ text: `CONNECTION ERROR: FAILED TO REACH GDS HOST`, isError: true });
            } finally {
                this.isExecuting = false;
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.$refs.cmdInput?.focus();
                });
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.terminalContainer) {
                    this.$refs.terminalContainer.scrollTop = this.$refs.terminalContainer.scrollHeight;
                }
            });
        }
    };
}
</script>
@endsection
