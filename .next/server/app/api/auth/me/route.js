"use strict";(()=>{var e={};e.id=788,e.ids=[788],e.modules={5890:e=>{e.exports=require("better-sqlite3")},399:e=>{e.exports=require("next/dist/compiled/next-server/app-page.runtime.prod.js")},517:e=>{e.exports=require("next/dist/compiled/next-server/app-route.runtime.prod.js")},5900:e=>{e.exports=require("pg")},8893:e=>{e.exports=require("buffer")},4770:e=>{e.exports=require("crypto")},5315:e=>{e.exports=require("path")},6162:e=>{e.exports=require("stream")},1764:e=>{e.exports=require("util")},1411:(e,t,r)=>{r.r(t),r.d(t,{originalPathname:()=>N,patchFetch:()=>A,requestAsyncStorage:()=>p,routeModule:()=>d,serverHooks:()=>L,staticGenerationAsyncStorage:()=>l});var s={};r.r(s),r.d(s,{GET:()=>c,POST:()=>E});var a=r(3278),n=r(5002),o=r(4877),T=r(1309),u=r(2469),i=r(1717);async function c(e){try{await (0,i.xN)(),await (0,u.XY)();let t=e.cookies.get("gds_token")?.value||e.headers.get("authorization")?.replace("Bearer ","");if(!t)return T.NextResponse.json({user:null});let r=(0,u.WX)(t);return T.NextResponse.json({user:r})}catch(e){return T.NextResponse.json({user:null})}}async function E(e){let t=T.NextResponse.json({success:!0});return t.cookies.set("gds_token","",{maxAge:0,path:"/"}),t}let d=new a.AppRouteRouteModule({definition:{kind:n.x.APP_ROUTE,page:"/api/auth/me/route",pathname:"/api/auth/me",filename:"route",bundlePath:"app/api/auth/me/route"},resolvedPagePath:"/home/saif/Documents/Projects/gds_amadeus/src/app/api/auth/me/route.ts",nextConfigOutput:"",userland:s}),{requestAsyncStorage:p,staticGenerationAsyncStorage:l,serverHooks:L}=d,N="/api/auth/me/route";function A(){return(0,o.patchFetch)({serverHooks:L,staticGenerationAsyncStorage:l})}},2469:(e,t,r)=>{r.d(t,{Gv:()=>i,V3:()=>c,WX:()=>E,XY:()=>d,c_:()=>u});var s=r(3981),a=r(7390),n=r.n(a),o=r(1717);let T=process.env.JWT_SECRET||"gds-amadeus-simulator-secret-key-2026";function u(e){return s.ZP.hashSync(e,10)}function i(e,t){return s.ZP.compareSync(e,t)}function c(e){return n().sign(e,T,{expiresIn:"7d"})}function E(e){try{return n().verify(e,T)}catch(e){return null}}async function d(){let e=(0,o.zA)();if(0===(await e.query("SELECT id FROM users LIMIT 1")).length){let t=new Date().toISOString(),r=u("amadeus123");await e.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,["usr-instructor","instructor","instructor@academy.aero",r,"instructor","LON1A0980","INST01",t]),await e.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,["usr-student","student","student@academy.aero",r,"student","LON1A0980","1234AB",t]),console.log("Seeded default instructor and student accounts.")}}},1717:(e,t,r)=>{r.d(t,{zA:()=>u,xN:()=>i});let s=require("fs");var a=r.n(s),n=r(5315),o=r.n(n);let T=null;function u(){return T||(T="postgres"===function(){let e=process.env.DATABASE_TYPE?.toLowerCase();return"postgres"===e||"postgresql"===e||process.env.DATABASE_URL?.startsWith("postgres://")||process.env.DATABASE_URL?.startsWith("postgresql://")?"postgres":"sqlite"}()?function(){let{Pool:e}=r(5900),t=new e({connectionString:process.env.DATABASE_URL||"postgresql://postgres:postgres@localhost:5432/gds_amadeus",max:10,idleTimeoutMillis:3e4});return{type:"postgres",query:async(e,r=[])=>(await t.query(e,r)).rows,execute:async(e,r=[])=>({rowCount:(await t.query(e,r)).rowCount||0}),async close(){await t.end()}}}():function(){let e=r(5890),t=o().join(process.cwd(),"data");a().existsSync(t)||a().mkdirSync(t,{recursive:!0});let s=new e(o().join(t,"gds.db"));return s.pragma("journal_mode = WAL"),{type:"sqlite",async query(e,t=[]){let r=[],a=e.replace(/\$(\d+)/g,(e,t)=>(r.push(parseInt(t,10)-1),"?")),n=r.length>0?r.map(e=>t[e]):t;return s.prepare(a).all(...n)},async execute(e,t=[]){let r=[],a=e.replace(/\$(\d+)/g,(e,t)=>(r.push(parseInt(t,10)-1),"?")),n=r.length>0?r.map(e=>t[e]):t,o=s.prepare(a).run(...n);return{rowCount:o.changes,lastInsertRowid:o.lastInsertRowid}},async close(){s.close()}}}()),T}async function i(){let e=u();await e.execute(`
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
  `)}}};var t=require("../../../../webpack-runtime.js");t.C(e);var r=e=>t(t.s=e),s=t.X(0,[787,217],()=>r(1411));module.exports=s})();