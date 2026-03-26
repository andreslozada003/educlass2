@extends('layouts.app')

@section('title', 'Editar Orden ' . $reparacion->orden)

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Editar Orden</h1>
        <p class="text-gray-500">{{ $reparacion->orden }}</p>
    </div>
    <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-gray-600 hover:text-gray-700">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('reparaciones.update', $reparacion) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Cliente -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                <input type="text" value="{{ $reparacion->cliente->nombre_completo }}" disabled
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500">
            </div>
            
            <!-- Técnico -->
            <div>
                <label for="tecnico_id" class="block text-sm font-medium text-gray-700 mb-1">Técnico Asignado</label>
                <select name="tecnico_id" id="tecnico_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Sin asignar</option>
                    @foreach($tecnicos as $tecnico)
                    <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $reparacion->tecnico_id) == $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Dispositivo -->
            <div>
                <label for="dispositivo_tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <input type="text" name="dispositivo_tipo" id="dispositivo_tipo" value="{{ old('dispositivo_tipo', $reparacion->dispositivo_tipo) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <div>
                <label for="dispositivo_marca" class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                <input type="text" name="dispositivo_marca" id="dispositivo_marca" value="{{ old('dispositivo_marca', $reparacion->dispositivo_marca) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <div>
                <label for="dispositivo_modelo" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                <input type="text" name="dispositivo_modelo" id="dispositivo_modelo" value="{{ old('dispositivo_modelo', $reparacion->dispositivo_modelo) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <div>
                <label for="dispositivo_color" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                <input type="text" name="dispositivo_color" id="dispositivo_color" value="{{ old('dispositivo_color', $reparacion->dispositivo_color) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <div>
                <label for="dispositivo_imei" class="block text-sm font-medium text-gray-700 mb-1">IMEI</label>
                <input type="text" name="dispositivo_imei" id="dispositivo_imei" value="{{ old('dispositivo_imei', $reparacion->dispositivo_imei) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <div>
                <label for="dispositivo_serial" class="block text-sm font-medium text-gray-700 mb-1">Serial</label>
                <input type="text" name="dispositivo_serial" id="dispositivo_serial" value="{{ old('dispositivo_serial', $reparacion->dispositivo_serial) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Problema -->
            <div class="lg:col-span-2">
                <label for="problema_reportado" class="block text-sm font-medium text-gray-700 mb-1">Problema Reportado</label>
                <textarea name="problema_reportado" id="problema_reportado" rows="3" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('problema_reportado', $reparacion->problema_reportado) }}</textarea>
            </div>
            
            <!-- Diagnóstico -->
            <div class="lg:col-span-2">
                <label for="diagnostico" class="block text-sm font-medium text-gray-700 mb-1">Diagnóstico</label>
                <textarea name="diagnostico" id="diagnostico" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('diagnostico', $reparacion->diagnostico) }}</textarea>
            </div>
            
            <!-- Solución -->
            <div class="lg:col-span-2">
                <label for="solucion" class="block text-sm font-medium text-gray-700 mb-1">Solución</label>
                <textarea name="solucion" id="solucion" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('solucion', $reparacion->solucion) }}</textarea>
            </div>
            
            <!-- Costos -->
            <div>
                <label for="costo_estimado" class="block text-sm font-medium text-gray-700 mb-1">Costo Estimado</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" name="costo_estimado" id="costo_estimado" step="0.01" min="0"
                        value="{{ old('costo_estimado', $reparacion->costo_estimado) }}"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            
            <div>
                <label for="costo_final" class="block text-sm font-medium text-gray-700 mb-1">Costo Final</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" name="costo_final" id="costo_final" step="0.01" min="0"
                        value="{{ old('costo_final', $reparacion->costo_final) }}"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            
            <div>
                <label for="adelanto" class="block text-sm font-medium text-gray-700 mb-1">Adelanto</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" name="adelanto" id="adelanto" step="0.01" min="0"
                        value="{{ old('adelanto', $reparacion->adelanto) }}"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            
            <div>
                <label for="garantia_dias" class="block text-sm font-medium text-gray-700 mb-1">Garantía (días)</label>
                <input type="number" name="garantia_dias" id="garantia_dias" min="0"
                    value="{{ old('garantia_dias', $reparacion->garantia_dias) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Notas -->
            <div class="lg:col-span-2">
                <label for="notas_tecnico" class="block text-sm font-medium text-gray-700 mb-1">Notas del Técnico</label>
                <textarea name="notas_tecnico" id="notas_tecnico" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notas_tecnico', $reparacion->notas_tecnico) }}</textarea>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('reparaciones.show', $reparacion) }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
