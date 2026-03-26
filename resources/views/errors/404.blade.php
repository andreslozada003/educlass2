@extends('layouts.app')

@section('title', 'Página no encontrada')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="text-center">
        <div class="mb-8">
            <i class="fas fa-exclamation-circle text-9xl text-gray-300"></i>
        </div>
        <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-600 mb-4">Página no encontrada</h2>
        <p class="text-gray-500 mb-8">La página que estás buscando no existe o ha sido movida.</p>
        <div class="space-x-4">
            <a href="{{ route('dashboard') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-home mr-2"></i>Ir al Dashboard
            </a>
            <button onclick="history.back()" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver atrás
            </button>
        </div>
    </div>
</div>
@endsection