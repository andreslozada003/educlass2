@extends('layouts.app')

@section('title', 'Nuevo proveedor')

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-800">Nuevo proveedor</h1>
    <p class="text-gray-500">Registra nombre, contacto, NIT, datos de ubicacion y categoria frecuente.</p>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<form action="{{ route('gastos.proveedores.store') }}" method="POST" class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @include('gastos.proveedores.form-fields')
    <div class="flex gap-3">
        <button type="submit" class="rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-700">Guardar proveedor</button>
        <a href="{{ route('gastos.proveedores.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
    </div>
</form>
@endsection
