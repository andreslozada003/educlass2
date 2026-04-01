<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Expense;
use App\Models\FacturaElectronica;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AzLibroSupport
{
    public function summaries(): array
    {
        $attachmentManifest = $this->buildAttachmentManifest();
        $moraCases = $this->buildMoraRows();

        return [
            'clientes' => $this->mergeSummaryMeta('clientes', Cliente::withTrashed()->count(), 0),
            'inventario' => $this->mergeSummaryMeta(
                'inventario',
                Producto::withTrashed()->count(),
                $attachmentManifest->where('module_key', 'inventario')->count()
            ),
            'ventas' => $this->mergeSummaryMeta(
                'ventas',
                Venta::withTrashed()->count(),
                $attachmentManifest->where('module_key', 'ventas')->count()
            ),
            'facturacion' => $this->mergeSummaryMeta(
                'facturacion',
                FacturaElectronica::query()->count(),
                $attachmentManifest->where('module_key', 'facturacion')->count()
            ),
            'gastos' => $this->mergeSummaryMeta(
                'gastos',
                Expense::withTrashed()->count(),
                $attachmentManifest->where('module_key', 'gastos')->count()
            ),
            'reparaciones' => $this->mergeSummaryMeta(
                'reparaciones',
                Reparacion::withTrashed()->count(),
                $attachmentManifest->where('module_key', 'reparaciones')->count()
            ),
            'mora' => $this->mergeSummaryMeta('mora', $moraCases->count(), 0),
            'usuarios' => $this->mergeSummaryMeta(
                'usuarios',
                User::query()->count(),
                $attachmentManifest->where('module_key', 'usuarios')->count()
            ),
            'reportes' => $this->mergeSummaryMeta('reportes', $this->buildReportRows($attachmentManifest, $moraCases)->count(), 0),
            'archivos-adjuntos' => $this->mergeSummaryMeta('archivos-adjuntos', $attachmentManifest->count(), $attachmentManifest->count()),
        ];
    }

    public function dataset(string $key): array
    {
        $attachmentManifest = $this->buildAttachmentManifest();
        $moraRows = $this->buildMoraRows();
        $reportRows = $this->buildReportRows($attachmentManifest, $moraRows);

        return match ($key) {
            'clientes' => $this->clientesDataset(),
            'inventario' => $this->inventarioDataset($attachmentManifest),
            'ventas' => $this->ventasDataset($attachmentManifest),
            'facturacion' => $this->facturacionDataset($attachmentManifest),
            'gastos' => $this->gastosDataset($attachmentManifest),
            'reparaciones' => $this->reparacionesDataset($attachmentManifest),
            'mora' => $this->moraDataset($moraRows),
            'usuarios' => $this->usuariosDataset($attachmentManifest),
            'reportes' => $this->reportesDataset($reportRows),
            'archivos-adjuntos' => $this->archivosAdjuntosDataset($attachmentManifest),
            default => abort(404),
        };
    }

    public function allDatasets(): array
    {
        return collect(array_keys($this->metadata()))
            ->mapWithKeys(fn (string $key) => [$key => $this->dataset($key)])
            ->all();
    }

    protected function metadata(): array
    {
        return [
            'clientes' => [
                'name' => 'Clientes',
                'description' => 'Ficha de clientes, actividad comercial e informacion fiscal.',
                'icon' => 'fa-users',
                'accent' => 'from-cyan-500 to-sky-600',
            ],
            'inventario' => [
                'name' => 'Inventario',
                'description' => 'Catalogo de productos, stock, costos, precios y multimedia.',
                'icon' => 'fa-boxes-stacked',
                'accent' => 'from-emerald-500 to-teal-600',
            ],
            'ventas' => [
                'name' => 'Ventas',
                'description' => 'Historial de ventas, cobros, productos y seguimiento comercial.',
                'icon' => 'fa-cash-register',
                'accent' => 'from-blue-500 to-indigo-600',
            ],
            'facturacion' => [
                'name' => 'Facturacion',
                'description' => 'CFDI, UUID, folios y documentos XML/PDF relacionados.',
                'icon' => 'fa-file-invoice-dollar',
                'accent' => 'from-fuchsia-500 to-pink-600',
            ],
            'gastos' => [
                'name' => 'Gastos',
                'description' => 'Egresos operativos, aprobaciones, proveedores y comprobantes.',
                'icon' => 'fa-wallet',
                'accent' => 'from-rose-500 to-red-600',
            ],
            'reparaciones' => [
                'name' => 'Reparaciones',
                'description' => 'Ordenes, estados, costos, tecnicos y registro fotografico.',
                'icon' => 'fa-screwdriver-wrench',
                'accent' => 'from-amber-500 to-orange-600',
            ],
            'mora' => [
                'name' => 'Cuentas por cobrar / mora',
                'description' => 'Cartera de ventas y reparaciones con saldo pendiente.',
                'icon' => 'fa-signal',
                'accent' => 'from-slate-500 to-slate-700',
            ],
            'usuarios' => [
                'name' => 'Usuarios',
                'description' => 'Usuarios del sistema, roles, permisos y avatar.',
                'icon' => 'fa-user-shield',
                'accent' => 'from-violet-500 to-purple-600',
            ],
            'reportes' => [
                'name' => 'Reportes',
                'description' => 'Resumen ejecutivo con indicadores clave del negocio.',
                'icon' => 'fa-chart-column',
                'accent' => 'from-lime-500 to-green-600',
            ],
            'archivos-adjuntos' => [
                'name' => 'Archivos adjuntos',
                'description' => 'Manifesto centralizado de archivos y evidencias del sistema.',
                'icon' => 'fa-paperclip',
                'accent' => 'from-stone-500 to-zinc-700',
            ],
        ];
    }

    protected function mergeSummaryMeta(string $key, int $records, int $attachments): array
    {
        return array_merge($this->metadata()[$key], [
            'key' => $key,
            'records' => $records,
            'attachments' => $attachments,
        ]);
    }

    protected function clientesDataset(): array
    {
        $rows = Cliente::withTrashed()
            ->withCount(['ventas', 'reparaciones', 'facturasElectronicas'])
            ->orderBy('nombre')
            ->get()
            ->map(fn (Cliente $cliente) => [
                $cliente->id,
                $cliente->nombre_completo ?: 'Sin nombre',
                $cliente->nombre_fiscal ?: 'Sin razon social',
                $cliente->telefono ?: 'Sin telefono',
                $cliente->email ?: 'Sin correo',
                trim(collect([$cliente->ciudad, $cliente->estado])->filter()->implode(', ')) ?: 'Sin ubicacion',
                $cliente->rfc ?: 'No aplica',
                $cliente->ventas_count,
                $cliente->reparaciones_count,
                $cliente->facturas_electronicas_count,
                $this->lifecycleLabel($cliente, $cliente->activo),
                optional($cliente->created_at)->format('Y-m-d H:i') ?: 'Sin fecha',
            ])
            ->values();

        return $this->makeDatasetPayload(
            'clientes',
            [
                'ID',
                'Cliente',
                'Nombre fiscal',
                'Telefono',
                'Correo',
                'Ubicacion',
                'RFC',
                'Ventas',
                'Reparaciones',
                'Facturas',
                'Estado registro',
                'Creado',
            ],
            $rows,
            collect()
        );
    }

    protected function inventarioDataset(Collection $attachmentManifest): array
    {
        $attachments = $attachmentManifest->where('module_key', 'inventario')->values();

        $rows = Producto::withTrashed()
            ->with('categoria')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Producto $producto) => [
                $producto->id,
                $producto->codigo ?: 'Sin codigo',
                $producto->codigo_barras ?: 'Sin codigo de barras',
                $producto->nombre,
                $producto->categoria?->nombre ?: 'Sin categoria',
                trim(collect([$producto->marca, $producto->modelo])->filter()->implode(' ')) ?: 'Sin referencia',
                $producto->es_servicio ? 'Servicio' : 'Producto',
                $this->decimal($producto->precio_compra),
                $this->decimal($producto->precio_venta),
                $producto->stock,
                $producto->stock_minimo,
                $this->decimal($producto->valor_inventario),
                $this->lifecycleLabel($producto, $producto->activo),
                $producto->imagen_principal ?: 'Sin adjunto',
            ])
            ->values();

        return $this->makeDatasetPayload(
            'inventario',
            [
                'ID',
                'Codigo',
                'Codigo barras',
                'Producto',
                'Categoria',
                'Marca / modelo',
                'Tipo',
                'Precio compra',
                'Precio venta',
                'Stock',
                'Stock minimo',
                'Valor inventario',
                'Estado registro',
                'Imagen principal',
            ],
            $rows,
            $attachments
        );
    }

    protected function ventasDataset(Collection $attachmentManifest): array
    {
        $attachments = $attachmentManifest->where('module_key', 'ventas')->values();

        $rows = Venta::withTrashed()
            ->with(['cliente', 'usuario', 'detalles.producto', 'facturaElectronica'])
            ->latest('fecha_venta')
            ->get()
            ->map(fn (Venta $venta) => [
                $venta->id,
                $venta->folio,
                optional($venta->fecha_venta)->format('Y-m-d H:i') ?: 'Sin fecha',
                $venta->cliente?->nombre_completo ?: 'Cliente general',
                $venta->usuario?->name ?: 'Sin usuario',
                ucfirst($venta->estado),
                ucfirst($venta->metodo_pago),
                $this->decimal($venta->total),
                $this->decimal($venta->monto_pagado),
                $this->decimal($venta->saldo_pendiente_mora),
                optional($venta->fecha_inicio_mora)->format('Y-m-d') ?: 'Sin mora',
                optional($venta->fecha_compromiso_pago)->format('Y-m-d') ?: 'Sin compromiso',
                $venta->detalles->pluck('producto.nombre')->filter()->take(4)->implode(', ') ?: 'Sin detalle',
                $venta->facturaElectronica ? 'Si' : 'No',
            ])
            ->values();

        return $this->makeDatasetPayload(
            'ventas',
            [
                'ID',
                'Folio',
                'Fecha',
                'Cliente',
                'Usuario',
                'Estado',
                'Metodo de pago',
                'Total',
                'Monto pagado',
                'Saldo pendiente',
                'Inicio mora',
                'Compromiso pago',
                'Productos',
                'Facturada',
            ],
            $rows,
            $attachments
        );
    }

    protected function facturacionDataset(Collection $attachmentManifest): array
    {
        $attachments = $attachmentManifest->where('module_key', 'facturacion')->values();

        $rows = FacturaElectronica::query()
            ->with(['cliente', 'usuario', 'venta'])
            ->latest()
            ->get()
            ->map(fn (FacturaElectronica $factura) => [
                $factura->id,
                $factura->folio_interno,
                ucfirst($factura->estado),
                $factura->uuid ?: 'Pendiente',
                trim(collect([$factura->serie, $factura->folio])->filter()->implode('-')) ?: 'Sin folio SAT',
                $factura->venta?->folio ?: 'Sin venta',
                $factura->cliente?->nombre_fiscal ?: $factura->cliente?->nombre_completo ?: 'Sin cliente',
                $factura->usuario?->name ?: 'Sin usuario',
                $this->decimal($factura->total),
                $factura->moneda,
                $factura->uso_cfdi ?: 'Sin uso',
                $factura->exportacion ?: 'Sin clave',
                optional($factura->fecha_timbrado)->format('Y-m-d H:i') ?: 'Sin timbrar',
                $factura->xml_path ?: 'Sin XML',
                $factura->pdf_path ?: 'Sin PDF',
            ])
            ->values();

        return $this->makeDatasetPayload(
            'facturacion',
            [
                'ID',
                'Folio interno',
                'Estado',
                'UUID',
                'Serie / folio',
                'Venta',
                'Cliente',
                'Usuario',
                'Total',
                'Moneda',
                'Uso CFDI',
                'Exportacion',
                'Fecha timbrado',
                'XML',
                'PDF',
            ],
            $rows,
            $attachments
        );
    }

    protected function gastosDataset(Collection $attachmentManifest): array
    {
        $attachments = $attachmentManifest->where('module_key', 'gastos')->values();

        $rows = Expense::withTrashed()
            ->with(['category.parent', 'subcategory', 'supplier', 'responsibleUser'])
            ->latest('expense_date')
            ->get()
            ->map(fn (Expense $gasto) => [
                $gasto->id,
                $gasto->expense_number,
                optional($gasto->expense_date)->format('Y-m-d') ?: 'Sin fecha',
                $gasto->description,
                $gasto->category?->name ?: 'Sin categoria',
                $gasto->subcategory?->name ?: 'Sin subcategoria',
                $gasto->supplier?->name ?: 'Sin proveedor',
                $gasto->responsibleUser?->name ?: 'Sin responsable',
                $gasto->expense_type_label,
                $gasto->payment_status_label,
                $gasto->approval_status_label,
                $this->decimal($gasto->amount),
                $this->decimal($gasto->paid_amount),
                $this->decimal($gasto->pending_balance),
                $gasto->payment_source_label,
                $gasto->receipt_image ?: 'Sin adjunto',
            ])
            ->values();

        return $this->makeDatasetPayload(
            'gastos',
            [
                'ID',
                'Numero',
                'Fecha',
                'Descripcion',
                'Categoria',
                'Subcategoria',
                'Proveedor',
                'Responsable',
                'Tipo',
                'Estado pago',
                'Aprobacion',
                'Monto',
                'Pagado',
                'Pendiente',
                'Fuente',
                'Adjunto',
            ],
            $rows,
            $attachments
        );
    }

    protected function reparacionesDataset(Collection $attachmentManifest): array
    {
        $attachments = $attachmentManifest->where('module_key', 'reparaciones')->values();

        $rows = Reparacion::withTrashed()
            ->with(['cliente', 'usuario', 'tecnico'])
            ->latest('fecha_recepcion')
            ->get()
            ->map(fn (Reparacion $reparacion) => [
                $reparacion->id,
                $reparacion->orden,
                $reparacion->cliente?->nombre_completo ?: 'Sin cliente',
                $reparacion->usuario?->name ?: 'Sin recepcionista',
                $reparacion->tecnico?->name ?: 'Sin tecnico',
                trim(collect([$reparacion->dispositivo_marca, $reparacion->dispositivo_modelo])->filter()->implode(' ')) ?: 'Sin equipo',
                $reparacion->dispositivo_imei ?: 'Sin IMEI',
                $reparacion->estado_nombre,
                optional($reparacion->fecha_recepcion)->format('Y-m-d H:i') ?: 'Sin fecha',
                optional($reparacion->fecha_entrega)->format('Y-m-d H:i') ?: 'Pendiente',
                $this->decimal($reparacion->costo_estimado),
                $this->decimal($reparacion->costo_final),
                $this->decimal($reparacion->adelanto),
                $this->decimal($reparacion->saldo_pendiente_mora),
                $reparacion->problema_reportado ?: 'Sin descripcion',
            ])
            ->values();

        return $this->makeDatasetPayload(
            'reparaciones',
            [
                'ID',
                'Orden',
                'Cliente',
                'Recibio',
                'Tecnico',
                'Equipo',
                'IMEI',
                'Estado',
                'Recepcion',
                'Entrega',
                'Costo estimado',
                'Costo final',
                'Adelanto',
                'Saldo pendiente',
                'Problema reportado',
            ],
            $rows,
            $attachments
        );
    }

    protected function moraDataset(Collection $moraRows): array
    {
        return $this->makeDatasetPayload(
            'mora',
            [
                'Tipo',
                'Documento',
                'Cliente',
                'Responsable',
                'Referencia',
                'Estado',
                'Inicio mora',
                'Dias en mora',
                'Semaforo',
                'Etapa',
                'Valor operacion',
                'Pagado / adelanto',
                'Saldo pendiente',
                'Ultima notificacion',
            ],
            $moraRows,
            collect()
        );
    }

    protected function usuariosDataset(Collection $attachmentManifest): array
    {
        $attachments = $attachmentManifest->where('module_key', 'usuarios')->values();

        $rows = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
                $user->phone ?: 'Sin telefono',
                $user->roles->pluck('name')->implode(', ') ?: 'Sin roles',
                $user->getAllPermissions()->pluck('name')->count(),
                $user->is_active ? 'Activo' : 'Inactivo',
                $user->avatar ?: 'Sin avatar',
                optional($user->created_at)->format('Y-m-d H:i') ?: 'Sin fecha',
            ])
            ->values();

        return $this->makeDatasetPayload(
            'usuarios',
            [
                'ID',
                'Nombre',
                'Correo',
                'Telefono',
                'Roles',
                'Total permisos',
                'Estado',
                'Avatar',
                'Creado',
            ],
            $rows,
            $attachments
        );
    }

    protected function reportesDataset(Collection $reportRows): array
    {
        return $this->makeDatasetPayload(
            'reportes',
            ['Seccion', 'Indicador', 'Valor', 'Detalle'],
            $reportRows,
            collect()
        );
    }

    protected function archivosAdjuntosDataset(Collection $attachmentManifest): array
    {
        $rows = $attachmentManifest
            ->map(fn (array $entry) => [
                $entry['module'],
                $entry['record'],
                $entry['type'],
                $entry['file_kind'],
                $entry['path'],
                $entry['filename'],
                $entry['extension'],
                $entry['size_human'],
                $entry['exists'] ? 'Si' : 'No',
            ])
            ->values();

        return $this->makeDatasetPayload(
            'archivos-adjuntos',
            ['Modulo', 'Registro', 'Tipo', 'Clase archivo', 'Ruta', 'Archivo', 'Extension', 'Tamano', 'Disponible'],
            $rows,
            $attachmentManifest
        );
    }

    protected function buildMoraRows(): Collection
    {
        $ventas = Venta::with(['cliente', 'usuario', 'detalles.producto', 'ultimaMoraNotificacion'])
            ->where('estado', '!=', 'cancelada')
            ->whereColumn('total', '>', 'monto_pagado')
            ->latest('fecha_venta')
            ->get()
            ->map(fn (Venta $venta) => [
                'Venta',
                $venta->folio,
                $venta->cliente?->nombre_completo ?: 'Cliente general',
                $venta->usuario?->name ?: 'Sin responsable',
                $venta->resumen_equipo_mora,
                ucfirst($venta->estado),
                optional($venta->fecha_inicio_mora)->format('Y-m-d') ?: 'Sin fecha',
                $venta->dias_en_mora,
                ucfirst($venta->mora_semaforo),
                ucfirst($venta->mora_etapa),
                $this->decimal($venta->total),
                $this->decimal($venta->monto_pagado),
                $this->decimal($venta->saldo_pendiente_mora),
                optional($venta->ultimaMoraNotificacion?->fecha_envio)->format('Y-m-d H:i') ?: 'Sin gestion',
            ]);

        $reparaciones = Reparacion::with(['cliente', 'usuario', 'tecnico', 'ultimaMoraNotificacion'])
            ->where(function ($query) {
                $query
                    ->where(function ($inner) {
                        $inner->where('costo_final', '>', 0)
                            ->whereColumn('costo_final', '>', 'adelanto');
                    })
                    ->orWhere(function ($inner) {
                        $inner->where('costo_final', '<=', 0)
                            ->whereColumn('costo_estimado', '>', 'adelanto');
                    });
            })
            ->latest('fecha_recepcion')
            ->get()
            ->map(fn (Reparacion $reparacion) => [
                'Reparacion',
                $reparacion->orden,
                $reparacion->cliente?->nombre_completo ?: 'Sin cliente',
                $reparacion->tecnico?->name ?: $reparacion->usuario?->name ?: 'Sin responsable',
                $reparacion->dispositivo_info,
                $reparacion->estado_nombre,
                optional($reparacion->fecha_inicio_mora)->format('Y-m-d') ?: 'Sin fecha',
                $reparacion->dias_en_mora,
                ucfirst($reparacion->mora_semaforo),
                ucfirst($reparacion->mora_etapa),
                $this->decimal($reparacion->valor_operacion_mora),
                $this->decimal($reparacion->adelanto),
                $this->decimal($reparacion->saldo_pendiente_mora),
                optional($reparacion->ultimaMoraNotificacion?->fecha_envio)->format('Y-m-d H:i') ?: 'Sin gestion',
            ]);

        return $ventas->concat($reparaciones)->values();
    }

    protected function buildReportRows(Collection $attachmentManifest, Collection $moraRows): Collection
    {
        $ventas = Venta::withTrashed()->where('estado', '!=', 'cancelada')->get();
        $reparaciones = Reparacion::withTrashed()->get();
        $gastos = Expense::withTrashed()->where('payment_status', '!=', 'cancelled')->get();
        $facturas = FacturaElectronica::query()->get();

        return collect([
            ['General', 'Clientes registrados', Cliente::withTrashed()->count(), 'Incluye activos, inactivos y eliminados'],
            ['General', 'Productos registrados', Producto::withTrashed()->count(), 'Inventario y servicios'],
            ['General', 'Usuarios registrados', User::query()->count(), 'Usuarios del sistema'],
            ['Ventas', 'Ventas registradas', $ventas->count(), 'Excluye ventas canceladas'],
            ['Ventas', 'Total vendido', $this->decimal($ventas->sum('total')), 'Suma historica de ventas'],
            ['Ventas', 'Saldo por cobrar', $this->decimal($ventas->sum(fn (Venta $venta) => $venta->saldo_pendiente_mora)), 'Ventas con saldo pendiente'],
            ['Facturacion', 'Facturas registradas', $facturas->count(), 'Facturas electronicas creadas'],
            ['Facturacion', 'Facturas timbradas', $facturas->where('estado', 'timbrada')->count(), 'Facturas listas ante el PAC'],
            ['Facturacion', 'Total facturado', $this->decimal($facturas->sum('total')), 'Suma total CFDI'],
            ['Gastos', 'Gastos registrados', $gastos->count(), 'Excluye egresos anulados'],
            ['Gastos', 'Total egresado', $this->decimal($gastos->sum('amount')), 'Suma historica de gastos'],
            ['Gastos', 'Pendiente por pagar', $this->decimal($gastos->sum(fn (Expense $gasto) => $gasto->pending_balance)), 'Saldo abierto en egresos'],
            ['Reparaciones', 'Ordenes registradas', $reparaciones->count(), 'Incluye reparaciones archivadas'],
            ['Reparaciones', 'Ingresos por reparacion', $this->decimal($reparaciones->where('estado', 'entregado')->sum('costo_final')), 'Solo trabajos entregados'],
            ['Reparaciones', 'Saldo pendiente taller', $this->decimal($reparaciones->sum(fn (Reparacion $reparacion) => $reparacion->saldo_pendiente_mora)), 'Cartera de reparaciones'],
            ['Mora', 'Casos en seguimiento', $moraRows->count(), 'Ventas y reparaciones con saldo pendiente'],
            ['Adjuntos', 'Archivos detectados', $attachmentManifest->count(), 'Manifestados para respaldo ZIP'],
        ])->values();
    }

    protected function buildAttachmentManifest(): Collection
    {
        $entries = collect();

        Producto::withTrashed()->orderBy('id')->get()->each(function (Producto $producto) use ($entries) {
            if ($producto->imagen_principal) {
                $entries->push($this->makeAttachmentEntry(
                    'inventario',
                    'Inventario',
                    "Producto {$producto->codigo} - {$producto->nombre}",
                    'Imagen principal',
                    $producto->imagen_principal
                ));
            }

            collect($producto->imagenes_adicionales ?? [])
                ->filter(fn ($path) => is_string($path) && trim($path) !== '')
                ->values()
                ->each(function (string $path, int $index) use ($entries, $producto) {
                    $entries->push($this->makeAttachmentEntry(
                        'inventario',
                        'Inventario',
                        "Producto {$producto->codigo} - {$producto->nombre}",
                        'Imagen adicional ' . ($index + 1),
                        $path
                    ));
                });
        });

        Venta::withTrashed()->orderBy('id')->get()->each(function (Venta $venta) use ($entries) {
            if ($venta->comprobante) {
                $entries->push($this->makeAttachmentEntry(
                    'ventas',
                    'Ventas',
                    "Venta {$venta->folio}",
                    'Comprobante',
                    $venta->comprobante
                ));
            }
        });

        FacturaElectronica::query()->orderBy('id')->get()->each(function (FacturaElectronica $factura) use ($entries) {
            foreach ([
                'XML' => $factura->xml_path,
                'PDF' => $factura->pdf_path,
                'Acuse cancelacion' => $factura->acuse_cancelacion_path,
            ] as $type => $path) {
                if ($path) {
                    $entries->push($this->makeAttachmentEntry(
                        'facturacion',
                        'Facturacion',
                        "Factura {$factura->folio_interno}",
                        $type,
                        $path
                    ));
                }
            }
        });

        Expense::withTrashed()->orderBy('id')->get()->each(function (Expense $gasto) use ($entries) {
            if ($gasto->receipt_image) {
                $entries->push($this->makeAttachmentEntry(
                    'gastos',
                    'Gastos',
                    "Gasto {$gasto->expense_number}",
                    'Soporte / comprobante',
                    $gasto->receipt_image
                ));
            }
        });

        Reparacion::withTrashed()->orderBy('id')->get()->each(function (Reparacion $reparacion) use ($entries) {
            foreach ([
                'Foto antes 1' => $reparacion->foto_antes_1,
                'Foto antes 2' => $reparacion->foto_antes_2,
                'Foto antes 3' => $reparacion->foto_antes_3,
                'Foto despues 1' => $reparacion->foto_despues_1,
                'Foto despues 2' => $reparacion->foto_despues_2,
                'Foto despues 3' => $reparacion->foto_despues_3,
            ] as $type => $path) {
                if ($path) {
                    $entries->push($this->makeAttachmentEntry(
                        'reparaciones',
                        'Reparaciones',
                        "Orden {$reparacion->orden}",
                        $type,
                        $path
                    ));
                }
            }
        });

        User::query()->orderBy('id')->get()->each(function (User $user) use ($entries) {
            if ($user->avatar) {
                $entries->push($this->makeAttachmentEntry(
                    'usuarios',
                    'Usuarios',
                    "Usuario {$user->name}",
                    'Avatar',
                    $user->avatar
                ));
            }
        });

        return $entries->values();
    }

    protected function makeAttachmentEntry(
        string $moduleKey,
        string $module,
        string $record,
        string $type,
        string $relativePath
    ): array {
        $relativePath = ltrim($relativePath, '/\\');
        $absolutePath = $this->resolveAbsolutePath($relativePath);
        $filename = basename($relativePath);
        $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: 'sin-extension';
        $zipPath = $moduleKey
            . '/'
            . $this->safeSegment($record)
            . '/'
            . $this->safeSegment($type)
            . '/'
            . $filename;
        $exists = $absolutePath !== null;

        return [
            'module_key' => $moduleKey,
            'module' => $module,
            'record' => $record,
            'type' => $type,
            'file_kind' => $this->detectFileKind($extension),
            'path' => $relativePath,
            'absolute_path' => $absolutePath,
            'filename' => $filename,
            'extension' => strtolower($extension),
            'size_human' => $exists ? $this->humanFileSize(filesize($absolutePath)) : 'No disponible',
            'exists' => $exists,
            'previewable_image' => $this->isPreviewableImage($extension),
            'zip_path' => $zipPath,
        ];
    }

    protected function resolveAbsolutePath(string $relativePath): ?string
    {
        $publicDisk = Storage::disk('public');

        if ($publicDisk->exists($relativePath)) {
            return $publicDisk->path($relativePath);
        }

        $storageCandidates = [
            storage_path('app/' . $relativePath),
            storage_path('app/public/' . $relativePath),
            public_path('storage/' . $relativePath),
        ];

        foreach ($storageCandidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function makeDatasetPayload(string $key, array $headings, Collection $rows, Collection $attachments): array
    {
        $meta = $this->metadata()[$key];

        return [
            'key' => $key,
            'name' => $meta['name'],
            'description' => $meta['description'],
            'icon' => $meta['icon'],
            'accent' => $meta['accent'],
            'headings' => $headings,
            'rows' => $rows->all(),
            'attachments' => $attachments->values()->all(),
            'record_count' => $rows->count(),
            'attachment_count' => $attachments->count(),
        ];
    }

    protected function lifecycleLabel(object $model, ?bool $active = null): string
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            return 'Eliminado';
        }

        if ($active === false) {
            return 'Inactivo';
        }

        return 'Activo';
    }

    protected function decimal(float|int|string|null $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    protected function humanFileSize(int|false $bytes): string
    {
        if ($bytes === false || $bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return number_format($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }

    protected function safeSegment(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: 'archivo';

        return trim($value, '-');
    }

    protected function detectFileKind(string $extension): string
    {
        $extension = strtolower($extension);

        return match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'], true) => 'Imagen',
            $extension === 'pdf' => 'PDF',
            $extension === 'xml' => 'XML',
            in_array($extension, ['csv', 'xls', 'xlsx'], true) => 'Hoja de calculo',
            in_array($extension, ['txt', 'log'], true) => 'Texto',
            default => 'Documento',
        };
    }

    protected function isPreviewableImage(string $extension): bool
    {
        $extension = strtolower($extension);

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            return true;
        }

        if (extension_loaded('gd')) {
            return in_array($extension, ['png', 'webp'], true);
        }

        return false;
    }
}
