@extends('layouts.app')

@section('title', 'Editar Producto')

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Editar Producto</h1>
        <p class="text-gray-500">{{ $producto->nombre }}</p>
    </div>
    <a href="{{ route('productos.show', $producto) }}" class="text-gray-600 hover:text-gray-700">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                <input type="text" id="codigo" value="{{ $producto->codigo }}" disabled
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500">
            </div>

            <div>
                <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-1">Codigo de barras</label>
                <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('codigo_barras') border-red-500 @enderror"
                    placeholder="Escanea aqui para reemplazar o registrar el codigo"
                    autocomplete="off">
                @error('codigo_barras')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Categoria <span class="text-red-500">*</span>
                </label>
                <select name="categoria_id" id="categoria_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="tipo" id="tipo"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="accesorio" {{ old('tipo', $producto->tipo) == 'accesorio' ? 'selected' : '' }}>Accesorio</option>
                    <option value="celular" {{ old('tipo', $producto->tipo) == 'celular' ? 'selected' : '' }}>Celular</option>
                    <option value="repuesto" {{ old('tipo', $producto->tipo) == 'repuesto' ? 'selected' : '' }}>Repuesto</option>
                    <option value="servicio" {{ old('tipo', $producto->tipo) == 'servicio' ? 'selected' : '' }}>Servicio</option>
                </select>
            </div>

            <div>
                <label for="marca" class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                <input type="text" name="marca" id="marca" value="{{ old('marca', $producto->marca) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="modelo" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                <input type="text" name="modelo" id="modelo" value="{{ old('modelo', $producto->modelo) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="precio_compra" class="block text-sm font-medium text-gray-700 mb-1">
                    Precio de compra <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" name="precio_compra" id="precio_compra" value="{{ old('precio_compra', $producto->precio_compra) }}" required step="0.01" min="0"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label for="precio_venta" class="block text-sm font-medium text-gray-700 mb-1">
                    Precio de venta <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" name="precio_venta" id="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}" required step="0.01" min="0"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-1">Stock minimo</label>
                <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}" min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="garantia" class="block text-sm font-medium text-gray-700 mb-1">Garantia</label>
                <input type="text" name="garantia" id="garantia" value="{{ old('garantia', $producto->garantia) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label class="flex items-center mt-6">
                    <input type="checkbox" name="es_servicio" value="1" {{ old('es_servicio', $producto->es_servicio) ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-gray-700">Es un servicio</span>
                </label>
            </div>

            <div>
                <label class="flex items-center mt-6">
                    <input type="checkbox" name="activo" value="1" {{ old('activo', $producto->activo) ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-gray-700">Producto activo</span>
                </label>
            </div>

            <div class="md:col-span-2 pt-4 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos fiscales para CFDI</h3>
            </div>

            <div>
                <label for="clave_prod_serv_sat" class="block text-sm font-medium text-gray-700 mb-1">ClaveProdServ SAT</label>
                <input type="text" name="clave_prod_serv_sat" id="clave_prod_serv_sat" value="{{ old('clave_prod_serv_sat', $producto->clave_prod_serv_sat ?: $facturacionDefaults['clave_prod_serv_sat']) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="clave_unidad_sat" class="block text-sm font-medium text-gray-700 mb-1">ClaveUnidad SAT</label>
                <input type="text" name="clave_unidad_sat" id="clave_unidad_sat" value="{{ old('clave_unidad_sat', $producto->clave_unidad_sat ?: $facturacionDefaults['clave_unidad_sat']) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="unidad_sat" class="block text-sm font-medium text-gray-700 mb-1">Unidad SAT</label>
                <input type="text" name="unidad_sat" id="unidad_sat" value="{{ old('unidad_sat', $producto->unidad_sat ?: $facturacionDefaults['unidad_sat']) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="objeto_impuesto" class="block text-sm font-medium text-gray-700 mb-1">Objeto de impuesto</label>
                <select name="objeto_impuesto" id="objeto_impuesto"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @foreach($objetosImpuesto as $clave => $descripcion)
                    <option value="{{ $clave }}" {{ old('objeto_impuesto', $producto->objeto_impuesto ?: $facturacionDefaults['objeto_impuesto']) === $clave ? 'selected' : '' }}>{{ $clave }} - {{ $descripcion }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripcion</label>
                <textarea name="descripcion" id="descripcion" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('descripcion', $producto->descripcion) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label for="imagen_principal" class="block text-sm font-medium text-gray-700 mb-1">Imagen del producto</label>
                @if($producto->imagen_principal)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $producto->imagen_principal) }}" alt="" class="h-32 rounded-lg">
                </div>
                @endif
                <input type="file" name="imagen_principal" id="imagen_principal" accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>
        </div>

        <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('productos.show', $producto) }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const barcodeInput = document.getElementById('codigo_barras');

    if (!barcodeInput) {
        return;
    }

    barcodeInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });
});
</script>
@endpush
