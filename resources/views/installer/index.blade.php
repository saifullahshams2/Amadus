<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Installer &bull; AMADEUS GDS SIM</title>
    <link rel="icon" type="image/png" href="{{ asset('airplane-ticket.png') }}">
    <link rel="shortcut icon" href="{{ asset('airplane-ticket.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --background: #f8fafc;
            --foreground: #0f172a;
        }
        body {
            background-color: var(--background);
            color: var(--foreground);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-center items-center p-4 sm:p-6 bg-[#f8fafc] text-slate-800 relative overflow-y-auto">
    <!-- Ambient background glow -->
    <div class="fixed inset-0 pointer-events-none opacity-40">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-100 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-100 rounded-full blur-3xl"></div>
    </div>

    <div x-data="installerWizard()" x-cloak class="w-full max-w-3xl bg-white border border-slate-200 rounded-2xl shadow-xl relative z-10 overflow-hidden my-8">
        <!-- Header Banner -->
        <div class="bg-slate-50 border-b border-slate-200 px-4 sm:px-6 py-4 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('airplane-ticket.png') }}" alt="Logo" class="w-9 h-9 sm:w-10 sm:h-10 object-contain">
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>AMADEUS GDS SIM</span>
                        <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-bold">Installer</span>
                    </h1>
                    <p class="text-xs text-slate-500">First-time system setup, database selection & instructor provisioning</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>PHP {{ PHP_VERSION }}</span>
            </div>
        </div>

        <!-- Wizard Step Tracker -->
        <div class="bg-slate-50/70 border-b border-slate-200 px-3 sm:px-6 py-3.5 overflow-x-auto">
            <div class="flex items-center justify-between">
                <!-- Step 1 Indicator -->
                <div class="flex items-center gap-2">
                    <div :class="step >= 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                        <span x-show="step > 1">✓</span>
                        <span x-show="step === 1">1</span>
                    </div>
                    <span :class="step === 1 ? 'text-slate-900 font-semibold' : 'text-slate-500'" class="text-xs hidden md:inline">Requirements</span>
                </div>
                <div class="h-0.5 flex-1 mx-3 bg-slate-200" :class="step > 1 ? 'bg-blue-600' : ''"></div>

                <!-- Step 2 Indicator -->
                <div class="flex items-center gap-2">
                    <div :class="step >= 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                        <span x-show="step > 2">✓</span>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <span :class="step === 2 ? 'text-slate-900 font-semibold' : 'text-slate-500'" class="text-xs hidden md:inline">Database (SQLite / MySQL)</span>
                </div>
                <div class="h-0.5 flex-1 mx-3 bg-slate-200" :class="step > 2 ? 'bg-blue-600' : ''"></div>

                <!-- Step 3 Indicator -->
                <div class="flex items-center gap-2">
                    <div :class="step >= 3 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                        <span x-show="step > 3">✓</span>
                        <span x-show="step <= 3">3</span>
                    </div>
                    <span :class="step === 3 ? 'text-slate-900 font-semibold' : 'text-slate-500'" class="text-xs hidden md:inline">Instructor Account</span>
                </div>
                <div class="h-0.5 flex-1 mx-3 bg-slate-200" :class="step > 3 ? 'bg-blue-600' : ''"></div>

                <!-- Step 4 Indicator -->
                <div class="flex items-center gap-2">
                    <div :class="step === 4 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors">
                        4
                    </div>
                    <span :class="step === 4 ? 'text-slate-900 font-semibold' : 'text-slate-500'" class="text-xs hidden md:inline">Installed & Locked</span>
                </div>
            </div>
        </div>

        <!-- Wizard Forms Container -->
        <div class="p-6 sm:p-8">
            <!-- Server-side errors banner -->
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="font-bold flex items-center gap-2 mb-1 text-rose-900">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
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
                    <h2 class="text-base font-semibold text-slate-900">System Prerequisites & Permissions</h2>
                    <p class="text-xs text-slate-500 mt-1">Verifying PHP runtime, database drivers, core extensions, and directory write permissions.</p>
                </div>

                <!-- PHP Version -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 font-mono text-xs font-bold">
                            PHP
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-900">PHP Version &ge; 8.2.0</div>
                            <div class="text-[11px] text-slate-500">Current: <span class="font-mono text-slate-700 font-semibold">{{ PHP_VERSION }}</span></div>
                        </div>
                    </div>
                    @if($requirements['php_version']['satisfied'])
                        <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1">
                            ✓ Passed
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1">
                            ✕ Upgrade Required
                        </span>
                    @endif
                </div>

                <!-- Database Drivers Grid -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider block">Database Drivers (At least 1 required)</span>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-xs text-slate-900 block">SQLite Driver (pdo_sqlite)</span>
                                <span class="text-[10px] text-slate-500">Embedded file database</span>
                            </div>
                            @if(!empty($requirements['database_drivers']['sqlite']))
                                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">AVAILABLE</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">MISSING</span>
                            @endif
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-xs text-slate-900 block">MySQL Driver (pdo_mysql)</span>
                                <span class="text-[10px] text-slate-500">Production SQL server</span>
                            </div>
                            @if(!empty($requirements['database_drivers']['mysql']))
                                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">AVAILABLE</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">MISSING</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Required Extensions Grid -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider block">Core PHP Extensions</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach($requirements['extensions'] as $ext => $loaded)
                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                                <span class="font-mono text-xs text-slate-700">{{ $ext }}</span>
                                @if($loaded)
                                    <span class="text-emerald-600 text-xs font-bold">✓</span>
                                @else
                                    <span class="text-rose-600 text-xs font-bold">✕</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Writable Directories -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider block">Directory Permissions</span>
                    <div class="space-y-2">
                        @foreach($requirements['directories'] as $dir => $writable)
                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                <span class="font-mono text-slate-700">{{ $dir }}</span>
                                @if($writable)
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">WRITABLE</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">READ ONLY</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Navigation -->
                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="button"
                            @click="step = 2"
                            :disabled="!{{ $requirements['satisfied'] ? 'true' : 'false' }}"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-xs flex items-center gap-2 transition-colors shadow-sm">
                        <span>Continue to Database Setup</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Database Configuration (SQLite & MySQL) -->
            <div x-show="step === 2" class="space-y-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Select & Configure Database</h2>
                    <p class="text-xs text-slate-500 mt-1">Choose between SQLite (file-based, zero configuration) or MySQL (dedicated database server).</p>
                </div>

                <!-- Database Engine Selection Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- SQLite Option -->
                    <label class="relative p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                           :class="dbConfig.driver === 'sqlite' ? 'bg-blue-50/50 border-blue-500 ring-1 ring-blue-500' : 'bg-slate-50 border-slate-200 hover:border-slate-300'">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="database_driver" value="sqlite" x-model="dbConfig.driver" @change="resetDbStatus()" class="text-blue-600 focus:ring-0">
                                <span class="font-bold text-sm text-slate-900">SQLite</span>
                            </div>
                            <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded bg-blue-100 text-blue-700 border border-blue-200 font-semibold">File Database</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-2">Zero setup. Database stored locally in a single file. Perfect for standalone installations & quick setup.</p>
                    </label>

                    <!-- MySQL Option -->
                    <label class="relative p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                           :class="dbConfig.driver === 'mysql' ? 'bg-blue-50/50 border-blue-500 ring-1 ring-blue-500' : 'bg-slate-50 border-slate-200 hover:border-slate-300'">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="database_driver" value="mysql" x-model="dbConfig.driver" @change="resetDbStatus()" class="text-blue-600 focus:ring-0">
                                <span class="font-bold text-sm text-slate-900">MySQL / MariaDB</span>
                            </div>
                            <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200 font-semibold">SQL Server</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-2">High concurrency. Connect to an external or local MySQL / MariaDB server with credentials.</p>
                    </label>
                </div>

                <!-- SQLite Configuration Sub-panel -->
                <div x-show="dbConfig.driver === 'sqlite'" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">SQLite Database File Path</label>
                        <input type="text" x-model="dbConfig.database_path" placeholder="database/database.sqlite"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-blue-500">
                        <span class="text-[11px] text-slate-500 mt-1 block">Relative to application root. File will be created automatically if missing.</span>
                    </div>
                </div>

                <!-- MySQL Configuration Sub-panel -->
                <div x-show="dbConfig.driver === 'mysql'" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3.5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Database Host <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="dbConfig.host" required placeholder="127.0.0.1 or localhost"
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Port</label>
                            <input type="number" x-model="dbConfig.port" placeholder="3306"
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Database Name <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="dbConfig.database" required placeholder="amadeus_gds"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-blue-500">
                        <span class="text-[11px] text-slate-500 mt-1 block">The database will be automatically created if your MySQL user has CREATE DATABASE permission.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Username <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="dbConfig.username" required placeholder="root"
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Password</label>
                            <input type="password" x-model="dbConfig.password" placeholder="••••••••"
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Test Connection Button & Status Banner -->
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="testDatabaseConnection()"
                                :disabled="isTestingDb"
                                class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 disabled:opacity-50 text-slate-800 font-semibold text-xs flex items-center gap-2 transition-colors border border-slate-300">
                            <svg x-show="isTestingDb" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="isTestingDb ? 'Testing Connection...' : 'Test Database Connection'"></span>
                        </button>
                        <span class="text-[11px] text-slate-500">Verify connection before proceeding</span>
                    </div>

                    <!-- Connection Success Prompt -->
                    <div x-show="dbTestSuccessMessage" class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span x-text="dbTestSuccessMessage"></span>
                    </div>

                    <!-- Connection Error Prompt -->
                    <div x-show="dbTestErrorMessage" class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="dbTestErrorMessage"></span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-between">
                    <button type="button" @click="step = 1" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                        Back
                    </button>
                    <button type="button" @click="proceedToInstructorStep()"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-2 transition-colors shadow-sm">
                        <span>Continue to Instructor Setup</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Instructor Account Creation Form -->
            <form x-show="step === 3" @submit.prevent="submitInstallation()" class="space-y-5">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Provision Primary Flight Instructor</h2>
                    <p class="text-xs text-slate-500 mt-1">This will be the sole administrative account created during setup. No demo accounts or student accounts will be created.</p>
                </div>

                <!-- Security Note -->
                <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span><strong>Instructor Exclusivity:</strong> Only this flight instructor account will be generated. Once installed, the installer is locked and cannot be rerun.</span>
                </div>

                <!-- Form Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Instructor Full Name -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Instructor Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required placeholder="e.g. Captain Sarah Jenkins"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Username <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" x-model="formData.username" required placeholder="e.g. instructor" autocomplete="username"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Email Address -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" x-model="formData.email" required placeholder="instructor@amadeus.training" autocomplete="email"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Password (min 6 chars) <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" x-model="formData.password" required minlength="6" placeholder="••••••••" autocomplete="new-password"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Confirm Password <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" x-model="formData.password_confirmation" required minlength="6" placeholder="••••••••" autocomplete="new-password"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Office ID -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Amadeus Office ID (9 chars)</label>
                        <input type="text" name="office_id" x-model="formData.office_id" placeholder="LON1A0980" maxlength="10"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono uppercase text-blue-600 focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Agent Duty Sign -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Agent Sign / Duty Code (6 chars)</label>
                        <input type="text" name="agent_id" x-model="formData.agent_id" placeholder="IN01GS" maxlength="10"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono uppercase text-amber-700 focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <!-- Client-side error prompt -->
                <div x-show="errorMessage" class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs" x-text="errorMessage"></div>

                <!-- Navigation & Submit -->
                <div class="pt-4 border-t border-slate-200 flex justify-between items-center">
                    <button type="button" @click="step = 2" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                        Back
                    </button>
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold text-xs flex items-center gap-2 transition-colors shadow-sm">
                        <svg x-show="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="isSubmitting ? 'Configuring DB & Installing...' : 'Install & Create Instructor Account'"></span>
                    </button>
                </div>
            </form>

            <!-- STEP 4: Installation Finished & Locked -->
            <div x-show="step === 4" class="text-center py-6 space-y-6">
                <div class="w-16 h-16 rounded-full bg-emerald-50 border-2 border-emerald-500 flex items-center justify-center mx-auto text-emerald-600 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">AMADEUS GDS SIM Installed Successfully!</h2>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Database migrated, reference airports & scenarios loaded, and your primary flight instructor account is active.
                    </p>
                </div>

                <!-- Installer Lock Security Badge -->
                <div class="max-w-md mx-auto p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-left space-y-1">
                    <div class="flex items-center gap-2 text-xs font-bold text-emerald-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <span>INSTALLER LOCKED & REMOVED</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        The installer lock file was written to <code class="font-mono text-slate-800 font-semibold">storage/installed</code>. All installer routes are now deactivated to prevent unauthorized re-installation.
                    </p>
                </div>

                <!-- Summary Details Box -->
                <div class="max-w-md mx-auto p-4 rounded-xl bg-slate-50 border border-slate-200 text-left space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Database Driver:</span>
                        <span class="font-mono font-bold text-slate-900 uppercase" x-text="installedData.database_driver || dbConfig.driver"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Instructor Name:</span>
                        <span class="font-semibold text-slate-900" x-text="installedInstructor.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Username:</span>
                        <span class="font-mono text-emerald-700 font-bold" x-text="installedInstructor.username"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Assigned Role:</span>
                        <span class="font-bold text-amber-700">INSTRUCTOR</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Office ID / Agent Sign:</span>
                        <span class="font-mono text-blue-700 font-semibold" x-text="(installedInstructor.office_id || 'LON1A0980') + ' / ' + (installedInstructor.agent_id || 'IN01GS')"></span>
                    </div>
                </div>

                <!-- Launch Buttons -->
                <div class="pt-4 flex items-center justify-center gap-3">
                    <a href="/dashboard" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-2 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span>Open Operations Dashboard</span>
                    </a>
                    <a href="/" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>Main Portal</span>
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
            isTestingDb: false,
            dbTestSuccessMessage: '',
            dbTestErrorMessage: '',
            errorMessage: '',

            dbConfig: {
                driver: '{{ $defaultDriver ?? "sqlite" }}',
                database_path: 'database/database.sqlite',
                host: '127.0.0.1',
                port: 3306,
                database: 'amadeus_gds',
                username: 'root',
                password: '',
            },

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
            installedData: {},

            resetDbStatus() {
                this.dbTestSuccessMessage = '';
                this.dbTestErrorMessage = '';
            },

            async testDatabaseConnection() {
                this.resetDbStatus();
                this.isTestingDb = true;

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch('{{ route("installer.test-db") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify(this.dbConfig)
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.dbTestSuccessMessage = data.message;
                    } else {
                        this.dbTestErrorMessage = data.message || 'Connection test failed';
                    }
                } catch (err) {
                    this.dbTestErrorMessage = 'Network error during database test: ' + err.message;
                } finally {
                    this.isTestingDb = false;
                }
            },

            proceedToInstructorStep() {
                this.step = 3;
            },

            async submitInstallation() {
                this.errorMessage = '';

                if (!this.formData.name || !this.formData.username || !this.formData.email) {
                    this.errorMessage = 'Please provide all required instructor information.';
                    return;
                }

                if (!this.formData.password) {
                    this.errorMessage = 'Please enter a password for the instructor account.';
                    return;
                }

                if (this.formData.password.length < 6) {
                    this.errorMessage = 'Instructor password must be at least 6 characters.';
                    return;
                }

                if (this.formData.password !== this.formData.password_confirmation) {
                    this.errorMessage = 'Password and password confirmation do not match.';
                    return;
                }

                this.isSubmitting = true;

                const payload = {
                    name: this.formData.name,
                    username: this.formData.username,
                    email: this.formData.email,
                    password: this.formData.password,
                    password_confirmation: this.formData.password_confirmation,
                    office_id: this.formData.office_id || 'LON1A0980',
                    agent_id: this.formData.agent_id || 'IN01GS',
                    database_driver: this.dbConfig.driver,
                    db_path: this.dbConfig.database_path,
                    db_host: this.dbConfig.host,
                    db_port: this.dbConfig.port,
                    db_database: this.dbConfig.database,
                    db_username: this.dbConfig.username,
                    db_password: this.dbConfig.password
                };

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch('{{ route("installer.install") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        if (data.errors) {
                            const errs = Object.values(data.errors).flat();
                            this.errorMessage = errs.join('; ');
                        } else {
                            this.errorMessage = data.message || data.error || 'Installation encountered an error';
                        }
                        this.isSubmitting = false;
                        return;
                    }

                    if (data.success) {
                        this.installedInstructor = data.instructor || {};
                        this.installedData = data;
                        this.step = 4;
                    }
                } catch (err) {
                    this.errorMessage = 'Network error submitting installation: ' + err.message;
                } finally {
                    this.isSubmitting = false;
                }
            }
        };
    }
    </script>
</body>
</html>
