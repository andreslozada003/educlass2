<!-- Flash Messages -->
@if(session('success'))
    <div class="alert-auto-close mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center justify-between fade-in">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3 text-green-500"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert-auto-close mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center justify-between fade-in">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('warning'))
    <div class="alert-auto-close mb-4 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-between fade-in">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-amber-500"></i>
            <span>{{ session('warning') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('info'))
    <div class="alert-auto-close mb-4 p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-between fade-in">
        <div class="flex items-center">
            <i class="fas fa-info-circle mr-3 text-blue-500"></i>
            <span>{{ session('info') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

<!-- Validation Errors -->
@if($errors->any())
    <div class="alert-auto-close mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 fade-in">
        <div class="flex items-center mb-2">
            <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
            <span class="font-semibold">Por favor corrige los siguientes errores:</span>
        </div>
        <ul class="ml-6 list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
