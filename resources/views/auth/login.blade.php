<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión | {{ config('app.name', 'CellFix Pro') }}</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen flex items-center justify-center bg-pattern">
    <div class="w-full max-w-5xl mx-4">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">
            <!-- Left Side - Branding -->
            <div class="lg:w-1/2 bg-gradient-to-br from-primary-600 to-primary-800 p-8 lg:p-12 flex flex-col justify-between text-white relative overflow-hidden">
                <!-- Decorative circles -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-mobile-alt text-2xl"></i>
                        </div>
                        <span class="text-2xl font-bold">Tecno Star</span>
                    </div>
                    
                    <h1 class="text-3xl lg:text-4xl font-bold mb-4 animate-float">
                        Sistema de Gestión
                    </h1>
                    <p class="text-primary-100 text-lg mb-8">
                        Ventas, reparaciones y control de inventario para tu negocio de celulares.
                    </p>
                </div>
                
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Punto de Venta</p>
                            <p class="text-sm text-primary-200">Ventas rápidas y eficientes</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Control de Reparaciones</p>
                            <p class="text-sm text-primary-200">Seguimiento completo</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Inventario Inteligente</p>
                            <p class="text-sm text-primary-200">Alertas de stock bajo</p>
                        </div>
                    </div>
                </div>
                
                <div class="relative z-10 mt-8 text-sm text-primary-200">
                    <p>&copy; {{ date('Y') }} CellFix Pro. Todos los derechos reservados.</p>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="lg:w-1/2 p-8 lg:p-12">
                <div class="max-w-md mx-auto">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Bienvenido de nuevo</h2>
                    <p class="text-gray-500 mb-8">Ingresa tus credenciales para continuar</p>
                    
                    @if(session('status'))
                        <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    <form action="{{ route('login') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Correo Electrónico
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email') }}"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('email') border-red-500 @enderror"
                                    placeholder="tu@email.com"
                                    required
                                    autofocus
                                >
                            </div>
                            @error('email')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('password') border-red-500 @enderror"
                                    placeholder="••••••••"
                                    required
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <i class="fas fa-eye" id="eye-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="remember" 
                                    name="remember"
                                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                                >
                                <label for="remember" class="ml-2 text-sm text-gray-600">
                                    Recordarme
                                </label>
                            </div>
                            <a href="{{ route('password.request') }}" class="text-sm text-primary-600 hover:text-primary-700">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                        >
                            Iniciar Sesión
                        </button>
                    </form>
                    
                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-500">
                            ¿No tienes cuenta? Contacta al administrador
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
