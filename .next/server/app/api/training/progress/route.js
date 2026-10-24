"use strict";(()=>{var e={};e.id=359,e.ids=[359],e.modules={5890:e=>{e.exports=require("better-sqlite3")},399:e=>{e.exports=require("next/dist/compiled/next-server/app-page.runtime.prod.js")},517:e=>{e.exports=require("next/dist/compiled/next-server/app-route.runtime.prod.js")},5900:e=>{e.exports=require("pg")},8893:e=>{e.exports=require("buffer")},4770:e=>{e.exports=require("crypto")},5315:e=>{e.exports=require("path")},6162:e=>{e.exports=require("stream")},1764:e=>{e.exports=require("util")},828:(e,t,s)=>{s.r(t),s.d(t,{originalPathname:()=>N,patchFetch:()=>g,requestAsyncStorage:()=>d,routeModule:()=>p,serverHooks:()=>L,staticGenerationAsyncStorage:()=>l});var r={};s.r(r),s.d(r,{GET:()=>c,POST:()=>E});var a=s(3278),o=s(5002),n=s(4877),i=s(1309),T=s(1717),u=s(2469);async function c(e){try{await (0,T.xN)();let t=e.cookies.get("gds_token")?.value,s=t?(0,u.WX)(t):null,r=s?.id||"guest",a=(0,T.zA)(),o=await a.query("SELECT * FROM lesson_progress WHERE user_id = $1",[r]);return i.NextResponse.json({progress:o})}catch(e){return i.NextResponse.json({error:e.message},{status:500})}}async function E(e){try{await (0,T.xN)();let t=e.cookies.get("gds_token")?.value,s=t?(0,u.WX)(t):null,r=s?.id||"guest",{lessonId:a,moduleId:o,status:n,score:c,completedObjectives:E}=await e.json(),p=(0,T.zA)(),d=`${r}_${a}`,l=new Date().toISOString();return await p.execute(`INSERT INTO lesson_progress (id, user_id, lesson_id, module_id, status, completed_objectives_json, score, attempts, completed_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, 1, $8)
       ON CONFLICT(user_id, lesson_id) DO UPDATE SET
       status = EXCLUDED.status,
       completed_objectives_json = EXCLUDED.completed_objectives_json,
       score = EXCLUDED.score,
       attempts = lesson_progress.attempts + 1,
       completed_at = EXCLUDED.completed_at;`,[d,r,a,o,n,JSON.stringify(E||[]),c||100,"completed"===n?l:null]),i.NextResponse.json({success:!0})}catch(e){return i.NextResponse.json({error:e.message},{status:500})}}let p=new a.AppRouteRouteModule({definition:{kind:o.x.APP_ROUTE,page:"/api/training/progress/route",pathname:"/api/training/progress",filename:"route",bundlePath:"app/api/training/progress/route"},resolvedPagePath:"/home/saif/Documents/Projects/gds_amadeus/src/app/api/training/progress/route.ts",nextConfigOutput:"",userland:r}),{requestAsyncStorage:d,staticGenerationAsyncStorage:l,serverHooks:L}=p,N="/api/training/progress/route";function g(){return(0,n.patchFetch)({serverHooks:L,staticGenerationAsyncStorage:l})}},2469:(e,t,s)=>{s.d(t,{Gv:()=>u,V3:()=>c,WX:()=>E,XY:()=>p,c_:()=>T});var r=s(3981),a=s(7390),o=s.n(a),n=s(1717);let i=process.env.JWT_SECRET||"gds-amadeus-simulator-secret-key-2026";function T(e){return r.ZP.hashSync(e,10)}function u(e,t){return r.ZP.compareSync(e,t)}function c(e){return o().sign(e,i,{expiresIn:"7d"})}function E(e){try{return o().verify(e,i)}catch(e){return null}}async function p(){let e=(0,n.zA)();if(0===(await e.query("SELECT id FROM users LIMIT 1")).length){let t=new Date().toISOString(),s=T("amadeus123");await e.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,["usr-instructor","instructor","instructor@academy.aero",s,"instructor","LON1A0980","INST01",t]),await e.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,["usr-student","student","student@academy.aero",s,"student","LON1A0980","1234AB",t]),console.log("Seeded default instructor and student accounts.")}}},1717:(e,t,s)=>{s.d(t,{zA:()=>T,xN:()=>u});let r=require("fs");var a=s.n(r),o=s(5315),n=s.n(o);let i=null;function T(){return i||(i="postgres"===function(){let e=process.env.DATABASE_TYPE?.toLowerCase();return"postgres"===e||"postgresql"===e||process.env.DATABASE_URL?.startsWith("postgres://")||process.env.DATABASE_URL?.startsWith("postgresql://")?"postgres":"sqlite"}()?function(){let{Pool:e}=s(5900),t=new e({connectionString:process.env.DATABASE_URL||"postgresql://postgres:postgres@localhost:5432/gds_amadeus",max:10,idleTimeoutMillis:3e4});return{type:"postgres",query:async(e,s=[])=>(await t.query(e,s)).rows,execute:async(e,s=[])=>({rowCount:(await t.query(e,s)).rowCount||0}),async close(){await t.end()}}}():function(){let e=s(5890),t=n().join(process.cwd(),"data");a().existsSync(t)||a().mkdirSync(t,{recursive:!0});let r=new e(n().join(t,"gds.db"));return r.pragma("journal_mode = WAL"),{type:"sqlite",async query(e,t=[]){let s=[],a=e.replace(/\$(\d+)/g,(e,t)=>(s.push(parseInt(t,10)-1),"?")),o=s.length>0?s.map(e=>t[e]):t;return r.prepare(a).all(...o)},async execute(e,t=[]){let s=[],a=e.replace(/\$(\d+)/g,(e,t)=>(s.push(parseInt(t,10)-1),"?")),o=s.length>0?s.map(e=>t[e]):t,n=r.prepare(a).run(...o);return{rowCount:n.changes,lastInsertRowid:n.lastInsertRowid}},async close(){r.close()}}}()),i}async function u(){let e=T();await e.execute(`
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
  `)}}};var t=require("../../../../webpack-runtime.js");t.C(e);var s=e=>t(t.s=e),r=t.X(0,[787,217],()=>s(828));module.exports=r})();