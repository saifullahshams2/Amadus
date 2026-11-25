@extends('layouts.profile')

@section('title', 'SMTP Settings - Amadus')
@section('profile_title', 'SMTP Settings')
@section('profile_subtitle', 'Mail server used for password reset and notification emails')

@section('profile_content')
<div class="space-y-5 max-w-2xl mx-auto pb-10">

    <section class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
        <div class="pb-3 border-b border-slate-200 flex items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900 tracking-wide">Add / Change SMTP Server</h2>
                <p class="text-xs text-slate-500 mt-0.5">When enabled, outbound emails (password resets, notifications) are sent through this server instead of the server default.</p>
            </div>
            <span class="shrink-0 px-2 py-1 rounded text-[10px] font-mono font-bold uppercase {{ $smtp->isConfigured() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                {{ $smtp->isConfigured() ? 'ACTIVE' : 'NOT CONFIGURED' }}
            </span>
        </div>

        <form action="{{ route('profile.smtp.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <label class="flex items-center gap-2.5 px-3.5 py-3 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer">
                <input type="hidden" name="enabled" value="0">
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $smtp->enabled)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-xs font-bold text-slate-700">Enable custom SMTP</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="host" class="block text-xs font-semibold text-slate-700 mb-1.5">SMTP HOST</label>
                    <input type="text" id="host" name="host" value="{{ old('host', $smtp->host) }}" placeholder="smtp.example.com"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label for="port" class="block text-xs font-semibold text-slate-700 mb-1.5">PORT</label>
                    <input type="number" id="port" name="port" value="{{ old('port', $smtp->port ?? 587) }}" min="1" max="65535"
                           class="w-full bg-white border border-slate-300 text-slate-900 font-mono text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="username" class="block text-xs font-semibold text-slate-700 mb-1.5">SMTP USERNAME</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $smtp->username) }}" autocomplete="off"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label for="encryption" class="block text-xs font-semibold text-slate-700 mb-1.5">ENCRYPTION</label>
                    <select id="encryption" name="encryption" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                        @foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('encryption', $smtp->encryption ?? 'tls') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">SMTP PASSWORD</label>
                <input type="password" id="password" name="password" autocomplete="new-password" placeholder="{{ $smtp->password ? '•••••••• (saved - leave blank to keep)' : 'Not set' }}"
                       class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                <p class="text-[10px] text-slate-400 mt-1">Stored encrypted. Leave blank to keep the currently saved password.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="from_address" class="block text-xs font-semibold text-slate-700 mb-1.5">FROM EMAIL ADDRESS</label>
                    <input type="email" id="from_address" name="from_address" value="{{ old('from_address', $smtp->from_address) }}" placeholder="no-reply@example.com"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label for="from_name" class="block text-xs font-semibold text-slate-700 mb-1.5">FROM NAME</label>
                    <input type="text" id="from_name" name="from_name" value="{{ old('from_name', $smtp->from_name) }}" placeholder="Amadus Training Simulator"
                           class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                    Save SMTP Settings
                </button>
            </div>
        </form>
    </section>

    <section class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
        <div class="pb-3 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 tracking-wide">Send Test Email</h2>
            <p class="text-xs text-slate-500 mt-0.5">Verify the saved configuration by sending a test message.</p>
        </div>

        <form action="{{ route('profile.smtp.test') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="recipient" class="block text-xs font-semibold text-slate-700 mb-1.5">SEND TO</label>
                <input type="email" id="recipient" name="recipient" value="{{ old('recipient', $user->email) }}"
                       class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3.5 py-2.5 rounded-lg outline-none focus:border-blue-500 transition-colors">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-lg transition-colors shadow-sm whitespace-nowrap">
                Send Test Email
            </button>
        </form>
    </section>
</div>
@endsection
