'use client';

import React from 'react';
import { useTerminalStore } from '@/store/terminalStore';
import { Plane, Users, Phone, FileCheck, CheckCircle2, AlertCircle, CreditCard, Ticket } from 'lucide-react';

export default function PnrVisualizer() {
  const { areas, activeArea } = useTerminalStore();
  const pnr = areas[activeArea]?.activePnr;

  const hasPhone = (pnr?.contacts.length || 0) > 0;
  const hasRF = !!pnr?.receivedFrom;
  const hasItin = (pnr?.segments.length || 0) > 0;
  const hasName = (pnr?.passengers.length || 0) > 0;
  const hasTK = !!pnr?.ticketing;

  const printReady = hasPhone && hasRF && hasItin && hasName && hasTK;

  return (
    <div className="bg-[#0e1626] border border-slate-800 rounded-xl p-4 text-xs text-slate-200 shadow-xl space-y-4">
      {/* PNR Header & Record Locator */}
      <div className="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
          <span className="text-[10px] uppercase font-semibold text-slate-400">
            Work Area {activeArea} Active PNR
          </span>
          <div className="text-base font-bold text-white flex items-center gap-2 mt-0.5">
            <span>Record Locator:</span>
            {pnr?.recordLocator ? (
              <span className="font-mono px-2 py-0.5 bg-blue-600/30 text-blue-300 rounded border border-blue-500/40 tracking-wider">
                {pnr.recordLocator}
              </span>
            ) : (
              <span className="font-mono text-xs text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                TRANSIENT BUFFER
              </span>
            )}
          </div>
        </div>

        {/* PRINT Readiness Badge */}
        <div className={`px-2.5 py-1 rounded-lg text-[11px] font-semibold flex items-center gap-1.5 border ${
          printReady
            ? 'bg-emerald-950/40 text-emerald-300 border-emerald-700/50'
            : 'bg-amber-950/40 text-amber-300 border-amber-700/50'
        }`}>
          {printReady ? (
            <>
              <CheckCircle2 className="w-3.5 h-3.5 text-emerald-400" />
              <span>PRINT Complete (ET Ready)</span>
            </>
          ) : (
            <>
              <AlertCircle className="w-3.5 h-3.5 text-amber-400" />
              <span>Incomplete PRINT</span>
            </>
          )}
        </div>
      </div>

      {/* Mandatory PRINT Health Indicator */}
      <div className="grid grid-cols-5 gap-1.5 text-center">
        <div className={`p-2 rounded border ${hasPhone ? 'bg-emerald-950/30 border-emerald-800/60 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-500'}`}>
          <div className="font-bold text-xs">P</div>
          <div className="text-[10px] mt-0.5">Phone ({pnr?.contacts.length || 0})</div>
        </div>
        <div className={`p-2 rounded border ${hasRF ? 'bg-emerald-950/30 border-emerald-800/60 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-500'}`}>
          <div className="font-bold text-xs">R</div>
          <div className="text-[10px] mt-0.5">{hasRF ? 'Received' : 'Need RF'}</div>
        </div>
        <div className={`p-2 rounded border ${hasItin ? 'bg-emerald-950/30 border-emerald-800/60 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-500'}`}>
          <div className="font-bold text-xs">I</div>
          <div className="text-[10px] mt-0.5">Flights ({pnr?.segments.length || 0})</div>
        </div>
        <div className={`p-2 rounded border ${hasName ? 'bg-emerald-950/30 border-emerald-800/60 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-500'}`}>
          <div className="font-bold text-xs">N</div>
          <div className="text-[10px] mt-0.5">Names ({pnr?.passengers.length || 0})</div>
        </div>
        <div className={`p-2 rounded border ${hasTK ? 'bg-emerald-950/30 border-emerald-800/60 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-500'}`}>
          <div className="font-bold text-xs">T</div>
          <div className="text-[10px] mt-0.5">{hasTK ? 'Ticketing' : 'Need TK'}</div>
        </div>
      </div>

      {/* Booked Passengers & Segments Details */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
        {/* Passengers List */}
        <div className="bg-[#090e18] p-3 rounded-lg border border-slate-800 space-y-1.5">
          <div className="text-[11px] font-semibold text-slate-400 uppercase flex items-center gap-1.5">
            <Users className="w-3.5 h-3.5 text-blue-400" />
            <span>Passengers ({pnr?.passengers.length || 0})</span>
          </div>
          {pnr?.passengers && pnr.passengers.length > 0 ? (
            <div className="space-y-1">
              {pnr.passengers.map((pax) => (
                <div key={pax.id} className="font-mono text-xs text-slate-200 bg-slate-800/40 px-2 py-1 rounded flex items-center justify-between">
                  <span>{pax.id}. {pax.lastName}/{pax.firstName} {pax.title || ''}</span>
                  <span className="text-[10px] px-1.5 py-0.2 rounded bg-blue-500/20 text-blue-300">{pax.type}</span>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-slate-500 italic text-[11px]">No names entered yet (use NM1...)</div>
          )}
        </div>

        {/* Flight Segments List */}
        <div className="bg-[#090e18] p-3 rounded-lg border border-slate-800 space-y-1.5">
          <div className="text-[11px] font-semibold text-slate-400 uppercase flex items-center gap-1.5">
            <Plane className="w-3.5 h-3.5 text-emerald-400" />
            <span>Itinerary Segments ({pnr?.segments.length || 0})</span>
          </div>
          {pnr?.segments && pnr.segments.length > 0 ? (
            <div className="space-y-1">
              {pnr.segments.map((seg) => (
                <div key={seg.segmentNumber} className="font-mono text-xs text-slate-200 bg-slate-800/40 px-2 py-1 rounded flex items-center justify-between">
                  <span>{seg.segmentNumber}. {seg.airlineCode} {seg.flightNumber} ({seg.bookingClass})</span>
                  <span>{seg.departureAirport} → {seg.arrivalAirport}</span>
                  <span className="text-[10px] px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-300">{seg.status}{seg.seats}</span>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-slate-500 italic text-[11px]">No flights booked yet (use AN & SS...)</div>
          )}
        </div>
      </div>

      {/* Pricing / TST / Ticket Status */}
      <div className="flex flex-wrap items-center justify-between pt-2 border-t border-slate-800 text-[11px] text-slate-400 gap-2">
        <div className="flex items-center gap-2">
          <CreditCard className="w-3.5 h-3.5 text-amber-400" />
          <span>TST Pricing: </span>
          {pnr?.tsts && pnr.tsts.length > 0 ? (
            <strong className="text-white font-mono">{pnr.tsts[0].currency} {pnr.tsts[0].total.toFixed(2)}</strong>
          ) : (
            <span className="italic text-slate-500">Not priced (enter FXP)</span>
          )}
        </div>

        <div className="flex items-center gap-2">
          <Ticket className="w-3.5 h-3.5 text-purple-400" />
          <span>E-Tickets: </span>
          {pnr?.tickets && pnr.tickets.length > 0 ? (
            <strong className="text-emerald-400 font-mono">{pnr.tickets.length} Issued ({pnr.tickets[0].ticketNumber})</strong>
          ) : (
            <span className="italic text-slate-500">Unissued (enter TTP)</span>
          )}
        </div>
      </div>
    </div>
  );
}
