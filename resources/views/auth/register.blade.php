@extends('layouts.app')

@section('title', 'Cadet Registration - Amadus')

@section('content')
<div class="min-h-screen w-full flex flex-col items-center justify-center px-4 py-8 sm:py-12 relative overflow-y-auto">
    <!-- Top Navigation Bar -->
    <div class="max-w-lg w-full mb-4 flex items-center justify-between">
        <a href="/" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Home</span>
        </a>
        <a href="{{ route('login') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
            <span>Sign In</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
    </div>

    <div class="max-w-lg w-full bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-6 sm:p-8 space-y-5 shadow-xl relative overflow-hidden">
        <!-- Brand / Header -->
        <div class="text-center space-y-2">
            <img src="{{ asset('airplane-ticket.png') }}" alt="Amadus" class="w-12 h-12 object-contain mx-auto">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-wide">Cadet Enrollment</h1>
            <p class="text-xs text-slate-500">Register for Amadus Flight Operations</p>
        </div>

        <!-- Instructor Approval Policy Notice -->
        <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-start gap-2.5">
            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <p class="text-[11px] leading-relaxed text-blue-700">
                <strong class="text-blue-900 font-semibold">Access Policy:</strong> All self-registered cadet profiles require verification and approval from your flight instructor before full terminal and scenario access is activated.
            </p>
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                @foreach($errors->all() as $error)
                    <div>&bull; {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Registration Form -->
        <form action="/register" method="POST" class="space-y-4 text-xs font-mono">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-slate-600 mb-1 font-semibold">FULL NAME</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Sarah Connor"
                           class="w-full bg-white border border-slate-300 text-slate-900 rounded-lg p-2.5 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-sans transition-colors placeholder:text-slate-400">
                </div>

                <div>
                    <label class="block text-slate-600 mb-1 font-semibold">CALLSIGN / USERNAME <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="e.g. sconnor"
                           class="w-full bg-white border border-slate-300 text-slate-900 rounded-lg p-2.5 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder:text-slate-400">
                </div>
            </div>

            <div>
                <label class="block text-slate-600 mb-1 font-semibold">EMAIL ADDRESS <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="cadet@academy.training"
                       class="w-full bg-white border border-slate-300 text-slate-900 rounded-lg p-2.5 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder:text-slate-400">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-slate-600 mb-1 font-semibold">PASSWORD <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required placeholder="Min 6 characters"
                           class="w-full bg-white border border-slate-300 text-slate-900 rounded-lg p-2.5 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder:text-slate-400">
                </div>

                <div>
                    <label class="block text-slate-600 mb-1 font-semibold">CONFIRM PASSWORD <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirmation" required placeholder="Re-enter password"
                           class="w-full bg-white border border-slate-300 text-slate-900 rounded-lg p-2.5 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder:text-slate-400">
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs sm:text-sm tracking-wider transition-colors shadow-sm flex items-center justify-center gap-1.5 mt-2">
                <span>SUBMIT ENROLLMENT FOR APPROVAL</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 pt-3 border-t border-slate-200 space-y-1.5">
            <div>
                Already have an active account? <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Sign In to Terminal</a>
            </div>
            <div>
                Need to recover access? <a href="{{ route('password.request') }}" class="text-slate-400 hover:text-slate-600 hover:underline">Reset Password</a>
            </div>
        </div>
    </div>
</div>
@endsection
