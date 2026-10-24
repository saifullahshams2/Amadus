import { NextRequest, NextResponse } from 'next/server';
import { getDb, initializeDatabase } from '@/lib/db';
import { verifyToken } from '@/lib/auth';

export async function GET(req: NextRequest) {
  try {
    await initializeDatabase();
    const token = req.cookies.get('gds_token')?.value;
    const session = token ? verifyToken(token) : null;

    if (!session || (session.role !== 'instructor' && session.role !== 'admin')) {
      return NextResponse.json({ error: 'Unauthorized: Instructor access required' }, { status: 403 });
    }

    const db = getDb();
    const students = await db.query(
      `SELECT id, username, email, role, office_id, agent_id, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC`
    );

    const progressRecords = await db.query(
      `SELECT user_id, lesson_id, module_id, status, score, completed_at FROM lesson_progress`
    );

    const pnrs = await db.query(
      `SELECT record_locator, user_id, office_id, status, data_json, created_at, updated_at FROM pnrs ORDER BY updated_at DESC`
    );

    return NextResponse.json({
      students,
      progressRecords,
      pnrs,
    });
  } catch (err: any) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }
}
