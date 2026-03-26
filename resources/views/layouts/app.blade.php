<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'CellFix Pro') }}</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* Animaciones */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        @include('partials.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64 transition-all duration-300">
            <!-- Header -->
            @include('partials.header')
            
            <!-- Main -->
            <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
                <!-- Flash Messages -->
                @include('partials.flash-messages')
                
                <!-- Page Header -->
                @hasSection('page-header')
                    <div class="mb-6">
                        @yield('page-header')
                    </div>
                @endif
                
                <!-- Content -->
                @yield('content')
            </main>
            
            <!-- Footer -->
            @include('partials.footer')
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @include('partials.access-denied-modal')
    
    <script>
        // CSRF Token para peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Toggle sidebar en móvil
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });
        
        // Cerrar sidebar al hacer click fuera en móvil
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebar-toggle');
            
            if (window.innerWidth < 1024 && 
                sidebar && 
                !sidebar.contains(e.target) && 
                toggle && 
                !toggle.contains(e.target) &&
                !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
        });
        
        // Confirmar acciones destructivas
        document.querySelectorAll('[data-confirm]').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm(this.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
        
        // Auto-cerrar alertas después de 5 segundos
        setTimeout(() => {
            document.querySelectorAll('.alert-auto-close').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        // Formatear moneda
        function formatCurrency(amount, decimals = 0) {
            const value = Number(amount ?? 0);
            const sign = value < 0 ? '-' : '';
            const formatted = new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(Math.abs(value));

            return `${sign}$ ${formatted}`;
        }
        
        // Formatear fecha
        function formatDate(date) {
            return new Intl.DateTimeFormat('es-MX', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }).format(new Date(date));
        }
    </script>
    
    @stack('scripts')
</body>
</html>
