@extends('layouts.app')

@section('title', 'Sign In - Amadeus GDS Simulator')

@section('content')
<div class="flex-1 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-[#0b101c] border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-2xl">
        <!-- Brand -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center mx-auto shadow-lg shadow-blue-500/20">
                <svg class="w-6 h-6 text-white transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </div>
            <h1 class="text-xl font-bold text-white tracking-wide">Amadeus GDS Connect</h1>
            <p class="text-xs text-slate-400">Sign in to your training work terminal or instructor hub</p>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-lg bg-rose-950/40 border border-rose-800 text-rose-300 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Form -->
        <form action="/login" method="POST" class="space-y-4 text-xs font-mono">
            @csrf
            <div>
                <label class="block text-slate-400 mb-1">USERNAME</label>
                <input type="text" name="username" id="usernameInput" required autofocus
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">PASSWORD</label>
                <input type="password" name="password" id="passwordInput" required
                       class="w-full bg-[#131c2e] border border-slate-700 text-white rounded-lg p-2.5 text-xs outline-none focus:border-blue-500">
            </div>

            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-xs tracking-wider transition-colors shadow-lg shadow-blue-600/20">
                SIGN IN
            </button>
        </form>

        <!-- 1-Click Demo Logins -->
        <div class="pt-4 border-t border-slate-800 space-y-2">
            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block text-center">1-CLICK DEMO ACCOUNTS</span>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="fillCredentials('student', 'amadeus123')"
                        class="p-2 bg-[#131c2e] hover:bg-slate-800 border border-slate-700 rounded-lg text-left transition-colors">
                    <span class="block text-white font-bold text-xs">Student</span>
                    <span class="block text-slate-400 text-[10px] font-mono">student / amadeus123</span>
                </button>
                <button type="button" onclick="fillCredentials('instructor', 'amadeus123')"
                        class="p-2 bg-[#131c2e] hover:bg-slate-800 border border-slate-700 rounded-lg text-left transition-colors">
                    <span class="block text-amber-400 font-bold text-xs">Instructor</span>
                    <span class="block text-slate-400 text-[10px] font-mono">instructor / amadeus123</span>
                </button>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500">
            Don't have an account? <a href="/register" class="text-blue-400 hover:underline">Register</a> or <a href="/" class="text-slate-400 hover:underline">continue as Guest</a>.
        </div>
    </div>
</div>

<script>
function fillCredentials(u, p) {
    document.getElementById('usernameInput').value = u;
    document.getElementById('passwordInput').value = p;
    document.forms[0].submit();
}
</script>
@endsection
