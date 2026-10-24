'use client';

import React, { useState, useEffect } from 'react';
import { useTerminalStore, TerminalTheme, AppMode } from '@/store/terminalStore';
import Link from 'next/link';
import { Plane, Monitor, Terminal, Award, Eye, LogOut, User, ShieldCheck } from 'lucide-react';

export default function Navbar() {
  const {
    theme,
    setTheme,
    mode,
    setMode,
    showVisualInspector,
    toggleVisualInspector,
  } = useTerminalStore();

  const [user, setUser] = useState<{ username: string; role: string; officeId: string } | null>(null);

  useEffect(() => {
    fetch('/api/auth/me')
      .then(res => res.json())
      .then(data => {
        if (data.user) setUser(data.user);
      })
      .catch(() => {});
  }, []);

  const handleLogout = async () => {
    await fetch('/api/auth/me', { method: 'POST' });
    setUser(null);
    window.location.reload();
  };

  return (
    <header className="bg-[#0b101b] border-b border-slate-800 px-4 py-2.5 flex items-center justify-between text-xs text-slate-300">
      {/* Brand & System Status */}
      <div className="flex items-center space-x-3">
        <Link href="/" className="flex items-center space-x-2 font-bold text-white tracking-wide text-sm">
          <div className="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
            <Plane className="w-4 h-4 text-white transform -rotate-45" />
          </div>
          <span className="bg-gradient-to-r from-blue-400 via-sky-300 to-indigo-300 bg-clip-text text-transparent">
            AMADEUS GDS SIMULATOR
          </span>
        </Link>
        <span className="hidden sm:inline-block text-[10px] uppercase font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">
          TRAINING & CERTIFICATION
        </span>
      </div>

      {/* Control Toggles: Mode, Theme, Inspector */}
      <div className="flex items-center space-x-2 sm:space-x-3">
        {/* Mode Selector: Training vs Sandbox */}
        <div className="flex items-center bg-[#131c2e] p-0.5 rounded-lg border border-slate-800">
          <button
            onClick={() => setMode('training')}
            className={`px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition-all ${
              mode === 'training'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            <Award className="w-3.5 h-3.5" />
            <span className="hidden md:inline">Training Mode</span>
          </button>
          <button
            onClick={() => setMode('sandbox')}
            className={`px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition-all ${
              mode === 'sandbox'
                ? 'bg-emerald-600 text-white shadow-sm'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            <Terminal className="w-3.5 h-3.5" />
            <span className="hidden md:inline">Free Sandbox</span>
          </button>
        </div>

        {/* Visual Inspector Toggle */}
        <button
          onClick={toggleVisualInspector}
          className={`px-2.5 py-1 rounded-lg border text-xs flex items-center gap-1.5 transition-colors ${
            showVisualInspector
              ? 'bg-blue-950/60 border-blue-500/60 text-blue-300'
              : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'
          }`}
          title="Toggle Visual PNR Inspector"
        >
          <Eye className="w-3.5 h-3.5" />
          <span className="hidden lg:inline">PNR Inspector</span>
        </button>

        {/* Theme Selector */}
        <div className="flex items-center bg-[#131c2e] p-0.5 rounded-lg border border-slate-800 text-xs">
          <button
            onClick={() => setTheme('modern')}
            className={`px-2 py-1 rounded transition-colors ${
              theme === 'modern' ? 'bg-slate-700 text-white font-bold' : 'text-slate-400 hover:text-white'
            }`}
            title="Modern 1A Selling Platform Connect Theme"
          >
            Modern
          </button>
          <button
            onClick={() => setTheme('crt-green')}
            className={`px-2 py-1 rounded transition-colors ${
              theme === 'crt-green' ? 'bg-[#1b8a1b] text-black font-bold' : 'text-emerald-500 hover:text-emerald-400'
            }`}
            title="Vintage Green CRT Theme"
          >
            CRT Green
          </button>
          <button
            onClick={() => setTheme('crt-amber')}
            className={`px-2 py-1 rounded transition-colors ${
              theme === 'crt-amber' ? 'bg-[#cc8800] text-black font-bold' : 'text-amber-500 hover:text-amber-400'
            }`}
            title="Vintage Amber CRT Theme"
          >
            CRT Amber
          </button>
        </div>

        {/* User Session & Instructor link */}
        {user ? (
          <div className="flex items-center space-x-2 pl-1 border-l border-slate-800">
            {user.role === 'instructor' && (
              <Link
                href="/instructor"
                className="px-2 py-1 bg-purple-900/40 text-purple-300 border border-purple-700/50 rounded-lg hover:bg-purple-900/60 transition-colors flex items-center gap-1"
              >
                <ShieldCheck className="w-3.5 h-3.5" />
                <span className="hidden xl:inline">Instructor Hub</span>
              </Link>
            )}
            <div className="flex items-center gap-1.5 px-2 py-1 bg-slate-900 rounded-lg border border-slate-800">
              <User className="w-3.5 h-3.5 text-blue-400" />
              <span className="font-semibold text-white">{user.username}</span>
              <span className="text-[10px] text-slate-500 uppercase">({user.role})</span>
            </div>
            <button
              onClick={handleLogout}
              className="p-1.5 text-slate-400 hover:text-rose-400 transition-colors"
              title="Logout"
            >
              <LogOut className="w-3.5 h-3.5" />
            </button>
          </div>
        ) : (
          <div className="flex items-center space-x-1 pl-1 border-l border-slate-800">
            <Link
              href="/login"
              className="px-2.5 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-semibold transition-colors"
            >
              Sign In
            </Link>
          </div>
        )}
      </div>
    </header>
  );
}
