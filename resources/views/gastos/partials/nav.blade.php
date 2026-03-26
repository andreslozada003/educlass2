<div class="mb-6 rounded-lg border border-gray-200 bg-white p-2 shadow-sm">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('gastos.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('gastos.index') ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Resumen
        </a>
        <a href="{{ route('gastos.create') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('gastos.create') ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Registrar gasto
        </a>
        <a href="{{ route('gastos.lista') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('gastos.lista', 'gastos.show', 'gastos.edit') ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Lista de gastos
        </a>
        <a href="{{ route('gastos.categorias.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('gastos.categorias.*') ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Categorias
        </a>
        <a href="{{ route('gastos.proveedores.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('gastos.proveedores.*') ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Proveedores
        </a>
        <a href="{{ route('gastos.recurrentes') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('gastos.recurrentes') ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Recurrentes
        </a>
        <a href="{{ route('gastos.aprobaciones') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('gastos.aprobaciones') ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Aprobaciones
        </a>
        <a href="{{ route('gastos.reportes') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('gastos.reportes') ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Reportes
        </a>
    </div>
</div>
