@extends('layouts.app')

@section('title', 'Cadet Registration - Amadeus GDS Training Platform')

@section('content')
<div class="flex-1 flex flex-col items-center justify-center p-4 relative">
    <!-- Back to Home Link -->
    <div class="max-w-md w-full mb-4">
        <a href="/" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Home</span>
        </a>
    </div>

    <div class="max-w-md w-full bg-[#0b101c] border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-5 shadow-2xl relative overflow-hidden">
        <!-- Brand / Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-sky-600 to-blue-500 flex items-center justify-center mx-auto shadow-lg shadow-sky-500/25">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
            <h1 class="text-xl font-bold text-white tracking-wide">Cadet Enrollment</h1>
            <p class="text-xs text-slate-400">Register for Amadeus GDS Training & Flight Operations</p>
        </div>

        <!-- Instructor Approval Policy Notice -->
        <div class="p-3.5 rounded-xl bg-blue-950/40 border border-blue-800/80 text-blue-300 text-xs flex items-start gap-2.5">
            <svg class="w-4 h-4 text-sky-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <p class="text-[11px] leading-relaxed text-blue-200/90">
                <strong class="text-white font-semibold">Access Policy:</strong> All self-registered cadet profiles require verification and approval from your flight instructor before full terminal and scenario access is activated.
            </p>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-lg bg-rose-950/40 border border-rose-800 text-rose-300 text-xs space-y-1">
                @foreach($errors->all() as $error)
                    <div>&bull; {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Registration Form -->
        <form action="/register" method="POST" class="space-y-3.5 text-xs font-mono">
            @csrf
            <div>
                <label class="block text-slate-400 mb-1">FULL NAME</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Cadet Sarah Connor"
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500 font-sans transition-colors">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">CALLSIGN / USERNAME <span class="text-rose-400">*</span></label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="e.g. sconnor"
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500 transition-colors">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">EMAIL ADDRESS <span class="text-rose-400">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="cadet@academy.training"
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500 transition-colors">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">PASSWORD (MIN 6 CHARS) <span class="text-rose-400">*</span></label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500 transition-colors">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">CONFIRM PASSWORD <span class="text-rose-400">*</span></label>
                <input type="password" name="password_confirmation" required placeholder="••••••••"
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500 transition-colors">
            </div>

            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-xs tracking-wider transition-colors shadow-lg shadow-blue-600/25 flex items-center justify-center gap-1.5 pt-2">
                <span>SUBMIT ENROLLMENT FOR APPROVAL</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-800">
            Already have an active account? <a href="/login" class="text-sky-400 font-semibold hover:underline">Sign In</a>.
        </div>
    </div>
</div>
@endsection
