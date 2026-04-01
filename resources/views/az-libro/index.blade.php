@extends('layouts.app')

@section('title', 'AZ libro')

@section('content')
<div class="mx-auto max-w-7xl space-y-8 px-4 py-6">
    <section class="overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 text-white shadow-xl">
        <div class="grid gap-6 px-6 py-8 lg:grid-cols-[1.4fr,0.8fr] lg:px-8">
            <div>
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-slate-100">
                    Centro de confianza
                </span>
                <h1 class="mt-4 text-3xl font-bold tracking-tight lg:text-4xl">AZ libro</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-200 lg:text-base">
                    Exporta la informacion mas sensible del negocio en un solo lugar. Cada bloque permite descargar tablas en Excel, CSV y PDF, y tambien un ZIP con manifiesto y adjuntos reales cuando existen.
                </p>
                <div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-200">
                    <span class="rounded-full bg-white/10 px-3 py-1">Clientes</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Inventario</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Ventas</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Facturacion</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Gastos</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Reparaciones</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Mora</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Usuarios</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Reportes</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Adjuntos</span>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                <p class="text-sm font-semibold text-white">Respaldo completo</p>
                <p class="mt-2 text-sm leading-6 text-slate-200">
                    Descarga todos los datasets del modulo y empaqueta los archivos disponibles en un solo ZIP para auditoria, migracion o respaldo externo.
                </p>
                <a
                    href="{{ route('az-libro.backup') }}"
                    class="mt-5 inline-flex items-center justify-center rounded-xl bg-emerald-400 px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-emerald-300"
                >
                    <i class="fas fa-box-archive mr-2"></i>Descargar respaldo ZIP
                </a>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-black/20 p-3">
                        <p class="text-slate-300">Formatos</p>
                        <p class="mt-1 font-semibold text-white">Excel, CSV, PDF, ZIP</p>
                    </div>
                    <div class="rounded-xl bg-black/20 p-3">
                        <p class="text-slate-300">Uso recomendado</p>
                        <p class="mt-1 font-semibold text-white">Soporte y auditoria</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
        @foreach($datasets as $dataset)
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <div class="bg-gradient-to-r {{ $dataset['accent'] }} px-6 py-5 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/80">AZ libro</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $dataset['name'] }}</h2>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-2xl">
                        <i class="fas {{ $dataset['icon'] }}"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-5 px-6 py-6">
                <p class="text-sm leading-6 text-slate-600">{{ $dataset['description'] }}</p>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registros</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($dataset['records']) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Adjuntos</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($dataset['attachments']) }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('az-libro.export', ['dataset' => $dataset['key'], 'format' => 'excel']) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        <i class="fas fa-file-excel mr-2"></i>Excel
                    </a>
                    <a href="{{ route('az-libro.export', ['dataset' => $dataset['key'], 'format' => 'csv']) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
                        <i class="fas fa-file-csv mr-2"></i>CSV
                    </a>
                    <a href="{{ route('az-libro.export', ['dataset' => $dataset['key'], 'format' => 'pdf']) }}" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                        <i class="fas fa-file-pdf mr-2"></i>PDF
                    </a>
                    <a href="{{ route('az-libro.export', ['dataset' => $dataset['key'], 'format' => 'zip']) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                        <i class="fas fa-file-zipper mr-2"></i>ZIP
                    </a>
                </div>

                <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-500">
                    El ZIP incluye un manifiesto CSV y, cuando el modulo tiene evidencia asociada, los archivos reales listos para respaldo.
                </p>
            </div>
        </article>
        @endforeach
    </section>
</div>
@endsection
