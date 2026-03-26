@php
    $accessDeniedModal = session('access_denied_modal');
@endphp

@if($accessDeniedModal)
    <div id="access-denied-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 px-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-lock text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ $accessDeniedModal['title'] ?? 'Acceso restringido' }}
                        </h3>
                        <p class="text-sm text-slate-600">Permiso requerido del administrador</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm leading-6 text-slate-700">
                    {{ $accessDeniedModal['message'] ?? 'No tienes acceso para continuar con esta accion.' }}
                </p>

                @if(!empty($accessDeniedModal['detail']))
                    <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        {{ $accessDeniedModal['detail'] }}
                    </p>
                @endif
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" data-close-access-denied class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                    Entendido
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('access-denied-modal');
            const closeButton = document.querySelector('[data-close-access-denied]');

            if (!modal) {
                return;
            }

            const closeModal = () => {
                modal.remove();
                document.body.classList.remove('overflow-hidden');
            };

            document.body.classList.add('overflow-hidden');

            closeButton?.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            }, { once: true });
        });
    </script>
@endif
