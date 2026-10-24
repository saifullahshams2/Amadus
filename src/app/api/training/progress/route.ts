import { NextRequest, NextResponse } from 'next/server';
import { getDb, initializeDatabase } from '@/lib/db';
import { verifyToken } from '@/lib/auth';

export async function GET(req: NextRequest) {
  try {
    await initializeDatabase();
    const token = req.cookies.get('gds_token')?.value;
    const session = token ? verifyToken(token) : null;
    const userId = session?.id || 'guest';

    const db = getDb();
    const rows = await db.query(
      `SELECT * FROM lesson_progress WHERE user_id = $1`,
      [userId]
    );

    return NextResponse.json({ progress: rows });
  } catch (err: any) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }
}

export async function POST(req: NextRequest) {
  try {
    await initializeDatabase();
    const token = req.cookies.get('gds_token')?.value;
    const session = token ? verifyToken(token) : null;
    const userId = session?.id || 'guest';

    const body = await req.json();
    const { lessonId, moduleId, status, score, completedObjectives } = body;

    const db = getDb();
    const id = `${userId}_${lessonId}`;
    const nowIso = new Date().toISOString();

    await db.execute(
      `INSERT INTO lesson_progress (id, user_id, lesson_id, module_id, status, completed_objectives_json, score, attempts, completed_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, 1, $8)
       ON CONFLICT(user_id, lesson_id) DO UPDATE SET
       status = EXCLUDED.status,
       completed_objectives_json = EXCLUDED.completed_objectives_json,
       score = EXCLUDED.score,
       attempts = lesson_progress.attempts + 1,
       completed_at = EXCLUDED.completed_at;`,
      [id, userId, lessonId, moduleId, status, JSON.stringify(completedObjectives || []), score || 100, status === 'completed' ? nowIso : null]
    );

    return NextResponse.json({ success: true });
  } catch (err: any) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }
}
