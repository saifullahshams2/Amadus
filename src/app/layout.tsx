import './globals.css';
import type { Metadata } from 'next';
import Navbar from '@/components/layout/Navbar';

export const metadata: Metadata = {
  title: 'Amadeus GDS Training Simulator - Professional Airline Reservation System',
  description: 'High-fidelity Amadeus GDS cryptic terminal simulator for aviation academies, ticketing agents, and airline trainees.',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="dark">
      <body className="bg-[#070b12] text-slate-100 min-h-screen flex flex-col">
        <Navbar />
        <main className="flex-1 flex flex-col overflow-hidden">
          {children}
        </main>
      </body>
    </html>
  );
}
