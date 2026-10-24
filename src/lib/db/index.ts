import fs from 'fs';
import path from 'path';

export type DatabaseType = 'sqlite' | 'postgres';

interface DBClient {
  type: DatabaseType;
  query<T = any>(sql: string, params?: any[]): Promise<T[]>;
  execute(sql: string, params?: any[]): Promise<{ rowCount: number; lastInsertRowid?: number | bigint }>;
  close(): Promise<void>;
}

let dbInstance: DBClient | null = null;

export function getDatabaseType(): DatabaseType {
  const envType = process.env.DATABASE_TYPE?.toLowerCase();
  if (envType === 'postgres' || envType === 'postgresql') return 'postgres';
  if (process.env.DATABASE_URL?.startsWith('postgres://') || process.env.DATABASE_URL?.startsWith('postgresql://')) {
    return 'postgres';
  }
  return 'sqlite';
}

function createSQLiteClient(): DBClient {
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  const Database = require('better-sqlite3');
  const dbDir = path.join(process.cwd(), 'data');
  if (!fs.existsSync(dbDir)) {
    fs.mkdirSync(dbDir, { recursive: true });
  }
  const dbPath = path.join(dbDir, 'gds.db');
  const sqlite = new Database(dbPath);

  // Enable WAL mode for better concurrency and reliability
  sqlite.pragma('journal_mode = WAL');

  return {
    type: 'sqlite',
    async query<T = any>(sql: string, params: any[] = []): Promise<T[]> {
      const paramIndexes: number[] = [];
      const convertedSql = sql.replace(/\$(\d+)/g, (_, num) => {
        paramIndexes.push(parseInt(num, 10) - 1);
        return '?';
      });
      const mappedParams = paramIndexes.length > 0 ? paramIndexes.map(idx => params[idx]) : params;
      const stmt = sqlite.prepare(convertedSql);
      return stmt.all(...mappedParams) as T[];
    },
    async execute(sql: string, params: any[] = []): Promise<{ rowCount: number; lastInsertRowid?: number | bigint }> {
      const paramIndexes: number[] = [];
      const convertedSql = sql.replace(/\$(\d+)/g, (_, num) => {
        paramIndexes.push(parseInt(num, 10) - 1);
        return '?';
      });
      const mappedParams = paramIndexes.length > 0 ? paramIndexes.map(idx => params[idx]) : params;
      const stmt = sqlite.prepare(convertedSql);
      const info = stmt.run(...mappedParams);
      return { rowCount: info.changes, lastInsertRowid: info.lastInsertRowid };
    },
    async close() {
      sqlite.close();
    }
  };
}

function createPostgresClient(): DBClient {
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  const { Pool } = require('pg');
  const connectionString = process.env.DATABASE_URL || 'postgresql://postgres:postgres@localhost:5432/gds_amadeus';
  const pool = new Pool({
    connectionString,
    max: 10,
    idleTimeoutMillis: 30000,
  });

  return {
    type: 'postgres',
    async query<T = any>(sql: string, params: any[] = []): Promise<T[]> {
      const res = await pool.query(sql, params);
      return res.rows as T[];
    },
    async execute(sql: string, params: any[] = []): Promise<{ rowCount: number }> {
      const res = await pool.query(sql, params);
      return { rowCount: res.rowCount || 0 };
    },
    async close() {
      await pool.end();
    }
  };
}

export function getDb(): DBClient {
  if (!dbInstance) {
    const dbType = getDatabaseType();
    if (dbType === 'postgres') {
      dbInstance = createPostgresClient();
    } else {
      dbInstance = createSQLiteClient();
    }
  }
  return dbInstance;
}

/**
 * Initializes database schemas (compatible with both SQLite & Postgres)
 */
export async function initializeDatabase(): Promise<void> {
  const db = getDb();

  // Users table
  await db.execute(`
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
  `);

  // PNRs table (stores confirmed/ticketed PNRs)
  await db.execute(`
    CREATE TABLE IF NOT EXISTS pnrs (
      record_locator TEXT PRIMARY KEY,
      user_id TEXT NOT NULL,
      office_id TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'CONFIRMED',
      data_json TEXT NOT NULL,
      created_at TEXT NOT NULL,
      updated_at TEXT NOT NULL
    );
  `);

  // Lesson Progress table
  await db.execute(`
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

  // Airports table
  await db.execute(`
    CREATE TABLE IF NOT EXISTS airports (
      iata TEXT PRIMARY KEY,
      icao TEXT,
      name TEXT NOT NULL,
      city TEXT NOT NULL,
      country TEXT NOT NULL,
      lat REAL,
      lon REAL,
      tz TEXT
    );
  `);

  // Airlines table
  await db.execute(`
    CREATE TABLE IF NOT EXISTS airlines (
      iata TEXT PRIMARY KEY,
      icao TEXT,
      name TEXT NOT NULL,
      country TEXT
    );
  `);

  // Aircraft table
  await db.execute(`
    CREATE TABLE IF NOT EXISTS aircraft (
      code TEXT PRIMARY KEY,
      name TEXT NOT NULL,
      manufacturer TEXT NOT NULL
    );
  `);
}
