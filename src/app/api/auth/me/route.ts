import { NextRequest, NextResponse } from 'next/server';
import { verifyToken, seedDefaultUsers } from '@/lib/auth';
import { initializeDatabase } from '@/lib/db';

export async function GET(req: NextRequest) {
  try {
    await initializeDatabase();
    await seedDefaultUsers();

    const token = req.cookies.get('gds_token')?.value || req.headers.get('authorization')?.replace('Bearer ', '');
    if (!token) {
      return NextResponse.json({ user: null });
    }

    const session = verifyToken(token);
    return NextResponse.json({ user: session });
  } catch (err: any) {
    return NextResponse.json({ user: null });
  }
}

export async function POST(req: NextRequest) {
  // Logout endpoint
  const res = NextResponse.json({ success: true });
  res.cookies.set('gds_token', '', { maxAge: 0, path: '/' });
  return res;
}
