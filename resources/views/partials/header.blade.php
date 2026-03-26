<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="flex items-center justify-between h-16 px-4 lg:px-6">
        <!-- Left -->
        <div class="flex items-center">
            <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <!-- Breadcrumbs -->
            <nav class="hidden md:flex ml-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-primary-600">
                            <i class="fas fa-home"></i>
                        </a>
                    </li>
                    @yield('breadcrumbs')
                </ol>
            </nav>
        </div>
        
        <!-- Right -->
        <div class="flex items-center space-x-4">
            <!-- Notificaciones -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button type="button" @click="open = !open" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-xl"></i>
                    <span id="notificacion-count" class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full hidden">
                        0
                    </span>
                </button>
                
                <!-- Dropdown Notificaciones -->
                <div x-show="open" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50" style="display: none;">
                    <div class="p-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700">Notificaciones</h3>
                    </div>
                    <div id="notificaciones-list" class="max-h-64 overflow-y-auto">
                        <p class="p-4 text-sm text-gray-500 text-center">Cargando...</p>
                    </div>
                    <div class="p-2 border-t border-gray-200">
                        <button type="button" onclick="marcarTodasLeidas()" class="w-full text-center text-sm text-primary-600 hover:text-primary-700">
                            Marcar todas como leídas
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Stock Bajo Alerta -->
            @php
                $stockBajoCount = \App\Models\Producto::stockBajo()->count();
            @endphp
            @if($stockBajoCount > 0)
            <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="relative p-2 rounded-lg text-amber-500 hover:bg-amber-50 transition-colors" title="Productos con stock bajo">
                <i class="fas fa-exclamation-triangle text-xl"></i>
                <span class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-amber-500 rounded-full">
                    {{ $stockBajoCount }}
                </span>
            </a>
            @endif
            
            <!-- Reparaciones Listas -->
            @php
                $reparacionesListas = \App\Models\Reparacion::listas()->count();
            @endphp
            @if($reparacionesListas > 0)
            <a href="{{ route('reparaciones.index', ['estado' => 'listo']) }}" class="relative p-2 rounded-lg text-green-500 hover:bg-green-50 transition-colors" title="Reparaciones listas">
                <i class="fas fa-check-circle text-xl"></i>
                <span class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-green-500 rounded-full">
                    {{ $reparacionesListas }}
                </span>
            </a>
            @endif
            
            <!-- User Menu -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button type="button" @click="open = !open" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                        @else
                            <i class="fas fa-user text-sm"></i>
                        @endif
                    </div>
                    <span class="hidden md:block text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                </button>
                
                <!-- Dropdown -->
                <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50" style="display: none;">
                    <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-user mr-2"></i> Mi Perfil
                    </a>
                    <div class="border-t border-gray-200"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
const notificationColors = {
    gray: { bg: '#f3f4f6', text: '#4b5563' },
    yellow: { bg: '#fef3c7', text: '#ca8a04' },
    orange: { bg: '#ffedd5', text: '#ea580c' },
    blue: { bg: '#dbeafe', text: '#2563eb' },
    indigo: { bg: '#e0e7ff', text: '#4f46e5' },
    green: { bg: '#dcfce7', text: '#16a34a' },
    emerald: { bg: '#d1fae5', text: '#059669' },
    red: { bg: '#fee2e2', text: '#dc2626' },
};

// Cargar notificaciones
function cargarNotificaciones() {
    const countBadge = document.getElementById('notificacion-count');
    const listContainer = document.getElementById('notificaciones-list');

    if (!countBadge || !listContainer) {
        return;
    }

    fetch('{{ route("dashboard.notificaciones") }}', {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('No se pudieron cargar las notificaciones.');
            }

            return response.json();
        })
        .then(data => {
            if (data.count > 0) {
                countBadge.textContent = data.count;
                countBadge.classList.remove('hidden');
            } else {
                countBadge.classList.add('hidden');
            }
            
            if (!Array.isArray(data.notificaciones) || data.notificaciones.length === 0) {
                listContainer.innerHTML = '<p class="p-4 text-sm text-gray-500 text-center">No hay notificaciones</p>';
            } else {
                listContainer.innerHTML = data.notificaciones.map(n => {
                    const palette = notificationColors[n.color] ?? notificationColors.blue;

                    return `
                        <div class="p-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer" onclick="marcarLeida(${n.id})">
                            <div class="flex items-start space-x-2">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full" style="background-color: ${palette.bg}; color: ${palette.text};">
                                        <i class="fas fa-${n.icono}"></i>
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">${n.titulo}</p>
                                    <p class="text-xs text-gray-500 truncate">${n.mensaje}</p>
                                    <p class="text-xs text-gray-400 mt-1">${new Date(n.created_at).toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        })
        .catch(error => {
            console.error(error);
            listContainer.innerHTML = '<p class="p-4 text-sm text-red-500 text-center">No se pudieron cargar las notificaciones</p>';
        });
}

// Marcar notificación como leída
function marcarLeida(id) {
    fetch(`{{ url('dashboard/notificaciones') }}/${id}/leida`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    }).then(() => {
        cargarNotificaciones();
    });
}

// Marcar todas como leídas
function marcarTodasLeidas() {
    fetch('{{ route("dashboard.notificaciones") }}', {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            const promises = (data.notificaciones || []).map(n => 
                fetch(`{{ url('dashboard/notificaciones') }}/${n.id}/leida`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
            );
            return Promise.all(promises);
        })
        .then(() => {
            cargarNotificaciones();
        })
        .catch(error => {
            console.error(error);
        });
}

if (!window.notificationPollingInitialized) {
    window.notificationPollingInitialized = true;

    document.addEventListener('DOMContentLoaded', () => {
        cargarNotificaciones();
    }, { once: true });

    window.notificationPollingInterval = window.setInterval(() => {
        if (document.visibilityState === 'visible') {
            cargarNotificaciones();
        }
    }, 30000);
}
</script>
