@extends('layouts.app')

@section('title', 'Configuración del Sistema')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Configuración del Sistema</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información General -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-store text-blue-600 mr-2"></i>Información del Negocio
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('configuracion.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Nombre del Negocio</label>
                                <input type="text" name="app_name" value="{{ $config['app_name'] ?? config('app.name') }}" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" value="{{ $config['telefono'] ?? '' }}" class="form-input">
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">Dirección</label>
                                <textarea name="direccion" rows="2" class="form-input">{{ $config['direccion'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ $config['email'] ?? '' }}" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">RFC (Opcional)</label>
                                <input type="text" name="rfc" value="{{ $config['rfc'] ?? '' }}" class="form-input">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Ventas -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-cash-register text-green-600 mr-2"></i>Configuración de Ventas
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('configuracion.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="form-label">Impuesto (%)</label>
                                <input type="number" name="impuesto" value="{{ $config['impuesto'] ?? 16 }}" class="form-input" step="0.01" min="0" max="100">
                            </div>
                            <div>
                                <label class="form-label">Moneda</label>
                                <select name="moneda" class="form-input">
                                    <option value="MXN" {{ ($config['moneda'] ?? 'MXN') == 'MXN' ? 'selected' : '' }}>MXN - Peso Mexicano</option>
                                    <option value="USD" {{ ($config['moneda'] ?? 'MXN') == 'USD' ? 'selected' : '' }}>USD - Dólar Americano</option>
                                    <option value="EUR" {{ ($config['moneda'] ?? 'MXN') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Decimales</label>
                                <select name="decimales" class="form-input">
                                    <option value="2" {{ ($config['decimales'] ?? 2) == 2 ? 'selected' : '' }}>2 decimales</option>
                                    <option value="0" {{ ($config['decimales'] ?? 2) == 0 ? 'selected' : '' }}>0 decimales</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Reparaciones -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-tools text-orange-600 mr-2"></i>Configuración de Reparaciones
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('configuracion.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Garantía por Defecto (días)</label>
                                <input type="number" name="garantia_dias" value="{{ $config['garantia_dias'] ?? 30 }}" class="form-input" min="0">
                            </div>
                            <div>
                                <label class="form-label">Prefijo de Orden</label>
                                <input type="text" name="prefijo_orden" value="{{ $config['prefijo_orden'] ?? 'REP' }}" class="form-input">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Notificaciones -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-bell text-red-600 mr-2"></i>Configuración de Notificaciones
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('configuracion.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="notif_stock_bajo" id="notif_stock_bajo" value="1" {{ ($config['notif_stock_bajo'] ?? true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                                <label for="notif_stock_bajo" class="ml-2 text-sm text-gray-700">Notificar cuando el stock esté bajo</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="notif_reparacion_lista" id="notif_reparacion_lista" value="1" {{ ($config['notif_reparacion_lista'] ?? true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                                <label for="notif_reparacion_lista" class="ml-2 text-sm text-gray-700">Notificar cuando una reparación esté lista</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="notif_nueva_venta" id="notif_nueva_venta" value="1" {{ ($config['notif_nueva_venta'] ?? false) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                                <label for="notif_nueva_venta" class="ml-2 text-sm text-gray-700">Notificar nuevas ventas</label>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Logo -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-image text-purple-600 mr-2"></i>Logo del Negocio
                </h3>
                <form action="{{ route('configuracion.logo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center mb-4">
                        @if(isset($config['logo']) && $config['logo'])
                        <img src="{{ Storage::url($config['logo']) }}" alt="Logo" class="mx-auto max-h-32 mb-4">
                        @else
                        <div class="w-32 h-32 bg-gray-200 rounded-lg mx-auto flex items-center justify-center mb-4">
                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                        </div>
                        @endif
                    </div>
                    <input type="file" name="logo" accept="image/*" class="form-input mb-4">
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-upload mr-2"></i>Subir Logo
                    </button>
                </form>
            </div>

            <!-- Información del Sistema -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información del Sistema
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Versión:</span>
                        <span class="font-medium">1.0.0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Laravel:</span>
                        <span class="font-medium">12.x</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">PHP:</span>
                        <span class="font-medium">8.2+</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base de Datos:</span>
                        <span class="font-medium">MySQL 8.0+</span>
                    </div>
                </div>
            </div>

            <!-- Backup -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-database text-green-600 mr-2"></i>Respaldo de Datos
                </h3>
                <p class="text-sm text-gray-600 mb-4">Descarga una copia de seguridad de tu base de datos.</p>
                <button type="button" onclick="alert('Función de respaldo en desarrollo')" class="btn btn-secondary w-full">
                    <i class="fas fa-download mr-2"></i>Generar Respaldo
                </button>
            </div>
        </div>
    </div>
</div>
@endsection