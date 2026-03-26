<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Venta;
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

    protected function createUserWithSalesPermission(): User
    {
        $permission = Permission::findOrCreate('ver ventas', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
    }

    protected function createCreditSale(User $user): Venta
    {
        $cliente = Cliente::create([
            'nombre' => 'Laura',
            'apellido' => 'Cobranza',
            'telefono' => '3001234567',
            'email' => 'laura@example.com',
            'activo' => true,
        ]);

        return Venta::create([
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
        ]);
    }
}
