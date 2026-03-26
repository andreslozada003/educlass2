<?php

use App\Models\Reparacion;
use App\Models\Venta;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addVentasColumns();
        $this->addReparacionesColumns();
        $this->createMoraAbonosTable();
        $this->createMoraNotificacionesTable();
        $this->backfillMoraData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mora_notificaciones');
        Schema::dropIfExists('mora_abonos');

        Schema::table('reparaciones', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_inicio_mora',
                'mora_observaciones',
                'ultima_notificacion_mora_at',
            ]);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'monto_pagado',
                'fecha_inicio_mora',
                'fecha_compromiso_pago',
                'numero_cuotas',
                'plazo_acordado_dias',
                'mora_observaciones',
                'ultima_notificacion_mora_at',
            ]);
        });
    }

    private function addVentasColumns(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (! Schema::hasColumn('ventas', 'monto_pagado')) {
                $table->decimal('monto_pagado', 12, 2)->default(0)->after('total');
            }

            if (! Schema::hasColumn('ventas', 'fecha_inicio_mora')) {
                $table->date('fecha_inicio_mora')->nullable()->after('estado');
            }

            if (! Schema::hasColumn('ventas', 'fecha_compromiso_pago')) {
                $table->date('fecha_compromiso_pago')->nullable()->after('fecha_inicio_mora');
            }

            if (! Schema::hasColumn('ventas', 'numero_cuotas')) {
                $table->unsignedInteger('numero_cuotas')->nullable()->after('fecha_compromiso_pago');
            }

            if (! Schema::hasColumn('ventas', 'plazo_acordado_dias')) {
                $table->unsignedInteger('plazo_acordado_dias')->nullable()->after('numero_cuotas');
            }

            if (! Schema::hasColumn('ventas', 'mora_observaciones')) {
                $table->text('mora_observaciones')->nullable()->after('plazo_acordado_dias');
            }

            if (! Schema::hasColumn('ventas', 'ultima_notificacion_mora_at')) {
                $table->dateTime('ultima_notificacion_mora_at')->nullable()->after('mora_observaciones');
            }
        });
    }

    private function addReparacionesColumns(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('reparaciones', 'fecha_inicio_mora')) {
                $table->date('fecha_inicio_mora')->nullable()->after('fecha_entrega');
            }

            if (! Schema::hasColumn('reparaciones', 'mora_observaciones')) {
                $table->text('mora_observaciones')->nullable()->after('notas_cliente');
            }

            if (! Schema::hasColumn('reparaciones', 'ultima_notificacion_mora_at')) {
                $table->dateTime('ultima_notificacion_mora_at')->nullable()->after('fecha_notificacion');
            }
        });
    }

    private function createMoraAbonosTable(): void
    {
        if (Schema::hasTable('mora_abonos')) {
            return;
        }

        Schema::create('mora_abonos', function (Blueprint $table) {
            $table->id();
            $table->morphs('mora_abonable', 'mora_abonable_lookup_idx');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 30)->default('abono');
            $table->decimal('monto', 12, 2);
            $table->string('metodo_pago', 50)->nullable();
            $table->string('origen', 50)->nullable();
            $table->dateTime('fecha_pago');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    private function createMoraNotificacionesTable(): void
    {
        if (Schema::hasTable('mora_notificaciones')) {
            return;
        }

        Schema::create('mora_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->morphs('mora_notificable', 'mora_notificable_lookup_idx');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('canal', 30)->default('whatsapp');
            $table->string('nivel', 30)->nullable();
            $table->string('plantilla', 80)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('estado_envio', 50)->default('registrado');
            $table->dateTime('fecha_envio');
            $table->text('mensaje');
            $table->timestamps();
        });
    }

    private function backfillMoraData(): void
    {
        if (! Schema::hasTable('mora_abonos') || DB::table('mora_abonos')->count() > 0) {
            return;
        }

        DB::table('ventas')
            ->orderBy('id')
            ->chunkById(100, function ($ventas) {
                $rows = [];

                foreach ($ventas as $venta) {
                    $total = (float) $venta->total;
                    $pagadoCon = max(0, (float) $venta->pagado_con);
                    $montoPagado = $venta->estado === 'credito'
                        ? min($total, $pagadoCon)
                        : $total;

                    DB::table('ventas')
                        ->where('id', $venta->id)
                        ->update([
                            'monto_pagado' => $montoPagado,
                        ]);

                    if ($venta->estado === 'credito' && $montoPagado > 0) {
                        $rows[] = [
                            'mora_abonable_type' => Venta::class,
                            'mora_abonable_id' => $venta->id,
                            'cliente_id' => $venta->cliente_id,
                            'user_id' => $venta->user_id,
                            'tipo' => 'abono',
                            'monto' => $montoPagado,
                            'metodo_pago' => $venta->metodo_pago,
                            'origen' => 'migracion',
                            'fecha_pago' => $venta->fecha_venta,
                            'notas' => 'Abono inicial migrado desde ventas existentes.',
                            'created_at' => $venta->created_at ?? now(),
                            'updated_at' => $venta->updated_at ?? now(),
                        ];
                    }
                }

                if ($rows !== []) {
                    DB::table('mora_abonos')->insert($rows);
                }
            });

        DB::table('reparaciones')
            ->where('adelanto', '>', 0)
            ->orderBy('id')
            ->chunkById(100, function ($reparaciones) {
                $rows = [];

                foreach ($reparaciones as $reparacion) {
                    $rows[] = [
                        'mora_abonable_type' => Reparacion::class,
                        'mora_abonable_id' => $reparacion->id,
                        'cliente_id' => $reparacion->cliente_id,
                        'user_id' => $reparacion->user_id,
                        'tipo' => 'abono',
                        'monto' => (float) $reparacion->adelanto,
                        'metodo_pago' => 'inicial',
                        'origen' => 'migracion',
                        'fecha_pago' => $reparacion->fecha_recepcion,
                        'notas' => 'Adelanto inicial migrado desde reparaciones existentes.',
                        'created_at' => $reparacion->created_at ?? now(),
                        'updated_at' => $reparacion->updated_at ?? now(),
                    ];
                }

                if ($rows !== []) {
                    DB::table('mora_abonos')->insert($rows);
                }
            });
    }
};
