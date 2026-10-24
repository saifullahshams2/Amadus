import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { getDb } from '../db';

const JWT_SECRET = process.env.JWT_SECRET || 'gds-amadeus-simulator-secret-key-2026';

export interface UserSession {
  id: string;
  username: string;
  email: string;
  role: 'student' | 'instructor' | 'admin';
  officeId: string;
  agentId: string;
}

export function hashPassword(password: string): string {
  return bcrypt.hashSync(password, 10);
}

export function verifyPassword(password: string, hash: string): boolean {
  return bcrypt.compareSync(password, hash);
}

export function createToken(user: UserSession): string {
  return jwt.sign(user, JWT_SECRET, { expiresIn: '7d' });
}

export function verifyToken(token: string): UserSession | null {
  try {
    return jwt.verify(token, JWT_SECRET) as UserSession;
  } catch (err) {
    return null;
  }
}

/**
 * Seeds default accounts if none exist:
 * 1. Instructor: instructor / amadeus123
 * 2. Student: student / amadeus123
 */
export async function seedDefaultUsers(): Promise<void> {
  const db = getDb();
  const existing = await db.query(`SELECT id FROM users LIMIT 1`);
  if (existing.length === 0) {
    const nowIso = new Date().toISOString();
    const defaultPassword = hashPassword('amadeus123');

    // Instructor
    await db.execute(
      `INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,
      ['usr-instructor', 'instructor', 'instructor@academy.aero', defaultPassword, 'instructor', 'LON1A0980', 'INST01', nowIso]
    );

    // Student
    await db.execute(
      `INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,
      ['usr-student', 'student', 'student@academy.aero', defaultPassword, 'student', 'LON1A0980', '1234AB', nowIso]
    );

    console.log('Seeded default instructor and student accounts.');
  }
}
