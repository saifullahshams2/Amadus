import { NextRequest, NextResponse } from 'next/server';
import { getDb, initializeDatabase } from '@/lib/db';
import { hashPassword, createToken } from '@/lib/auth';

export async function POST(req: NextRequest) {
  try {
    await initializeDatabase();
    const body = await req.json();
    const { username, email, password, role = 'student', officeId = 'LON1A0980', agentId = '1234AB' } = body;

    if (!username || !email || !password) {
      return NextResponse.json({ error: 'Username, email, and password are required' }, { status: 400 });
    }

    const db = getDb();
    const existing = await db.query(
      `SELECT id FROM users WHERE username = $1 OR email = $2 LIMIT 1`,
      [username.trim(), email.trim()]
    );

    if (existing.length > 0) {
      return NextResponse.json({ error: 'Username or email already exists' }, { status: 409 });
    }

    const newId = `usr-${Date.now()}`;
    const passwordHash = hashPassword(password);
    const nowIso = new Date().toISOString();

    await db.execute(
      `INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,
      [newId, username.trim(), email.trim(), passwordHash, role, officeId.toUpperCase(), agentId.toUpperCase(), nowIso]
    );

    const sessionUser = {
      id: newId,
      username: username.trim(),
      email: email.trim(),
      role: role as 'student' | 'instructor' | 'admin',
      officeId: officeId.toUpperCase(),
      agentId: agentId.toUpperCase(),
    };

    const token = createToken(sessionUser);

    const response = NextResponse.json({
      success: true,
      user: sessionUser,
      token,
    });

    response.cookies.set('gds_token', token, {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      path: '/',
      maxAge: 60 * 60 * 24 * 7,
    });

    return response;
  } catch (error: any) {
    console.error('Register error:', error);
    return NextResponse.json({ error: error.message || 'Internal server error' }, { status: 500 });
  }
}
