@extends('layouts.guest', ['title' => 'Iniciar Sesión'])

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
    <!-- Brand Header -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-md bg-brand-900 border border-brand-800 text-white font-bold text-lg mb-3">
            PF
        </div>
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">PymeFlow ERP</h2>
        <p class="text-xs text-slate-500 mt-0.5">Sistema de Gestión Empresarial</p>
    </div>

    <!-- Session messages -->
    @if(session('info'))
    <div class="mb-4 p-3 rounded-md bg-sky-50 border border-sky-200 text-sky-800 text-xs">
        {{ session('info') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 p-3 rounded-md bg-rose-50 border border-rose-200 text-rose-800 text-xs">
        {{ $errors->first() }}
    </div>
    @endif

    <!-- Login Form -->
    <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 text-sm outline-none transition-colors"
                   placeholder="usuario@pymeflow.com">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-semibold text-slate-700">Contraseña</label>
            </div>
            <input type="password" id="password" name="password" required
                   class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 text-sm outline-none transition-colors"
                   placeholder="••••••••">
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center cursor-pointer text-slate-600">
                <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded text-brand-700 border-slate-300 focus:ring-brand-500 mr-2">
                Recordar mi sesión
            </label>
        </div>

        <button type="submit"
                class="w-full py-2.5 px-4 rounded-md text-white font-medium text-sm bg-brand-900 hover:bg-brand-800 transition-colors shadow-sm">
            Ingresar al Sistema
        </button>
    </form>

    <!-- Quick Demo Accounts for Portfolio demonstration -->
    <div class="mt-6 pt-5 border-t border-slate-100">
        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-center mb-2.5">Acceso Rápido de Demostración</p>
        <div class="grid grid-cols-2 gap-2">
            <button type="button" onclick="fillCredentials('admin@pymeflow.com', 'password')"
                    class="p-2.5 text-left rounded-md border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 transition-colors text-xs group">
                <div class="flex items-center space-x-1.5 font-bold text-slate-800">
                    <svg class="w-3.5 h-3.5 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Administrador</span>
                </div>
                <span class="text-slate-500 text-[11px] block mt-0.5">Control Total</span>
            </button>
            <button type="button" onclick="fillCredentials('vendedor@pymeflow.com', 'password')"
                    class="p-2.5 text-left rounded-md border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 transition-colors text-xs group">
                <div class="flex items-center space-x-1.5 font-bold text-slate-800">
                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span>Vendedor</span>
                </div>
                <span class="text-slate-500 text-[11px] block mt-0.5">Ventas y Clientes</span>
            </button>
        </div>
        <p class="text-[11px] text-slate-500 text-center mt-2">Contraseña demo: <code class="font-mono text-slate-700 font-semibold">password</code></p>
    </div>
</div>

<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
