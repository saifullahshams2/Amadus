<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Installer &bull; Amadeus GDS Simulator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --background: #080c14;
            --foreground: #e2e8f0;
        }
        body {
            background-color: var(--background);
            color: var(--foreground);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-center items-center p-4 sm:p-6 bg-[#080c14] relative overflow-y-auto">
    <!-- Ambient background glow -->
    <div class="fixed inset-0 pointer-events-none opacity-20">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600 rounded-full blur-3xl"></div>
    </div>

    <div x-data="installerWizard()" x-cloak class="w-full max-w-3xl bg-[#0f172a] border border-slate-800 rounded-2xl shadow-2xl relative z-10 overflow-hidden my-8">
        <!-- Header Banner -->
        <div class="bg-[#0b101c] border-b border-slate-800 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <svg class="w-6 h-6 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <span>Amadeus GDS Platform</span>
                        <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-800">Installer</span>
                    </h1>
                    <p class="text-xs text-slate-400">System initialization & instructor account setup</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-xs font-mono text-slate-400 bg-slate-900/80 px-3 py-1.5 rounded-lg border border-slate-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>PHP {{ PHP_VERSION }}</span>
            </div>
        </div>

        <!-- Wizard Step Tracker -->
        <div class="bg-[#0b1322] border-b border-slate-800/80 px-6 py-3.5">
            <div class="flex items-center justify-between">
                <!-- Step 1 Indicator -->
                <div class="flex items-center gap-2">
                    <div :class="step >= 1 ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                        <span x-show="step > 1">✓</span>
                        <span x-show="step === 1">1</span>
                    </div>
                    <span :class="step === 1 ? 'text-white font-semibold' : 'text-slate-400'" class="text-xs hidden md:inline">Requirements</span>
                </div>
                <div class="h-0.5 flex-1 mx-3 bg-slate-800" :class="step > 1 ? 'bg-blue-600/60' : ''"></div>

                <!-- Step 2 Indicator -->
                <div class="flex items-center gap-2">
                    <div :class="step >= 2 ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                        <span x-show="step > 2">✓</span>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <span :class="step === 2 ? 'text-white font-semibold' : 'text-slate-400'" class="text-xs hidden md:inline">Database & Platform</span>
                </div>
                <div class="h-0.5 flex-1 mx-3 bg-slate-800" :class="step > 2 ? 'bg-blue-600/60' : ''"></div>

                <!-- Step 3 Indicator -->
                <div class="flex items-center gap-2">
                    <div :class="step >= 3 ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                        <span x-show="step > 3">✓</span>
                        <span x-show="step <= 3">3</span>
                    </div>
                    <span :class="step === 3 ? 'text-white font-semibold' : 'text-slate-400'" class="text-xs hidden md:inline">Instructor Account</span>
                </div>
                <div class="h-0.5 flex-1 mx-3 bg-slate-800" :class="step > 3 ? 'bg-blue-600/60' : ''"></div>

                <!-- Step 4 Indicator -->
                <div class="flex items-center gap-2">
                    <div :class="step === 4 ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                        4
                    </div>
                    <span :class="step === 4 ? 'text-white font-semibold' : 'text-slate-400'" class="text-xs hidden md:inline">Finished</span>
                </div>
            </div>
        </div>

        <!-- Wizard Forms Container -->
        <div class="p-6 sm:p-8">
            <!-- Server-side errors banner -->
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-950/40 border border-rose-800/80 text-rose-300 text-xs">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- STEP 1: Requirements Check -->
            <div x-show="step === 1" class="space-y-6">
                <div>
                    <h2 class="text-base font-semibold text-white">System Prerequisites & Permissions</h2>
                    <p class="text-xs text-slate-400 mt-1">Verifying PHP runtime, core extensions, and directory write permissions.</p>
                </div>

                <!-- PHP Version -->
                <div class="p-3.5 rounded-xl bg-[#0b101c] border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-950 border border-blue-800 flex items-center justify-center text-blue-400 font-mono text-xs font-bold">
                            PHP
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-white">PHP Version &ge; 8.2.0</div>
                            <div class="text-[11px] text-slate-400">Current: <span class="font-mono text-slate-300">{{ PHP_VERSION }}</span></div>
                        </div>
                    </div>
                    @if($requirements['php_version']['satisfied'])
                        <span class="px-2.5 py-1 rounded-md bg-emerald-950/60 text-emerald-400 border border-emerald-800 text-xs font-bold flex items-center gap-1">
                            ✓ Passed
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-md bg-rose-950/60 text-rose-400 border border-rose-800 text-xs font-bold flex items-center gap-1">
                            ✕ Upgrade Required
                        </span>
                    @endif
                </div>

                <!-- Required Extensions Grid -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider block">PHP Extensions</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach($requirements['extensions'] as $ext => $loaded)
                            <div class="p-2.5 rounded-lg bg-[#0b101c] border border-slate-800 flex items-center justify-between">
                                <span class="font-mono text-xs text-slate-300">{{ $ext }}</span>
                                @if($loaded)
                                    <span class="text-emerald-400 text-xs font-bold">✓</span>
                                @else
                                    <span class="text-rose-400 text-xs font-bold">✕</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Writable Directories -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider block">Directory Permissions</span>
                    <div class="space-y-2">
                        @foreach($requirements['directories'] as $dir => $writable)
                            <div class="p-2.5 rounded-lg bg-[#0b101c] border border-slate-800 flex items-center justify-between text-xs">
                                <span class="font-mono text-slate-300">{{ $dir }}</span>
                                @if($writable)
                                    <span class="px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800 text-[10px] font-bold">WRITABLE</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-rose-950 text-rose-400 border border-rose-800 text-[10px] font-bold">READ ONLY</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Navigation -->
                <div class="pt-4 border-t border-slate-800 flex justify-end">
                    <button type="button"
                            @click="step = 2"
                            :disabled="!{{ $requirements['satisfied'] ? 'true' : 'false' }}"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-xs flex items-center gap-2 transition-colors">
                        <span>Continue to Database</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Database & Platform Overview -->
            <div x-show="step === 2" class="space-y-6">
                <div>
                    <h2 class="text-base font-semibold text-white">Database & Initial Data Seeding</h2>
                    <p class="text-xs text-slate-400 mt-1">Configure database connection and prepare default training entities.</p>
                </div>

                <div class="p-4 rounded-xl bg-[#0b101c] border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Database Driver:</span>
                        <span class="font-mono font-bold text-white uppercase">{{ config('database.default') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Database Name / Path:</span>
                        <span class="font-mono text-sky-400 text-[11px] truncate max-w-xs">{{ config('database.connections.' . config('database.default') . '.database') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Default Office ID:</span>
                        <span class="font-mono text-amber-300">LON1A0980 (1A London Training)</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider block">Automatic Migration & Seed Assets</span>
                    <ul class="text-xs text-slate-400 space-y-1.5 list-disc list-inside bg-[#0b101c] p-3.5 rounded-xl border border-slate-800">
                        <li>Core user authentication & dual-role RBAC schema</li>
                        <li>Global airport lookup table (IATA / ICAO / City)</li>
                        <li>Airline carriers & aircraft equipment database</li>
                        <li>Flight booking practice scenarios & PRINT grading engine</li>
                        <li>Simulated GDS Queues (1: TTL, 7: Sched Change, 8: Waitlist, 9: General, 87: Exchange)</li>
                    </ul>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-between">
                    <button type="button" @click="step = 1" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition-colors">
                        Back
                    </button>
                    <button type="button" @click="step = 3" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs flex items-center gap-2 transition-colors">
                        <span>Continue to Instructor Setup</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Instructor Account Creation Form -->
            <form x-show="step === 3" @submit.prevent="submitInstallation()" class="space-y-5">
                <div>
                    <h2 class="text-base font-semibold text-white">Provision Primary Flight Instructor</h2>
                    <p class="text-xs text-slate-400 mt-1">This account will hold full administrator and instructor privileges for classroom spectating, scenario grading, and queue simulation.</p>
                </div>

                <!-- Form Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Instructor Full Name -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Instructor Full Name <span class="text-rose-400">*</span></label>
                        <input type="text" x-model="formData.name" required placeholder="e.g. Captain Sarah Jenkins"
                               class="w-full px-3 py-2 bg-[#0b101c] border border-slate-800 rounded-lg text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Username <span class="text-rose-400">*</span></label>
                        <input type="text" x-model="formData.username" required placeholder="e.g. instructor"
                               class="w-full px-3 py-2 bg-[#0b101c] border border-slate-800 rounded-lg text-xs font-mono text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Email Address -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-300 mb-1">Email Address <span class="text-rose-400">*</span></label>
                        <input type="email" x-model="formData.email" required placeholder="instructor@amadeus.training"
                               class="w-full px-3 py-2 bg-[#0b101c] border border-slate-800 rounded-lg text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Password (min 6 chars) <span class="text-rose-400">*</span></label>
                        <input type="password" x-model="formData.password" required minlength="6" placeholder="••••••••"
                               class="w-full px-3 py-2 bg-[#0b101c] border border-slate-800 rounded-lg text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Confirm Password <span class="text-rose-400">*</span></label>
                        <input type="password" x-model="formData.password_confirmation" required minlength="6" placeholder="••••••••"
                               class="w-full px-3 py-2 bg-[#0b101c] border border-slate-800 rounded-lg text-xs text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Office ID -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Amadeus Office ID (9 chars)</label>
                        <input type="text" x-model="formData.office_id" placeholder="LON1A0980" maxlength="10"
                               class="w-full px-3 py-2 bg-[#0b101c] border border-slate-800 rounded-lg text-xs font-mono uppercase text-sky-400 focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Agent Duty Sign -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Agent Sign / Duty Code (6 chars)</label>
                        <input type="text" x-model="formData.agent_id" placeholder="IN01GS" maxlength="10"
                               class="w-full px-3 py-2 bg-[#0b101c] border border-slate-800 rounded-lg text-xs font-mono uppercase text-amber-300 focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <!-- Client-side error prompt -->
                <div x-show="errorMessage" class="p-3 rounded-lg bg-rose-950/60 border border-rose-800 text-rose-300 text-xs" x-text="errorMessage"></div>

                <!-- Navigation & Submit -->
                <div class="pt-4 border-t border-slate-800 flex justify-between items-center">
                    <button type="button" @click="step = 2" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition-colors">
                        Back
                    </button>
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-semibold text-xs flex items-center gap-2 transition-colors shadow-lg shadow-emerald-600/20">
                        <svg x-show="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="isSubmitting ? 'Installing Platform & Creating Account...' : 'Install & Create Instructor Account'"></span>
                    </button>
                </div>
            </form>

            <!-- STEP 4: Installation Finished -->
            <div x-show="step === 4" class="text-center py-6 space-y-6">
                <div class="w-16 h-16 rounded-full bg-emerald-950/80 border-2 border-emerald-500 flex items-center justify-center mx-auto text-emerald-400 shadow-xl shadow-emerald-500/20">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-white">Amadeus GDS Platform Installed Successfully!</h2>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        Database tables have been migrated, default airport and scenario seeds loaded, and the primary instructor account has been created.
                    </p>
                </div>

                <!-- Summary Details Box -->
                <div class="max-w-md mx-auto p-4 rounded-xl bg-[#0b101c] border border-slate-800 text-left space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Instructor Name:</span>
                        <span class="font-semibold text-white" x-text="installedInstructor.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Username:</span>
                        <span class="font-mono text-emerald-400" x-text="installedInstructor.username"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Assigned Role:</span>
                        <span class="font-bold text-amber-300">INSTRUCTOR</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Office ID / Agent Sign:</span>
                        <span class="font-mono text-sky-400" x-text="(installedInstructor.office_id || 'LON1A0980') + ' / ' + (installedInstructor.agent_id || 'IN01GS')"></span>
                    </div>
                </div>

                <!-- Launch Buttons -->
                <div class="pt-4 flex items-center justify-center gap-3">
                    <a href="/dashboard" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs flex items-center gap-2 transition-colors shadow-lg shadow-blue-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span>Open Operations Dashboard</span>
                    </a>
                    <a href="/" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>Launch Cryptic Simulator</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function installerWizard() {
        return {
            step: 1,
            isSubmitting: false,
            errorMessage: '',
            formData: {
                name: 'Flight Instructor',
                username: 'instructor',
                email: 'instructor@amadeus.training',
                password: '',
                password_confirmation: '',
                office_id: 'LON1A0980',
                agent_id: 'IN01GS',
            },
            installedInstructor: {},

            async submitInstallation() {
                this.errorMessage = '';

                if (this.formData.password !== this.formData.password_confirmation) {
                    this.errorMessage = 'Passwords do not match.';
                    return;
                }

                if (this.formData.password.length < 6) {
                    this.errorMessage = 'Password must be at least 6 characters.';
                    return;
                }

                this.isSubmitting = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                try {
                    const res = await fetch('/install', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        this.errorMessage = data.error || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Installation failed.');
                        this.isSubmitting = false;
                        return;
                    }

                    this.installedInstructor = {
                        name: this.formData.name,
                        username: data.instructor?.username || this.formData.username,
                        office_id: data.instructor?.office_id || this.formData.office_id,
                        agent_id: data.instructor?.agent_id || this.formData.agent_id,
                    };

                    this.step = 4;
                } catch (err) {
                    this.errorMessage = 'Network connection failed during installation process.';
                } finally {
                    this.isSubmitting = false;
                }
            }
        };
    }
    </script>
</body>
</html>
