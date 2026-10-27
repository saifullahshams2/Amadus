@extends('layouts.app')

@section('title', 'Sign In - AMADEUS GDS SIM')

@section('content')
<div class="min-h-screen w-full flex flex-col items-center justify-center px-4 py-8 sm:py-12 relative overflow-y-auto">
    <!-- Back to Home Link -->
    <div class="max-w-md w-full mb-4 flex items-center justify-between">
        <a href="/" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Home</span>
        </a>
        <a href="{{ route('register') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
            Create Account &rarr;
        </a>
    </div>

    <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl relative overflow-hidden">
        <!-- Brand -->
        <div class="text-center space-y-2">
            <img src="{{ asset('airplane-ticket.png') }}" alt="AMADEUS GDS SIM" class="w-12 h-12 object-contain mx-auto">
            <h1 class="text-xl font-bold text-slate-900 tracking-wide">AMADEUS GDS SIM</h1>
            <p class="text-xs text-slate-500">Sign in to your training terminal or instructor hub</p>
        </div>

        @if(session('info'))
            <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('status'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            @php $firstErr = $errors->first(); @endphp
            @if(str_contains(strtolower($firstErr), 'approval') || str_contains(strtolower($firstErr), 'pending'))
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs space-y-1.5">
                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Account Awaiting Authorization</span>
                    </div>
                    <p class="text-[11px] text-amber-700 leading-relaxed">{{ $firstErr }}</p>
                    <div class="pt-1">
                        <a href="/pending-approval" class="text-amber-800 font-bold underline hover:text-amber-900">View Approval Information &rarr;</a>
                    </div>
                </div>
            @else
                <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ $firstErr }}</span>
                </div>
            @endif
        @endif

        <!-- Form -->
        <form action="/login" method="POST" class="space-y-4 text-xs font-mono">
            @csrf
            <div>
                <label class="block text-slate-600 mb-1 font-semibold">USERNAME OR CALLSIGN</label>
                <input type="text" name="username" id="usernameInput" value="{{ old('username') }}" required autofocus placeholder="Enter your username or email"
                       class="w-full bg-white border border-slate-300 text-slate-900 rounded-lg p-2.5 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder:text-slate-400">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-slate-600 font-semibold">PASSWORD</label>
                    <a href="{{ route('password.request') }}" class="text-[11px] font-sans text-blue-600 hover:text-blue-800 hover:underline">Forgot password?</a>
                </div>
                <input type="password" name="password" id="passwordInput" required placeholder="Enter your password"
                       class="w-full bg-white border border-slate-300 text-slate-900 rounded-lg p-2.5 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder:text-slate-400">
            </div>

            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs tracking-wider transition-colors shadow-sm flex items-center justify-center gap-1.5">
                <span>SIGN IN &bull; LAUNCH DASHBOARD</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 space-y-2 pt-2 border-t border-slate-100">
            <div>
                Need to reset your password? <a href="{{ route('password.request') }}" class="text-blue-600 font-semibold hover:underline">Reset Password</a>
            </div>
            <div>
                Don't have an account? <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Sign Up as Trainee Cadet</a>
            </div>
            @if(!file_exists(storage_path('installed')))
            <div class="pt-1 text-[11px]">
                <a href="/install" class="text-amber-600 hover:underline font-semibold flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Run System Installer (Setup Instructor Account)</span>
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
