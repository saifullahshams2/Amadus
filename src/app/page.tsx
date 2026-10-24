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
    <div className={`flex-1 flex flex-col p-2 sm:p-3 gap-2 min-h-0 overflow-hidden ${isCrt ? 'crt-screen relative' : ''}`}>
      {/* Upper Area: Terminal + Training Companion (if in training mode) */}
      <div className="flex-1 flex flex-col lg:flex-row gap-2 min-h-0 overflow-hidden">
        {/* Terminal Area */}
        <div className="flex-1 h-full min-h-0 flex flex-col overflow-hidden">
          <AmadeusTerminal />
        </div>

        {/* Training Companion Sidebar (Visible in Training Mode) */}
        {mode === 'training' && (
          <div className="w-full lg:w-[400px] xl:w-[440px] h-[300px] lg:h-full flex flex-col shrink-0 min-h-0 overflow-hidden">
            <TrainingCompanion />
          </div>
        )}
      </div>

      {/* Lower Area: Visual PNR Inspector */}
      {showVisualInspector && (
        <div className="shrink-0 max-h-[190px] overflow-y-auto rounded-xl">
          <PnrVisualizer />
        </div>
      )}
    </div>
  );
}
