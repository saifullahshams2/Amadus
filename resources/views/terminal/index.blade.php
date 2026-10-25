@extends('layouts.app')

@section('title', 'Amadeus GDS Training Simulator (1A)')

@section('content')
<div x-data="amadeusApp()" x-init="init()" class="flex-1 flex flex-col h-full overflow-hidden">
    <!-- Navbar -->
    <header class="bg-[#0b101b] border-b border-slate-800 px-4 py-2 flex items-center justify-between text-xs text-slate-300 shrink-0">
        <!-- Brand -->
        <div class="flex items-center space-x-3">
            <a href="/" class="flex items-center space-x-2 font-bold text-white tracking-wide text-sm">
                <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg class="w-4 h-4 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </div>
                <span class="bg-gradient-to-r from-blue-400 via-sky-300 to-indigo-300 bg-clip-text text-transparent">
                    AMADEUS GDS SIMULATOR
                </span>
            </a>
            <span class="hidden sm:inline-block text-[10px] uppercase font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">
                1A TRAINING CONNECT
            </span>
            <a href="/dashboard" class="px-2.5 py-1 rounded-md bg-blue-600/20 hover:bg-blue-600/30 text-sky-300 border border-blue-500/30 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard Hub</span>
            </a>
        </div>

        <!-- Controls: Inspector, Theme, User -->
        <div class="flex items-center space-x-2 sm:space-x-3">
            <!-- PNR Inspector Toggle -->
            <button @click="showVisualInspector = !showVisualInspector" :class="showVisualInspector ? 'bg-blue-950/60 border-blue-500/60 text-blue-300' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg border text-xs flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <span class="hidden lg:inline">PNR Inspector</span>
            </button>

            <!-- Theme Selector -->
            <div class="flex items-center bg-[#131c2e] p-0.5 rounded-lg border border-slate-800 text-xs">
                <button @click="theme = 'modern'" :class="theme === 'modern' ? 'bg-slate-700 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded transition-colors">Modern</button>
                <button @click="theme = 'crt-green'" :class="theme === 'crt-green' ? 'bg-emerald-950 text-emerald-400 font-bold border border-emerald-800' : 'text-slate-400 hover:text-emerald-400'" class="px-2 py-1 rounded transition-colors">CRT Green</button>
                <button @click="theme = 'crt-amber'" :class="theme === 'crt-amber' ? 'bg-amber-950 text-amber-400 font-bold border border-amber-800' : 'text-slate-400 hover:text-amber-400'" class="px-2 py-1 rounded transition-colors">CRT Amber</button>
            </div>

            <!-- User Status / Auth -->
            @auth
                <div class="flex items-center space-x-2 pl-2 border-l border-slate-800">
                    <span class="text-slate-300 font-mono text-xs hidden sm:inline">{{ Auth::user()->username }}</span>
                    @if(Auth::user()->role === 'instructor')
                        <a href="/instructor" class="px-2 py-1 bg-amber-600/20 text-amber-300 hover:bg-amber-600/30 border border-amber-500/30 rounded font-semibold text-xs">
                            Instructor Hub
                        </a>
                    @endif
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-rose-400 p-1" title="Sign Out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            @else
                <a href="/login" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg text-xs">
                    Sign In
                </a>
            @endauth
        </div>
    </header>

    <!-- Main Workspace Container -->
    <div :class="{ 'crt-screen relative': theme === 'crt-green' || theme === 'crt-amber' }" class="flex-1 flex flex-col p-2 sm:p-3 gap-2 min-h-0 overflow-hidden">
        <!-- Upper Area: Cryptic Terminal Workspace -->
        <div class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <!-- Terminal Component -->
            <div class="flex-1 h-full min-h-0 flex flex-col overflow-hidden rounded-xl border border-slate-800 bg-[#060a12] shadow-2xl">
                <!-- Terminal Header & Work Area Tabs -->
                <div class="bg-[#0b101c] border-b border-slate-800 px-3 py-2 flex items-center justify-between shrink-0">
                    <div class="flex items-center space-x-1">
                        <span class="text-[11px] font-mono text-slate-400 mr-2 uppercase tracking-wider hidden sm:inline">WORK AREA:</span>
                        <template x-for="a in ['A', 'B', 'C', 'D', 'E', 'F']" :key="a">
                            <button @click="selectArea(a)"
                                    :class="activeArea === a ? 'bg-blue-600 text-white font-bold shadow' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'"
                                    class="w-7 h-6 rounded flex items-center justify-center font-mono text-xs transition-colors"
                                    x-text="a">
                            </button>
                        </template>
                    </div>

                    <div class="flex items-center space-x-3 text-xs font-mono text-slate-400">
                        <span class="hidden md:inline">OFFICE: <strong class="text-white">LON1A0980</strong></span>
                        <span class="hidden md:inline">AGENT: <strong class="text-white">1234AB</strong></span>
                        <button @click="clearTerminal()" class="hover:text-rose-400 transition-colors" title="Clear Terminal">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                        <button @click="executeCommand('HE')" class="hover:text-blue-400 transition-colors" title="Quick Help">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Monospaced Terminal Output Console -->
                <div x-ref="terminalContainer"
                     @click="if (!window.getSelection()?.toString()) $refs.cmdInput?.focus()"
                     :class="{
                        'bg-black text-emerald-400': theme === 'crt-green',
                        'bg-black text-amber-400': theme === 'crt-amber',
                        'bg-[#060a12] text-slate-200': theme === 'modern'
                     }"
                     class="flex-1 p-3 overflow-y-auto font-mono text-[13px] sm:text-[14px] leading-relaxed whitespace-pre-wrap select-text cursor-text">
                    <template x-for="(line, idx) in currentArea.outputLines" :key="idx">
                        <div :class="{
                            'text-sky-400 font-bold': line.isInput && theme === 'modern',
                            'text-rose-400 font-semibold': line.isError && theme === 'modern',
                            'text-slate-400': line.isHeader && theme === 'modern',
                            'underline': line.isInput && (theme === 'crt-green' || theme === 'crt-amber')
                        }" x-text="line.text"></div>
                    </template>
                </div>

                <!-- Cryptic Command Input Line -->
                <form @submit.prevent="submitCommand()"
                      :class="{
                        'bg-black border-emerald-900 text-emerald-400': theme === 'crt-green',
                        'bg-black border-amber-900 text-amber-400': theme === 'crt-amber',
                        'bg-[#090f1d] border-slate-800 text-white': theme === 'modern'
                      }"
                      class="border-t px-3 py-2 flex items-center gap-2 shrink-0">
                    <span class="font-mono font-bold text-sm select-none">&gt;</span>
                    <input type="text"
                           x-ref="cmdInput"
                           x-model="inputVal"
                           @keydown.up.prevent="navigateHistory('up')"
                           @keydown.down.prevent="navigateHistory('down')"
                           :readonly="isExecuting"
                           placeholder="ENTER CRYPTIC COMMAND (E.G. AN15NOVLHRJFK, SS1Y1, NM1DOE/JOHN MR, FXP, ET)..."
                           autocomplete="off"
                           spellcheck="false"
                           class="flex-1 bg-transparent border-0 outline-none font-mono text-sm tracking-wider uppercase placeholder:text-slate-600 placeholder:normal-case">
                    <div x-show="isExecuting" class="animate-spin text-blue-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lower Area: Visual PNR Inspector -->
        <div x-show="showVisualInspector" class="shrink-0 max-h-[190px] overflow-y-auto rounded-xl border border-slate-800 bg-[#0b101c] p-3 text-xs">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-2 pb-2 mb-2 border-b border-slate-800">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-white tracking-wide">ACTIVE PNR INSPECTOR</span>
                    <span class="text-slate-400 font-mono text-[11px]" x-text="'(Work Area ' + activeArea + ')'"></span>
                    <span x-show="activePnr?.recordLocator" class="px-2 py-0.5 bg-blue-900/60 border border-blue-500/60 text-blue-300 rounded font-mono font-bold" x-text="'RLOC: ' + activePnr?.recordLocator"></span>
                </div>

                <!-- Mandatory PRINT Status Badges -->
                <div class="flex items-center gap-1 text-[10px] font-mono">
                    <span class="text-slate-400 mr-1 font-bold">PRINT:</span>
                    <span :class="pnrHasPhone ? 'bg-emerald-950 text-emerald-400 border-emerald-700' : 'bg-rose-950 text-rose-400 border-rose-800'" class="px-1.5 py-0.5 rounded border">P: Phone</span>
                    <span :class="pnrHasReceivedFrom ? 'bg-emerald-950 text-emerald-400 border-emerald-700' : 'bg-rose-950 text-rose-400 border-rose-800'" class="px-1.5 py-0.5 rounded border">R: RecvFrom</span>
                    <span :class="pnrHasItinerary ? 'bg-emerald-950 text-emerald-400 border-emerald-700' : 'bg-rose-950 text-rose-400 border-rose-800'" class="px-1.5 py-0.5 rounded border">I: Itinerary</span>
                    <span :class="pnrHasName ? 'bg-emerald-950 text-emerald-400 border-emerald-700' : 'bg-rose-950 text-rose-400 border-rose-800'" class="px-1.5 py-0.5 rounded border">N: Name</span>
                    <span :class="pnrHasTicketing ? 'bg-emerald-950 text-emerald-400 border-emerald-700' : 'bg-rose-950 text-rose-400 border-rose-800'" class="px-1.5 py-0.5 rounded border">T: Ticket</span>
                </div>
            </div>

            <!-- Segments, Passengers & TST Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <!-- Booked Segments -->
                <div class="bg-[#101726] p-2 rounded-lg border border-slate-800/80">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">FLIGHT SEGMENTS (<span x-text="activePnr?.segments?.length || 0"></span>)</span>
                    <template x-if="!activePnr?.segments?.length">
                        <span class="text-slate-500 italic text-[11px]">No segments booked yet (use AN + SS1Y1)</span>
                    </template>
                    <div class="space-y-1">
                        <template x-for="s in activePnr?.segments || []" :key="s.segmentNumber">
                            <div class="bg-[#080d17] p-1.5 rounded border border-slate-800 font-mono text-[11px] flex justify-between">
                                <span class="text-sky-300" x-text="s.airlineCode + ' ' + s.flightNumber + ' (' + s.bookingClass + ')'"></span>
                                <span class="text-slate-300" x-text="s.departureAirport + '-' + s.arrivalAirport + ' ' + s.departureDate"></span>
                                <span class="text-emerald-400" x-text="'HK' + s.seats"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Passengers -->
                <div class="bg-[#101726] p-2 rounded-lg border border-slate-800/80">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">PASSENGERS (<span x-text="activePnr?.passengers?.length || 0"></span>)</span>
                    <template x-if="!activePnr?.passengers?.length">
                        <span class="text-slate-500 italic text-[11px]">No passengers added (use NM1SURNAME/NAME MR)</span>
                    </template>
                    <div class="space-y-1">
                        <template x-for="p in activePnr?.passengers || []" :key="p.id">
                            <div class="bg-[#080d17] p-1.5 rounded border border-slate-800 font-mono text-[11px] flex justify-between">
                                <span class="text-white" x-text="p.lastName + '/' + p.firstName + (p.title ? ' ' + p.title : '')"></span>
                                <span class="text-slate-400 text-[10px]" x-text="'(' + p.type + ')'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- TST & Tickets -->
                <div class="bg-[#101726] p-2 rounded-lg border border-slate-800/80">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">STORED FARES & TICKETS</span>
                    <template x-if="!activePnr?.tsts?.length && !activePnr?.tickets?.length">
                        <span class="text-slate-500 italic text-[11px]">Unpriced (use FXP to price & store TST)</span>
                    </template>
                    <div class="space-y-1">
                        <template x-for="t in activePnr?.tsts || []" :key="t.tstNumber">
                            <div class="bg-[#080d17] p-1 rounded border border-slate-800 font-mono text-[11px] flex justify-between">
                                <span class="text-amber-300" x-text="'TST ' + t.tstNumber + ' (' + t.fareBasis + ')'"></span>
                                <span class="text-emerald-400 font-bold" x-text="t.currency + ' ' + t.total.toFixed(2)"></span>
                            </div>
                        </template>
                        <template x-for="tkt in activePnr?.tickets || []" :key="tkt.ticketNumber">
                            <div class="bg-emerald-950/40 p-1 rounded border border-emerald-800 font-mono text-[11px] flex justify-between">
                                <span class="text-emerald-300 font-bold" x-text="'ETKT: ' + tkt.ticketNumber"></span>
                                <span class="text-emerald-400 font-semibold" x-text="tkt.coupons.length + ' CPN OPEN'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function amadeusApp() {
    const initialAreaState = (area) => ({
        area: area,
        isSignedIn: true,
        officeId: 'LON1A0980',
        agentId: '1234AB',
        activePnr: null,
        lastAvailabilityDisplay: null,
        history: [],
        historyIndex: -1,
        outputLines: [
            { text: `AMADEUS GDS SIMULATOR - WORK AREA ${area} READY`, isHeader: true },
            { text: `OFFICE: LON1A0980  AGENT: 1234AB  SYSTEM ACTIVE`, isHeader: true },
            { text: `ENTER "HE" OR "HELP" FOR COMMAND REFERENCE`, isHeader: true },
            { text: `------------------------------------------------------------` },
        ],
    });

    return {
        theme: 'modern',
        showVisualInspector: true,
        activeArea: 'A',
        areas: {
            A: initialAreaState('A'),
            B: initialAreaState('B'),
            C: initialAreaState('C'),
            D: initialAreaState('D'),
            E: initialAreaState('E'),
            F: initialAreaState('F'),
        },

        inputVal: '',
        isExecuting: false,
        historyCursor: -1,

        get currentArea() {
            return this.areas[this.activeArea];
        },
        get activePnr() {
            return this.currentArea.activePnr;
        },

        // PRINT status helpers
        get pnrHasPhone() { return (this.activePnr?.contacts?.length || 0) > 0; },
        get pnrHasReceivedFrom() { return !!this.activePnr?.receivedFrom; },
        get pnrHasItinerary() { return (this.activePnr?.segments?.length || 0) > 0; },
        get pnrHasName() { return (this.activePnr?.passengers?.length || 0) > 0; },
        get pnrHasTicketing() { return !!this.activePnr?.ticketing; },

        init() {
            this.$nextTick(() => {
                this.scrollToBottom();
                this.$refs.cmdInput?.focus();
            });
        },

        selectArea(area) {
            this.activeArea = area;
            this.historyCursor = -1;
            this.$nextTick(() => {
                this.scrollToBottom();
                this.$refs.cmdInput?.focus();
            });
        },

        clearTerminal() {
            this.currentArea.outputLines = [];
        },

        navigateHistory(direction) {
            const hist = this.currentArea.history;
            if (hist.length === 0) return;

            if (direction === 'up') {
                if (this.historyCursor === -1) {
                    this.historyCursor = hist.length - 1;
                } else if (this.historyCursor > 0) {
                    this.historyCursor--;
                }
            } else if (direction === 'down') {
                if (this.historyCursor !== -1) {
                    if (this.historyCursor < hist.length - 1) {
                        this.historyCursor++;
                    } else {
                        this.historyCursor = -1;
                        this.inputVal = '';
                        return;
                    }
                }
            }

            if (this.historyCursor >= 0 && this.historyCursor < hist.length) {
                this.inputVal = hist[this.historyCursor];
            }
        },

        async submitCommand() {
            const cmd = this.inputVal.trim();
            if (!cmd || this.isExecuting) return;

            this.inputVal = '';
            this.historyCursor = -1;
            await this.executeCommand(cmd);
        },

        async executeCommand(command) {
            this.isExecuting = true;
            const area = this.activeArea;
            const state = this.areas[area];

            // Echo input
            state.outputLines.push({ text: `> ${command.toUpperCase()}`, isInput: true });
            state.history.push(command);
            this.scrollToBottom();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const res = await fetch('/terminal/execute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        command,
                        state,
                    }),
                });

                const data = await res.json();

                if (data.outputLines) {
                    for (const line of data.outputLines) {
                        state.outputLines.push({ text: line, isError: data.isError });
                    }
                }

                if (data.updatedState) {
                    state.activePnr = data.updatedState.activePnr;
                    state.lastAvailabilityDisplay = data.updatedState.lastAvailabilityDisplay;
                    state.isSignedIn = data.updatedState.isSignedIn;
                }
            } catch (err) {
                state.outputLines.push({ text: `CONNECTION ERROR: FAILED TO REACH GDS HOST`, isError: true });
            } finally {
                this.isExecuting = false;
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.$refs.cmdInput?.focus();
                });
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.terminalContainer) {
                    this.$refs.terminalContainer.scrollTop = this.$refs.terminalContainer.scrollHeight;
                }
            });
        }
    };
}
</script>
@endsection
