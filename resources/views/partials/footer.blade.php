<!-- Footer -->
<footer class="bg-white border-t border-gray-200 py-4 px-4 lg:px-6">
    <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-500">
        <div class="mb-2 md:mb-0">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'CellFix Pro') }}. Todos los derechos reservados.</p>
        </div>
        <div class="flex items-center space-x-4">
            <span>Versión 1.0.0</span>
            <span class="hidden md:inline">|</span>
            <span class="hidden md:inline">Laravel {{ app()->version() }}</span>
        </div>
    </div>
</footer>
