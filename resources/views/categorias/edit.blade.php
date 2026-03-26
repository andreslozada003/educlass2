@extends('layouts.app')

@section('title', 'Editar Categoría')

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Editar Categoría</h1>
        <p class="text-gray-500">{{ $categoria->nombre }}</p>
    </div>
    <a href="{{ route('categorias.index') }}" class="text-gray-600 hover:text-gray-700">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('categorias.update', $categoria) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $categoria->nombre) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Categoría Padre -->
            <div>
                <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría Padre</label>
                <select name="parent_id" id="parent_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Ninguna (Categoría Principal)</option>
                    @foreach($categoriasPadre as $cat)
                    <option value="{{ $cat->id }}" {{ old('parent_id', $categoria->parent_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Icono -->
            <div>
                <label for="icono" class="block text-sm font-medium text-gray-700 mb-1">Icono (FontAwesome)</label>
                <input type="text" name="icono" id="icono" value="{{ old('icono', $categoria->icono) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Ej: mobile-alt, box, tools...">
            </div>
            
            <!-- Color -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                <input type="color" name="color" id="color" value="{{ old('color', $categoria->color ?: '#3b82f6') }}"
                    class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Orden -->
            <div>
                <label for="orden" class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                <input type="number" name="orden" id="orden" value="{{ old('orden', $categoria->orden) }}" min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Activo -->
            <div>
                <label class="flex items-center mt-6">
                    <input type="checkbox" name="activo" value="1" {{ old('activo', $categoria->activo) ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-gray-700">Categoría activa</span>
                </label>
            </div>
            
            <!-- Descripción -->
            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('descripcion', $categoria->descripcion) }}</textarea>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('categorias.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
