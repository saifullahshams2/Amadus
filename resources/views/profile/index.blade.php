@extends('layouts.profile')

@section('title', 'My Profile - Amadus')
@section('profile_title', 'My Profile')
@section('profile_subtitle', 'Account overview and settings hub')

@section('profile_content')
<div class="space-y-5 max-w-3xl mx-auto pb-10">

    <!-- Identity Card -->
    <section class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="w-14 h-14 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-xl uppercase shrink-0">
            {{ substr($user->username, 0, 2) }}
        </div>
        <div class="flex-1 min-w-0 space-y-1">
            <h2 class="text-base font-black text-slate-900 truncate">{{ $user->name ?? $user->username }}</h2>
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-mono">
                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-bold">{{ $user->username }}</span>
                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">{{ $user->email }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3 text-[11px] font-mono text-slate-500 shrink-0">
            <div class="text-center px-3">
                <div class="text-lg font-black text-slate-900">{{ $pnrCount }}</div>
                <div class="uppercase tracking-wider">Stored PNRs</div>
            </div>
        </div>
    </section>

    <!-- User's Name (login username) -->
    <section class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
        <div class="pb-3 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 tracking-wide">User's Name</h2>
            <p class="text-xs text-slate-500 mt-0.5">Your login identifier, linked to your PNRs, submissions and terminal history. Changing it updates all of your records automatically.</p>
        </div>

        <form action="{{ route('profile.name.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-700 mb-1.5">NEW USER NAME *</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
                           minlength="3" maxlength="60" autocomplete="username"
                           class="w-full bg-white border border-slate-300 text-slate-900 font-mono text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                    <p class="text-[10px] text-slate-400 mt-1">Letters, numbers, dots, dashes and underscores (3-60 chars).</p>
                </div>
                <div>
                    <label for="username_current_password" class="block text-xs font-semibold text-slate-700 mb-1.5">CURRENT PASSWORD *</label>
                    <input type="password" id="username_current_password" name="current_password" required autocomplete="current-password"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                    <p class="text-[10px] text-slate-400 mt-1">Confirm your password to authorise the change.</p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                    Save User Name
                </button>
            </div>
        </form>
    </section>

    <!-- Display Name -->
    <section class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
        <div class="pb-3 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 tracking-wide">Display Name</h2>
            <p class="text-xs text-slate-500 mt-0.5">The name shown on the dashboard, sidebars and grading summaries. Does not affect your login.</p>
        </div>

        <form action="{{ route('profile.display-name.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">DISPLAY NAME *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       minlength="2" maxlength="80" autocomplete="name"
                       class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                    Save Display Name
                </button>
            </div>
        </form>
    </section>

    <!-- Email Address -->
    <section class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
        <div class="pb-3 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 tracking-wide">Email Address</h2>
            <p class="text-xs text-slate-500 mt-0.5">Used for password reset links and notifications. Currently <span class="font-mono text-slate-700">{{ $user->email }}</span>.</p>
        </div>

        <form action="{{ route('profile.email.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">NEW EMAIL ADDRESS *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           maxlength="190" autocomplete="email"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label for="email_current_password" class="block text-xs font-semibold text-slate-700 mb-1.5">CURRENT PASSWORD *</label>
                    <input type="password" id="email_current_password" name="current_password" required autocomplete="current-password"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                    <p class="text-[10px] text-slate-400 mt-1">Confirm your password to authorise the change.</p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                    Save Email Address
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
