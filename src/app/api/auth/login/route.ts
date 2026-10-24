import { NextRequest, NextResponse } from 'next/server';
import { getDb, initializeDatabase } from '@/lib/db';
import { verifyPassword, createToken, seedDefaultUsers } from '@/lib/auth';

export async function POST(req: NextRequest) {
  try {
    await initializeDatabase();
    await seedDefaultUsers();

    const body = await req.json();
    const { username, password } = body;

    if (!username || !password) {
      return NextResponse.json({ error: 'Username and password are required' }, { status: 400 });
    }

    const db = getDb();
    const users = await db.query(
      `SELECT * FROM users WHERE username = $1 OR email = $1 LIMIT 1`,
      [username.trim()]
    );

    if (users.length === 0) {
      return NextResponse.json({ error: 'Invalid username or password' }, { status: 401 });
    }

    const user = users[0];
    const passwordMatch = verifyPassword(password, user.password_hash);

    if (!passwordMatch) {
      return NextResponse.json({ error: 'Invalid username or password' }, { status: 401 });
    }

    const sessionUser = {
      id: user.id,
      username: user.username,
      email: user.email,
      role: user.role,
      officeId: user.office_id,
      agentId: user.agent_id,
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
      maxAge: 60 * 60 * 24 * 7, // 7 days
    });

    return response;
  } catch (error: any) {
    console.error('Login error:', error);
    return NextResponse.json({ error: error.message || 'Internal server error' }, { status: 500 });
  }
}
