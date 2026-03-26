<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex h-screen w-64 flex-col bg-gray-900 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 bg-gray-800 border-b border-gray-700">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-mobile-alt text-xl"></i>
            </div>
            <span class="text-xl font-bold">SISTEMA DE VENTAS</span>
        </a>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 min-h-0 overflow-y-auto p-4 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span>Dashboard</span>
        </a>
        
        @can('ver ventas')
        <!-- Ventas -->
        <a href="{{ route('ventas.pos') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('ventas.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
            <i class="fas fa-cash-register w-5"></i>
            <span>Punto de Venta</span>
        </a>
        <a href="{{ route('facturacion.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('facturacion.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
            <i class="fas fa-file-invoice-dollar w-5"></i>
            <span>Facturacion</span>
        </a>
        @endcan

        @can('ver gastos')
        <div x-data="{ open: {{ request()->routeIs('gastos.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg {{ request()->routeIs('gastos.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-wallet w-5"></i>
                    <span>Gastos</span>
                </div>
                <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" class="mt-1 ml-4 space-y-1">
                <a href="{{ route('gastos.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('gastos.index') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-chart-pie w-4"></i>
                    <span>Resumen</span>
                </a>
                <a href="{{ route('gastos.create') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('gastos.create') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-plus w-4"></i>
                    <span>Registrar gasto</span>
                </a>
                <a href="{{ route('gastos.lista') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('gastos.lista', 'gastos.show', 'gastos.edit') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-list w-4"></i>
                    <span>Lista de gastos</span>
                </a>
                <a href="{{ route('gastos.categorias.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('gastos.categorias.*') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-tags w-4"></i>
                    <span>Categorias</span>
                </a>
                <a href="{{ route('gastos.proveedores.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('gastos.proveedores.*') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-handshake w-4"></i>
                    <span>Proveedores</span>
                </a>
                <a href="{{ route('gastos.recurrentes') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('gastos.recurrentes') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-arrows-rotate w-4"></i>
                    <span>Recurrentes</span>
                </a>
                <a href="{{ route('gastos.aprobaciones') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('gastos.aprobaciones') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-user-check w-4"></i>
                    <span>Aprobaciones</span>
                </a>
                <a href="{{ route('gastos.reportes') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('gastos.reportes') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-chart-line w-4"></i>
                    <span>Reportes</span>
                </a>
            </div>
        </div>
        @endcan
        
        @can('ver reparaciones')
        <!-- Reparaciones -->
        <div x-data="{ open: {{ request()->routeIs('reparaciones.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg {{ request()->routeIs('reparaciones.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-tools w-5"></i>
                    <span>Reparaciones</span>
                </div>
                <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" class="mt-1 ml-4 space-y-1">
                <a href="{{ route('reparaciones.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reparaciones.index') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-list w-4"></i>
                    <span>Todas las Órdenes</span>
                </a>
                @can('crear reparaciones')
                <a href="{{ route('reparaciones.create') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reparaciones.create') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-plus w-4"></i>
                    <span>Nueva Orden</span>
                </a>
                @endcan
                @role('tecnico')
                <a href="{{ route('reparaciones.panel-tecnico') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reparaciones.panel-tecnico') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-user-cog w-4"></i>
                    <span>Mi Panel</span>
                </a>
                @endrole
            </div>
        </div>
        @endcan
        
        @can('ver clientes')
        <!-- Clientes -->
        <a href="{{ route('clientes.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('clientes.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
            <i class="fas fa-users w-5"></i>
            <span>Clientes</span>
        </a>
        @endcan
        
        @can('ver productos')
        <!-- Inventario -->
        <div x-data="{ open: {{ request()->routeIs('productos.*', 'categorias.*', 'inventario', 'herramientas.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg {{ request()->routeIs('productos.*', 'categorias.*', 'inventario', 'herramientas.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-box w-5"></i>
                    <span>Inventario</span>
                </div>
                <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" class="mt-1 ml-4 space-y-1">
                <a href="{{ route('productos.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('productos.index') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-mobile-alt w-4"></i>
                    <span>Productos</span>
                </a>
                <a href="{{ route('categorias.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('categorias.index') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-tags w-4"></i>
                    <span>Categorías</span>
                </a>
                <a href="{{ route('inventario') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('inventario') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-clipboard-list w-4"></i>
                    <span>Movimientos</span>
                </a>
                <a href="{{ route('herramientas.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('herramientas.*') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-toolbox w-4"></i>
                    <span>Herramientas</span>
                </a>
            </div>
        </div>
        @endcan
        
        @can('ver reportes')
        <!-- Reportes -->
        <div x-data="{ open: {{ request()->routeIs('reportes.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg {{ request()->routeIs('reportes.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Reportes</span>
                </div>
                <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" class="mt-1 ml-4 space-y-1">
                <a href="{{ route('reportes.ventas') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reportes.ventas') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-shopping-cart w-4"></i>
                    <span>Ventas</span>
                </a>
                <a href="{{ route('reportes.reparaciones') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reportes.reparaciones') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-tools w-4"></i>
                    <span>Reparaciones</span>
                </a>
                <a href="{{ route('reportes.inventario') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reportes.inventario') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-box w-4"></i>
                    <span>Inventario</span>
                </a>
                <a href="{{ route('reportes.financiero') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reportes.financiero') ? 'text-primary-400' : 'text-gray-400 hover:text-white' }}">
                    <i class="fas fa-dollar-sign w-4"></i>
                    <span>Financiero</span>
                </a>
            </div>
        </div>
        @endcan

        @if(auth()->user()->can('ver ventas') || auth()->user()->can('ver reparaciones'))
        <a href="{{ route('mora.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('mora.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
            <i class="fas fa-signal w-5"></i>
            <span>Modulo de Mora</span>
        </a>
        @endif
        
        @can('ver usuarios')
        <!-- Usuarios -->
        <a href="{{ route('usuarios.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('usuarios.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
            <i class="fas fa-user-shield w-5"></i>
            <span>Usuarios</span>
        </a>
        @endcan
        
        <!-- Configuración -->
        <a href="{{ route('profile') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('profile') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-colors">
            <i class="fas fa-cog w-5"></i>
            <span>Configuración</span>
        </a>
    </nav>
    
    <!-- Logout -->
    <div class="mt-auto shrink-0 border-t border-gray-700 p-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-red-400 hover:bg-red-900/30 hover:text-red-300 transition-colors">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>

<!-- Alpine.js para el menú desplegable -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
