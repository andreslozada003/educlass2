@extends('layouts.app')

@section('title', 'Editar gasto')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Editar gasto</h1>
        <p class="text-gray-500">Ajusta montos, fechas, comprobantes y estado del pago.</p>
    </div>
    <a href="{{ route('gastos.show', $gasto) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i class="fas fa-eye mr-2"></i>Ver detalle
    </a>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<form action="{{ route('gastos.update', $gasto) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    @include('gastos.partials.form')

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-700">
            <i class="fas fa-save mr-2"></i>Actualizar gasto
        </button>
        <a href="{{ route('gastos.show', $gasto) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Cancelar
        </a>
    </div>
</form>
@endsection
