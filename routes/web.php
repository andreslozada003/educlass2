<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReparacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\FacturacionElectronicaController;
use App\Http\Controllers\GastoCategoriaController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\GastoProveedorController;
use App\Http\Controllers\HerramientaController;
use App\Http\Controllers\MoraController;
use App\Http\Controllers\AzLibroController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas
Route::get('/', function () {
    return redirect()->route('login');
});

// Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Recuperación de contraseña
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
    
    // Perfil
    Route::get('/perfil', [AuthController::class, 'profile'])->name('profile');
    Route::put('/perfil', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/perfil/password', [AuthController::class, 'changePassword'])->name('profile.password');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart');
    Route::get('/dashboard/notificaciones', [DashboardController::class, 'notificaciones'])->name('dashboard.notificaciones');
    Route::post('/dashboard/notificaciones/{id}/leida', [DashboardController::class, 'marcarNotificacionLeida'])->name('dashboard.notificaciones.leida');

    // Clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('clientes/{cliente}/activar', [ClienteController::class, 'activar'])->name('clientes.activar');
    Route::get('clientes/{cliente}/historial', [ClienteController::class, 'historial'])->name('clientes.historial');
    Route::get('api/clientes/buscar', [ClienteController::class, 'buscar'])->name('clientes.buscar');

    // Productos
    Route::resource('productos', ProductoController::class);
    Route::post('productos/{producto}/ajustar-stock', [ProductoController::class, 'ajustarStock'])->name('productos.ajustar-stock');
    Route::post('productos/{producto}/entrada', [ProductoController::class, 'entradaInventario'])->name('productos.entrada');
    Route::get('api/productos/buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');
    Route::get('inventario', [ProductoController::class, 'inventario'])->name('inventario');
    Route::resource('herramientas', HerramientaController::class);

    // Categorías
    Route::resource('categorias', CategoriaController::class);

    // Ventas
    Route::resource('ventas', VentaController::class)->only(['index', 'show']);
    Route::get('ventas/create/pos', [VentaController::class, 'pos'])->name('ventas.pos');
    Route::post('ventas/store', [VentaController::class, 'store'])->name('ventas.store');
    Route::post('ventas/{venta}/cancelar', [VentaController::class, 'cancelar'])->name('ventas.cancelar');
    Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    Route::get('ventas/{venta}/comprobante', [VentaController::class, 'comprobante'])->name('ventas.comprobante');
    Route::post('ventas/{venta}/facturacion/preparar', [FacturacionElectronicaController::class, 'prepararDesdeVenta'])->name('ventas.facturacion.preparar');
    Route::get('ventas-estadisticas', [VentaController::class, 'estadisticas'])->name('ventas.estadisticas');

    // Facturacion electronica
    Route::get('facturacion', [FacturacionElectronicaController::class, 'index'])->name('facturacion.index');
    Route::put('facturacion/configuracion', [FacturacionElectronicaController::class, 'updateConfiguracion'])->name('facturacion.configuracion.update');
    Route::get('facturacion/{factura}', [FacturacionElectronicaController::class, 'show'])->name('facturacion.show');

    // Reparaciones
    Route::resource('reparaciones', ReparacionController::class)
        ->parameters(['reparaciones' => 'reparacion']);
    Route::post('reparaciones/{reparacion}/cambiar-estado', [ReparacionController::class, 'cambiarEstado'])->name('reparaciones.cambiar-estado');
    Route::post('reparaciones/{reparacion}/fotos-despues', [ReparacionController::class, 'subirFotosDespues'])->name('reparaciones.fotos-despues');
    Route::get('reparaciones/{reparacion}/notificar', [ReparacionController::class, 'notificar'])->name('reparaciones.notificar');
    Route::get('reparaciones/{reparacion}/imprimir-orden', [ReparacionController::class, 'imprimirOrden'])->name('reparaciones.imprimir-orden');
    Route::get('reparaciones/{reparacion}/ticket-entrega', [ReparacionController::class, 'ticketEntrega'])->name('reparaciones.ticket-entrega');
    Route::get('panel-tecnico', [ReparacionController::class, 'panelTecnico'])->name('reparaciones.panel-tecnico');
    Route::get('reparaciones-estadisticas', [ReparacionController::class, 'estadisticas'])->name('reparaciones.estadisticas');

    // Reportes
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('reportes/reparaciones', [ReporteController::class, 'reparaciones'])->name('reportes.reparaciones');
    Route::get('reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::get('reportes/financiero', [ReporteController::class, 'financiero'])->name('reportes.financiero');
    Route::get('reportes/exportar/{tipo}/pdf', [ReporteController::class, 'exportarPdf'])->name('reportes.exportar.pdf');
    Route::get('reportes/exportar/{tipo}/excel', [ReporteController::class, 'exportarExcel'])->name('reportes.exportar.excel');

    // AZ libro
    Route::prefix('az-libro')->name('az-libro.')->group(function () {
        Route::get('/', [AzLibroController::class, 'index'])->name('index');
        Route::get('exportar/{dataset}/{format}', [AzLibroController::class, 'export'])->name('export');
        Route::get('respaldo/zip', [AzLibroController::class, 'backup'])->name('backup');
    });

    // Mora
    Route::prefix('mora')->name('mora.')->group(function () {
        Route::get('/', [MoraController::class, 'index'])->name('index');
        Route::get('ventas/{venta}', [MoraController::class, 'showVenta'])->name('ventas.show');
        Route::patch('ventas/{venta}', [MoraController::class, 'updateVenta'])->name('ventas.update');
        Route::post('ventas/{venta}/abonos', [MoraController::class, 'storeVentaAbono'])->name('ventas.abonos.store');
        Route::get('ventas/{venta}/whatsapp', [MoraController::class, 'ventaWhatsapp'])->name('ventas.whatsapp');

        Route::get('reparaciones/{reparacion}', [MoraController::class, 'showReparacion'])->name('reparaciones.show');
        Route::patch('reparaciones/{reparacion}', [MoraController::class, 'updateReparacion'])->name('reparaciones.update');
        Route::post('reparaciones/{reparacion}/abonos', [MoraController::class, 'storeReparacionAbono'])->name('reparaciones.abonos.store');
        Route::get('reparaciones/{reparacion}/whatsapp', [MoraController::class, 'reparacionWhatsapp'])->name('reparaciones.whatsapp');
    });

    // Gastos
    Route::prefix('gastos')->name('gastos.')->group(function () {
        Route::get('/', [GastoController::class, 'index'])->name('index');
        Route::get('lista', [GastoController::class, 'lista'])->name('lista');
        Route::get('crear', [GastoController::class, 'create'])->name('create');
        Route::post('/', [GastoController::class, 'store'])->name('store');
        Route::get('recurrentes', [GastoController::class, 'recurrentes'])->name('recurrentes');
        Route::post('recurrentes/{gasto}/generar', [GastoController::class, 'generarRecurrente'])->name('recurrentes.generar');
        Route::get('reportes', [GastoController::class, 'reportes'])->name('reportes');
        Route::get('aprobaciones', [GastoController::class, 'aprobaciones'])->name('aprobaciones');

        Route::prefix('categorias')->name('categorias.')->group(function () {
            Route::get('/', [GastoCategoriaController::class, 'index'])->name('index');
            Route::get('crear', [GastoCategoriaController::class, 'create'])->name('create');
            Route::post('/', [GastoCategoriaController::class, 'store'])->name('store');
            Route::get('{categoria}/editar', [GastoCategoriaController::class, 'edit'])->name('edit');
            Route::put('{categoria}', [GastoCategoriaController::class, 'update'])->name('update');
            Route::delete('{categoria}', [GastoCategoriaController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('proveedores')->name('proveedores.')->group(function () {
            Route::get('/', [GastoProveedorController::class, 'index'])->name('index');
            Route::get('crear', [GastoProveedorController::class, 'create'])->name('create');
            Route::post('/', [GastoProveedorController::class, 'store'])->name('store');
            Route::get('{proveedor}', [GastoProveedorController::class, 'show'])->name('show');
            Route::get('{proveedor}/editar', [GastoProveedorController::class, 'edit'])->name('edit');
            Route::put('{proveedor}', [GastoProveedorController::class, 'update'])->name('update');
            Route::delete('{proveedor}', [GastoProveedorController::class, 'destroy'])->name('destroy');
        });

        Route::post('{gasto}/aprobar', [GastoController::class, 'aprobar'])->name('aprobar');
        Route::post('{gasto}/rechazar', [GastoController::class, 'rechazar'])->name('rechazar');
        Route::post('{gasto}/marcar-pagado', [GastoController::class, 'marcarPagado'])->name('marcar-pagado');
        Route::post('{gasto}/duplicar', [GastoController::class, 'duplicar'])->name('duplicar');
        Route::get('{gasto}', [GastoController::class, 'show'])->name('show');
        Route::get('{gasto}/editar', [GastoController::class, 'edit'])->name('edit');
        Route::put('{gasto}', [GastoController::class, 'update'])->name('update');
        Route::delete('{gasto}', [GastoController::class, 'destroy'])->name('destroy');
    });

    // Usuarios
    Route::resource('usuarios', UserController::class);
    Route::get('usuarios/{usuario}/activar', [UserController::class, 'activar'])->name('usuarios.activar');
});
