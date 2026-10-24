'use client';

import React from 'react';
import { useTerminalStore } from '@/store/terminalStore';
import AmadeusTerminal from '@/components/terminal/AmadeusTerminal';
import TrainingCompanion from '@/components/companion/TrainingCompanion';
import PnrVisualizer from '@/components/companion/PnrVisualizer';

export default function HomePage() {
  const { mode, showVisualInspector, theme } = useTerminalStore();

  const isCrt = theme === 'crt-green' || theme === 'crt-amber';

  return (
    <div className={`flex-1 flex flex-col p-2 sm:p-4 gap-3 overflow-hidden ${isCrt ? 'crt-screen relative' : ''}`}>
      {/* Upper Area: Terminal + Training Companion (if in training mode) */}
      <div className="flex-1 flex flex-col lg:flex-row gap-3 min-h-0 overflow-hidden">
        {/* Terminal Area */}
        <div className="flex-1 h-full min-h-[420px] flex flex-col overflow-hidden">
          <AmadeusTerminal />
        </div>

        {/* Training Companion Sidebar (Visible in Training Mode) */}
        {mode === 'training' && (
          <div className="w-full lg:w-[420px] xl:w-[460px] h-[360px] lg:h-full flex flex-col shrink-0 overflow-hidden">
            <TrainingCompanion />
          </div>
        )}
      </div>

      {/* Lower Area: Visual PNR Inspector */}
      {showVisualInspector && (
        <div className="shrink-0">
          <PnrVisualizer />
        </div>
      )}
    </div>
  );
}
