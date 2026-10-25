@extends('layouts.app')

@section('title', 'Register - Amadeus GDS Simulator')

@section('content')
<div class="flex-1 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-[#0b101c] border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-2xl">
        <div class="text-center space-y-2">
            <h1 class="text-xl font-bold text-white tracking-wide">Register Account</h1>
            <p class="text-xs text-slate-400">Join the Amadeus GDS Training Platform</p>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-lg bg-rose-950/40 border border-rose-800 text-rose-300 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/register" method="POST" class="space-y-4 text-xs font-mono">
            @csrf
            <div>
                <label class="block text-slate-400 mb-1">USERNAME</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">EMAIL</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">PASSWORD (MIN 6 CHARS)</label>
                <input type="password" name="password" required
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500">
            </div>


            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-xs tracking-wider transition-colors shadow-lg shadow-blue-600/20">
                CREATE ACCOUNT
            </button>
        </form>

        <div class="text-center text-xs text-slate-500">
            Already have an account? <a href="/login" class="text-blue-400 hover:underline">Sign In</a>.
        </div>
    </div>
</div>
@endsection
