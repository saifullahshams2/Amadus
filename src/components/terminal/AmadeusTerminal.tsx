'use client';

import React, { useState, useRef, useEffect } from 'react';
import { useTerminalStore } from '@/store/terminalStore';
import { WorkArea } from '@/types/amadeus';
import { Terminal, RefreshCw, Trash2, HelpCircle } from 'lucide-react';

export default function AmadeusTerminal() {
  const {
    theme,
    activeArea,
    areas,
    setActiveArea,
    appendOutput,
    clearTerminal,
    updateAreaState,
    activeLesson,
    currentObjectiveIndex,
    nextObjective,
  } = useTerminalStore();

  const [inputVal, setInputVal] = useState('');
  const [historyIndex, setHistoryIndex] = useState(-1);
  const [isExecuting, setIsExecuting] = useState(false);
  const terminalContainerRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const currentAreaState = areas[activeArea];

  // Auto-scroll ONLY inside the terminal output buffer (does NOT move outer page)
  useEffect(() => {
    if (terminalContainerRef.current) {
      terminalContainerRef.current.scrollTop = terminalContainerRef.current.scrollHeight;
    }
  }, [currentAreaState.outputLines]);

  // Focus input without scrolling the browser window
  useEffect(() => {
    inputRef.current?.focus({ preventScroll: true });
  }, [activeArea]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const command = inputVal.trim();
    if (!command || isExecuting) return;

    setInputVal('');
    setHistoryIndex(-1);

    // Append command echo to terminal
    appendOutput(activeArea, [{ text: `> ${command.toUpperCase()}`, isInput: true }]);

    // Update command history
    const newHistory = [...currentAreaState.history, command];
    updateAreaState(activeArea, { history: newHistory });

    setIsExecuting(true);

    try {
      const res = await fetch('/api/terminal/execute', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          command,
          state: currentAreaState,
          activeLessonId: activeLesson?.id,
          currentObjectiveIndex,
        }),
      });

      const data = await res.json();

      if (data.outputLines) {
        appendOutput(
          activeArea,
          data.outputLines.map((line: string) => ({
            text: line,
            isError: data.isError,
          }))
        );
      }

      if (data.updatedState) {
        updateAreaState(activeArea, {
          activePnr: data.updatedState.activePnr,
          lastAvailabilityDisplay: data.updatedState.lastAvailabilityDisplay,
          isSignedIn: data.updatedState.isSignedIn,
        });
      }

      // If training objective satisfied, advance
      if (data.objectiveCompleted) {
        nextObjective();
      }
    } catch (err: any) {
      appendOutput(activeArea, [
        { text: `CONNECTION ERROR: ${err.message || 'FAILED TO REACH GDS HOST'}`, isError: true },
      ]);
    } finally {
      setIsExecuting(false);
      setTimeout(() => inputRef.current?.focus({ preventScroll: true }), 50);
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    // History navigation with Up/Down arrows
    if (e.key === 'ArrowUp') {
      e.preventDefault();
      const history = currentAreaState.history;
      if (history.length === 0) return;
      const newIdx = historyIndex === -1 ? history.length - 1 : Math.max(0, historyIndex - 1);
      setHistoryIndex(newIdx);
      setInputVal(history[newIdx]);
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      const history = currentAreaState.history;
      if (historyIndex === -1) return;
      const newIdx = historyIndex + 1;
      if (newIdx >= history.length) {
        setHistoryIndex(-1);
        setInputVal('');
      } else {
        setHistoryIndex(newIdx);
        setInputVal(history[newIdx]);
      }
    }
  };

  // Color theme classes
  const themeStyles = {
    modern: {
      wrapper: 'bg-[#0b101b] border-slate-800 text-slate-100 shadow-2xl',
      header: 'bg-[#111928] border-b border-slate-800 text-slate-300',
      terminalBody: 'bg-[#070b12] text-slate-200',
      tabActive: 'bg-blue-600 text-white font-bold',
      tabInactive: 'bg-slate-800/60 text-slate-400 hover:bg-slate-700 hover:text-white',
      lineInput: 'text-amber-400 font-semibold',
      lineError: 'text-rose-400',
      lineNormal: 'text-emerald-400',
      prompt: 'text-blue-400',
      cursor: 'bg-blue-400',
    },
    'crt-green': {
      wrapper: 'bg-[#040804] border-emerald-950 text-[#33ff33] shadow-[0_0_40px_rgba(51,255,51,0.15)]',
      header: 'bg-[#081208] border-b border-emerald-900/60 text-[#22c55e]',
      terminalBody: 'bg-[#020502] text-[#33ff33]',
      tabActive: 'bg-[#1b8a1b] text-black font-bold shadow-[0_0_10px_#33ff33]',
      tabInactive: 'bg-[#0a1a0a] text-emerald-600 hover:bg-[#112411] hover:text-[#33ff33]',
      lineInput: 'text-[#66ff66] font-bold',
      lineError: 'text-red-400',
      lineNormal: 'text-[#33ff33]',
      prompt: 'text-[#33ff33]',
      cursor: 'bg-[#33ff33]',
    },
    'crt-amber': {
      wrapper: 'bg-[#080500] border-amber-950 text-[#ffb000] shadow-[0_0_40px_rgba(255,176,0,0.15)]',
      header: 'bg-[#140b00] border-b border-amber-900/60 text-amber-500',
      terminalBody: 'bg-[#050300] text-[#ffb000]',
      tabActive: 'bg-[#cc8800] text-black font-bold shadow-[0_0_10px_#ffb000]',
      tabInactive: 'bg-[#1f1100] text-amber-700 hover:bg-[#2b1800] hover:text-[#ffb000]',
      lineInput: 'text-[#ffd266] font-bold',
      lineError: 'text-red-400',
      lineNormal: 'text-[#ffb000]',
      prompt: 'text-[#ffb000]',
      cursor: 'bg-[#ffb000]',
    },
  }[theme];

  const workAreas: WorkArea[] = ['A', 'B', 'C', 'D', 'E', 'F'];

  return (
    <div className={`flex flex-col h-full rounded-xl border overflow-hidden ${themeStyles.wrapper}`}>
      {/* Top GDS Status Bar & Work Area Tabs */}
      <div className={`px-4 py-2 flex items-center justify-between text-xs font-mono select-none ${themeStyles.header}`}>
        <div className="flex items-center space-x-3">
          <div className="flex items-center space-x-1.5 font-bold tracking-wider">
            <Terminal className="w-4 h-4 text-blue-400" />
            <span className="hidden sm:inline">AMADEUS CONNECT</span>
            <span className="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-semibold border border-emerald-500/30">
              LIVE 1A
            </span>
          </div>

          {/* Work Area Tabs: A through F */}
          <div className="flex items-center space-x-1 pl-2">
            {workAreas.map((area) => (
              <button
                key={area}
                onClick={() => setActiveArea(area)}
                className={`px-2 py-0.5 rounded text-xs transition-all ${
                  activeArea === area ? themeStyles.tabActive : themeStyles.tabInactive
                }`}
                title={`Switch to Work Area ${area}`}
              >
                AREA {area}
              </button>
            ))}
          </div>
        </div>

        {/* Agency Office & Host Info */}
        <div className="flex items-center space-x-4 text-[11px] opacity-80">
          <span className="hidden md:inline">OFFICE: <strong className="text-white">{currentAreaState.officeId}</strong></span>
          <span className="hidden md:inline">AGENT: <strong className="text-white">{currentAreaState.agentId}</strong></span>
          <button
            onClick={() => clearTerminal(activeArea)}
            className="p-1 hover:text-white transition-colors"
            title="Clear screen"
          >
            <Trash2 className="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

      {/* Terminal Screen Body (80-column monospace) */}
      <div
        ref={terminalContainerRef}
        className={`flex-1 min-h-0 p-4 font-mono text-xs sm:text-sm overflow-y-auto leading-relaxed select-text space-y-1 ${themeStyles.terminalBody}`}
        onClick={() => inputRef.current?.focus({ preventScroll: true })}
      >
        {currentAreaState.outputLines.map((line, idx) => (
          <div
            key={idx}
            className={`whitespace-pre font-mono tracking-wide ${
              line.isInput
                ? themeStyles.lineInput
                : line.isError
                ? themeStyles.lineError
                : themeStyles.lineNormal
            }`}
          >
            {line.text}
          </div>
        ))}
      </div>

      {/* Bottom Command Prompt Line */}
      <div className={`p-3 border-t border-slate-800/80 bg-black/40 flex items-center space-x-2`}>
        <span className={`font-mono text-base font-bold ${themeStyles.prompt}`}>&gt;</span>
        <form onSubmit={handleSubmit} className="flex-1 flex items-center">
          <input
            ref={inputRef}
            type="text"
            value={inputVal}
            onChange={(e) => setInputVal(e.target.value.toUpperCase())}
            onKeyDown={handleKeyDown}
            placeholder='Enter cryptic command (e.g. AN15NOVLHRJFK, NM1DOE/JOHN MR, FXP, HE)'
            className="w-full bg-transparent font-mono text-sm uppercase focus:outline-none text-white tracking-wider placeholder:normal-case placeholder:text-slate-600"
            disabled={isExecuting}
            autoFocus
            spellCheck={false}
            autoComplete="off"
          />
        </form>
        {isExecuting && (
          <RefreshCw className="w-4 h-4 text-blue-400 animate-spin mr-2" />
        )}
      </div>
    </div>
  );
}
