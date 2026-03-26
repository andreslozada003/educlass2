@extends('layouts.app')

@section('title', 'Herramientas')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Herramientas</h1>
        <p class="text-gray-500">Control de herramientas, daños y fechas de compra</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('herramientas.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Nueva Herramienta
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Registros</p>
                <p class="text-2xl font-bold text-primary-600">{{ $totalHerramientas }}</p>
            </div>
            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-toolbox text-primary-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Unidades</p>
                <p class="text-2xl font-bold text-gray-800">{{ $unidadesTotales }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-layer-group text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Disponibles</p>
                <p class="text-2xl font-bold text-green-600">{{ $unidadesDisponibles }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Dañadas</p>
                <p class="text-2xl font-bold {{ $unidadesDanadas > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $unidadesDanadas }}</p>
            </div>
            <div class="w-12 h-12 {{ $unidadesDanadas > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle {{ $unidadesDanadas > 0 ? 'text-red-600' : 'text-green-600' }} text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <form action="{{ route('herramientas.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Buscar por nombre, codigo, marca o ubicacion...">
        </div>
        <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
            <input type="checkbox" name="danadas" value="1" {{ request('danadas') ? 'checked' : '' }} class="mr-2">
            <span class="text-sm">Solo dañadas</span>
        </label>
        <div class="flex gap-2">
            <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="inactivas" value="1" {{ request('inactivas') ? 'checked' : '' }} class="mr-2">
                <span class="text-sm">Inactivas</span>
            </label>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Herramienta</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicacion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Compra</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Disponibles</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Dañadas</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($herramientas as $herramienta)
                <tr class="hover:bg-gray-50 {{ $herramienta->cantidad_danada > 0 ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $herramienta->nombre }}</p>
                            <p class="text-xs text-gray-500">{{ trim(($herramienta->marca ?? '') . ' ' . ($herramienta->modelo ?? '')) ?: 'Sin marca/modelo' }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $herramienta->codigo }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $herramienta->ubicacion ?: 'Sin ubicación' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $herramienta->fecha_compra?->format('d/m/Y') ?: 'Sin fecha' }}</td>
                    <td class="px-4 py-3 text-center text-sm font-medium text-gray-800">{{ $herramienta->cantidad }}</td>
                    <td class="px-4 py-3 text-center text-sm font-medium text-green-600">{{ $herramienta->cantidad_disponible }}</td>
                    <td class="px-4 py-3 text-center text-sm font-medium {{ $herramienta->cantidad_danada > 0 ? 'text-red-600' : 'text-gray-700' }}">{{ $herramienta->cantidad_danada }}</td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $estado = $herramienta->estado_general;
                            $estadoClass = match($estado) {
                                'Dañada' => 'bg-red-100 text-red-700',
                                'Con daños' => 'bg-yellow-100 text-yellow-700',
                                'Inactiva' => 'bg-gray-100 text-gray-700',
                                default => 'bg-green-100 text-green-700',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $estadoClass }}">
                            {{ $estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('herramientas.show', $herramienta) }}" class="text-blue-600 hover:text-blue-700" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('herramientas.edit', $herramienta) }}" class="text-amber-600 hover:text-amber-700" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-toolbox text-4xl mb-3 text-gray-300"></i>
                        <p>No hay herramientas registradas</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-gray-200">
        {{ $herramientas->links() }}
    </div>
</div>
@endsection
