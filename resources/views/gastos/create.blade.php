@extends('layouts.app')

@section('title', 'Registrar gasto')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Registrar gasto</h1>
        <p class="text-gray-500">Controla egresos, comprobantes, responsables y pagos pendientes.</p>
    </div>
    <a href="{{ route('gastos.lista') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i class="fas fa-list mr-2"></i>Ver lista
    </a>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<form action="{{ route('gastos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @include('gastos.partials.form')

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-700">
            <i class="fas fa-save mr-2"></i>Guardar gasto
        </button>
        <a href="{{ route('gastos.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Cancelar
        </a>
    </div>
</form>
@endsection
