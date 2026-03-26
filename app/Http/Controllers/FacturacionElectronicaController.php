<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\FacturaElectronica;
use App\Models\Venta;
use App\Services\Facturacion\FacturaElectronicaService;
use App\Support\FacturacionCatalogos;
use Illuminate\Http\Request;

class FacturacionElectronicaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, FacturaElectronicaService $facturaElectronicaService)
    {
        $query = FacturaElectronica::with(['venta', 'cliente', 'usuario'])->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('folio_interno', 'like', "%{$request->search}%")
                    ->orWhereHas('venta', function ($ventaQuery) use ($request) {
                        $ventaQuery->where('folio', 'like', "%{$request->search}%");
                    })
                    ->orWhereHas('cliente', function ($clienteQuery) use ($request) {
                        $clienteQuery->where('nombre', 'like', "%{$request->search}%")
                            ->orWhere('apellido', 'like', "%{$request->search}%")
                            ->orWhere('razon_social', 'like', "%{$request->search}%")
                            ->orWhere('rfc', 'like', "%{$request->search}%");
                    });
            });
        }

        $facturas = $query->paginate(15)->withQueryString();
        $configuracion = $facturaElectronicaService->obtenerConfiguracion();

        $ventasPendientes = Venta::with('cliente')
            ->where('estado', 'pagada')
            ->whereDoesntHave('facturaElectronica')
            ->latest()
            ->limit(8)
            ->get();

        $estadisticas = [
            'listas' => FacturaElectronica::where('estado', 'lista_para_timbrar')->count(),
            'timbradas' => FacturaElectronica::where('estado', 'timbrada')->count(),
            'errores' => FacturaElectronica::where('estado', 'error')->count(),
            'pendientes' => $ventasPendientes->count(),
        ];

        $regimenesFiscales = FacturacionCatalogos::regimenesFiscales();
        $modosPac = FacturacionCatalogos::modosPac();
        $objetosImpuesto = FacturacionCatalogos::objetosImpuesto();
        $exportaciones = FacturacionCatalogos::exportaciones();
        $estados = [
            'borrador' => 'Borrador',
            'lista_para_timbrar' => 'Lista para timbrar',
            'timbrada' => 'Timbrada',
            'error' => 'Con error',
            'cancelada' => 'Cancelada',
        ];

        return view('facturacion.index', compact(
            'facturas',
            'configuracion',
            'ventasPendientes',
            'estadisticas',
            'regimenesFiscales',
            'modosPac',
            'objetosImpuesto',
            'exportaciones',
            'estados'
        ));
    }

    public function updateConfiguracion(Request $request)
    {
        $validated = $request->validate([
            'empresa_nombre' => 'required|string|max:255',
            'empresa_rfc' => 'nullable|string|max:13',
            'empresa_direccion' => 'nullable|string',
            'empresa_telefono' => 'nullable|string|max:50',
            'empresa_email' => 'nullable|email|max:100',
            'facturacion_cfdi_version' => 'required|string|max:10',
            'facturacion_serie' => 'nullable|string|max:20',
            'facturacion_lugar_expedicion' => 'nullable|string|max:10',
            'facturacion_regimen_fiscal_emisor' => 'nullable|string|max:10',
            'facturacion_exportacion' => 'nullable|string|max:5',
            'facturacion_pac_nombre' => 'nullable|string|max:100',
            'facturacion_pac_modo' => 'nullable|in:sandbox,production',
            'facturacion_pac_url' => 'nullable|string|max:255',
            'facturacion_pac_usuario' => 'nullable|string|max:100',
            'facturacion_pac_password' => 'nullable|string|max:255',
            'facturacion_pac_token' => 'nullable|string|max:255',
            'facturacion_certificado_cer' => 'nullable|string|max:255',
            'facturacion_certificado_key' => 'nullable|string|max:255',
            'facturacion_certificado_password' => 'nullable|string|max:255',
            'facturacion_clave_prod_serv_default' => 'nullable|string|max:20',
            'facturacion_clave_unidad_default' => 'nullable|string|max:10',
            'facturacion_unidad_default' => 'nullable|string|max:50',
            'facturacion_objeto_impuesto_default' => 'nullable|in:01,02,03,04',
        ]);

        Configuracion::set('empresa.nombre', $validated['empresa_nombre']);
        Configuracion::set('empresa.rfc', $validated['empresa_rfc'] ?? '');
        Configuracion::set('empresa.direccion', $validated['empresa_direccion'] ?? '');
        Configuracion::set('empresa.telefono', $validated['empresa_telefono'] ?? '');
        Configuracion::set('empresa.email', $validated['empresa_email'] ?? '');

        Configuracion::set('facturacion.activo', $request->boolean('facturacion_activo'));
        Configuracion::set('facturacion.cfdi_version', $validated['facturacion_cfdi_version']);
        Configuracion::set('facturacion.serie', $validated['facturacion_serie'] ?? '');
        Configuracion::set('facturacion.lugar_expedicion', $validated['facturacion_lugar_expedicion'] ?? '');
        Configuracion::set('facturacion.regimen_fiscal_emisor', $validated['facturacion_regimen_fiscal_emisor'] ?? '');
        Configuracion::set('facturacion.exportacion', $validated['facturacion_exportacion'] ?? '01');
        Configuracion::set('facturacion.pac_nombre', $validated['facturacion_pac_nombre'] ?? '');
        Configuracion::set('facturacion.pac_modo', $validated['facturacion_pac_modo'] ?? 'sandbox');
        Configuracion::set('facturacion.pac_url', $validated['facturacion_pac_url'] ?? '');
        Configuracion::set('facturacion.pac_usuario', $validated['facturacion_pac_usuario'] ?? '');
        Configuracion::set('facturacion.pac_password', $validated['facturacion_pac_password'] ?? '');
        Configuracion::set('facturacion.pac_token', $validated['facturacion_pac_token'] ?? '');
        Configuracion::set('facturacion.certificado_cer', $validated['facturacion_certificado_cer'] ?? '');
        Configuracion::set('facturacion.certificado_key', $validated['facturacion_certificado_key'] ?? '');
        Configuracion::set('facturacion.certificado_password', $validated['facturacion_certificado_password'] ?? '');
        Configuracion::set('facturacion.clave_prod_serv_default', $validated['facturacion_clave_prod_serv_default'] ?? '01010101');
        Configuracion::set('facturacion.clave_unidad_default', $validated['facturacion_clave_unidad_default'] ?? 'H87');
        Configuracion::set('facturacion.unidad_default', $validated['facturacion_unidad_default'] ?? 'Pieza');
        Configuracion::set('facturacion.objeto_impuesto_default', $validated['facturacion_objeto_impuesto_default'] ?? '02');

        return redirect()->route('facturacion.index')
            ->with('success', 'Configuracion de facturacion actualizada correctamente.');
    }

    public function prepararDesdeVenta(Venta $venta, FacturaElectronicaService $facturaElectronicaService)
    {
        $factura = $facturaElectronicaService->preparar($venta, auth()->id());

        return redirect()->route('facturacion.show', $factura)
            ->with('success', 'La factura electronica quedo preparada. Solo faltara timbrarla cuando conectes tu PAC.');
    }

    public function show(FacturaElectronica $factura, FacturaElectronicaService $facturaElectronicaService)
    {
        $factura->load(['venta.detalles.producto', 'cliente', 'usuario']);
        $revision = $facturaElectronicaService->revisarVenta($factura->venta);

        $regimenesFiscales = FacturacionCatalogos::regimenesFiscales();
        $usosCfdi = FacturacionCatalogos::usosCfdi();
        $formasPago = FacturacionCatalogos::formasPago();
        $metodosPago = FacturacionCatalogos::metodosPago();
        $objetosImpuesto = FacturacionCatalogos::objetosImpuesto();
        $exportaciones = FacturacionCatalogos::exportaciones();

        return view('facturacion.show', compact(
            'factura',
            'revision',
            'regimenesFiscales',
            'usosCfdi',
            'formasPago',
            'metodosPago',
            'objetosImpuesto',
            'exportaciones'
        ));
    }
}
