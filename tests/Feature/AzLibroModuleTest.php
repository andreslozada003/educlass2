<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AzLibroModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_az_libro_index_is_available_for_users_with_reports_permission(): void
    {
        $user = $this->createUserWithReportsPermission();

        $response = $this
            ->actingAs($user)
            ->get(route('az-libro.index'));

        $response
            ->assertOk()
            ->assertSee('AZ libro', false)
            ->assertSee('Clientes', false)
            ->assertSee('Descargar respaldo ZIP', false);
    }

    public function test_az_libro_can_export_clients_to_csv(): void
    {
        $user = $this->createUserWithReportsPermission();

        Cliente::create([
            'nombre' => 'Andrea',
            'apellido' => 'Exporta',
            'telefono' => '3000000000',
            'email' => 'andrea@example.com',
            'activo' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('az-libro.export', ['dataset' => 'clientes', 'format' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('.csv', $response->headers->get('content-disposition', ''));
    }

    public function test_az_libro_can_export_users_dataset_as_zip(): void
    {
        Storage::disk('public')->put('avatars/test-avatar.txt', 'avatar de prueba');

        $user = $this->createUserWithReportsPermission();
        $user->update(['avatar' => 'avatars/test-avatar.txt']);

        $response = $this
            ->actingAs($user)
            ->get(route('az-libro.export', ['dataset' => 'usuarios', 'format' => 'zip']));

        $response->assertOk();
        $this->assertStringContainsString('.zip', $response->headers->get('content-disposition', ''));
    }

    public function test_az_libro_can_generate_full_backup_zip(): void
    {
        $user = $this->createUserWithReportsPermission();

        $response = $this
            ->actingAs($user)
            ->get(route('az-libro.backup'));

        $response->assertOk();
        $this->assertStringContainsString('.zip', $response->headers->get('content-disposition', ''));
    }

    public function test_az_libro_can_export_attachments_to_pdf(): void
    {
        $jpg = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBUQEBIVFRAQFRUVFRUVFRUVFRUQFRUXFhUVFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGy0lICYtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAQMBEQACEQEDEQH/xAAXAAADAQAAAAAAAAAAAAAAAAAAAQID/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEAMQAAAB6gD/xAAZEAEBAQEBAQAAAAAAAAAAAAABAgMABAX/2gAIAQEAAQUCZ0J6srP/xAAVEQEBAAAAAAAAAAAAAAAAAAABAP/aAAgBAwEBPwF//8QAFBEBAAAAAAAAAAAAAAAAAAAAEP/aAAgBAgEBPwB//8QAGhABAQEBAQEBAAAAAAAAAAAAAREAITFBUf/aAAgBAQAGPwJm+2M0MZ//xAAaEAACAgMAAAAAAAAAAAAAAAABEQAhMUFR/9oACAEBAAE/IdVx9M6R4dEmR4j/2gAMAwEAAgADAAAAEC//xAAVEQEBAAAAAAAAAAAAAAAAAAABEP/aAAgBAwEBPxBf/8QAFBEBAAAAAAAAAAAAAAAAAAAAEP/aAAgBAgEBPxB//8QAGhABAAIDAQAAAAAAAAAAAAAAAQARITFBYf/aAAgBAQABPxBfFdbCMryGZGsS2fy4VBf/2Q=='
        );

        Storage::disk('public')->put('avatars/pdf-preview.jpg', $jpg);

        $user = $this->createUserWithReportsPermission();
        $user->update(['avatar' => 'avatars/pdf-preview.jpg']);

        $response = $this
            ->actingAs($user)
            ->get(route('az-libro.export', ['dataset' => 'archivos-adjuntos', 'format' => 'pdf']));

        $response->assertOk();
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition', ''));
    }

    protected function createUserWithReportsPermission(): User
    {
        $permission = Permission::findOrCreate('ver reportes', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
    }
}
