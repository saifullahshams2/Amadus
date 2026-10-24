'use client';

import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Plane, Lock, User, Mail, ArrowRight, Building2, BadgeCheck } from 'lucide-react';

export default function RegisterPage() {
  const router = useRouter();
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [role, setRole] = useState<'student' | 'instructor'>('student');
  const [officeId, setOfficeId] = useState('LON1A0980');
  const [agentId, setAgentId] = useState('1234AB');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    try {
      const res = await fetch('/api/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          username,
          email,
          password,
          role,
          officeId,
          agentId,
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error || 'Registration failed');
      }

      router.push('/');
      router.refresh();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="flex-1 flex items-center justify-center p-4 bg-[#070b12]">
      <div className="w-full max-w-md bg-[#0e1626] border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6">
        <div className="text-center space-y-2">
          <div className="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center mx-auto shadow-lg shadow-blue-500/20">
            <Plane className="w-6 h-6 text-white transform -rotate-45" />
          </div>
          <h1 className="text-xl font-bold text-white tracking-wide">
            Register Agent Account
          </h1>
          <p className="text-xs text-slate-400">
            Create your Amadeus trainee or instructor profile
          </p>
        </div>

        {error && (
          <div className="p-3 rounded-lg bg-rose-950/40 border border-rose-800/60 text-xs text-rose-300 text-center">
            {error}
          </div>
        )}

        <form onSubmit={onSubmit} className="space-y-3.5">
          {/* Role selector */}
          <div className="grid grid-cols-2 gap-2 p-1 bg-[#090e18] rounded-lg border border-slate-800 text-xs">
            <button
              type="button"
              onClick={() => setRole('student')}
              className={`py-1.5 rounded-md font-semibold transition-all ${
                role === 'student' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white'
              }`}
            >
              Trainee / Student
            </button>
            <button
              type="button"
              onClick={() => setRole('instructor')}
              className={`py-1.5 rounded-md font-semibold transition-all ${
                role === 'instructor' ? 'bg-purple-600 text-white' : 'text-slate-400 hover:text-white'
              }`}
            >
              Instructor
            </button>
          </div>

          <div className="space-y-1">
            <label className="text-xs font-semibold text-slate-300">Agent Username</label>
            <div className="relative">
              <User className="w-4 h-4 text-slate-500 absolute left-3 top-3" />
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="e.g. john_doe"
                className="w-full pl-9 pr-3 py-2 bg-[#090e18] border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-blue-500"
                required
              />
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-xs font-semibold text-slate-300">Email Address</label>
            <div className="relative">
              <Mail className="w-4 h-4 text-slate-500 absolute left-3 top-3" />
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="agent@agency.com"
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

          <div className="grid grid-cols-2 gap-2">
            <div className="space-y-1">
              <label className="text-[11px] font-semibold text-slate-300">Office ID</label>
              <div className="relative">
                <Building2 className="w-3.5 h-3.5 text-slate-500 absolute left-3 top-3" />
                <input
                  type="text"
                  value={officeId}
                  onChange={(e) => setOfficeId(e.target.value.toUpperCase())}
                  placeholder="LON1A0980"
                  className="w-full pl-8 pr-2 py-2 bg-[#090e18] border border-slate-800 rounded-lg text-xs font-mono uppercase text-white focus:outline-none focus:border-blue-500"
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-[11px] font-semibold text-slate-300">Agent Sign</label>
              <div className="relative">
                <BadgeCheck className="w-3.5 h-3.5 text-slate-500 absolute left-3 top-3" />
                <input
                  type="text"
                  value={agentId}
                  onChange={(e) => setAgentId(e.target.value.toUpperCase())}
                  placeholder="1234AB"
                  className="w-full pl-8 pr-2 py-2 bg-[#090e18] border border-slate-800 rounded-lg text-xs font-mono uppercase text-white focus:outline-none focus:border-blue-500"
                />
              </div>
            </div>
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-semibold text-sm transition-colors shadow-lg shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-2 mt-2"
          >
            <span>{isLoading ? 'Creating Account...' : 'Register Account'}</span>
            <ArrowRight className="w-4 h-4" />
          </button>
        </form>

        <div className="text-center text-xs text-slate-400">
          Already have an account?{' '}
          <Link href="/login" className="text-blue-400 hover:underline font-semibold">
            Sign In here
          </Link>
        </div>
      </div>
    </div>
  );
}
