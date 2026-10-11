@extends('layouts.guest', ['title' => 'Iniciar Sesión'])

@section('content')
<div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/80 p-8">
    <!-- Brand Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white font-black text-2xl shadow-xl shadow-brand-500/30 mb-3">
            PF
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">PymeFlow ERP</h2>
        <p class="text-xs text-slate-500 mt-1">Gestión Empresarial Inteligente para PYMEs</p>
    </div>

    <!-- Session messages -->
    @if(session('info'))
    <div class="mb-4 p-3 rounded-lg bg-sky-50 border border-sky-200 text-sky-800 text-xs">
        {{ session('info') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
        {{ $errors->first() }}
    </div>
    @endif

    <!-- Login Form -->
    <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition outline-none"
                   placeholder="ejemplo@pymeflow.com">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Contraseña</label>
            </div>
            <input type="password" id="password" name="password" required
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition outline-none"
                   placeholder="••••••••">
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center cursor-pointer text-slate-600">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500 mr-2">
                Recordar mi sesión
            </label>
        </div>

        <button type="submit"
                class="w-full py-3 px-4 rounded-xl text-white font-semibold text-sm bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 shadow-lg shadow-brand-500/25 transition duration-200 active:scale-[0.99]">
            Ingresar al Sistema
        </button>
    </form>

    <!-- Quick Demo Accounts for Portfolio demonstration -->
    <div class="mt-8 pt-6 border-t border-slate-100">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider text-center mb-3">Acceso Rápido de Demostración</p>
        <div class="grid grid-cols-2 gap-2.5">
            <button type="button" onclick="fillCredentials('admin@pymeflow.com', 'password')"
                    class="p-2.5 text-left rounded-xl border border-purple-200 bg-purple-50/70 hover:bg-purple-100 transition text-xs group">
                <span class="font-bold text-purple-900 block group-hover:text-purple-950">👑 Administrador</span>
                <span class="text-purple-700 text-[11px] block mt-0.5">Control Total</span>
            </button>
            <button type="button" onclick="fillCredentials('vendedor@pymeflow.com', 'password')"
                    class="p-2.5 text-left rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100 transition text-xs group">
                <span class="font-bold text-emerald-900 block group-hover:text-emerald-950">🛒 Vendedora</span>
                <span class="text-emerald-700 text-[11px] block mt-0.5">Ventas y Clientes</span>
            </button>
        </div>
        <p class="text-[11px] text-slate-600 text-center mt-2.5">Contraseña demo: <code class="font-mono text-slate-700 font-semibold">password</code></p>
    </div>
</div>

<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
