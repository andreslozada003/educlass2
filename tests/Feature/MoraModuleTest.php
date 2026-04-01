<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Venta;
use App\Support\MoraSupport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MoraModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_debt_appears_in_mora_dashboard(): void
    {
        $user = $this->createUserWithSalesPermission();
        $venta = $this->createCreditSale($user);

        $response = $this
            ->actingAs($user)
            ->get(route('mora.index', ['tab' => 'ventas']));

        $response
            ->assertOk()
            ->assertSee('Modulo de mora', false)
            ->assertSee($venta->folio, false);
    }

    public function test_registering_a_payment_updates_the_sale_balance(): void
    {
        $user = $this->createUserWithSalesPermission();
        $venta = $this->createCreditSale($user);

        $response = $this
            ->actingAs($user)
            ->from(route('mora.ventas.show', $venta))
            ->post(route('mora.ventas.abonos.store', $venta), [
                'monto' => 200,
                'metodo_pago' => 'transferencia',
                'fecha_pago' => now()->format('Y-m-d'),
                'notas' => 'Abono de prueba',
            ]);

        $response->assertRedirect(route('mora.ventas.show', $venta));

        $venta->refresh();

        $this->assertSame(500.0, (float) $venta->monto_pagado);
        $this->assertSame(500.0, (float) $venta->saldo_pendiente_mora);
        $this->assertDatabaseHas('mora_abonos', [
            'mora_abonable_type' => Venta::class,
            'mora_abonable_id' => $venta->id,
            'monto' => 200,
            'metodo_pago' => 'transferencia',
        ]);
    }

    public function test_credit_installments_use_monthly_due_dates_from_base_date(): void
    {
        Carbon::setTestNow('2026-05-05 09:00:00');

        try {
            $user = $this->createUserWithSalesPermission();
            $venta = $this->createCreditSale($user, [
                'folio' => 'V-MORA-PLAN-001',
                'fecha_venta' => '2026-03-31 10:00:00',
                'fecha_inicio_mora' => '2026-03-31',
                'subtotal' => 1008.40,
                'impuestos' => 191.60,
                'total' => 1200,
                'monto_pagado' => 0,
                'pagado_con' => 0,
                'numero_cuotas' => 12,
                'plazo_acordado_dias' => 365,
            ]);

            $this->assertSame('2026-04-30', MoraSupport::saleCurrentDueDate($venta)?->format('Y-m-d'));
            $this->assertSame(5, $venta->dias_en_mora);
            $this->assertSame('verde', $venta->mora_semaforo);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_paid_installments_move_mora_to_the_next_unpaid_due_date(): void
    {
        Carbon::setTestNow('2026-07-03 09:00:00');

        try {
            $user = $this->createUserWithSalesPermission();
            $venta = $this->createCreditSale($user, [
                'folio' => 'V-MORA-PLAN-002',
                'fecha_venta' => '2026-03-31 10:00:00',
                'fecha_inicio_mora' => '2026-03-31',
                'subtotal' => 1008.40,
                'impuestos' => 191.60,
                'total' => 1200,
                'monto_pagado' => 200,
                'pagado_con' => 200,
                'numero_cuotas' => 12,
                'plazo_acordado_dias' => 365,
            ]);

            $summary = $venta->resumen_mora_credito;

            $this->assertSame(2, $summary['covered_installments']);
            $this->assertSame(3, $summary['current_installment_number']);
            $this->assertSame('2026-06-30', $summary['current_due_date']?->format('Y-m-d'));
            $this->assertSame(3, $venta->dias_en_mora);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_sale_detail_uses_due_month_by_default_and_accepts_calendar_navigation(): void
    {
        Carbon::setTestNow('2026-05-05 09:00:00');

        try {
            $user = $this->createUserWithSalesPermission();
            $venta = $this->createCreditSale($user, [
                'folio' => 'V-MORA-PLAN-003',
                'fecha_venta' => '2026-03-31 10:00:00',
                'fecha_inicio_mora' => '2026-03-31',
                'subtotal' => 1008.40,
                'impuestos' => 191.60,
                'total' => 1200,
                'monto_pagado' => 0,
                'pagado_con' => 0,
                'numero_cuotas' => 12,
                'plazo_acordado_dias' => 365,
            ]);

            $defaultResponse = $this
                ->actingAs($user)
                ->get(route('mora.ventas.show', $venta));

            $defaultResponse
                ->assertOk()
                ->assertViewHas('visibleMonth', fn ($month) => $month instanceof Carbon && $month->format('Y-m') === '2026-04');

            $nextResponse = $this
                ->actingAs($user)
                ->get(route('mora.ventas.show', $venta, false) . '?month=2026-05');

            $nextResponse
                ->assertOk()
                ->assertViewHas('visibleMonth', fn ($month) => $month instanceof Carbon && $month->format('Y-m') === '2026-05');
        } finally {
            Carbon::setTestNow();
        }
    }

    protected function createUserWithSalesPermission(): User
    {
        $permission = Permission::findOrCreate('ver ventas', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
    }

    protected function createCreditSale(User $user, array $overrides = []): Venta
    {
        $cliente = Cliente::create([
            'nombre' => 'Laura',
            'apellido' => 'Cobranza',
            'telefono' => '3001234567',
            'email' => 'laura@example.com',
            'activo' => true,
        ]);

        return Venta::create(array_merge([
            'folio' => 'V-MORA-001',
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'fecha_venta' => now()->subDays(12),
            'subtotal' => 840.34,
            'descuento' => 0,
            'impuestos' => 159.66,
            'total' => 1000,
            'monto_pagado' => 300,
            'metodo_pago' => 'credito',
            'pagado_con' => 300,
            'cambio' => 0,
            'estado' => 'credito',
            'fecha_inicio_mora' => now()->subDays(10)->toDateString(),
        ], $overrides));
    }
}
