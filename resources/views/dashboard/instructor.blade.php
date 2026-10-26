@extends('layouts.dashboard')

@section('title', 'Instructor Operations Hub - AMADEUS GDS SIM')

@section('content')
<div x-data="instructorDashboard()" x-init="init()" class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- KPI Summary Row -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div>
                <div class="text-[11px] font-mono text-slate-500 uppercase tracking-wider">Enrolled Trainees</div>
                <div class="text-2xl font-black text-slate-900 mt-1" x-text="students.length">{{ count($students) }}</div>
                <div class="text-[11px] text-emerald-600 mt-1 flex items-center gap-1 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Classroom Active</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div>
                <div class="text-[11px] font-mono text-slate-500 uppercase tracking-wider">Active Drills</div>
                <div class="text-2xl font-black text-blue-600 mt-1" x-text="scenarios.length">{{ count($scenarios) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Available assignments</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div>
                <div class="text-[11px] font-mono text-slate-500 uppercase tracking-wider">Submissions</div>
                <div class="text-2xl font-black text-purple-600 mt-1">{{ count($submissions) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Graded & Audited</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div>
                <div class="text-[11px] font-mono text-slate-500 uppercase tracking-wider">Queue Bank</div>
                <div class="text-2xl font-black text-amber-600 mt-1">{{ array_sum(array_column($queueSummary, 'count')) }} PNRs</div>
                <div class="text-[11px] text-amber-700/80 mt-1 font-medium">Simulated agency queues</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
        </div>
    </section>

    <!-- Pending Cadet Approvals Notification Widget (When pending registrations exist) -->
    <template x-if="pendingApprovals.length > 0">
        <section class="bg-amber-50/70 border-2 border-amber-300 rounded-xl overflow-hidden shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-300 flex items-center justify-center text-amber-700">
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-amber-950 flex items-center gap-2">
                            <span>Pending Cadet Registrations</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[11px] font-mono font-bold" x-text="pendingApprovals.length + ' Awaiting Approval'"></span>
                        </h2>
                        <p class="text-xs text-amber-800">These trainees self-registered and require your authorization before simulator access is activated.</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-amber-200 bg-white">
                <table class="w-full text-xs text-left">
                    <thead class="bg-amber-50/80 text-amber-900 uppercase font-mono text-[10px] border-b border-amber-200">
                        <tr>
                            <th class="py-2.5 px-4">Cadet Name</th>
                            <th class="py-2.5 px-4">Username</th>
                            <th class="py-2.5 px-4">Email</th>
                            <th class="py-2.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100 font-mono">
                        <template x-for="cadet in pendingApprovals" :key="cadet.id">
                            <tr class="hover:bg-amber-50/40 transition-colors">
                                <td class="py-2.5 px-4 font-sans font-semibold text-slate-900" x-text="cadet.name || cadet.username"></td>
                                <td class="py-2.5 px-4 text-blue-700 font-bold" x-text="cadet.username"></td>
                                <td class="py-2.5 px-4 text-slate-600 font-sans" x-text="cadet.email"></td>
                                <td class="py-2.5 px-4 text-right space-x-2">
                                    <button @click="approveCadet(cadet.id)" :disabled="isApproving" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded font-bold text-[11px] font-sans transition-colors inline-flex items-center gap-1 shadow-xs">
                                        <span>✓ Approve Access</span>
                                    </button>
                                    <button @click="rejectCadet(cadet.id)" class="px-2.5 py-1 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 rounded font-semibold text-[11px] font-sans transition-colors">
                                        <span>✕ Reject</span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>
    </template>

    <!-- Section 1: Live Classroom Overview & Student Grid -->
    <section id="classroom" class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div id="students-enrollment" class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900 tracking-wide">Live Classroom Grid & Spectator</h2>
                <p class="text-xs text-slate-500">Real-time status monitoring, keystroke streaming, and student account enrollment</p>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-3 flex-wrap gap-2">
                <input type="text"
                       x-model="studentFilter"
                       placeholder="Filter by name, agent..."
                       class="bg-white border border-slate-300 text-slate-800 placeholder:text-slate-400 rounded-lg px-3 py-1.5 text-xs outline-none w-40 sm:w-48 focus:border-blue-500 font-mono">
                <button @click="openCreateStudentModal()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    <span>+ Enroll Student</span>
                </button>
                <button @click="openBatchModal()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>⚡ Batch Provision</span>
                </button>
            </div>
        </div>

        <!-- Student Cards Grid -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="st in filteredStudents" :key="st.id">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col justify-between hover:border-slate-300 hover:shadow-xs transition-all">
                    <div>
                        <!-- Header: Name, Status Badge -->
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 uppercase" x-text="st.username.substring(0, 2)"></div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900" x-text="st.name || st.username"></div>
                                    <div class="text-[10px] font-mono text-slate-500" x-text="'Agent: ' + st.agent_id + ' • ' + st.office_id"></div>
                                </div>
                            </div>

                            <!-- Live Session Status Badge -->
                            <span :class="sessionStatusClass(st.status)" class="px-2 py-0.5 rounded text-[10px] font-bold font-mono tracking-wider flex items-center gap-1 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full" :class="sessionDotClass(st.status)"></span>
                                <span x-text="st.status"></span>
                            </span>
                        </div>

                        <!-- Mini Details -->
                        <div class="bg-white p-2.5 rounded-lg border border-slate-200 font-mono text-[11px] space-y-1 my-3 shadow-xs">
                            <div class="flex items-center justify-between text-slate-500">
                                <span>LAST COMMAND:</span>
                                <span class="text-emerald-700 font-bold truncate max-w-[140px]" x-text="st.last_command"></span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>LAST ACTIVE:</span>
                                <span class="text-slate-700" x-text="st.last_active"></span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>ACTIVE PNR:</span>
                                <span class="text-blue-600 font-bold" x-text="st.active_pnr_locator || 'NONE'"></span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>RECENT ERROR RATE:</span>
                                <span :class="st.error_rate > 30 ? 'text-rose-600 font-bold' : 'text-slate-700'" x-text="st.error_rate + '%'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Action: Spectate Button -->
                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-[10px] text-slate-500" x-text="st.total_commands + ' total cmds • ' + st.submissions_count + ' submissions'"></span>
                        <button @click="spectateStudent(st.username)" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-blue-700 border border-slate-200 rounded text-xs font-semibold flex items-center gap-1 transition-colors shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span>Inspect / Spectate</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <!-- Section 2: Scenario & Drill Builder (Form Interface) -->
    <section id="builder" class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 tracking-wide">Scenario & Drill Builder</h2>
                <p class="text-xs text-slate-500">Configure new mock booking assignments, passenger profiles, and validation rules</p>
            </div>
            <button @click="showBuilderForm = !showBuilderForm" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm">
                <span x-text="showBuilderForm ? 'Hide Builder Form' : '+ New Scenario'"></span>
            </button>
        </div>

        <!-- Builder Form Body (Toggleable) -->
        <div x-show="showBuilderForm" class="p-6 border-b border-slate-200 bg-slate-50/50">
            <form @submit.prevent="saveNewScenario()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-mono text-slate-600 mb-1 font-semibold">SCENARIO ID</label>
                        <input type="text" x-model="newScenario.id" placeholder="e.g. SCN-MC-05" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-slate-600 mb-1 font-semibold">SCENARIO TITLE</label>
                        <input type="text" x-model="newScenario.title" placeholder="e.g. Open-Jaw Transpacific Booking" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-slate-600 mb-1 font-semibold">CATEGORY</label>
                        <select x-model="newScenario.category" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg outline-none focus:border-blue-500">
                            <option value="Round-trip">Round-trip</option>
                            <option value="Multi-city">Multi-city</option>
                            <option value="Infant-in-lap">Infant-in-lap</option>
                            <option value="Schedule Exchange">Schedule Exchange</option>
                            <option value="Urgent Ticketing">Urgent Ticketing</option>
                            <option value="Tariff & Pricing">Tariff & Pricing</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-mono text-slate-600 mb-1 font-semibold">DIFFICULTY LEVEL</label>
                        <select x-model="newScenario.difficulty" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg outline-none">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-slate-600 mb-1 font-semibold">TIME LIMIT (MINUTES)</label>
                        <input type="number" x-model="newScenario.time_limit_minutes" min="5" max="120" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-mono text-slate-600 mb-1 font-semibold">SCENARIO DESCRIPTION & INSTRUCTIONS</label>
                    <textarea x-model="newScenario.description" rows="2" placeholder="Describe the traveler journey, objectives, and any special airline requirements..." required class="w-full bg-white border border-slate-300 text-slate-900 text-xs p-3 rounded-lg outline-none"></textarea>
                </div>

                <!-- Passenger Profile -->
                <div class="p-3.5 bg-white rounded-lg border border-slate-200 space-y-3 shadow-xs">
                    <span class="text-xs font-mono font-bold text-slate-700 block">PASSENGER PROFILE & MANDATORY CRITERIA</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1 font-medium">Passenger Name & Title</label>
                            <input type="text" x-model="newScenario.passengerName" placeholder="e.g. MILLER/DAVID MR" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-1.5 rounded font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1 font-medium">Route & Carrier</label>
                            <input type="text" x-model="newScenario.route" placeholder="e.g. LHR-DXB on EK" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-1.5 rounded font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1 font-medium">Validation Options</label>
                            <div class="flex items-center space-x-3 pt-1 text-xs">
                                <label class="flex items-center space-x-1.5 text-slate-700">
                                    <input type="checkbox" x-model="newScenario.requireTst" class="rounded border-slate-300 text-blue-600">
                                    <span>Require FXP (TST)</span>
                                </label>
                                <label class="flex items-center space-x-1.5 text-slate-700">
                                    <input type="checkbox" x-model="newScenario.requireETicket" class="rounded border-slate-300 text-blue-600">
                                    <span>Require TTP (Ticket)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showBuilderForm = false" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs shadow-sm transition-colors">Save & Publish Assignment</button>
                </div>
            </form>
        </div>

        <!-- Existing Scenarios List -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-2.5">ID</th>
                        <th class="px-4 py-2.5">Title</th>
                        <th class="px-4 py-2.5">Category</th>
                        <th class="px-4 py-2.5">Difficulty</th>
                        <th class="px-4 py-2.5">Time Limit</th>
                        <th class="px-4 py-2.5">Created By</th>
                        <th class="px-4 py-2.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="sc in scenarios" :key="sc.id">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-2.5 font-bold text-blue-600" x-text="sc.id"></td>
                            <td class="px-4 py-2.5 text-slate-900 font-sans font-medium" x-text="sc.title"></td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 text-[10px]" x-text="sc.category"></span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600" x-text="sc.difficulty"></td>
                            <td class="px-4 py-2.5 text-slate-500" x-text="sc.time_limit_minutes + ' mins'"></td>
                            <td class="px-4 py-2.5 text-slate-500" x-text="sc.created_by"></td>
                            <td class="px-4 py-2.5 text-right">
                                <button type="button"
                                        @click="deleteScenario(sc.id, sc.title)"
                                        class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded text-[11px] font-semibold inline-flex items-center gap-1 transition-all"
                                        title="Delete Scenario">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span>Delete</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="scenarios.length === 0" style="display: none;">
                        <td colspan="7" class="px-4 py-6 text-center text-slate-400 italic">No training scenarios currently exist. Create one above.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Section 3: Submissions & Gradebook -->
    <section id="gradebook" class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 tracking-wide">Submissions & Gradebook</h2>
                <p class="text-xs text-slate-500">Automated grading audit results, chronological playback, and score overrides</p>
            </div>
            <div class="text-xs text-slate-500 font-mono">
                Total Submissions: <strong class="text-slate-900">{{ count($submissions) }}</strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-2.5">Trainee</th>
                        <th class="px-4 py-2.5">Scenario</th>
                        <th class="px-4 py-2.5">Status</th>
                        <th class="px-4 py-2.5">Auto Score</th>
                        <th class="px-4 py-2.5">Final Score</th>
                        <th class="px-4 py-2.5">Submitted</th>
                        <th class="px-4 py-2.5">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="sub in submissions" :key="sub.id">
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 font-bold text-slate-900" x-text="sub.user_id"></td>
                            <td class="px-4 py-2.5 text-blue-600 font-semibold" x-text="sub.scenario_id + ' - ' + (sub.scenario?.title || '')"></td>
                            <td class="px-4 py-2.5">
                                <span :class="sub.status === 'reviewed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'" class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase" x-text="sub.status"></span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600" x-text="sub.score + ' / 100'"></td>
                            <td class="px-4 py-2.5 font-bold text-emerald-700" x-text="(sub.manual_override_score !== null ? sub.manual_override_score : sub.score) + ' / 100'"></td>
                            <td class="px-4 py-2.5 text-slate-500" x-text="sub.submitted_at ? new Date(sub.submitted_at).toLocaleDateString() : 'N/A'"></td>
                            <td class="px-4 py-2.5">
                                <button @click="openGradeModal(sub)" class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded text-[11px] font-semibold transition-colors shadow-xs">
                                    Audit & Override
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Section 4: Simulated Queue & Sandbox Management -->
    <section id="queues" class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900 tracking-wide">Simulated Queue & Sandbox Management</h2>
                <p class="text-xs text-slate-500">Broadcast test PNRs directly to student agency queues or reset sandbox environments</p>
            </div>

            <div class="flex items-center space-x-2">
                <button @click="showBroadcastModal = true" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    <span>Broadcast PNR to Queue</span>
                </button>
                <button @click="resetSandbox(null)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-xs font-bold transition-colors">
                    Reset Entire Classroom Sandbox
                </button>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($queueSummary as $q)
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col justify-between shadow-xs">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-mono text-xs font-bold">Queue {{ $q['queueNumber'] }}</span>
                            <span class="text-lg font-black text-slate-900 font-mono">{{ $q['count'] }}</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900 mt-1">{{ $q['name'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ $q['description'] }}</p>
                    </div>
                    <div class="pt-3 border-t border-slate-200 mt-3 flex items-center justify-between text-[11px] font-mono">
                        <span class="text-slate-500">Amadeus Entry:</span>
                        <span class="text-emerald-700 font-bold">QD {{ $q['queueNumber'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Student Live Spectate Modal (Command Stream & Playback) -->
    <div x-show="spectateData" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div @click.away="spectateData = null" class="bg-white border border-slate-200 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col overflow-hidden shadow-2xl">
            <!-- Modal Header -->
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>Live Terminal Spectator:</span>
                            <span class="text-blue-600 font-mono" x-text="spectateData?.student?.name + ' (' + spectateData?.student?.username + ')'"></span>
                        </h3>
                        <span class="text-[11px] text-slate-500 font-mono" x-text="'Office: ' + spectateData?.student?.office_id + ' • Agent: ' + spectateData?.student?.agent_id"></span>
                    </div>
                </div>
                <button @click="spectateData = null" class="text-slate-400 hover:text-slate-700 font-bold text-base">✕</button>
            </div>

            <!-- Terminal Keystroke / Command Stream Timeline -->
            <div class="p-4 overflow-y-auto flex-1 font-mono text-xs space-y-3 bg-slate-900 text-emerald-400">
                <div class="text-slate-400 text-[11px] pb-2 border-b border-slate-800 flex items-center justify-between font-sans">
                    <span>CHRONOLOGICAL TERMINAL LOG STREAM</span>
                    <span x-text="(spectateData?.logs?.length || 0) + ' events recorded'"></span>
                </div>

                <div class="space-y-2">
                    <template x-for="log in (spectateData?.logs || [])" :key="log.id">
                        <div class="p-2.5 rounded bg-black/60 border border-slate-800">
                            <div class="flex items-center justify-between text-[11px]">
                                <div class="flex items-center space-x-2">
                                    <span class="text-slate-500" x-text="'[' + log.timestamp + ']'"></span>
                                    <span class="font-bold" :class="log.is_error ? 'text-rose-400' : 'text-emerald-400'" x-text="'&gt; ' + log.command"></span>
                                </div>
                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-300 font-sans" x-text="log.command_type"></span>
                            </div>

                            <!-- Output lines -->
                            <div class="mt-1.5 pl-3 border-l-2 text-[11px] space-y-0.5" :class="log.is_error ? 'border-rose-500 text-rose-300' : 'border-emerald-600 text-emerald-300/90'">
                                <template x-for="(line, idx) in (log.output_lines || [])" :key="idx">
                                    <div class="whitespace-pre" x-text="line"></div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs">
                <span class="text-slate-500">Viewing active keystroke and response playback buffer.</span>
                <button @click="spectateData = null" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded">Close Stream</button>
            </div>
        </div>
    </div>

    <!-- Audit & Override Modal -->
    <div x-show="selectedSubModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div @click.away="selectedSubModal = null" class="bg-white border border-slate-200 rounded-xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden shadow-2xl">
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Submission Audit & Manual Score Override</h3>
                    <div class="text-[11px] font-mono text-slate-500" x-text="'Student: ' + selectedSubModal?.user_id + ' • Scenario: ' + selectedSubModal?.scenario_id"></div>
                </div>
                <button @click="selectedSubModal = null" class="text-slate-400 hover:text-slate-700 font-bold text-base">✕</button>
            </div>

            <div class="p-5 overflow-y-auto space-y-4 text-xs">
                <!-- Auto Grade Criteria Checklist -->
                <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 space-y-2">
                    <span class="font-bold text-slate-900 font-mono text-xs block mb-1">AUTOMATED PRINT & SEGMENT VALIDATION RESULTS</span>
                    <div class="space-y-1.5 font-mono text-[11px]">
                        <template x-for="chk in (selectedSubModal?.auto_grade_results?.checks || [])" :key="chk.code">
                            <div class="flex items-center justify-between p-1.5 rounded bg-white border border-slate-200 shadow-xs">
                                <span class="flex items-center gap-1.5">
                                    <span :class="chk.passed ? 'text-emerald-600' : 'text-rose-600'" x-text="chk.passed ? '✓' : '✗'"></span>
                                    <span class="text-slate-800 font-sans" x-text="chk.name"></span>
                                </span>
                                <span :class="chk.passed ? 'text-emerald-700' : 'text-rose-700'" class="font-bold" x-text="chk.pointsAwarded + ' / ' + chk.maxPoints + ' pts'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Score Override Form -->
                <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200 space-y-3">
                    <span class="font-bold text-slate-900 font-mono text-xs block">INSTRUCTOR EVALUATION & OVERRIDE</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-600 mb-1 font-semibold">Override Score (0 - 100)</label>
                            <input type="number" min="0" max="100" x-model="overrideScore" class="w-full bg-white border border-slate-300 text-slate-900 font-mono text-xs px-3 py-1.5 rounded outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-slate-600 mb-1 font-semibold">Inline Instructor Feedback</label>
                        <textarea rows="3" x-model="instructorFeedback" placeholder="Enter actionable feedback for the trainee..." class="w-full bg-white border border-slate-300 text-slate-900 text-xs p-2.5 rounded outline-none focus:border-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-end space-x-2">
                <button @click="selectedSubModal = null" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded text-xs">Cancel</button>
                <button @click="submitGradeOverride()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs transition-colors shadow-sm">Save Score & Feedback</button>
            </div>
        </div>
    </div>

    <!-- Broadcast PNR to Queue Modal -->
    <div x-show="showBroadcastModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showBroadcastModal = false" class="bg-white border border-slate-200 rounded-xl max-w-lg w-full flex flex-col overflow-hidden shadow-2xl">
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Broadcast Test PNR to Queue</h3>
                <button @click="showBroadcastModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-base">✕</button>
            </div>

            <form @submit.prevent="broadcastPnr()" class="p-5 space-y-3.5 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">TARGET QUEUE</label>
                        <select x-model="broadcastForm.queue_number" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded outline-none font-mono focus:border-blue-500">
                            <option value="1">Queue 1 - Ticketing Time Limit</option>
                            <option value="7">Queue 7 - Schedule Changes</option>
                            <option value="8">Queue 8 - Waitlist Confirmations</option>
                            <option value="9">Queue 9 - General Messages</option>
                            <option value="87">Queue 87 - Exchange & Reissue</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">URGENCY</label>
                        <select x-model="broadcastForm.urgency" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded outline-none focus:border-blue-500">
                            <option value="NORMAL">NORMAL</option>
                            <option value="HIGH">HIGH</option>
                            <option value="CRITICAL">CRITICAL</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">RECORD LOCATOR (6-CHAR)</label>
                        <input type="text" x-model="broadcastForm.pnr_locator" maxlength="6" placeholder="e.g. M7K2R9" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded font-mono uppercase outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">TARGET STUDENT (OR ALL)</label>
                        <select x-model="broadcastForm.target_user_id" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded outline-none font-mono focus:border-blue-500">
                            <option value="">Broadcast to All Students</option>
                            <template x-for="st in students" :key="st.username">
                                <option :value="st.username" x-text="st.username + ' (' + st.name + ')'"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-mono mb-1 font-semibold">PASSENGER NAME & ITINERARY</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" x-model="broadcastForm.passenger" placeholder="e.g. SMITH/JOHN MR" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-1.5 rounded font-mono outline-none focus:border-blue-500">
                        <input type="text" x-model="broadcastForm.itinerary" placeholder="e.g. LHR-JFK (BA 117)" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-1.5 rounded font-mono outline-none focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-mono mb-1 font-semibold">QUEUE ADVISORY MESSAGE</label>
                    <textarea rows="2" x-model="broadcastForm.message" placeholder="e.g. TTL EXPIRING IN 1 HOUR. ISSUE TICKET OR AUTO-CANCEL..." required class="w-full bg-white border border-slate-300 text-slate-900 text-xs p-2.5 rounded outline-none focus:border-blue-500"></textarea>
                </div>

                <div class="p-3 border-t border-slate-200 bg-slate-50 flex justify-end space-x-2 -mx-5 -mb-5 mt-4">
                    <button type="button" @click="showBroadcastModal = false" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded text-xs">Cancel</button>
                    <button type="submit" class="px-5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded text-xs shadow-sm">Broadcast PNR</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Enroll Single Student -->
    <div x-show="showCreateStudentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
        <div @click.away="showCreateStudentModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Enroll New Student Account</h3>
                        <p class="text-[11px] text-slate-500">Create a dedicated trainee account with office & agent credentials</p>
                    </div>
                </div>
                <button @click="showCreateStudentModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
            </div>

            <div x-show="studentCreateError" class="mt-4 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-mono" x-text="studentCreateError" style="display: none;"></div>

            <form @submit.prevent="submitCreateStudent()" class="mt-4 space-y-3.5 text-xs">
                <div>
                    <label class="block text-slate-600 font-mono mb-1 font-semibold">FULL NAME <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="newStudent.name" placeholder="e.g. John Doe" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg outline-none focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">USERNAME <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="newStudent.username" placeholder="e.g. jdoe" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">EMAIL ADDRESS <span class="text-rose-500">*</span></label>
                        <input type="email" x-model="newStudent.email" placeholder="e.g. jdoe@example.com" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg outline-none focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-slate-600 font-mono font-semibold">INITIAL PASSWORD <span class="text-rose-500">*</span></label>
                        <button type="button" @click="generateRandomStudentPassword()" class="text-[10px] text-blue-600 hover:text-blue-700 font-mono underline font-semibold">Generate Random</button>
                    </div>
                    <input type="text" x-model="newStudent.password" minlength="6" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">OFFICE ID</label>
                        <input type="text" x-model="newStudent.office_id" placeholder="LON1A0980" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono uppercase outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">AGENT ID (OPTIONAL)</label>
                        <input type="text" x-model="newStudent.agent_id" placeholder="Auto-generated" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono uppercase outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                    <button type="button" @click="showCreateStudentModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-xs font-semibold transition-colors">Cancel</button>
                    <button type="submit" :disabled="isCreatingStudent" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold rounded-lg text-xs shadow-sm transition-colors flex items-center gap-1.5">
                        <span x-show="!isCreatingStudent">Create Student Account</span>
                        <span x-show="isCreatingStudent">Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Batch Provision Unlimited Students -->
    <div x-show="showBatchModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
        <div @click.away="showBatchModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Batch Provision Unlimited Students</h3>
                        <p class="text-[11px] text-slate-500">Instantly generate unlimited student accounts for a classroom batch</p>
                    </div>
                </div>
                <button @click="showBatchModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
            </div>

            <div x-show="batchError" class="mt-4 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-mono" x-text="batchError" style="display: none;"></div>

            <form @submit.prevent="submitBatchStudents()" class="mt-4 space-y-3.5 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">NUMBER OF ACCOUNTS <span class="text-rose-500">*</span></label>
                        <input type="number" x-model="batchForm.count" min="1" max="500" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-mono mb-1 font-semibold">USERNAME PREFIX</label>
                        <input type="text" x-model="batchForm.prefix" placeholder="e.g. trainee or student" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-mono mb-1 font-semibold">DEFAULT PASSWORD <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="batchForm.password" minlength="6" required class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-slate-600 font-mono mb-1 font-semibold">ASSIGNED OFFICE ID</label>
                    <input type="text" x-model="batchForm.office_id" placeholder="LON1A0980" class="w-full bg-white border border-slate-300 text-slate-900 text-xs px-3 py-2 rounded-lg font-mono uppercase outline-none focus:border-indigo-500">
                </div>

                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-[11px] text-slate-600 space-y-1">
                    <div class="text-slate-800 font-semibold">Automatic Generation Details:</div>
                    <div>• Usernames will be sequential: <span class="font-mono text-blue-600 font-bold" x-text="(batchForm.prefix || 'student') + '_1, ' + (batchForm.prefix || 'student') + '_2...'"></span></div>
                    <div>• Emails will be: <span class="font-mono text-blue-600" x-text="(batchForm.prefix || 'student') + '_N@classroom.local'"></span></div>
                    <div>• Role is securely set to <span class="font-mono text-emerald-700 font-bold">student</span> without instructor privileges.</div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                    <button type="button" @click="showBatchModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-xs font-semibold transition-colors">Cancel</button>
                    <button type="submit" :disabled="isBatchCreating" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold rounded-lg text-xs shadow-sm transition-colors flex items-center gap-1.5">
                        <span x-show="!isBatchCreating" x-text="'Generate ' + (batchForm.count || 0) + ' Accounts'"></span>
                        <span x-show="isBatchCreating">Generating Accounts...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Credentials Display & Copy -->
    <div x-show="showCredentialsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
        <div @click.away="showCredentialsModal = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-2xl p-6 shadow-2xl relative flex flex-col max-h-[85vh]">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900" x-text="createdCredentialsTitle"></h3>
                        <p class="text-[11px] text-emerald-600 font-semibold">Accounts created and active in system</p>
                    </div>
                </div>
                <button @click="showCredentialsModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
            </div>

            <div class="my-4 overflow-y-auto flex-1 border border-slate-200 rounded-lg">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] sticky top-0 border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-2">Username</th>
                            <th class="px-3 py-2">Password</th>
                            <th class="px-3 py-2">Email</th>
                            <th class="px-3 py-2">Office</th>
                            <th class="px-3 py-2">Agent ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="c in createdCredentialsList" :key="c.username">
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-bold text-blue-600" x-text="c.username"></td>
                                <td class="px-3 py-2 font-mono text-amber-700 font-bold" x-text="c.password"></td>
                                <td class="px-3 py-2 text-slate-700" x-text="c.email"></td>
                                <td class="px-3 py-2 text-slate-500" x-text="c.office_id"></td>
                                <td class="px-3 py-2 text-slate-500" x-text="c.agent_id"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-between shrink-0">
                <button @click="copyCredentials()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span>Copy All to Clipboard</span>
                </button>
                <button type="button" @click="showCredentialsModal = false" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs shadow-sm transition-colors">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function instructorDashboard() {
    const studentsData = @json($students);
    const pendingApprovalsData = @json($pendingApprovals);
    const submissionsData = @json($submissions);
    const scenariosData = @json($scenarios);

    return {
        students: studentsData,
        pendingApprovals: pendingApprovalsData,
        submissions: submissionsData,
        scenarios: scenariosData,
        studentFilter: '',
        isApproving: false,

        async approveCadet(cadetId) {
            this.isApproving = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
                const res = await fetch(`/api/instructor/students/${cadetId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });
                if (res.ok) {
                    const approvedIndex = this.pendingApprovals.findIndex(c => c.id === cadetId);
                    if (approvedIndex !== -1) {
                        const approved = this.pendingApprovals.splice(approvedIndex, 1)[0];
                        this.students.push({
                            id: approved.id,
                            username: approved.username,
                            name: approved.name || approved.username,
                            email: approved.email,
                            office_id: approved.office_id || 'LON1A0980',
                            agent_id: approved.agent_id || '1234AB',
                            status: 'Online',
                            last_command: 'None',
                            last_active: 'Just now',
                            total_commands: 0,
                            submissions_count: 0,
                            active_pnr_locator: null,
                            error_rate: 0,
                        });
                    }
                }
            } catch (err) {
                console.error('Failed to approve cadet', err);
            } finally {
                this.isApproving = false;
            }
        },

        async rejectCadet(cadetId) {
            if (!confirm('Are you sure you want to reject and remove this cadet registration?')) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
                const res = await fetch(`/api/instructor/students/${cadetId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });
                if (res.ok) {
                    this.pendingApprovals = this.pendingApprovals.filter(c => c.id !== cadetId);
                }
            } catch (err) {
                console.error('Failed to reject cadet', err);
            }
        },

        showCreateStudentModal: false,
        isCreatingStudent: false,
        studentCreateError: '',
        newStudent: {
            name: '',
            username: '',
            email: '',
            password: '',
            office_id: 'LON1A0980',
            agent_id: ''
        },

        showBatchModal: false,
        isBatchCreating: false,
        batchError: '',
        batchForm: {
            count: 5,
            prefix: 'trainee',
            password: 'Amadeus@2026',
            office_id: 'LON1A0980'
        },

        showCredentialsModal: false,
        createdCredentialsTitle: '',
        createdCredentialsList: [],

        showBuilderForm: false,
        newScenario: {
            id: '',
            title: '',
            category: 'Round-trip',
            difficulty: 'Beginner',
            time_limit_minutes: 30,
            description: '',
            passengerName: '',
            route: '',
            requireTst: true,
            requireETicket: false
        },

        spectateData: null,
        selectedSubModal: null,
        overrideScore: 100,
        instructorFeedback: '',

        showBroadcastModal: false,
        broadcastForm: {
            queue_number: 1,
            category: 'TICKETING',
            urgency: 'NORMAL',
            pnr_locator: '',
            target_user_id: '',
            passenger: '',
            itinerary: '',
            message: ''
        },

        init() {},

        get filteredStudents() {
            if (!this.studentFilter.trim()) return this.students;
            const q = this.studentFilter.toLowerCase();
            return this.students.filter(s =>
                (s.username || '').toLowerCase().includes(q) ||
                (s.name || '').toLowerCase().includes(q) ||
                (s.agent_id || '').toLowerCase().includes(q)
            );
        },

        sessionStatusClass(status) {
            switch (status) {
                case 'In Terminal': return 'bg-sky-50 text-sky-700 border border-sky-200';
                case 'Online': return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                case 'Error Loop': return 'bg-rose-50 text-rose-700 border border-rose-200 animate-pulse';
                default: return 'bg-slate-100 text-slate-600 border border-slate-200';
            }
        },

        sessionDotClass(status) {
            switch (status) {
                case 'In Terminal': return 'bg-sky-500';
                case 'Online': return 'bg-emerald-500';
                case 'Error Loop': return 'bg-rose-500';
                default: return 'bg-slate-400';
            }
        },

        async spectateStudent(username) {
            try {
                const res = await fetch(`/api/instructor/spectate/${username}`);
                if (!res.ok) throw new Error('Failed to load spectate data');
                this.spectateData = await res.json();
            } catch (e) {
                alert('Could not spectate student: ' + e.message);
            }
        },

        openGradeModal(sub) {
            this.selectedSubModal = sub;
            this.overrideScore = sub.manual_override_score !== null ? sub.manual_override_score : sub.score;
            this.instructorFeedback = sub.instructor_feedback || '';
        },

        async submitGradeOverride() {
            if (!this.selectedSubModal) return;

            try {
                const res = await fetch(`/api/instructor/submissions/${this.selectedSubModal.id}/grade`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        score: parseInt(this.overrideScore, 10),
                        feedback: this.instructorFeedback
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.selectedSubModal.manual_override_score = data.submission.manual_override_score;
                    this.selectedSubModal.instructor_feedback = data.submission.instructor_feedback;
                    this.selectedSubModal.status = 'reviewed';
                    this.selectedSubModal = null;
                    alert('Grade override and instructor feedback saved!');
                }
            } catch (e) {
                alert('Failed to save score override.');
            }
        },

        async saveNewScenario() {
            const payload = {
                id: this.newScenario.id.toUpperCase(),
                title: this.newScenario.title,
                category: this.newScenario.category,
                difficulty: this.newScenario.difficulty,
                time_limit_minutes: parseInt(this.newScenario.time_limit_minutes, 10),
                description: this.newScenario.description,
                passenger_profile: {
                    names: [{ lastName: this.newScenario.passengerName || 'SMITH/JOHN MR', firstName: '', title: 'MR', type: 'ADT' }],
                    contactPhone: 'LON 44-20-7111-2222-A'
                },
                flight_segments: [
                    { origin: 'LHR', destination: 'JFK', date: '15NOV', airline: 'BA' }
                ],
                validation_criteria: {
                    mandatoryPrint: { name: true, itinerary: true, contact: true, ticketing: true, receivedFrom: true },
                    minSegments: 1,
                    requireTst: this.newScenario.requireTst,
                    requireETicket: this.newScenario.requireETicket
                }
            };

            try {
                const res = await fetch('/api/instructor/scenarios', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    alert('New booking scenario published to students successfully!');
                    window.location.reload();
                }
            } catch (e) {
                alert('Failed to publish scenario.');
            }
        },

        async deleteScenario(id, title) {
            const confirmed = confirm(`Are you sure you want to delete scenario "${id}" (${title || ''})?\nAll student submissions associated with this drill will also be removed.`);
            if (!confirmed) {
                return;
            }

            try {
                const res = await fetch(`/api/instructor/scenarios/${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || data.message || 'Failed to delete scenario');
                }

                // Remove from local scenarios array
                this.scenarios = this.scenarios.filter(s => s.id !== id);
                // Remove corresponding submissions from gradebook table
                this.submissions = this.submissions.filter(sub => sub.scenario_id !== id);

                alert(data.message || `Scenario '${id}' deleted successfully.`);
            } catch (err) {
                alert('Error deleting scenario: ' + err.message);
            }
        },

        async broadcastPnr() {
            try {
                const res = await fetch('/api/instructor/queues/broadcast', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.broadcastForm)
                });

                const data = await res.json();
                if (data.success) {
                    alert(`PNR ${this.broadcastForm.pnr_locator} broadcasted to Queue ${this.broadcastForm.queue_number}!`);
                    this.showBroadcastModal = false;
                    window.location.reload();
                }
            } catch (e) {
                alert('Failed to broadcast PNR to queue.');
            }
        },

        openCreateStudentModal() {
            this.studentCreateError = '';
            this.newStudent = {
                name: '',
                username: '',
                email: '',
                password: 'Trainee@' + Math.floor(1000 + Math.random() * 9000),
                office_id: 'LON1A0980',
                agent_id: 'ST' + Math.floor(10 + Math.random() * 90) + 'AB'
            };
            this.showCreateStudentModal = true;
        },

        generateRandomStudentPassword() {
            this.newStudent.password = 'Trainee@' + Math.floor(1000 + Math.random() * 9000) + '$';
        },

        async submitCreateStudent() {
            this.studentCreateError = '';
            this.isCreatingStudent = true;
            try {
                const res = await fetch('/api/instructor/students', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newStudent)
                });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Failed to create student'));
                }

                this.students.unshift(data.student);
                this.showCreateStudentModal = false;

                this.createdCredentialsTitle = `Student '${data.student.username}' Created`;
                this.createdCredentialsList = [{
                    username: data.student.username,
                    email: data.student.email,
                    password: this.newStudent.password,
                    office_id: data.student.office_id,
                    agent_id: data.student.agent_id
                }];
                this.showCredentialsModal = true;
            } catch (err) {
                this.studentCreateError = err.message;
            } finally {
                this.isCreatingStudent = false;
            }
        },

        openBatchModal() {
            this.batchError = '';
            this.batchForm = {
                count: 5,
                prefix: 'trainee',
                password: 'Amadeus@' + new Date().getFullYear(),
                office_id: 'LON1A0980'
            };
            this.showBatchModal = true;
        },

        async submitBatchStudents() {
            this.batchError = '';
            this.isBatchCreating = true;
            try {
                const res = await fetch('/api/instructor/students/batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.batchForm)
                });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Failed to batch create students');
                }

                data.students.forEach(st => this.students.unshift(st));
                this.showBatchModal = false;

                this.createdCredentialsTitle = `${data.count} Student Accounts Generated`;
                this.createdCredentialsList = data.students.map(st => ({
                    username: st.username,
                    email: st.email,
                    password: st.plain_password || this.batchForm.password,
                    office_id: st.office_id,
                    agent_id: st.agent_id
                }));
                this.showCredentialsModal = true;
            } catch (err) {
                this.batchError = err.message;
            } finally {
                this.isBatchCreating = false;
            }
        },

        copyCredentials() {
            const text = this.createdCredentialsList.map(c =>
                `Username: ${c.username} | Password: ${c.password} | Email: ${c.email} | Office: ${c.office_id} | Agent: ${c.agent_id}`
            ).join('\n');
            navigator.clipboard.writeText(text).then(() => {
                alert('All student credentials copied to clipboard!');
            }).catch(() => {
                prompt('Copy student credentials below:', text);
            });
        },

        async resetSandbox(studentUsername) {
            if (!confirm(studentUsername ? `Reset sandbox for ${studentUsername}?` : 'Are you sure you want to reset the entire classroom sandbox? All pending queue items and logs will be purged.')) {
                return;
            }

            try {
                const res = await fetch('/api/instructor/sandbox/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ target_user_id: studentUsername })
                });

                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                }
            } catch (e) {
                alert('Sandbox reset failed.');
            }
        }
    };
}
</script>
@endsection
