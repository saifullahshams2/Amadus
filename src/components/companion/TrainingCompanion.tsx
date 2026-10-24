'use client';

import React, { useState } from 'react';
import { useTerminalStore } from '@/store/terminalStore';
import { TRAINING_MODULES } from '@/lib/curriculum/modules';
import { CheckCircle2, Circle, Lightbulb, ChevronRight, BookOpen, Sparkles, KeyRound } from 'lucide-react';

export default function TrainingCompanion() {
  const {
    activeLesson,
    activeModuleId,
    currentObjectiveIndex,
    completedObjectives,
    hintLevel,
    incrementHint,
    selectLesson,
    appendOutput,
    activeArea,
  } = useTerminalStore();

  const [showModulePicker, setShowModulePicker] = useState(false);
  const currentObjective = activeLesson?.objectives[currentObjectiveIndex];
  const allCompleted = currentObjectiveIndex >= (activeLesson?.objectives.length || 0);

  const handleInsertSolution = (cmd: string) => {
    // Helpful shortcut: appends instruction to try command
    appendOutput(activeArea, [
      { text: `[TRAINER TIP]: Try entering the command below:`, isHeader: true },
      { text: `  ${cmd}`, isInput: true },
    ]);
  };

  return (
    <div className="flex flex-col h-full bg-[#0e1626] border border-slate-800 rounded-xl overflow-hidden shadow-xl text-slate-200">
      {/* Header & Module Switcher */}
      <div className="p-4 bg-[#142036] border-b border-slate-800 flex items-center justify-between">
        <div>
          <span className="text-[10px] font-semibold tracking-wider text-blue-400 uppercase bg-blue-500/10 px-2 py-0.5 rounded border border-blue-500/20">
            {activeLesson?.moduleTitle}
          </span>
          <h2 className="text-sm sm:text-base font-bold text-white mt-1 flex items-center gap-2">
            Lesson {activeLesson?.lessonNumber}: {activeLesson?.title}
          </h2>
        </div>
        <button
          onClick={() => setShowModulePicker(!showModulePicker)}
          className="px-2.5 py-1.5 text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg flex items-center gap-1.5 transition-colors border border-slate-700"
        >
          <BookOpen className="w-3.5 h-3.5 text-blue-400" />
          <span>Curriculum</span>
        </button>
      </div>

      {/* Module Picker Drawer (when toggled) */}
      {showModulePicker && (
        <div className="p-4 bg-[#0a0f1d] border-b border-slate-800 max-h-72 overflow-y-auto space-y-3">
          <div className="text-xs font-semibold text-slate-400 tracking-wider uppercase mb-2">
            Select Training Module
          </div>
          {TRAINING_MODULES.map((mod) => (
            <div key={mod.id} className="space-y-1">
              <div className="text-xs font-bold text-slate-300 flex items-center justify-between">
                <span>Module {mod.number}: {mod.title}</span>
                <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">
                  {mod.level}
                </span>
              </div>
              <div className="grid grid-cols-1 gap-1 pl-2">
                {mod.lessons.map((les) => (
                  <button
                    key={les.id}
                    onClick={() => {
                      selectLesson(les);
                      setShowModulePicker(false);
                    }}
                    className={`text-left px-2.5 py-1.5 rounded text-xs transition-colors flex items-center justify-between ${
                      activeLesson.id === les.id
                        ? 'bg-blue-600 text-white font-semibold'
                        : 'bg-slate-800/40 text-slate-400 hover:bg-slate-800 hover:text-slate-200'
                    }`}
                  >
                    <span>Lesson {les.lessonNumber}: {les.title}</span>
                    <ChevronRight className="w-3.5 h-3.5 opacity-60" />
                  </button>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Main Content Area */}
      <div className="flex-1 p-4 overflow-y-auto space-y-4">
        {/* Scenario Story Card */}
        <div className="p-3 bg-blue-950/30 border border-blue-900/40 rounded-lg text-xs leading-relaxed text-blue-200">
          <div className="font-semibold text-blue-300 flex items-center gap-1.5 mb-1">
            <Sparkles className="w-3.5 h-3.5 text-blue-400" />
            <span>Scenario Briefing</span>
          </div>
          {activeLesson?.scenarioStory}
        </div>

        {/* Step-by-Step Objectives */}
        <div className="space-y-2">
          <div className="text-xs font-semibold tracking-wider text-slate-400 uppercase">
            Lesson Steps ({completedObjectives.length} / {activeLesson?.objectives.length})
          </div>

          {activeLesson?.objectives.map((obj, idx) => {
            const isCompleted = idx < currentObjectiveIndex;
            const isCurrent = idx === currentObjectiveIndex;

            return (
              <div
                key={obj.id}
                className={`p-3 rounded-lg border text-xs transition-all ${
                  isCompleted
                    ? 'bg-emerald-950/20 border-emerald-800/50 text-emerald-200'
                    : isCurrent
                    ? 'bg-slate-800/80 border-blue-500 shadow-md ring-1 ring-blue-500/30'
                    : 'bg-slate-900/40 border-slate-800/60 text-slate-500 opacity-60'
                }`}
              >
                <div className="flex items-start gap-2.5">
                  {isCompleted ? (
                    <CheckCircle2 className="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" />
                  ) : isCurrent ? (
                    <div className="w-4 h-4 rounded-full border-2 border-blue-400 flex items-center justify-center mt-0.5 shrink-0 animate-pulse">
                      <div className="w-1.5 h-1.5 rounded-full bg-blue-400" />
                    </div>
                  ) : (
                    <Circle className="w-4 h-4 text-slate-600 mt-0.5 shrink-0" />
                  )}

                  <div className="flex-1">
                    <div className="font-bold text-white flex items-center justify-between">
                      <span>{obj.title}</span>
                      {isCurrent && (
                        <span className="text-[10px] font-normal px-1.5 py-0.5 bg-blue-500/20 text-blue-300 rounded border border-blue-500/30">
                          Active Step
                        </span>
                      )}
                    </div>
                    <p className="mt-1 text-slate-300 leading-relaxed">
                      {obj.description}
                    </p>

                    {/* Hint Section for Current Step */}
                    {isCurrent && (
                      <div className="mt-3 pt-2.5 border-t border-slate-700/60 space-y-2">
                        {hintLevel >= 1 && (
                          <div className="p-2 rounded bg-amber-950/30 border border-amber-800/40 text-amber-200 text-[11px]">
                            <strong>Hint:</strong> {obj.hint1}
                          </div>
                        )}
                        {hintLevel >= 2 && (
                          <div className="p-2 rounded bg-amber-950/40 border border-amber-800/50 text-amber-100 text-[11px]">
                            <strong>Syntax Guide:</strong> {obj.hint2}
                          </div>
                        )}
                        {hintLevel >= 3 && (
                          <div className="p-2.5 rounded bg-blue-900/40 border border-blue-700/60 text-white text-[11px] flex items-center justify-between">
                            <div>
                              <span className="text-blue-300">Exact Entry: </span>
                              <code className="font-mono font-bold text-amber-300 bg-black/40 px-1.5 py-0.5 rounded">
                                {obj.solutionCommand}
                              </code>
                            </div>
                            <button
                              onClick={() => handleInsertSolution(obj.solutionCommand)}
                              className="px-2 py-0.5 text-[10px] bg-blue-600 hover:bg-blue-500 rounded font-semibold text-white"
                            >
                              Show in Terminal
                            </button>
                          </div>
                        )}

                        <div className="flex items-center justify-between pt-1">
                          <button
                            onClick={incrementHint}
                            className="text-[11px] text-amber-400 hover:text-amber-300 flex items-center gap-1 transition-colors font-medium"
                          >
                            <Lightbulb className="w-3.5 h-3.5" />
                            <span>
                              {hintLevel === 0
                                ? 'Need a hint?'
                                : hintLevel === 1
                                ? 'Need more help?'
                                : hintLevel === 2
                                ? 'Show exact solution'
                                : 'Solution visible'}
                            </span>
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            );
          })}

          {allCompleted && (
            <div className="p-4 bg-emerald-950/30 border border-emerald-700/60 rounded-xl text-center space-y-2">
              <CheckCircle2 className="w-8 h-8 text-emerald-400 mx-auto" />
              <h3 className="font-bold text-white text-sm">Lesson Completed!</h3>
              <p className="text-xs text-emerald-300">
                You successfully mastered all objectives for this training module.
              </p>
            </div>
          )}
        </div>

        {/* Lesson Key Commands Cheat Sheet */}
        <div className="pt-2 border-t border-slate-800 space-y-2">
          <div className="text-xs font-semibold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
            <KeyRound className="w-3.5 h-3.5 text-blue-400" />
            <span>Key Commands Reference</span>
          </div>
          <div className="space-y-1.5">
            {activeLesson?.keyCommands.map((cmd, i) => (
              <div key={i} className="p-2 rounded bg-slate-900/60 border border-slate-800 text-[11px]">
                <div className="font-mono font-bold text-blue-300">{cmd.syntax}</div>
                <div className="text-slate-400 mt-0.5">{cmd.description}</div>
                <div className="text-[10px] text-amber-400/80 font-mono mt-0.5">
                  e.g. {cmd.example}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
