@extends('layouts.app')

@section('title', 'Detalle de Herramienta')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $herramienta->nombre }}</h1>
        <p class="text-gray-500">{{ $herramienta->codigo }}</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('herramientas.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
        <a href="{{ route('herramientas.edit', $herramienta) }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors">
            <i class="fas fa-edit mr-2"></i>Editar
        </a>
        <form action="{{ route('herramientas.destroy', $herramienta) }}" method="POST" onsubmit="return confirm('¿Eliminar esta herramienta?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-2"></i>Eliminar
            </button>
        </form>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Cantidad total</p>
        <p class="text-2xl font-bold text-gray-800">{{ $herramienta->cantidad }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Disponibles</p>
        <p class="text-2xl font-bold text-green-600">{{ $herramienta->cantidad_disponible }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Dañadas</p>
        <p class="text-2xl font-bold {{ $herramienta->cantidad_danada > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $herramienta->cantidad_danada }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Fecha de compra</p>
        <p class="text-xl font-bold text-gray-800">{{ $herramienta->fecha_compra?->format('d/m/Y') ?: 'Sin fecha' }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informacion general</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Marca</p>
                <p class="font-medium text-gray-800">{{ $herramienta->marca ?: 'No registrada' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Modelo</p>
                <p class="font-medium text-gray-800">{{ $herramienta->modelo ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Ubicacion</p>
                <p class="font-medium text-gray-800">{{ $herramienta->ubicacion ?: 'No registrada' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Costo unitario</p>
                <p class="font-medium text-gray-800">{{ money($herramienta->costo_compra) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Valor total</p>
                <p class="font-medium text-green-600">{{ money($herramienta->valor_inventario) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-medium text-gray-800">{{ $herramienta->estado_general }}</p>
            </div>
        </div>

        <div class="mt-6">
            <p class="text-sm text-gray-500 mb-1">Descripcion</p>
            <div class="rounded-lg bg-gray-50 p-4 text-gray-700">
                {{ $herramienta->descripcion ?: 'Sin descripcion' }}
            </div>
        </div>

        <div class="mt-4">
            <p class="text-sm text-gray-500 mb-1">Observaciones</p>
            <div class="rounded-lg bg-gray-50 p-4 text-gray-700">
                {{ $herramienta->observaciones ?: 'Sin observaciones' }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Resumen rapido</h2>
        <div class="space-y-4">
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Herramientas disponibles</p>
                <p class="text-2xl font-bold text-green-600">{{ $herramienta->cantidad_disponible }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Herramientas dañadas</p>
                <p class="text-2xl font-bold {{ $herramienta->cantidad_danada > 0 ? 'text-red-600' : 'text-gray-700' }}">{{ $herramienta->cantidad_danada }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Registrada</p>
                <p class="font-medium text-gray-800">{{ $herramienta->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
