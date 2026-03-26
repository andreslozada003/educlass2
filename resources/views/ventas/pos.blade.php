@extends('layouts.app')

@section('title', 'Punto de Venta')

@section('page-header')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">Punto de Venta</h1>
    <a href="{{ route('ventas.index') }}" class="text-primary-600 hover:text-primary-700">
        <i class="fas fa-arrow-left mr-1"></i> Ver Historial
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-200px)]">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
        <div class="p-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input
                        type="text"
                        id="buscar-producto"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Buscar producto por nombre, codigo, barras, marca o modelo..."
                    >
                </div>
                <select id="filtro-categoria" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Todas las categorias</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3 flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-barcode text-gray-400"></i>
                    </div>
                    <input
                        type="text"
                        id="scan-codigo-barras"
                        class="w-full pl-10 pr-4 py-2 border border-emerald-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-emerald-50/40"
                        placeholder="Escanea aqui con el lector de barras"
                        autocomplete="off"
                    >
                </div>
                <button
                    type="button"
                    onclick="enfocarScanner()"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                >
                    <i class="fas fa-bullseye mr-2"></i>Activar lector
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <div id="productos-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($productos as $producto)
                <div
                    class="producto-card bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:shadow-md hover:border-primary-300 transition-all"
                    data-id="{{ $producto->id }}"
                    data-nombre="{{ $producto->nombre }}"
                    data-codigo="{{ $producto->codigo }}"
                    data-codigo-barras="{{ $producto->codigo_barras }}"
                    data-marca="{{ $producto->marca }}"
                    data-modelo="{{ $producto->modelo }}"
                    data-precio="{{ $producto->precio_venta }}"
                    data-stock="{{ $producto->stock }}"
                    data-categoria="{{ $producto->categoria_id }}"
                    data-servicio="{{ $producto->es_servicio ? '1' : '0' }}"
                    onclick="agregarAlCarrito({{ $producto->id }})"
                >
                    <div class="aspect-square bg-gray-100 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                        @if($producto->imagen_principal)
                            <img src="{{ asset('storage/' . $producto->imagen_principal) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-box text-3xl text-gray-300"></i>
                        @endif
                    </div>
                    <h4 class="font-medium text-gray-800 text-sm line-clamp-2">{{ $producto->nombre }}</h4>
                    <p class="text-primary-600 font-bold mt-1">${{ number_format((float) $producto->precio_venta, 0, ',', '.') }}</p>
                    @if($producto->codigo_barras)
                    <p class="text-[11px] text-gray-500 mt-1">Barras: {{ $producto->codigo_barras }}</p>
                    @endif
                    @if(!$producto->es_servicio)
                    <p class="text-xs {{ $producto->stock <= $producto->stock_minimo ? 'text-red-500' : 'text-gray-500' }}">
                        Stock: {{ $producto->stock }}
                    </p>
                    @else
                    <p class="text-xs text-green-600">Servicio</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-shopping-cart mr-2"></i>Carrito
            </h3>
        </div>

        <div id="carrito-items" class="flex-1 overflow-y-auto p-4">
            <div id="carrito-vacio" class="text-center text-gray-400 py-8">
                <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                <p>El carrito esta vacio</p>
                <p class="text-sm">Haz clic o escanea un producto para agregarlo</p>
            </div>
            <div id="carrito-lista" class="space-y-3 hidden"></div>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span id="subtotal" class="font-medium">$0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">IVA (16%):</span>
                    <span id="impuestos" class="font-medium">$0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Descuento:</span>
                    <div class="flex items-center">
                        <span class="text-gray-400 mr-1">$</span>
                        <input
                            type="number"
                            id="descuento"
                            class="w-20 text-right text-sm border border-gray-300 rounded px-2 py-1"
                            value="0"
                            min="0"
                            step="0.01"
                            onchange="calcularTotales()"
                        >
                    </div>
                </div>
                <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                    <span>Total:</span>
                    <span id="total" class="text-primary-600">$0</span>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                <div class="relative">
                    <select id="cliente_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Cliente General</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Metodo de Pago</label>
                <select id="metodo_pago" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" onchange="togglePagoEfectivo()">
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="deposito">Deposito</option>
                    <option value="credito">Credito</option>
                    <option value="mixto">Mixto</option>
                </select>
            </div>

            <div id="pago-efectivo" class="mb-4">
                <label id="pagado_con_label" class="block text-sm font-medium text-gray-700 mb-1">Paga con</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input
                        type="number"
                        id="pagado_con"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="0.00"
                        min="0"
                        step="0.01"
                        oninput="calcularCambio()"
                    >
                </div>
                <div id="cambio-container" class="mt-2 text-sm hidden">
                    <span class="text-gray-600">Cambio:</span>
                    <span id="cambio" class="font-bold text-green-600 ml-2">$0</span>
                </div>
                <p id="pagado_con_help" class="mt-2 text-xs text-gray-500">Ingresa el valor recibido del cliente para calcular el cambio.</p>
            </div>

            <div id="credito-fields" class="mb-4 hidden rounded-xl border border-amber-200 bg-amber-50/70 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-semibold text-amber-900">
                            <i class="fas fa-credit-card mr-2"></i>Venta a credito
                        </h4>
                        <p class="mt-1 text-xs leading-5 text-amber-800">
                            Registra el abono inicial y la fecha desde la cual quieres que esta venta entre al modulo de mora.
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700 ring-1 ring-amber-200">
                        Seguimiento
                    </span>
                </div>

                <div class="mt-4 grid gap-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="fecha_inicio_mora" class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio mora</label>
                            <input
                                type="date"
                                id="fecha_inicio_mora"
                                class="w-full px-3 py-2 border border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
                                value="{{ now()->format('Y-m-d') }}"
                            >
                        </div>
                        <div>
                            <label for="fecha_compromiso_pago" class="block text-sm font-medium text-gray-700 mb-1">Compromiso de pago</label>
                            <input
                                type="date"
                                id="fecha_compromiso_pago"
                                class="w-full px-3 py-2 border border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
                                value="{{ now()->addDays(7)->format('Y-m-d') }}"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="numero_cuotas" class="block text-sm font-medium text-gray-700 mb-1">Numero de cuotas</label>
                            <input
                                type="number"
                                id="numero_cuotas"
                                class="w-full px-3 py-2 border border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
                                value="1"
                                min="1"
                                max="48"
                            >
                        </div>
                        <div>
                            <label for="plazo_acordado_dias" class="block text-sm font-medium text-gray-700 mb-1">Plazo acordado en dias</label>
                            <input
                                type="number"
                                id="plazo_acordado_dias"
                                class="w-full px-3 py-2 border border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
                                value="30"
                                min="1"
                                max="365"
                            >
                        </div>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-white/80 px-3 py-3 text-xs text-amber-900">
                        La venta aparecera en <span class="font-semibold">/mora</span> si el abono inicial es menor al total y existe una fecha de inicio de mora.
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <button
                    onclick="procesarVenta()"
                    id="btn-cobrar"
                    class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled
                >
                    <i class="fas fa-check mr-2"></i>
                    Cobrar
                </button>
                <button
                    onclick="limpiarCarrito()"
                    class="w-full bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-300 transition-colors"
                >
                    <i class="fas fa-trash mr-2"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<div id="modal-exito" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-4xl text-green-600"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Venta Exitosa</h3>
        <p class="text-gray-600 mb-2">Folio: <span id="folio-venta" class="font-bold"></span></p>
        <p class="text-3xl font-bold text-primary-600 mb-4" id="total-venta"></p>
        <p class="text-sm font-medium text-amber-700 mb-2 hidden" id="estado-venta"></p>
        <p class="text-base font-semibold text-rose-600 mb-4 hidden" id="saldo-pendiente-venta"></p>
        <p class="text-gray-600 mb-6" id="cambio-venta"></p>
        <div class="space-y-2">
            <button onclick="imprimirTicket()" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                <i class="fas fa-print mr-2"></i>Imprimir Ticket
            </button>
            <button onclick="nuevaVenta()" class="w-full bg-gray-200 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                Nueva Venta
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let carrito = [];
let ventaId = null;
const scannerSelector = '#scan-codigo-barras';

function formatPosCurrency(amount) {
    const value = Number(amount || 0);
    return '$' + new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(Math.round(value));
}

function obtenerResumenVenta() {
    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const impuestos = subtotal * 0.16;
    const descuento = parseFloat($('#descuento').val()) || 0;
    const total = Math.max(0, subtotal + impuestos - descuento);

    return {
        subtotal,
        impuestos,
        descuento,
        total
    };
}

function actualizarTextoBotonCobrar() {
    const metodo = $('#metodo_pago').val();
    const btn = $('#btn-cobrar');

    if (metodo === 'credito') {
        btn.html('<i class="fas fa-file-invoice-dollar mr-2"></i>Registrar Credito');
        return;
    }

    btn.html('<i class="fas fa-check mr-2"></i>Cobrar');
}

$('#buscar-producto').on('input', filtrarProductos);
$('#filtro-categoria').on('change', filtrarProductos);

$(scannerSelector).on('keydown', function(event) {
    if (event.key !== 'Enter') {
        return;
    }

    event.preventDefault();
    procesarCodigoEscaneado($(this).val());
});

function filtrarProductos() {
    const search = ($('#buscar-producto').val() || '').toLowerCase().trim();
    const categoria = $('#filtro-categoria').val();

    $('.producto-card').each(function() {
        const textoBusqueda = [
            $(this).data('nombre'),
            $(this).data('codigo'),
            $(this).data('codigo-barras'),
            $(this).data('marca'),
            $(this).data('modelo')
        ].join(' ').toLowerCase();

        const coincideBusqueda = !search || textoBusqueda.includes(search);
        const coincideCategoria = !categoria || $(this).data('categoria') == categoria;

        $(this).toggle(coincideBusqueda && coincideCategoria);
    });
}

function enfocarScanner() {
    const input = document.querySelector(scannerSelector);
    if (!input) {
        return;
    }

    input.focus();
    input.select();
}

function procesarCodigoEscaneado(codigoEscaneado) {
    const codigo = (codigoEscaneado || '').trim();

    if (!codigo) {
        return;
    }

    let productoEncontrado = null;

    $('.producto-card').each(function() {
        const codigoInterno = String($(this).data('codigo') || '').trim();
        const codigoBarras = String($(this).data('codigo-barras') || '').trim();

        if (codigo === codigoBarras || codigo === codigoInterno) {
            productoEncontrado = $(this);
            return false;
        }
    });

    if (!productoEncontrado) {
        alert('No se encontro ningun producto con ese codigo.');
        $(scannerSelector).val('');
        enfocarScanner();
        return;
    }

    agregarAlCarrito(productoEncontrado.data('id'));
    $(scannerSelector).val('');
    enfocarScanner();
}

function agregarAlCarrito(productoId) {
    const card = $(`.producto-card[data-id="${productoId}"]`);
    const producto = {
        id: productoId,
        nombre: card.data('nombre'),
        precio: parseFloat(card.data('precio')),
        stock: parseInt(card.data('stock')),
        es_servicio: card.data('servicio') === '1'
    };

    const existente = carrito.find(item => item.id === productoId);
    if (existente) {
        if (!producto.es_servicio && existente.cantidad >= producto.stock) {
            alert('No hay suficiente stock');
            return;
        }
        existente.cantidad++;
    } else {
        carrito.push({
            ...producto,
            cantidad: 1
        });
    }

    actualizarCarrito();
}

function actualizarCarrito() {
    const lista = $('#carrito-lista');
    const vacio = $('#carrito-vacio');

    if (carrito.length === 0) {
        lista.addClass('hidden');
        vacio.removeClass('hidden');
        $('#btn-cobrar').prop('disabled', true);
    } else {
        vacio.addClass('hidden');
        lista.removeClass('hidden');
        $('#btn-cobrar').prop('disabled', false);

        lista.html(carrito.map((item, index) => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-800 text-sm">${item.nombre}</h4>
                    <p class="text-primary-600 font-bold">${formatPosCurrency(item.precio)}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="cambiarCantidad(${index}, -1)" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-minus text-xs"></i>
                    </button>
                    <span class="w-8 text-center font-medium">${item.cantidad}</span>
                    <button onclick="cambiarCantidad(${index}, 1)" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                    <button onclick="eliminarDelCarrito(${index})" class="w-8 h-8 rounded-full bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center ml-2">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
            </div>
        `).join(''));
    }

    actualizarTextoBotonCobrar();
    calcularTotales();
}

function cambiarCantidad(index, delta) {
    const item = carrito[index];
    const nuevaCantidad = item.cantidad + delta;

    if (nuevaCantidad <= 0) {
        eliminarDelCarrito(index);
        return;
    }

    if (!item.es_servicio && nuevaCantidad > item.stock) {
        alert('No hay suficiente stock');
        return;
    }

    item.cantidad = nuevaCantidad;
    actualizarCarrito();
}

function eliminarDelCarrito(index) {
    carrito.splice(index, 1);
    actualizarCarrito();
}

function calcularTotales() {
    const resumen = obtenerResumenVenta();

    $('#subtotal').text(formatPosCurrency(resumen.subtotal));
    $('#impuestos').text(formatPosCurrency(resumen.impuestos));
    $('#total').text(formatPosCurrency(resumen.total));

    calcularCambio();
}

function togglePagoEfectivo() {
    const metodo = $('#metodo_pago').val();
    const pagoEfectivo = $('#pago-efectivo');
    const creditoFields = $('#credito-fields');
    const label = $('#pagado_con_label');
    const help = $('#pagado_con_help');

    if (metodo === 'efectivo') {
        pagoEfectivo.removeClass('hidden');
        creditoFields.addClass('hidden');
        label.text('Paga con');
        help.text('Ingresa el valor recibido del cliente para calcular el cambio.');
        $('#pagado_con').attr('placeholder', '0.00');
    } else if (metodo === 'credito') {
        pagoEfectivo.removeClass('hidden');
        creditoFields.removeClass('hidden');
        label.text('Abono inicial');
        help.text('Si dejas un valor menor al total, la venta quedara pendiente y aparecera en el modulo de mora.');
        $('#pagado_con').attr('placeholder', '0.00');
    } else {
        pagoEfectivo.addClass('hidden');
        creditoFields.addClass('hidden');
        $('#cambio-container').addClass('hidden');
    }

    actualizarTextoBotonCobrar();
}

function calcularCambio() {
    const metodo = $('#metodo_pago').val();
    const { total } = obtenerResumenVenta();
    const pagado = parseFloat($('#pagado_con').val()) || 0;
    const cambio = pagado - total;

    if (metodo === 'efectivo' && cambio >= 0 && pagado > 0) {
        $('#cambio').text(formatPosCurrency(cambio));
        $('#cambio-container').removeClass('hidden');
    } else {
        $('#cambio-container').addClass('hidden');
    }
}

function resetCamposCredito() {
    $('#fecha_inicio_mora').val('{{ now()->format('Y-m-d') }}');
    $('#fecha_compromiso_pago').val('{{ now()->addDays(7)->format('Y-m-d') }}');
    $('#numero_cuotas').val('1');
    $('#plazo_acordado_dias').val('30');
}

function limpiarCarrito() {
    if (carrito.length === 0) return;

    if (!confirm('¿Estas seguro de cancelar la venta?')) return;

    carrito = [];
    $('#descuento').val(0);
    $('#pagado_con').val('');
    resetCamposCredito();
    $('#cambio-container').addClass('hidden');
    actualizarCarrito();
    enfocarScanner();
}

function procesarVenta() {
    if (carrito.length === 0) {
        alert('El carrito esta vacio');
        return;
    }

    const { total } = obtenerResumenVenta();
    const metodoPago = $('#metodo_pago').val();
    const pagadoCon = parseFloat($('#pagado_con').val()) || 0;
    const clienteId = $('#cliente_id').val() || null;
    const esCredito = metodoPago === 'credito';

    if (metodoPago === 'efectivo' && pagadoCon < total) {
        alert('El monto pagado es menor al total');
        return;
    }

    if (esCredito && !clienteId) {
        alert('Debes seleccionar un cliente para registrar una venta a credito.');
        return;
    }

    if (esCredito && !$('#fecha_inicio_mora').val()) {
        alert('Debes registrar la fecha de inicio de mora.');
        return;
    }

    if (esCredito && pagadoCon >= total) {
        alert('Si el abono cubre el total, usa otro metodo de pago o reduce el abono para dejar saldo pendiente.');
        return;
    }

    const btn = $('#btn-cobrar');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...');

    const data = {
        cliente_id: clienteId,
        productos: carrito.map(item => ({
            id: item.id,
            cantidad: item.cantidad,
            precio: item.precio
        })),
        descuento: parseFloat($('#descuento').val()) || 0,
        metodo_pago: metodoPago,
        pagado_con: pagadoCon,
        fecha_inicio_mora: esCredito ? $('#fecha_inicio_mora').val() : null,
        fecha_compromiso_pago: esCredito ? ($('#fecha_compromiso_pago').val() || null) : null,
        numero_cuotas: esCredito ? ($('#numero_cuotas').val() || null) : null,
        plazo_acordado_dias: esCredito ? ($('#plazo_acordado_dias').val() || null) : null
    };

    $.ajax({
        url: '{{ route("ventas.store") }}',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(response) {
            const totalVenta = Number(response.total || 0);
            const cambioVenta = Number(response.cambio || 0);
            const saldoPendiente = Number(response.saldo_pendiente || 0);

            ventaId = response.venta_id;
            $('#folio-venta').text(response.folio);
            $('#total-venta').text(formatPosCurrency(totalVenta));
            $('#estado-venta').addClass('hidden').text('');
            $('#saldo-pendiente-venta').addClass('hidden').text('');

            if (cambioVenta > 0) {
                $('#cambio-venta').text('Cambio: ' + formatPosCurrency(cambioVenta));
            } else {
                $('#cambio-venta').text('');
            }

            if (response.estado === 'credito' && saldoPendiente > 0) {
                $('#estado-venta').removeClass('hidden').text('Venta registrada a credito');
                $('#saldo-pendiente-venta').removeClass('hidden').text('Saldo pendiente: ' + formatPosCurrency(saldoPendiente));
                $('#cambio-venta').text('La venta ya quedo lista para seguimiento en el modulo de mora.');
            }

            actualizarTextoBotonCobrar();
            btn.prop('disabled', false);
            $('#modal-exito').removeClass('hidden');
        },
        error: function(xhr) {
            alert('Error al procesar la venta: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            btn.prop('disabled', false);
            actualizarTextoBotonCobrar();
            enfocarScanner();
        }
    });
}

function imprimirTicket() {
    if (ventaId) {
        window.open(`{{ url('ventas') }}/${ventaId}/ticket`, '_blank');
    }
}

function nuevaVenta() {
    carrito = [];
    ventaId = null;
    $('#descuento').val(0);
    $('#pagado_con').val('');
    resetCamposCredito();
    $('#metodo_pago').val('efectivo');
    $('#estado-venta').addClass('hidden').text('');
    $('#saldo-pendiente-venta').addClass('hidden').text('');
    $('#cambio-container').addClass('hidden');
    $('#modal-exito').addClass('hidden');
    $('#btn-cobrar').prop('disabled', false);
    actualizarCarrito();
    togglePagoEfectivo();
    actualizarTextoBotonCobrar();
    enfocarScanner();
}

$(document).ready(function() {
    $.get('{{ route("clientes.buscar") }}', function(data) {
        const select = $('#cliente_id');
        data.forEach(cliente => {
            select.append(`<option value="${cliente.id}">${cliente.nombre} ${cliente.apellido || ''}</option>`);
        });
    });

    resetCamposCredito();
    togglePagoEfectivo();
    actualizarTextoBotonCobrar();
    enfocarScanner();
});
</script>
@endpush
