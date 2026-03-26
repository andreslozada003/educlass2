@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('page-header')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">Mi Perfil</h1>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Información del Perfil -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-user mr-2 text-primary-600"></i>Información Personal
        </h3>
        
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <!-- Avatar -->
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center overflow-hidden">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-20 h-20 object-cover">
                        @else
                            <i class="fas fa-user text-3xl text-primary-600"></i>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto de perfil</label>
                        <input type="file" name="avatar" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>
                </div>
                
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <!-- Teléfono -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <!-- Rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <input type="text" value="{{ ucfirst(auth()->user()->getRoleNames()->first()) }}" disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500">
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Cambiar Contraseña -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-lock mr-2 text-primary-600"></i>Cambiar Contraseña
        </h3>
        
        <form action="{{ route('profile.password') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                    <input type="password" name="current_password" id="current_password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                    <input type="password" name="password" id="password" required minlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-gray-800 text-white py-2 px-4 rounded-lg hover:bg-gray-900 transition-colors">
                        <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
