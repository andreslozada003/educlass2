@extends('layouts.app')

@section('title', $producto->nombre)

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div class="flex items-center">
        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mr-4 overflow-hidden">
            @if($producto->imagen_principal)
                <img src="{{ asset('storage/' . $producto->imagen_principal) }}" alt="" class="w-full h-full object-cover">
            @else
                <i class="fas fa-box text-3xl text-gray-400"></i>
            @endif
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $producto->nombre }}</h1>
            <p class="text-gray-500">{{ $producto->codigo }} | {{ $producto->categoria?->nombre }}</p>
        </div>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('productos.edit', $producto) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
            <i class="fas fa-edit mr-2"></i>Editar
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informacion general</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Marca</p>
                    <p class="font-medium">{{ $producto->marca ?: 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Modelo</p>
                    <p class="font-medium">{{ $producto->modelo ?: 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tipo</p>
                    <p class="font-medium capitalize">{{ $producto->tipo }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Proveedor</p>
                    <p class="font-medium">{{ $producto->proveedor ?: 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">IMEI</p>
                    <p class="font-medium">{{ $producto->imei ?: 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Codigo de barras</p>
                    <p class="font-medium">{{ $producto->codigo_barras ?: 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Garantia</p>
                    <p class="font-medium">{{ $producto->garantia ?: 'N/A' }}</p>
                </div>
            </div>

            @if($producto->descripcion)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-1">Descripcion</p>
                <p class="text-gray-700">{{ $producto->descripcion }}</p>
            </div>
            @endif

            @if($producto->especificaciones_tecnicas)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-1">Especificaciones tecnicas</p>
                <p class="text-gray-700">{{ $producto->especificaciones_tecnicas }}</p>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos fiscales CFDI</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">ClaveProdServ SAT</p>
                    <p class="font-medium">{{ $producto->clave_prod_serv_sat }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ClaveUnidad SAT</p>
                    <p class="font-medium">{{ $producto->clave_unidad_sat }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Unidad SAT</p>
                    <p class="font-medium">{{ $producto->unidad_sat }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Objeto de impuesto</p>
                    <p class="font-medium">{{ $producto->objeto_impuesto }} - {{ $objetosImpuesto[$producto->objeto_impuesto] ?? 'Catalogo no identificado' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ultimos movimientos</h3>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($producto->movimientos->take(10) as $movimiento)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-1 text-xs rounded-full bg-{{ $movimiento->tipo_color }}-100 text-{{ $movimiento->tipo_color }}-800">
                                    {{ $movimiento->tipo_nombre }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-sm text-right">{{ $movimiento->cantidad }}</td>
                            <td class="px-3 py-2 text-sm text-right">{{ $movimiento->stock_nuevo }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $movimiento->usuario?->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">No hay movimientos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Precios</h3>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Precio de compra:</span>
                    <span class="font-medium">{{ money($producto->precio_compra) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Precio de venta:</span>
                    <span class="font-medium text-lg text-primary-600">{{ money($producto->precio_venta) }}</span>
                </div>
                <div class="flex justify-between pt-3 border-t border-gray-200">
                    <span class="text-gray-500">Ganancia:</span>
                    <span class="font-medium text-green-600">{{ money($producto->ganancia) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Margen:</span>
                    <span class="font-medium text-green-600">{{ number_format($producto->margen_ganancia, 1) }}%</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventario</h3>

            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Stock actual:</span>
                    <span class="text-2xl font-bold {{ $producto->stock_bajo ? 'text-red-600' : 'text-green-600' }}">
                        {{ $producto->stock }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Stock minimo:</span>
                    <span class="font-medium">{{ $producto->stock_minimo }}</span>
                </div>
                <div class="flex justify-between pt-3 border-t border-gray-200">
                    <span class="text-gray-500">Valor en inventario:</span>
                    <span class="font-medium">{{ money($producto->valor_inventario) }}</span>
                </div>
            </div>

            @if($producto->stock_bajo)
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Stock bajo. Se recomienda reabastecer.
                </p>
            </div>
            @endif

            <div class="mt-4 space-y-2">
                <button onclick="document.getElementById('modal-entrada').classList.remove('hidden')" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Entrada de inventario
                </button>
                <button onclick="document.getElementById('modal-ajuste').classList.remove('hidden')" class="w-full bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                    <i class="fas fa-adjust mr-2"></i>Ajustar stock
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Estadisticas</h3>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Vendidos (30 dias):</span>
                    <span class="font-medium">{{ $ventasMes }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Vendidos (total):</span>
                    <span class="font-medium">{{ $ventasTotal }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-entrada" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Entrada de inventario</h3>
        <form action="{{ route('productos.entrada', $producto) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                    <input type="number" name="cantidad" required min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Costo unitario</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" name="costo_unitario" step="0.01" min="0"
                            value="{{ $producto->precio_compra }}"
                            class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <textarea name="motivo" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="document.getElementById('modal-entrada').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Guardar entrada</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-ajuste" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ajustar stock</h3>
        <form action="{{ route('productos.ajustar-stock', $producto) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo stock</label>
                    <input type="number" name="nuevo_stock" required min="0" value="{{ $producto->stock }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <textarea name="motivo" rows="2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="document.getElementById('modal-ajuste').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">Ajustar</button>
            </div>
        </form>
    </div>
</div>
@endsection
