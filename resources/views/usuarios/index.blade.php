@extends('layouts.app')

@section('title', 'Usuarios')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Usuarios</h1>
        <p class="text-gray-500">Gestión de usuarios del sistema</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('usuarios.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Nuevo Usuario
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Filtros -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <form action="{{ route('usuarios.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Buscar por nombre o email...">
        </div>
        <div>
            <select name="rol" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Todos los roles</option>
                @foreach($roles as $rol)
                <option value="{{ $rol->name }}" {{ request('rol') == $rol->name ? 'selected' : '' }}>{{ ucfirst($rol->name) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="inactivos" value="1" {{ request('inactivos') ? 'checked' : '' }} class="mr-2">
                <span class="text-sm">Mostrar inactivos</span>
            </label>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-search"></i>
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($usuarios as $usuario)
                <tr class="hover:bg-gray-50 {{ !$usuario->is_active ? 'bg-gray-100' : '' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center mr-3">
                                @if($usuario->avatar)
                                    <img src="{{ asset('storage/' . $usuario->avatar) }}" alt="" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <span class="text-primary-600 font-bold">{{ substr($usuario->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $usuario->name }}</p>
                                <p class="text-xs text-gray-500">{{ $usuario->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $usuario->isAdmin() ? 'bg-red-100 text-red-700' : ($usuario->isTecnico() ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                            {{ ucfirst($usuario->getRoleNames()->first()) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $usuario->phone ?: 'N/A' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $usuario->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $usuario->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('usuarios.show', $usuario) }}" class="text-blue-600 hover:text-blue-700" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="text-amber-600 hover:text-amber-700" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($usuario->id !== auth()->id())
                                @if($usuario->is_active)
                                <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="inline" onsubmit="return confirm('¿Desactivar este usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" title="Desactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @else
                                <a href="{{ route('usuarios.activar', $usuario) }}" class="text-green-600 hover:text-green-700" title="Activar">
                                    <i class="fas fa-check"></i>
                                </a>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-3 text-gray-300"></i>
                        <p>No hay usuarios registrados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $usuarios->withQueryString()->links() }}
    </div>
</div>
@endsection
