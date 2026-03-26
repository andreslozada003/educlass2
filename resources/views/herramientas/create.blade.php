@extends('layouts.app')

@section('title', 'Nueva Herramienta')

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Nueva Herramienta</h1>
        <p class="text-gray-500">Registrar herramientas del taller</p>
    </div>
    <a href="{{ route('herramientas.index') }}" class="text-gray-600 hover:text-gray-700">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('herramientas.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Se genera automaticamente si lo dejas vacio">
            </div>

            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="marca" class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                <input type="text" name="marca" id="marca" value="{{ old('marca') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="modelo" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                <input type="text" name="modelo" id="modelo" value="{{ old('modelo') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-1">Ubicacion</label>
                <input type="text" name="ubicacion" id="ubicacion" value="{{ old('ubicacion') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Ej: Mesa tecnica, bodega, cajon 2">
            </div>

            <div>
                <label for="fecha_compra" class="block text-sm font-medium text-gray-700 mb-1">Fecha de compra</label>
                <input type="date" name="fecha_compra" id="fecha_compra" value="{{ old('fecha_compra') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="costo_compra" class="block text-sm font-medium text-gray-700 mb-1">Costo de compra</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" name="costo_compra" id="costo_compra" value="{{ old('costo_compra', 0) }}" min="0" step="0.01"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label for="cantidad" class="block text-sm font-medium text-gray-700 mb-1">
                    Cantidad total <span class="text-red-500">*</span>
                </label>
                <input type="number" name="cantidad" id="cantidad" value="{{ old('cantidad', 1) }}" min="1" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="cantidad_danada" class="block text-sm font-medium text-gray-700 mb-1">Cantidad dañada</label>
                <input type="number" name="cantidad_danada" id="cantidad_danada" value="{{ old('cantidad_danada', 0) }}" min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="flex items-center">
                <label class="flex items-center mt-6">
                    <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-gray-700">Herramienta activa</span>
                </label>
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripcion</label>
                <textarea name="descripcion" id="descripcion" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('descripcion') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                <textarea name="observaciones" id="observaciones" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Ej: una unidad con punta dañada, mantenimiento pendiente, etc.">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('herramientas.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Guardar Herramienta
            </button>
        </div>
    </form>
</div>
@endsection
