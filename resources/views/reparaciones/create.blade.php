@extends('layouts.app')

@section('title', 'Nueva Reparación')

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Nueva Orden de Reparación</h1>
        <p class="text-gray-500">Registra una nueva reparación</p>
    </div>
    <a href="{{ route('reparaciones.index') }}" class="text-gray-600 hover:text-gray-700">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@endsection

@section('content')
<form action="{{ route('reparaciones.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cliente -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-user mr-2 text-primary-600"></i>Cliente
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Cliente <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="cliente_id" id="cliente_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('cliente_id') border-red-500 @enderror">
                            <option value="">Seleccionar cliente...</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre_completo }} - {{ $cliente->telefono }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @error('cliente_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">¿El cliente no está registrado?</span>
                    <a href="{{ route('clientes.create') }}" target="_blank" class="text-sm text-primary-600 hover:text-primary-700">
                        <i class="fas fa-plus mr-1"></i>Registrar nuevo cliente
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Dispositivo -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-mobile-alt mr-2 text-primary-600"></i>Dispositivo
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="dispositivo_tipo" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select name="dispositivo_tipo" id="dispositivo_tipo" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="Celular">Celular</option>
                        <option value="Tablet">Tablet</option>
                        <option value="Smartwatch">Smartwatch</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label for="dispositivo_marca" class="block text-sm font-medium text-gray-700 mb-1">
                        Marca <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="dispositivo_marca" id="dispositivo_marca" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Ej: Samsung, iPhone, etc.">
                </div>
                <div>
                    <label for="dispositivo_modelo" class="block text-sm font-medium text-gray-700 mb-1">
                        Modelo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="dispositivo_modelo" id="dispositivo_modelo" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Ej: Galaxy S21, iPhone 13, etc.">
                </div>
                <div>
                    <label for="dispositivo_color" class="block text-sm font-medium text-gray-700 mb-1">
                        Color
                    </label>
                    <input type="text" name="dispositivo_color" id="dispositivo_color"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Ej: Negro, Azul, etc.">
                </div>
                <div>
                    <label for="dispositivo_imei" class="block text-sm font-medium text-gray-700 mb-1">
                        IMEI
                    </label>
                    <input type="text" name="dispositivo_imei" id="dispositivo_imei"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="15 dígitos">
                </div>
                <div>
                    <label for="dispositivo_serial" class="block text-sm font-medium text-gray-700 mb-1">
                        Número de Serie
                    </label>
                    <input type="text" name="dispositivo_serial" id="dispositivo_serial"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            
            <div class="mt-4">
                <label for="dispositivo_contrasena" class="block text-sm font-medium text-gray-700 mb-1">
                    Contraseña / Patrón
                </label>
                <input type="text" name="dispositivo_contrasena" id="dispositivo_contrasena"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Contraseña o patrón de desbloqueo">
                <p class="text-xs text-gray-500 mt-1">Esta información es confidencial y solo será usada para pruebas.</p>
            </div>
        </div>
    </div>
    
    <!-- Problema y Costos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-exclamation-circle mr-2 text-primary-600"></i>Problema Reportado
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label for="problema_reportado" class="block text-sm font-medium text-gray-700 mb-1">
                        Descripción del problema <span class="text-red-500">*</span>
                    </label>
                    <textarea name="problema_reportado" id="problema_reportado" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Describe el problema que reporta el cliente..."></textarea>
                </div>
                
                <div>
                    <label for="condiciones_previas" class="block text-sm font-medium text-gray-700 mb-1">
                        Condiciones previas del equipo
                    </label>
                    <textarea name="condiciones_previas" id="condiciones_previas" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Rayones, golpes, piezas faltantes, etc."></textarea>
                </div>
                
                <div>
                    <label for="accesorios_incluidos" class="block text-sm font-medium text-gray-700 mb-1">
                        Accesorios incluidos
                    </label>
                    <textarea name="accesorios_incluidos" id="accesorios_incluidos" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Cargador, funda, cable, etc."></textarea>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-dollar-sign mr-2 text-primary-600"></i>Costos y Asignación
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label for="tecnico_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Técnico asignado
                    </label>
                    <select name="tecnico_id" id="tecnico_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Sin asignar</option>
                        @foreach($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="costo_estimado" class="block text-sm font-medium text-gray-700 mb-1">
                            Costo Estimado
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="costo_estimado" id="costo_estimado" step="0.01" min="0"
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label for="adelanto" class="block text-sm font-medium text-gray-700 mb-1">
                            Adelanto
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="adelanto" id="adelanto" step="0.01" min="0"
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="0.00">
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="fecha_estimada_entrega" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha estimada de entrega
                        </label>
                        <input type="date" name="fecha_estimada_entrega" id="fecha_estimada_entrega"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            value="{{ now()->addDays($diasEstimados)->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label for="garantia_dias" class="block text-sm font-medium text-gray-700 mb-1">
                            Garantía (días)
                        </label>
                        <input type="number" name="garantia_dias" id="garantia_dias" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            value="{{ $garantiaDefault }}">
                    </div>
                </div>
                
                <div>
                    <label for="notas_cliente" class="block text-sm font-medium text-gray-700 mb-1">
                        Notas adicionales
                    </label>
                    <textarea name="notas_cliente" id="notas_cliente" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Notas adicionales para el cliente..."></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fotos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-camera mr-2 text-primary-600"></i>Fotos del Equipo (Antes)
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @for($i = 1; $i <= 3; $i++)
            <div>
                <label for="foto_antes_{{ $i }}" class="block text-sm font-medium text-gray-700 mb-1">
                    Foto {{ $i }}
                </label>
                <input type="file" name="foto_antes_{{ $i }}" id="foto_antes_{{ $i }}" accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>
            @endfor
        </div>
        <p class="text-sm text-gray-500 mt-2">Toma fotos del equipo desde diferentes ángulos para documentar su estado.</p>
    </div>
    
    <!-- Botones -->
    <div class="flex justify-end space-x-4">
        <a href="{{ route('reparaciones.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
            <i class="fas fa-save mr-2"></i>Guardar Orden
        </button>
    </div>
</form>
@endsection
