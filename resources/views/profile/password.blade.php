@extends('layouts.profile')

@section('title', 'Change Password - Amadus')
@section('profile_title', 'Change Password')
@section('profile_subtitle', 'Update your account password')

@section('profile_content')
<div class="space-y-5 max-w-2xl mx-auto pb-10">

    <section class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
        <div class="pb-3 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 tracking-wide">Change Your Password</h2>
            <p class="text-xs text-slate-500 mt-0.5">Enter your current password once, then choose a new password. Minimum 6 characters.</p>
        </div>

        <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-xs font-semibold text-slate-700 mb-1.5">CURRENT PASSWORD *</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                       class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">NEW PASSWORD *</label>
                    <input type="password" id="password" name="password" required minlength="6" maxlength="64" autocomplete="new-password"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">CONFIRM NEW PASSWORD *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6" autocomplete="new-password"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                    Change Password
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
