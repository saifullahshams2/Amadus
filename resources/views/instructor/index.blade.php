@extends('layouts.app')

@section('title', 'Instructor Operations Hub - Amadeus GDS')

@section('content')
<div x-data="instructorHub()" class="flex-1 flex flex-col h-full overflow-hidden bg-[#070b14] text-slate-200">
    <!-- Hub Header -->
    <header class="bg-[#0b101c] border-b border-slate-800 px-6 py-3 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-3">
            <a href="/dashboard" class="p-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition-colors" title="Back to Dashboard">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-base font-bold text-white tracking-wide">Instructor Operations Hub</h1>
                <p class="text-[11px] text-slate-400">Live trainee progress monitoring, assessment audits, and PNR inspection</p>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-1.5 px-3 py-1 bg-emerald-950/40 border border-emerald-800/60 rounded-lg text-xs font-mono text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>SYSTEM ACTIVE (SQLite / MySQL / MariaDB)</span>
            </div>
            <a href="/" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold transition-colors">
                Open Simulator
            </a>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="flex-1 p-6 overflow-y-auto space-y-6">
        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-[#0e1626] border border-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white" x-text="students.length">{{ count($students) }}</div>
                    <div class="text-xs text-slate-400">Enrolled Trainees</div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-[#0e1626] border border-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white">{{ count($progress) }}</div>
                    <div class="text-xs text-slate-400">Lessons Attempted</div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-[#0e1626] border border-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-600/20 text-amber-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white">{{ count($pnrs) }}</div>
                    <div class="text-xs text-slate-400">Committed PNR Records</div>
                </div>
            </div>
        </div>

        <!-- Student Roster -->
        <div class="rounded-xl bg-[#0e1626] border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-white tracking-wide">Enrolled Student Roster</h2>
                    <p class="text-xs text-slate-400">Manage trainee accounts with full access control</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="openCreateStudentModal()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-md shadow-blue-600/20">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        <span>+ Enroll Student</span>
                    </button>
                    <button @click="openBatchModal()" class="px-3 py-1.5 bg-indigo-600/80 hover:bg-indigo-600 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 border border-indigo-500/30">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>⚡ Batch Provision</span>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#131c2e] text-slate-400 uppercase text-[10px]">
                        <tr>
                            <th class="px-4 py-2.5">Username</th>
                            <th class="px-4 py-2.5">Name</th>
                            <th class="px-4 py-2.5">Email</th>
                            <th class="px-4 py-2.5">Assigned Office</th>
                            <th class="px-4 py-2.5">Agent ID</th>
                            <th class="px-4 py-2.5">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <template x-for="st in students" :key="st.id">
                            <tr class="hover:bg-[#131c2e]/50">
                                <td class="px-4 py-2.5 font-bold text-sky-400" x-text="st.username"></td>
                                <td class="px-4 py-2.5 text-white" x-text="st.name || st.username"></td>
                                <td class="px-4 py-2.5 text-slate-300" x-text="st.email"></td>
                                <td class="px-4 py-2.5 text-slate-400" x-text="st.office_id"></td>
                                <td class="px-4 py-2.5 text-slate-400" x-text="st.agent_id"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="st.created_at"></td>
                            </tr>
                        </template>
                        <tr x-show="students.length === 0" style="display: none;">
                            <td colspan="6" class="px-4 py-4 text-center text-slate-500 italic">No students registered yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Confirmed PNR Audit Table -->
        <div class="rounded-xl bg-[#0e1626] border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-white tracking-wide">Student PNR Audit Trail</h2>
                <input type="text"
                       x-model="search"
                       placeholder="Filter by locator, student, office..."
                       class="bg-[#131c2e] border border-slate-700 text-white rounded-lg px-3 py-1.5 text-xs outline-none w-full sm:w-64">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#131c2e] text-slate-400 uppercase text-[10px]">
                        <tr>
                            <th class="px-4 py-2.5">Record Locator</th>
                            <th class="px-4 py-2.5">Trainee</th>
                            <th class="px-4 py-2.5">Office</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5">Segments</th>
                            <th class="px-4 py-2.5">Updated</th>
                            <th class="px-4 py-2.5">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <template x-for="pnr in filteredPnrs" :key="pnr.record_locator">
                            <tr class="hover:bg-[#131c2e]/50">
                                <td class="px-4 py-2.5 font-bold text-sky-400" x-text="pnr.record_locator"></td>
                                <td class="px-4 py-2.5 text-white" x-text="pnr.user_id"></td>
                                <td class="px-4 py-2.5 text-slate-400" x-text="pnr.office_id"></td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-950 text-emerald-400 border border-emerald-800" x-text="pnr.status"></span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-300" x-text="pnr.data_json?.segments?.length || 0"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="new Date(pnr.updated_at).toLocaleDateString()"></td>
                                <td class="px-4 py-2.5">
                                    <button @click="inspect(pnr)" class="text-blue-400 hover:text-blue-300 text-xs">Inspect</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredPnrs.length === 0" style="display: none;">
                            <td colspan="7" class="px-4 py-4 text-center text-slate-500 italic">No matching PNRs found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Enroll Single Student -->
    <div x-show="showCreateStudentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div @click.away="showCreateStudentModal = false" class="bg-[#0b101d] border border-slate-700 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/20 border border-blue-500/40 flex items-center justify-center text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Enroll New Student Account</h3>
                        <p class="text-[11px] text-slate-400">Create a dedicated trainee account</p>
                    </div>
                </div>
                <button @click="showCreateStudentModal = false" class="text-slate-400 hover:text-white text-lg font-bold leading-none">&times;</button>
            </div>

            <div x-show="studentCreateError" class="mt-4 p-2.5 rounded-lg bg-rose-950/50 border border-rose-800/80 text-rose-300 text-xs font-mono" x-text="studentCreateError" style="display: none;"></div>

            <form @submit.prevent="submitCreateStudent()" class="mt-4 space-y-3.5 text-xs">
                <div>
                    <label class="block text-slate-400 font-mono mb-1">FULL NAME <span class="text-rose-400">*</span></label>
                    <input type="text" x-model="newStudent.name" placeholder="e.g. John Doe" required class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg outline-none focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-mono mb-1">USERNAME <span class="text-rose-400">*</span></label>
                        <input type="text" x-model="newStudent.username" placeholder="e.g. jdoe" required class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-mono mb-1">EMAIL ADDRESS <span class="text-rose-400">*</span></label>
                        <input type="email" x-model="newStudent.email" placeholder="e.g. jdoe@example.com" required class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg outline-none focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-slate-400 font-mono">INITIAL PASSWORD <span class="text-rose-400">*</span></label>
                        <button type="button" @click="generateRandomStudentPassword()" class="text-[10px] text-sky-400 hover:text-sky-300 font-mono underline">Generate Random</button>
                    </div>
                    <input type="text" x-model="newStudent.password" minlength="6" required class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-mono mb-1">OFFICE ID</label>
                        <input type="text" x-model="newStudent.office_id" placeholder="LON1A0980" class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg font-mono uppercase outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-mono mb-1">AGENT ID (OPTIONAL)</label>
                        <input type="text" x-model="newStudent.agent_id" placeholder="Auto-generated" class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg font-mono uppercase outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800 flex justify-end space-x-2">
                    <button type="button" @click="showCreateStudentModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg text-xs font-semibold hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" :disabled="isCreatingStudent" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold rounded-lg text-xs shadow-md transition-colors flex items-center gap-1.5">
                        <span x-show="!isCreatingStudent">Create Student Account</span>
                        <span x-show="isCreatingStudent">Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Batch Provision Unlimited Students -->
    <div x-show="showBatchModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div @click.away="showBatchModal = false" class="bg-[#0b101d] border border-slate-700 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Batch Provision Unlimited Students</h3>
                        <p class="text-[11px] text-slate-400">Instantly generate unlimited student accounts for a classroom batch</p>
                    </div>
                </div>
                <button @click="showBatchModal = false" class="text-slate-400 hover:text-white text-lg font-bold leading-none">&times;</button>
            </div>

            <div x-show="batchError" class="mt-4 p-2.5 rounded-lg bg-rose-950/50 border border-rose-800/80 text-rose-300 text-xs font-mono" x-text="batchError" style="display: none;"></div>

            <form @submit.prevent="submitBatchStudents()" class="mt-4 space-y-3.5 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-mono mb-1">NUMBER OF ACCOUNTS <span class="text-rose-400">*</span></label>
                        <input type="number" x-model="batchForm.count" min="1" max="500" required class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-mono mb-1">USERNAME PREFIX</label>
                        <input type="text" x-model="batchForm.prefix" placeholder="e.g. trainee or student" required class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 font-mono mb-1">DEFAULT PASSWORD <span class="text-rose-400">*</span></label>
                    <input type="text" x-model="batchForm.password" minlength="6" required class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg font-mono outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-slate-400 font-mono mb-1">ASSIGNED OFFICE ID</label>
                    <input type="text" x-model="batchForm.office_id" placeholder="LON1A0980" class="w-full bg-[#050811] border border-slate-700 text-white text-xs px-3 py-2 rounded-lg font-mono uppercase outline-none focus:border-indigo-500">
                </div>

                <div class="pt-3 border-t border-slate-800 flex justify-end space-x-2">
                    <button type="button" @click="showBatchModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg text-xs font-semibold hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" :disabled="isBatchCreating" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-bold rounded-lg text-xs shadow-md transition-colors flex items-center gap-1.5">
                        <span x-show="!isBatchCreating" x-text="'Generate ' + (batchForm.count || 0) + ' Accounts'"></span>
                        <span x-show="isBatchCreating">Generating Accounts...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Credentials Display & Copy -->
    <div x-show="showCredentialsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div @click.away="showCredentialsModal = false" class="bg-[#0b101d] border border-slate-700 rounded-2xl w-full max-w-2xl p-6 shadow-2xl relative flex flex-col max-h-[85vh]">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white" x-text="createdCredentialsTitle"></h3>
                        <p class="text-[11px] text-emerald-400">Accounts created and active in system</p>
                    </div>
                </div>
                <button @click="showCredentialsModal = false" class="text-slate-400 hover:text-white text-lg font-bold leading-none">&times;</button>
            </div>

            <div class="my-4 overflow-y-auto flex-1 border border-slate-800 rounded-lg">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#131c2e] text-slate-400 uppercase text-[10px] sticky top-0">
                        <tr>
                            <th class="px-3 py-2">Username</th>
                            <th class="px-3 py-2">Password</th>
                            <th class="px-3 py-2">Email</th>
                            <th class="px-3 py-2">Office</th>
                            <th class="px-3 py-2">Agent ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <template x-for="c in createdCredentialsList" :key="c.username">
                            <tr class="hover:bg-[#131c2e]/50">
                                <td class="px-3 py-2 font-bold text-sky-400" x-text="c.username"></td>
                                <td class="px-3 py-2 font-mono text-amber-300" x-text="c.password"></td>
                                <td class="px-3 py-2 text-slate-300" x-text="c.email"></td>
                                <td class="px-3 py-2 text-slate-400" x-text="c.office_id"></td>
                                <td class="px-3 py-2 text-slate-400" x-text="c.agent_id"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-between shrink-0">
                <button @click="copyCredentials()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-sky-400 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span>Copy All to Clipboard</span>
                </button>
                <button type="button" @click="showCredentialsModal = false" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg text-xs shadow-md transition-colors">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- PNR Inspect Modal -->
    <div x-show="selectedPnr" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="selectedPnr = null" class="bg-[#0b101c] border border-slate-800 rounded-xl max-w-2xl w-full max-h-[80vh] flex flex-col overflow-hidden shadow-2xl">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white" x-text="'PNR Audit: ' + selectedPnr?.record_locator"></h3>
                    <span class="text-[11px] text-slate-400" x-text="'Agent: ' + selectedPnr?.user_id + ' • Office: ' + selectedPnr?.office_id"></span>
                </div>
                <button @click="selectedPnr = null" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <div class="p-4 overflow-y-auto font-mono text-xs space-y-3">
                <div class="bg-black p-3 rounded border border-slate-800 text-emerald-400 whitespace-pre-wrap" x-text="formatPnr(selectedPnr)"></div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Raw JSON Data</span>
                    <pre class="bg-[#060a12] p-3 rounded border border-slate-800 text-[11px] text-slate-300 overflow-x-auto" x-text="JSON.stringify(selectedPnr?.data_json, null, 2)"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function instructorHub() {
    const pnrsData = @json($pnrs);
    const studentsData = @json($students);

    return {
        pnrs: pnrsData,
        students: studentsData.map(st => ({
            ...st,
            created_at: new Date(st.created_at).toLocaleDateString()
        })),
        search: '',
        selectedPnr: null,

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

        get filteredPnrs() {
            if (!this.search.trim()) return this.pnrs;
            const q = this.search.toLowerCase();
            return this.pnrs.filter(p =>
                (p.record_locator || '').toLowerCase().includes(q) ||
                (p.user_id || '').toLowerCase().includes(q) ||
                (p.office_id || '').toLowerCase().includes(q)
            );
        },

        inspect(pnr) {
            this.selectedPnr = pnr;
        },

        formatPnr(pnr) {
            if (!pnr || !pnr.data_json) return 'NO DATA';
            const d = pnr.data_json;
            const lines = ['--- RLR ---'];
            lines.push(`RP/${pnr.office_id}/${d.creationAgent || '1234AB'}   ${pnr.record_locator}`);

            (d.passengers || []).forEach((pax, idx) => {
                lines.push(`  ${idx + 1}.${pax.lastName}/${pax.firstName} ${pax.title || ''}`);
            });
            (d.segments || []).forEach((seg) => {
                lines.push(`  ${seg.segmentNumber}  ${seg.airlineCode} ${seg.flightNumber} ${seg.bookingClass} ${seg.departureDate} ${seg.departureAirport}${seg.arrivalAirport} HK${seg.seats}  ${seg.departureTime} ${seg.arrivalTime}`);
            });
            (d.contacts || []).forEach((c, idx) => {
                lines.push(`  ${idx + 1} AP ${c.detail}`);
            });
            if (d.ticketing) lines.push(`  TK ${d.ticketing.raw || 'OK'}`);
            if (d.fop) lines.push(`  FP ${d.fop.details}`);
            if (d.receivedFrom) lines.push(`  RF ${d.receivedFrom}`);

            return lines.join('\n');
        }
    };
}
</script>
@endsection
