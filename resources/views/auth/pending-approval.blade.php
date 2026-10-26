@extends('layouts.app')

@section('title', 'Pending Instructor Approval - Amadeus GDS Training')

@section('content')
<div class="flex-1 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-[#0b101c] border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden">
        <!-- Ambient subtle glow -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Header / Status Icon -->
        <div class="text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-amber-950/60 border border-amber-500/40 flex items-center justify-center mx-auto shadow-xl shadow-amber-500/10">
                <svg class="w-7 h-7 text-amber-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-950/80 border border-amber-800 text-amber-300 font-mono text-[11px] font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>Pending Instructor Approval</span>
            </div>

            <h1 class="text-xl font-bold text-white tracking-wide">Cadet Enrollment Submitted</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                Your registration has been recorded in the Amadeus GDS training database. Platform access is restricted to verified students.
            </p>
        </div>

        @if(session('registered_username'))
            <div class="p-3 bg-[#131c2e] border border-slate-700/80 rounded-xl text-xs space-y-1">
                <div class="flex justify-between text-slate-400">
                    <span>Registered Cadet:</span>
                    <span class="font-mono text-white font-bold">{{ session('registered_username') }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Training Office:</span>
                    <span class="font-mono text-sky-400">LON1A0980 (1A London)</span>
                </div>
            </div>
        @endif

        <!-- Instructions Box -->
        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2 text-xs text-slate-300">
            <div class="font-semibold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>What happens next?</span>
            </div>
            <p class="text-[11px] text-slate-400 leading-normal">
                Your flight instructor or academy administrator must approve your cadet profile from the <strong>Instructor Operations Hub</strong> before you can log in, practice cryptic commands, or access assigned booking scenarios.
            </p>
        </div>

        <!-- Action Links -->
        <div class="space-y-2 pt-2">
            <a href="/login" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-xs tracking-wider transition-colors shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2">
                <span>Go to Sign In</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>

            <a href="/" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg font-semibold text-xs transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Return to Home Page</span>
            </a>
        </div>
    </div>
</div>
@endsection
