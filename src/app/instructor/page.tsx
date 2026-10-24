'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { Users, FileText, CheckCircle2, ShieldAlert, ArrowLeft, Database, Search } from 'lucide-react';

export default function InstructorDashboard() {
  const [students, setStudents] = useState<any[]>([]);
  const [progress, setProgress] = useState<any[]>([]);
  const [pnrs, setPnrs] = useState<any[]>([]);
  const [selectedPnr, setSelectedPnr] = useState<any | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [searchQuery, setSearchQuery] = useState('');

  useEffect(() => {
    fetch('/api/instructor/students')
      .then(async (res) => {
        if (!res.ok) {
          const errData = await res.json();
          throw new Error(errData.error || 'Failed to load instructor data');
        }
        return res.json();
      })
      .then((data) => {
        setStudents(data.students || []);
        setProgress(data.progressRecords || []);
        setPnrs(data.pnrs || []);
        setLoading(false);
      })
      .catch((err) => {
        setError(err.message);
        setLoading(false);
      });
  }, []);

  if (loading) {
    return (
      <div className="flex-1 flex items-center justify-center text-slate-400 font-mono text-sm">
        Loading instructor dashboard...
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex-1 flex flex-col items-center justify-center p-6 text-center space-y-4">
        <ShieldAlert className="w-12 h-12 text-rose-500" />
        <h2 className="text-lg font-bold text-white">Access Restricted</h2>
        <p className="text-sm text-slate-400 max-w-md">{error}</p>
        <Link
          href="/login"
          className="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold"
        >
          Sign in as Instructor (instructor / amadeus123)
        </Link>
      </div>
    );
  }

  const filteredPnrs = pnrs.filter(p => 
    p.record_locator.toLowerCase().includes(searchQuery.toLowerCase()) ||
    p.user_id.toLowerCase().includes(searchQuery.toLowerCase()) ||
    p.office_id.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="flex-1 p-4 sm:p-6 max-w-7xl mx-auto w-full space-y-6 overflow-y-auto">
      {/* Top Banner */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
          <div className="flex items-center gap-2">
            <Link href="/" className="text-slate-400 hover:text-white transition-colors">
              <ArrowLeft className="w-4 h-4" />
            </Link>
            <h1 className="text-xl sm:text-2xl font-bold text-white tracking-wide">
              Instructor Operations Hub
            </h1>
          </div>
          <p className="text-xs text-slate-400 mt-1">
            Real-time trainee performance monitoring, assessment audits, and PNR inspection
          </p>
        </div>

        {/* Database & Environment Status Badge */}
        <div className="flex items-center gap-2 bg-[#0e1626] border border-slate-800 px-3 py-1.5 rounded-lg text-xs text-slate-300">
          <Database className="w-4 h-4 text-emerald-400" />
          <span>System DB: <strong className="text-white">Active (SQLite / PostgreSQL Ready)</strong></span>
        </div>
      </div>

      {/* Summary KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="p-4 rounded-xl bg-[#0e1626] border border-slate-800 flex items-center gap-3">
          <div className="w-10 h-10 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center font-bold">
            <Users className="w-5 h-5" />
          </div>
          <div>
            <div className="text-xl font-bold text-white">{students.length}</div>
            <div className="text-xs text-slate-400">Enrolled Trainees</div>
          </div>
        </div>

        <div className="p-4 rounded-xl bg-[#0e1626] border border-slate-800 flex items-center gap-3">
          <div className="w-10 h-10 rounded-lg bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-bold">
            <FileText className="w-5 h-5" />
          </div>
          <div>
            <div className="text-xl font-bold text-white">{pnrs.length}</div>
            <div className="text-xs text-slate-400">Committed PNR Records</div>
          </div>
        </div>

        <div className="p-4 rounded-xl bg-[#0e1626] border border-slate-800 flex items-center gap-3">
          <div className="w-10 h-10 rounded-lg bg-purple-600/20 text-purple-400 flex items-center justify-center font-bold">
            <CheckCircle2 className="w-5 h-5" />
          </div>
          <div>
            <div className="text-xl font-bold text-white">{progress.length}</div>
            <div className="text-xs text-slate-400">Lesson Submissions</div>
          </div>
        </div>
      </div>

      {/* Section 1: Trainee Roster */}
      <div className="bg-[#0e1626] border border-slate-800 rounded-xl overflow-hidden shadow-lg space-y-3 p-4">
        <h2 className="text-sm font-bold text-white flex items-center gap-2">
          <Users className="w-4 h-4 text-blue-400" />
          <span>Trainee Roster & Progress</span>
        </h2>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs text-slate-300">
            <thead className="bg-[#090e18] text-slate-400 font-semibold border-b border-slate-800 uppercase tracking-wider text-[10px]">
              <tr>
                <th className="p-2.5">Agent / Username</th>
                <th className="p-2.5">Email</th>
                <th className="p-2.5">Office ID</th>
                <th className="p-2.5">Agent Sign</th>
                <th className="p-2.5">Lessons Attempted</th>
                <th className="p-2.5">Enrolled Date</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800">
              {students.map((st) => {
                const userProgress = progress.filter(p => p.user_id === st.id);
                return (
                  <tr key={st.id} className="hover:bg-slate-800/30 transition-colors">
                    <td className="p-2.5 font-semibold text-white">{st.username}</td>
                    <td className="p-2.5 text-slate-400">{st.email}</td>
                    <td className="p-2.5 font-mono text-blue-300">{st.office_id}</td>
                    <td className="p-2.5 font-mono">{st.agent_id}</td>
                    <td className="p-2.5">
                      <span className="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 font-semibold">
                        {userProgress.length} Lessons
                      </span>
                    </td>
                    <td className="p-2.5 text-slate-400">{new Date(st.created_at).toLocaleDateString()}</td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* Section 2: PNR Review & Audit Table */}
      <div className="bg-[#0e1626] border border-slate-800 rounded-xl overflow-hidden shadow-lg space-y-3 p-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h2 className="text-sm font-bold text-white flex items-center gap-2">
            <FileText className="w-4 h-4 text-emerald-400" />
            <span>Audited PNR Reservations</span>
          </h2>

          <div className="relative">
            <Search className="w-3.5 h-3.5 text-slate-500 absolute left-3 top-2.5" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search Record Locator..."
              className="pl-8 pr-3 py-1.5 bg-[#090e18] border border-slate-800 rounded-lg text-xs text-white focus:outline-none focus:border-blue-500"
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs text-slate-300">
            <thead className="bg-[#090e18] text-slate-400 font-semibold border-b border-slate-800 uppercase tracking-wider text-[10px]">
              <tr>
                <th className="p-2.5">Record Locator</th>
                <th className="p-2.5">Status</th>
                <th className="p-2.5">Created By</th>
                <th className="p-2.5">Office ID</th>
                <th className="p-2.5">Passengers</th>
                <th className="p-2.5">Updated</th>
                <th className="p-2.5">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800">
              {filteredPnrs.map((p) => {
                let parsed = null;
                try { parsed = JSON.parse(p.data_json); } catch (e) {}
                const paxNames = parsed?.passengers?.map((pax: any) => `${pax.lastName}/${pax.firstName}`).join(', ') || 'N/A';

                return (
                  <tr key={p.record_locator} className="hover:bg-slate-800/30 transition-colors">
                    <td className="p-2.5 font-mono font-bold text-blue-300 tracking-wider">
                      {p.record_locator}
                    </td>
                    <td className="p-2.5">
                      <span className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                        p.status === 'TICKETED'
                          ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30'
                          : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                      }`}>
                        {p.status}
                      </span>
                    </td>
                    <td className="p-2.5 font-semibold text-white">{p.user_id}</td>
                    <td className="p-2.5 font-mono">{p.office_id}</td>
                    <td className="p-2.5 text-slate-400 max-w-xs truncate">{paxNames}</td>
                    <td className="p-2.5 text-slate-400">{new Date(p.updated_at).toLocaleString()}</td>
                    <td className="p-2.5">
                      <button
                        onClick={() => setSelectedPnr(parsed)}
                        className="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-blue-300 rounded font-semibold transition-colors"
                      >
                        Inspect PNR
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* PNR Inspection Modal */}
      {selectedPnr && (
        <div className="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-[#0b101b] border border-slate-700 rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="font-bold text-white text-base">
                PNR Record: <span className="font-mono text-blue-400">{selectedPnr.recordLocator}</span>
              </h3>
              <button
                onClick={() => setSelectedPnr(null)}
                className="text-slate-400 hover:text-white text-sm"
              >
                ✕ Close
              </button>
            </div>

            <div className="p-4 bg-[#05080f] rounded-xl border border-slate-800 font-mono text-xs text-emerald-400 space-y-1 overflow-x-auto">
              <div>--- RLR ---</div>
              <div>RP/{selectedPnr.creationOffice}/{selectedPnr.creationAgent}   {selectedPnr.recordLocator}</div>
              {selectedPnr.passengers?.map((pax: any, i: number) => (
                <div key={i}>  {i + 1}.{pax.lastName}/{pax.firstName} {pax.title || ''}</div>
              ))}
              {selectedPnr.segments?.map((seg: any) => (
                <div key={seg.segmentNumber}>
                  {'  '}{seg.segmentNumber}  {seg.airlineCode} {seg.flightNumber} {seg.bookingClass} {seg.departureDate} {seg.departureAirport}{seg.arrivalAirport} {seg.status}{seg.seats}  {seg.departureTime} {seg.arrivalTime}
                </div>
              ))}
              {selectedPnr.contacts?.map((c: any, i: number) => (
                <div key={i}>  AP {c.detail}</div>
              ))}
              {selectedPnr.ticketing && (
                <div>  TK {selectedPnr.ticketing.raw}</div>
              )}
              {selectedPnr.fop && (
                <div>  FP {selectedPnr.fop.details}</div>
              )}
              {selectedPnr.receivedFrom && (
                <div>  RF {selectedPnr.receivedFrom}</div>
              )}
              {selectedPnr.tickets?.map((t: any, i: number) => (
                <div key={i} className="text-purple-300">
                  {'  '}FA PAX {t.ticketNumber} / ETKT ISSUED
                </div>
              ))}
            </div>

            <div className="text-right">
              <button
                onClick={() => setSelectedPnr(null)}
                className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold"
              >
                Done
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
