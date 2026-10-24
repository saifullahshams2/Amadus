"use strict";(()=>{var e={};e.id=2,e.ids=[2],e.modules={5890:e=>{e.exports=require("better-sqlite3")},399:e=>{e.exports=require("next/dist/compiled/next-server/app-page.runtime.prod.js")},517:e=>{e.exports=require("next/dist/compiled/next-server/app-route.runtime.prod.js")},5900:e=>{e.exports=require("pg")},8893:e=>{e.exports=require("buffer")},4770:e=>{e.exports=require("crypto")},5315:e=>{e.exports=require("path")},6162:e=>{e.exports=require("stream")},1764:e=>{e.exports=require("util")},748:(e,t,r)=>{r.r(t),r.d(t,{originalPathname:()=>L,patchFetch:()=>N,requestAsyncStorage:()=>d,routeModule:()=>E,serverHooks:()=>l,staticGenerationAsyncStorage:()=>p});var s={};r.r(s),r.d(s,{POST:()=>c});var a=r(3278),n=r(5002),o=r(4877),i=r(1309),T=r(1717),u=r(2469);async function c(e){try{await (0,T.xN)();let{username:t,email:r,password:s,role:a="student",officeId:n="LON1A0980",agentId:o="1234AB"}=await e.json();if(!t||!r||!s)return i.NextResponse.json({error:"Username, email, and password are required"},{status:400});let c=(0,T.zA)();if((await c.query("SELECT id FROM users WHERE username = $1 OR email = $2 LIMIT 1",[t.trim(),r.trim()])).length>0)return i.NextResponse.json({error:"Username or email already exists"},{status:409});let E=`usr-${Date.now()}`,d=(0,u.c_)(s),p=new Date().toISOString();await c.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,[E,t.trim(),r.trim(),d,a,n.toUpperCase(),o.toUpperCase(),p]);let l={id:E,username:t.trim(),email:r.trim(),role:a,officeId:n.toUpperCase(),agentId:o.toUpperCase()},L=(0,u.V3)(l),N=i.NextResponse.json({success:!0,user:l,token:L});return N.cookies.set("gds_token",L,{httpOnly:!0,secure:!0,sameSite:"lax",path:"/",maxAge:604800}),N}catch(e){return console.error("Register error:",e),i.NextResponse.json({error:e.message||"Internal server error"},{status:500})}}let E=new a.AppRouteRouteModule({definition:{kind:n.x.APP_ROUTE,page:"/api/auth/register/route",pathname:"/api/auth/register",filename:"route",bundlePath:"app/api/auth/register/route"},resolvedPagePath:"/home/saif/Documents/Projects/gds_amadeus/src/app/api/auth/register/route.ts",nextConfigOutput:"",userland:s}),{requestAsyncStorage:d,staticGenerationAsyncStorage:p,serverHooks:l}=E,L="/api/auth/register/route";function N(){return(0,o.patchFetch)({serverHooks:l,staticGenerationAsyncStorage:p})}},2469:(e,t,r)=>{r.d(t,{Gv:()=>u,V3:()=>c,WX:()=>E,XY:()=>d,c_:()=>T});var s=r(3981),a=r(7390),n=r.n(a),o=r(1717);let i=process.env.JWT_SECRET||"gds-amadeus-simulator-secret-key-2026";function T(e){return s.ZP.hashSync(e,10)}function u(e,t){return s.ZP.compareSync(e,t)}function c(e){return n().sign(e,i,{expiresIn:"7d"})}function E(e){try{return n().verify(e,i)}catch(e){return null}}async function d(){let e=(0,o.zA)();if(0===(await e.query("SELECT id FROM users LIMIT 1")).length){let t=new Date().toISOString(),r=T("amadeus123");await e.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,["usr-instructor","instructor","instructor@academy.aero",r,"instructor","LON1A0980","INST01",t]),await e.execute(`INSERT INTO users (id, username, email, password_hash, role, office_id, agent_id, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)`,["usr-student","student","student@academy.aero",r,"student","LON1A0980","1234AB",t]),console.log("Seeded default instructor and student accounts.")}}},1717:(e,t,r)=>{r.d(t,{zA:()=>T,xN:()=>u});let s=require("fs");var a=r.n(s),n=r(5315),o=r.n(n);let i=null;function T(){return i||(i="postgres"===function(){let e=process.env.DATABASE_TYPE?.toLowerCase();return"postgres"===e||"postgresql"===e||process.env.DATABASE_URL?.startsWith("postgres://")||process.env.DATABASE_URL?.startsWith("postgresql://")?"postgres":"sqlite"}()?function(){let{Pool:e}=r(5900),t=new e({connectionString:process.env.DATABASE_URL||"postgresql://postgres:postgres@localhost:5432/gds_amadeus",max:10,idleTimeoutMillis:3e4});return{type:"postgres",query:async(e,r=[])=>(await t.query(e,r)).rows,execute:async(e,r=[])=>({rowCount:(await t.query(e,r)).rowCount||0}),async close(){await t.end()}}}():function(){let e=r(5890),t=o().join(process.cwd(),"data");a().existsSync(t)||a().mkdirSync(t,{recursive:!0});let s=new e(o().join(t,"gds.db"));return s.pragma("journal_mode = WAL"),{type:"sqlite",async query(e,t=[]){let r=[],a=e.replace(/\$(\d+)/g,(e,t)=>(r.push(parseInt(t,10)-1),"?")),n=r.length>0?r.map(e=>t[e]):t;return s.prepare(a).all(...n)},async execute(e,t=[]){let r=[],a=e.replace(/\$(\d+)/g,(e,t)=>(r.push(parseInt(t,10)-1),"?")),n=r.length>0?r.map(e=>t[e]):t,o=s.prepare(a).run(...n);return{rowCount:o.changes,lastInsertRowid:o.lastInsertRowid}},async close(){s.close()}}}()),i}async function u(){let e=T();await e.execute(`
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
  `)}}};var t=require("../../../../webpack-runtime.js");t.C(e);var r=e=>t(t.s=e),s=t.X(0,[787,217],()=>r(748));module.exports=s})();