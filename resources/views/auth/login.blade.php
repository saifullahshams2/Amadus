@extends('layouts.app')

@section('title', 'Sign In - Amadeus GDS Simulator')

@section('content')
<div class="flex-1 flex flex-col items-center justify-center p-4 relative">
    <!-- Back to Home Link -->
    <div class="max-w-md w-full mb-4">
        <a href="/" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Home</span>
        </a>
    </div>

    <div class="max-w-md w-full bg-[#0b101c] border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden">
        <!-- Brand -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-700 to-sky-500 flex items-center justify-center mx-auto shadow-lg shadow-blue-500/25">
                <svg class="w-6 h-6 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </div>
            <h1 class="text-xl font-bold text-white tracking-wide">Amadeus GDS Connect</h1>
            <p class="text-xs text-slate-400">Sign in to your training terminal or instructor hub</p>
        </div>

        @if(session('info'))
            <div class="p-3 rounded-lg bg-blue-950/50 border border-blue-800 text-blue-300 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if($errors->any())
            @php $firstErr = $errors->first(); @endphp
            @if(str_contains(strtolower($firstErr), 'approval') || str_contains(strtolower($firstErr), 'pending'))
                <div class="p-3.5 rounded-xl bg-amber-950/50 border border-amber-800/80 text-amber-300 text-xs space-y-1.5">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Account Awaiting Authorization</span>
                    </div>
                    <p class="text-[11px] text-amber-200/90 leading-relaxed">{{ $firstErr }}</p>
                    <div class="pt-1">
                        <a href="/pending-approval" class="text-amber-400 font-bold underline hover:text-amber-300">View Approval Information &rarr;</a>
                    </div>
                </div>
            @else
                <div class="p-3 rounded-lg bg-rose-950/40 border border-rose-800 text-rose-300 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ $firstErr }}</span>
                </div>
            @endif
        @endif

        <!-- Form -->
        <form action="/login" method="POST" class="space-y-4 text-xs font-mono">
            @csrf
            <div>
                <label class="block text-slate-400 mb-1">USERNAME OR CALLSIGN</label>
                <input type="text" name="username" id="usernameInput" value="{{ old('username') }}" required autofocus placeholder="e.g. username or email"
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500 transition-colors">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">PASSWORD</label>
                <input type="password" name="password" id="passwordInput" required placeholder="••••••••"
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500 transition-colors">
            </div>

            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-xs tracking-wider transition-colors shadow-lg shadow-blue-600/25 flex items-center justify-center gap-1.5">
                <span>SIGN IN &bull; LAUNCH DASHBOARD</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 space-y-1">
            <div>
                Don't have an account? <a href="/register" class="text-sky-400 font-semibold hover:underline">Sign Up as Trainee Cadet</a>.
            </div>
            @if(!file_exists(storage_path('installed')))
            <div class="pt-1 text-[11px]">
                <a href="/install" class="text-amber-400 hover:underline font-semibold flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Run System Installer (Setup Instructor Account)</span>
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
