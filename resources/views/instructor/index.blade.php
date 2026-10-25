@extends('layouts.app')

@section('title', 'Instructor Operations Hub - Amadeus GDS')

@section('content')
<div x-data="instructorHub()" class="flex-1 flex flex-col h-full overflow-hidden bg-[#070b14] text-slate-200">
    <!-- Hub Header -->
    <header class="bg-[#0b101c] border-b border-slate-800 px-6 py-3 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-3">
            <a href="/" class="p-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition-colors">
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
                    <div class="text-2xl font-bold text-white">{{ count($students) }}</div>
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
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-sm font-bold text-white tracking-wide">Enrolled Student Roster</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#131c2e] text-slate-400 uppercase text-[10px]">
                        <tr>
                            <th class="px-4 py-2.5">Username</th>
                            <th class="px-4 py-2.5">Email</th>
                            <th class="px-4 py-2.5">Assigned Office</th>
                            <th class="px-4 py-2.5">Agent ID</th>
                            <th class="px-4 py-2.5">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($students as $st)
                            <tr class="hover:bg-[#131c2e]/50">
                                <td class="px-4 py-2.5 font-bold text-white">{{ $st->username }}</td>
                                <td class="px-4 py-2.5 text-slate-300">{{ $st->email }}</td>
                                <td class="px-4 py-2.5 text-slate-400">{{ $st->office_id }}</td>
                                <td class="px-4 py-2.5 text-slate-400">{{ $st->agent_id }}</td>
                                <td class="px-4 py-2.5 text-slate-500">{{ $st->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-slate-500 italic">No students registered yet.</td>
                            </tr>
                        @endforelse
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
                        <template x-for="p in filteredPnrs" :key="p.record_locator">
                            <tr class="hover:bg-[#131c2e]/50">
                                <td class="px-4 py-2.5 font-bold text-sky-400" x-text="p.record_locator"></td>
                                <td class="px-4 py-2.5 text-white" x-text="p.user_id"></td>
                                <td class="px-4 py-2.5 text-slate-400" x-text="p.office_id"></td>
                                <td class="px-4 py-2.5">
                                    <span :class="p.status === 'TICKETED' ? 'bg-emerald-950 text-emerald-400 border-emerald-800' : 'bg-blue-950 text-blue-400 border-blue-800'" class="px-2 py-0.5 rounded border text-[10px] font-bold" x-text="p.status"></span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-300" x-text="(p.data_json?.segments?.length || 0) + ' seg(s)'"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="p.updated_at ? new Date(p.updated_at).toLocaleDateString() : 'N/A'"></td>
                                <td class="px-4 py-2.5">
                                    <button @click="inspect(p)" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded text-[11px] transition-colors">
                                        Inspect
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredPnrs.length === 0">
                            <td colspan="7" class="px-4 py-4 text-center text-slate-500 italic">No matching PNRs found.</td>
                        </tr>
                    </tbody>
                </table>
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

    return {
        pnrs: pnrsData,
        search: '',
        selectedPnr: null,

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
