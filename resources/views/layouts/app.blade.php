<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PymeFlow ERP' }} - Sistema de Gestión Empresarial</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-slate-800">

    <div class="flex h-screen overflow-hidden bg-slate-100">
        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 -translate-x-full flex flex-col justify-between shadow-xl">
            <div>
                <!-- Brand Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-brand-500/30 group-hover:scale-105 transition">
                            PF
                        </div>
                        <div>
                            <span class="text-xl font-extrabold tracking-tight text-white block">PymeFlow</span>
                            <span class="text-xs uppercase tracking-wider font-semibold text-brand-500 block -mt-1">ERP Cloud</span>
                        </div>
                    </a>
                    <button id="closeSidebarBtn" class="lg:hidden text-slate-400 hover:text-white p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="px-4 py-6 space-y-1.5 overflow-y-auto max-h-[calc(100vh-180px)]">
                    <div class="px-3 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Menú Principal</div>

                    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('sales.create') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('sales.create') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-emerald-400 hover:bg-slate-800 hover:text-emerald-300' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span>Punto de Venta (POS)</span>
                    </a>

                    <a href="{{ route('sales.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('sales.index', 'sales.show') ? 'bg-brand-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        Historial de Ventas
                    </a>

                    <div class="pt-4 px-3 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Catálogo e Inventario</div>

                    <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('products.*') ? 'bg-brand-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Productos
                    </a>

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('categories.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('categories.*') ? 'bg-brand-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Categorías
                    </a>
                    @endif

                    <a href="{{ route('inventory.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('inventory.*') ? 'bg-brand-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Movimientos de Stock
                    </a>

                    <div class="pt-4 px-3 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Contactos y Relaciones</div>

                    <a href="{{ route('customers.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('customers.*') ? 'bg-brand-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Clientes
                    </a>

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('suppliers.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('suppliers.*') ? 'bg-brand-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Proveedores
                    </a>

                    <div class="pt-4 px-3 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Analítica</div>

                    <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('reports.*') ? 'bg-brand-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Reportes & Exportación
                    </a>
                    @endif
                </nav>
            </div>

            <!-- User Status in Sidebar Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/60">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-sm border border-slate-700">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ auth()->user()->isAdmin() ? 'bg-purple-900/60 text-purple-300 border border-purple-800' : 'bg-emerald-900/60 text-emerald-300 border border-emerald-800' }}">
                                {{ auth()->user()->isAdmin() ? 'Administrador' : 'Vendedor' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white border-b border-slate-200 shadow-sm z-30">
                <div class="flex items-center justify-between px-6 py-3.5">
                    <div class="flex items-center space-x-4">
                        <button id="openSidebarBtn" class="lg:hidden text-slate-500 hover:text-slate-800 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1 class="text-lg font-bold text-slate-800 hidden sm:block">
                            {{ $header ?? 'PymeFlow ERP' }}
                        </h1>
                    </div>

                    <!-- Right Actions -->
                    <div class="flex items-center space-x-4">
                        <div class="text-right hidden sm:block">
                            <span class="text-xs text-slate-500 block">Sesión activa como:</span>
                            <span class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                        </div>

                        <!-- Logout Form -->
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-sm font-medium text-slate-700 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 transition border border-slate-200">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Alerts Container -->
            <div class="px-6 pt-4">
                @if (session('success'))
                <div class="mb-4 flex items-center p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                @endif

                @if (session('error'))
                <div class="mb-4 flex items-center p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                @endif

                @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 shadow-sm">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span class="text-sm font-bold">Por favor corrija los siguientes errores:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 ml-5">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <!-- Dynamic Page Content -->
            <main class="flex-1 overflow-y-auto px-6 py-4">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Script for mobile toggle -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('openSidebarBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        if (openBtn) {
            openBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
            });
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
            });
        }
    </script>
</body>
</html>
