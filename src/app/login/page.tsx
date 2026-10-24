'use client';

import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Plane, Lock, User, ArrowRight, ShieldCheck, GraduationCap } from 'lucide-react';

export default function LoginPage() {
  const router = useRouter();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleLogin = async (u: string, p: string) => {
    setIsLoading(true);
    setError('');

    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: u, password: p }),
      });

      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error || 'Login failed');
      }

      router.push('/');
      router.refresh();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    handleLogin(username, password);
  };

  return (
    <div className="flex-1 flex items-center justify-center p-4 bg-[#070b12]">
      <div className="w-full max-w-md bg-[#0e1626] border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6">
        {/* Brand header */}
        <div className="text-center space-y-2">
          <div className="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center mx-auto shadow-lg shadow-blue-500/20">
            <Plane className="w-6 h-6 text-white transform -rotate-45" />
          </div>
          <h1 className="text-xl font-bold text-white tracking-wide">
            Amadeus GDS Sign-In
          </h1>
          <p className="text-xs text-slate-400">
            Enter your agent credentials or use a quick demo account
          </p>
        </div>

        {error && (
          <div className="p-3 rounded-lg bg-rose-950/40 border border-rose-800/60 text-xs text-rose-300 text-center">
            {error}
          </div>
        )}

        {/* Credentials Form */}
        <form onSubmit={onSubmit} className="space-y-4">
          <div className="space-y-1">
            <label className="text-xs font-semibold text-slate-300">Username or Email</label>
            <div className="relative">
              <User className="w-4 h-4 text-slate-500 absolute left-3 top-3" />
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="e.g. student or instructor"
                className="w-full pl-9 pr-3 py-2 bg-[#090e18] border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-blue-500"
                required
              />
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-xs font-semibold text-slate-300">Password</label>
            <div className="relative">
              <Lock className="w-4 h-4 text-slate-500 absolute left-3 top-3" />
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
                className="w-full pl-9 pr-3 py-2 bg-[#090e18] border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-blue-500"
                required
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-semibold text-sm transition-colors shadow-lg shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <span>{isLoading ? 'Signing In...' : 'Sign In'}</span>
            <ArrowRight className="w-4 h-4" />
          </button>
        </form>

        {/* Quick 1-Click Demo Accounts */}
        <div className="pt-4 border-t border-slate-800/80 space-y-2">
          <div className="text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center">
            Quick 1-Click Demo Logins
          </div>
          <div className="grid grid-cols-2 gap-2">
            <button
              type="button"
              onClick={() => handleLogin('student', 'amadeus123')}
              className="p-2.5 rounded-lg bg-blue-950/40 hover:bg-blue-900/40 border border-blue-800/50 text-left transition-colors"
            >
              <div className="font-bold text-xs text-blue-300 flex items-center gap-1.5">
                <GraduationCap className="w-3.5 h-3.5" />
                <span>Student</span>
              </div>
              <div className="text-[10px] text-slate-400 mt-0.5">student / amadeus123</div>
            </button>

            <button
              type="button"
              onClick={() => handleLogin('instructor', 'amadeus123')}
              className="p-2.5 rounded-lg bg-purple-950/40 hover:bg-purple-900/40 border border-purple-800/50 text-left transition-colors"
            >
              <div className="font-bold text-xs text-purple-300 flex items-center gap-1.5">
                <ShieldCheck className="w-3.5 h-3.5" />
                <span>Instructor</span>
              </div>
              <div className="text-[10px] text-slate-400 mt-0.5">instructor / amadeus123</div>
            </button>
          </div>
        </div>

        <div className="text-center text-xs text-slate-400">
          Need a new trainee account?{' '}
          <Link href="/register" className="text-blue-400 hover:underline font-semibold">
            Register here
          </Link>
        </div>
      </div>
    </div>
  );
}
