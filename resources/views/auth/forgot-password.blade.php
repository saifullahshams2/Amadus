@extends('layouts.app')

@section('title', 'Reset Password - AMADEUS GDS SIM')

@section('content')
<div class="min-h-screen w-full flex flex-col items-center justify-center px-4 py-8 sm:py-12 relative overflow-y-auto">
    <!-- Navigation Links -->
    <div class="max-w-md w-full mb-4 flex items-center justify-between">
        <a href="/" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Home</span>
        </a>
        <a href="{{ route('login') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
            &larr; Back to Sign In
        </a>
    </div>

    <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl relative overflow-hidden">
        <!-- Brand / Header -->
        <div class="text-center space-y-2">
            <img src="{{ asset('airplane-ticket.png') }}" alt="AMADEUS GDS SIM" class="w-12 h-12 object-contain mx-auto">
            <h1 class="text-xl font-bold text-slate-900 tracking-wide">Recover Password</h1>
            <p class="text-xs text-slate-500">Restore access to your training terminal account</p>
        </div>

        @if(session('status'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if(session('reset_link'))
            <div class="p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-xs space-y-1.5">
                <div class="font-bold flex items-center gap-1 text-sky-900">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>Password Reset Link Generated</span>
                </div>
                <p class="text-[11px] text-sky-700 leading-relaxed">
                    Click the link below to set your new password:
                </p>
                <div class="pt-1">
                    <a href="{{ session('reset_link') }}" class="text-sky-700 font-bold underline hover:text-sky-900 break-all">
                        Reset Your Password &rarr;
                    </a>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                @foreach($errors->all() as $error)
                    <div>&bull; {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-4 text-xs font-mono">
            @csrf
            <div>
                <label class="block text-slate-600 mb-1 font-semibold">EMAIL ADDRESS OR CALLSIGN</label>
                <input type="text" name="email" value="{{ old('email') }}" required autofocus placeholder="e.g. cadet@airline.com or sconnor"
                       class="w-full bg-white border border-slate-300 text-slate-900 rounded-lg p-2.5 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder:text-slate-400">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs sm:text-sm tracking-wider transition-colors shadow-sm flex items-center justify-center gap-1.5">
                <span>SEND RESET LINK</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 pt-3 border-t border-slate-100 space-y-1.5">
            <div>
                Remember your credentials? <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Sign In</a>
            </div>
            <div>
                Don't have an account yet? <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Register as Cadet</a>
            </div>
        </div>
    </div>
</div>
@endsection
