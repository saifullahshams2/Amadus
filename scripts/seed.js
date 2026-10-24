#!/usr/bin/env node
/**
 * Standalone database initialization and seed runner
 */
const path = require('path');
const fs = require('fs');

async function seed() {
  console.log('--- Initializing Amadeus GDS Database ---');
  const dbDir = path.join(process.cwd(), 'data');
  if (!fs.existsSync(dbDir)) {
    fs.mkdirSync(dbDir, { recursive: true });
  }

  // Load Database
  const Database = require('better-sqlite3');
  const db = new Database(path.join(dbDir, 'gds.db'));
  db.pragma('journal_mode = WAL');

  console.log('Creating database schemas...');
  db.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id TEXT PRIMARY KEY,
      username TEXT UNIQUE NOT NULL,
      email TEXT UNIQUE NOT NULL,
      password_hash TEXT NOT NULL,
      role TEXT NOT NULL DEFAULT 'student',
      office_id TEXT NOT NULL DEFAULT 'LON1A0980',
      agent_id TEXT NOT NULL DEFAULT '1234AB',
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS pnrs (
      record_locator TEXT PRIMARY KEY,
      user_id TEXT NOT NULL,
      office_id TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'CONFIRMED',
      data_json TEXT NOT NULL,
      created_at TEXT NOT NULL,
      updated_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS lesson_progress (
      id TEXT PRIMARY KEY,
      user_id TEXT NOT NULL,
      lesson_id TEXT NOT NULL,
      module_id TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'in_progress',
      completed_objectives_json TEXT NOT NULL DEFAULT '[]',
      score INTEGER NOT NULL DEFAULT 0,
      attempts INTEGER NOT NULL DEFAULT 1,
      completed_at TEXT,
      UNIQUE(user_id, lesson_id)
    );
  `);

  // Seed default users with bcrypt hash for 'amadeus123'
  const bcrypt = require('bcryptjs');
  const passwordHash = bcrypt.hashSync('amadeus123', 10);
  const nowIso = new Date().toISOString();

  const insertUser = db.prepare(`
    INSERT OR IGNORE INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  `);

  insertUser.run('usr-instructor', 'instructor', 'instructor@academy.aero', passwordHash, 'instructor', 'LON1A0980', 'INST01', nowIso);
  insertUser.run('usr-student', 'student', 'student@academy.aero', passwordHash, 'student', 'LON1A0980', '1234AB', nowIso);

  console.log('Seeded default instructor (instructor/amadeus123) and student (student/amadeus123) accounts.');
  console.log('Database initialization completed successfully!');
  db.close();
}

seed().catch(err => {
  console.error('Seed error:', err);
  process.exit(1);
});
