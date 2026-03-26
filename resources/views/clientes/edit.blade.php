@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Editar Cliente</h1>
        <p class="text-gray-500">{{ $cliente->nombre_completo }}</p>
    </div>
    <a href="{{ route('clientes.show', $cliente) }}" class="text-gray-600 hover:text-gray-700">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('clientes.update', $cliente) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $cliente->nombre) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('nombre') border-red-500 @enderror">
                @error('nombre')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="apellido" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $cliente->apellido) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="md:col-span-2">
                <label for="razon_social" class="block text-sm font-medium text-gray-700 mb-1">Razon social / nombre fiscal</label>
                <input type="text" name="razon_social" id="razon_social" value="{{ old('razon_social', $cliente->razon_social) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $cliente->telefono) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electronico</label>
                <input type="email" name="email" id="email" value="{{ old('email', $cliente->email) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="rfc" class="block text-sm font-medium text-gray-700 mb-1">RFC</label>
                <input type="text" name="rfc" id="rfc" value="{{ old('rfc', $cliente->rfc) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="codigo_postal" class="block text-sm font-medium text-gray-700 mb-1">Codigo postal fiscal</label>
                <input type="text" name="codigo_postal" id="codigo_postal" value="{{ old('codigo_postal', $cliente->codigo_postal) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="regimen_fiscal" class="block text-sm font-medium text-gray-700 mb-1">Regimen fiscal</label>
                <select name="regimen_fiscal" id="regimen_fiscal"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Seleccionar regimen...</option>
                    @foreach($regimenesFiscales as $clave => $descripcion)
                    <option value="{{ $clave }}" {{ old('regimen_fiscal', $cliente->regimen_fiscal) === $clave ? 'selected' : '' }}>{{ $clave }} - {{ $descripcion }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="uso_cfdi" class="block text-sm font-medium text-gray-700 mb-1">Uso CFDI</label>
                <select name="uso_cfdi" id="uso_cfdi"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Seleccionar uso CFDI...</option>
                    @foreach($usosCfdi as $clave => $descripcion)
                    <option value="{{ $clave }}" {{ old('uso_cfdi', $cliente->uso_cfdi) === $clave ? 'selected' : '' }}>{{ $clave }} - {{ $descripcion }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                    value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento?->format('Y-m-d')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="md:col-span-2">
                <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                <textarea name="direccion" id="direccion" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('direccion', $cliente->direccion) }}</textarea>
            </div>

            <div>
                <label for="ciudad" class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                <input type="text" name="ciudad" id="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <input type="text" name="estado" id="estado" value="{{ old('estado', $cliente->estado) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label class="flex items-center mt-6">
                    <input type="checkbox" name="activo" value="1" {{ old('activo', $cliente->activo) ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-gray-700">Cliente activo</span>
                </label>
            </div>

            <div class="md:col-span-2">
                <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea name="notas" id="notas" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notas', $cliente->notas) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('clientes.show', $cliente) }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
