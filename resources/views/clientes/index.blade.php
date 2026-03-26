@extends('layouts.app')

@section('title', 'Clientes')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Clientes</h1>
        <p class="text-gray-500">Gestión de clientes</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('clientes.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Cliente
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Filtros -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <form action="{{ route('clientes.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Buscar por nombre, teléfono o email...">
        </div>
        <div class="flex gap-2">
            <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="inactivos" value="1" {{ request('inactivos') ? 'checked' : '' }} class="mr-2">
                <span class="text-sm">Mostrar inactivos</span>
            </label>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Compras</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reparaciones</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clientes as $cliente)
                <tr class="hover:bg-gray-50 {{ !$cliente->activo ? 'bg-gray-100' : '' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center mr-3">
                                <span class="text-primary-600 font-bold">{{ substr($cliente->nombre, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $cliente->nombre_completo }}</p>
                                @if($cliente->rfc)
                                <p class="text-xs text-gray-500">RFC: {{ $cliente->rfc }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-700">
                            @if($cliente->telefono)
                            <p><i class="fas fa-phone mr-1 text-gray-400"></i> {{ $cliente->telefono }}</p>
                            @endif
                            @if($cliente->email)
                            <p><i class="fas fa-envelope mr-1 text-gray-400"></i> {{ $cliente->email }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium">{{ money($cliente->total_gastado) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm">{{ $cliente->total_reparaciones }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $cliente->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('clientes.show', $cliente) }}" class="text-blue-600 hover:text-blue-700" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('clientes.edit', $cliente) }}" class="text-amber-600 hover:text-amber-700" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($cliente->telefono)
                            <a href="{{ $cliente->whatsapp_link }}" target="_blank" class="text-green-600 hover:text-green-700" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            @endif
                            @if($cliente->activo)
                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de desactivar este cliente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700" title="Desactivar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <a href="{{ route('clientes.activar', $cliente) }}" class="text-green-600 hover:text-green-700" title="Activar">
                                <i class="fas fa-check"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-3 text-gray-300"></i>
                        <p>No hay clientes registrados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $clientes->withQueryString()->links() }}
    </div>
</div>
@endsection
