"use strict";(()=>{var e={};e.id=130,e.ids=[130],e.modules={5890:e=>{e.exports=require("better-sqlite3")},399:e=>{e.exports=require("next/dist/compiled/next-server/app-page.runtime.prod.js")},517:e=>{e.exports=require("next/dist/compiled/next-server/app-route.runtime.prod.js")},5900:e=>{e.exports=require("pg")},8893:e=>{e.exports=require("buffer")},4770:e=>{e.exports=require("crypto")},5315:e=>{e.exports=require("path")},6162:e=>{e.exports=require("stream")},1764:e=>{e.exports=require("util")},1175:(e,t,r)=>{r.r(t),r.d(t,{originalPathname:()=>L,patchFetch:()=>N,requestAsyncStorage:()=>E,routeModule:()=>d,serverHooks:()=>l,staticGenerationAsyncStorage:()=>p});var s={};r.r(s),r.d(s,{GET:()=>c});var a=r(3278),n=r(5002),o=r(4877),u=r(1309),i=r(1717),T=r(2469);async function c(e){try{await (0,i.xN)();let t=e.cookies.get("gds_token")?.value,r=t?(0,T.WX)(t):null;if(!r||"instructor"!==r.role&&"admin"!==r.role)return u.NextResponse.json({error:"Unauthorized: Instructor access required"},{status:403});let s=(0,i.zA)(),a=await s.query("SELECT id, username, email, role, office_id, agent_id, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC"),n=await s.query("SELECT user_id, lesson_id, module_id, status, score, completed_at FROM lesson_progress"),o=await s.query("SELECT record_locator, user_id, office_id, status, data_json, created_at, updated_at FROM pnrs ORDER BY updated_at DESC");return u.NextResponse.json({students:a,progressRecords:n,pnrs:o})}catch(e){return u.NextResponse.json({error:e.message},{status:500})}}let d=new a.AppRouteRouteModule({definition:{kind:n.x.APP_ROUTE,page:"/api/instructor/students/route",pathname:"/api/instructor/students",filename:"route",bundlePath:"app/api/instructor/students/route"},resolvedPagePath:"/home/saif/Documents/Projects/gds_amadeus/src/app/api/instructor/students/route.ts",nextConfigOutput:"",userland:s}),{requestAsyncStorage:E,staticGenerationAsyncStorage:p,serverHooks:l}=d,L="/api/instructor/students/route";function N(){return(0,o.patchFetch)({serverHooks:l,staticGenerationAsyncStorage:p})}},2469:(e,t,r)=>{r.d(t,{Gv:()=>T,V3:()=>c,WX:()=>d,XY:()=>E,c_:()=>i});var s=r(3981),a=r(7390),n=r.n(a),o=r(1717);let u=process.env.JWT_SECRET||"gds-amadeus-simulator-secret-key-2026";function i(e){return s.ZP.hashSync(e,10)}function T(e,t){return s.ZP.compareSync(e,t)}function c(e){return n().sign(e,u,{expiresIn:"7d"})}function d(e){try{return n().verify(e,u)}catch(e){return null}}async function E(){let e=(0,o.zA)();if(0===(await e.query("SELECT id FROM users LIMIT 1")).length){let t=new Date().toISOString(),r=i("amadeus123");await e.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,["usr-instructor","instructor","instructor@academy.aero",r,"instructor","LON1A0980","INST01",t]),await e.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,["usr-student","student","student@academy.aero",r,"student","LON1A0980","1234AB",t]),console.log("Seeded default instructor and student accounts.")}}},1717:(e,t,r)=>{r.d(t,{zA:()=>i,xN:()=>T});let s=require("fs");var a=r.n(s),n=r(5315),o=r.n(n);let u=null;function i(){return u||(u="postgres"===function(){let e=process.env.DATABASE_TYPE?.toLowerCase();return"postgres"===e||"postgresql"===e||process.env.DATABASE_URL?.startsWith("postgres://")||process.env.DATABASE_URL?.startsWith("postgresql://")?"postgres":"sqlite"}()?function(){let{Pool:e}=r(5900),t=new e({connectionString:process.env.DATABASE_URL||"postgresql://postgres:postgres@localhost:5432/gds_amadeus",max:10,idleTimeoutMillis:3e4});return{type:"postgres",query:async(e,r=[])=>(await t.query(e,r)).rows,execute:async(e,r=[])=>({rowCount:(await t.query(e,r)).rowCount||0}),async close(){await t.end()}}}():function(){let e=r(5890),t=o().join(process.cwd(),"data");a().existsSync(t)||a().mkdirSync(t,{recursive:!0});let s=new e(o().join(t,"gds.db"));return s.pragma("journal_mode = WAL"),{type:"sqlite",async query(e,t=[]){let r=[],a=e.replace(/\$(\d+)/g,(e,t)=>(r.push(parseInt(t,10)-1),"?")),n=r.length>0?r.map(e=>t[e]):t;return s.prepare(a).all(...n)},async execute(e,t=[]){let r=[],a=e.replace(/\$(\d+)/g,(e,t)=>(r.push(parseInt(t,10)-1),"?")),n=r.length>0?r.map(e=>t[e]):t,o=s.prepare(a).run(...n);return{rowCount:o.changes,lastInsertRowid:o.lastInsertRowid}},async close(){s.close()}}}()),u}async function T(){let e=i();await e.execute(`
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
  `),await e.execute(`
    CREATE TABLE IF NOT EXISTS pnrs (
      record_locator TEXT PRIMARY KEY,
      user_id TEXT NOT NULL,
      office_id TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'CONFIRMED',
      data_json TEXT NOT NULL,
      created_at TEXT NOT NULL,
      updated_at TEXT NOT NULL
    );
  `),await e.execute(`
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
  `),await e.execute(`
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
  `),await e.execute(`
    CREATE TABLE IF NOT EXISTS airlines (
      iata TEXT PRIMARY KEY,
      icao TEXT,
      name TEXT NOT NULL,
      country TEXT
    );
  `),await e.execute(`
    CREATE TABLE IF NOT EXISTS aircraft (
      code TEXT PRIMARY KEY,
      name TEXT NOT NULL,
      manufacturer TEXT NOT NULL
    );
  `)}}};var t=require("../../../../webpack-runtime.js");t.C(e);var r=e=>t(t.s=e),s=t.X(0,[787,217],()=>r(1175));module.exports=s})();