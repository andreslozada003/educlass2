@php
    $selectedCategory = old('category_id', $gasto->category_id ?? '');
    $selectedSubcategory = old('subcategory_id', $gasto->subcategory_id ?? '');
    $selectedSupplier = old('supplier_id', $gasto->supplier_id ?? '');
    $isRecurring = old('is_recurring', isset($gasto) && $gasto ? $gasto->is_recurring : false);
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Informacion general</h2>
            <p class="text-sm text-gray-500">Registra salidas de dinero, comprobantes, recurrencia y responsables.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Fecha</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', isset($gasto) && $gasto?->expense_date ? $gasto->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Fecha de vencimiento</label>
                <input type="date" name="due_date" value="{{ old('due_date', isset($gasto) && $gasto?->due_date ? $gasto->due_date->format('Y-m-d') : '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tipo de gasto</label>
                <select name="expense_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" required>
                    @foreach($tiposGasto as $value => $label)
                    <option value="{{ $value }}" {{ old('expense_type', $gasto->expense_type ?? 'variable') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Monto</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', isset($gasto) ? $gasto->amount : '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="0.00" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Categoria</label>
                <select name="category_id" id="category_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" required>
                    <option value="">Selecciona...</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ (string) $selectedCategory === (string) $categoria->id ? 'selected' : '' }}>{{ $categoria->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Subcategoria</label>
                <select name="subcategory_id" id="subcategory_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Sin subcategoria</option>
                    @foreach($categorias as $categoria)
                        @foreach($categoria->children as $subcategoria)
                        <option value="{{ $subcategoria->id }}" data-parent="{{ $categoria->id }}" {{ (string) $selectedSubcategory === (string) $subcategoria->id ? 'selected' : '' }}>
                            {{ $subcategoria->name }}
                        </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Descripcion</label>
                <input type="text" name="description" value="{{ old('description', $gasto->description ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="Ej. Pago de alquiler marzo" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Metodo de pago</label>
                <select name="payment_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" required>
                    @foreach($metodosPago as $value => $label)
                    <option value="{{ $value }}" {{ old('payment_method', $gasto->payment_method ?? 'cash') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Caja o cuenta</label>
                <select name="payment_source" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Selecciona...</option>
                    @foreach($fuentesPago as $value => $label)
                    <option value="{{ $value }}" {{ old('payment_source', $gasto->payment_source ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Estado del pago</label>
                <select name="payment_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" required>
                    @foreach($estadosPago as $value => $label)
                    <option value="{{ $value }}" {{ old('payment_status', $gasto->payment_status ?? 'pending') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Valor pagado</label>
                <input type="number" step="0.01" min="0" name="paid_amount" value="{{ old('paid_amount', isset($gasto) ? $gasto->paid_amount : '0') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="0.00">
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Control administrativo</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Proveedor</label>
                    <select name="supplier_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                        <option value="">Sin proveedor</option>
                        @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}" {{ (string) $selectedSupplier === (string) $proveedor->id ? 'selected' : '' }}>
                            {{ $proveedor->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Responsable</label>
                    <select name="responsible_user_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                        <option value="">Selecciona...</option>
                        @foreach($responsables as $responsable)
                        <option value="{{ $responsable->id }}" {{ old('responsible_user_id', $gasto->responsible_user_id ?? auth()->id()) == $responsable->id ? 'selected' : '' }}>
                            {{ $responsable->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Sucursal</label>
                    <input type="text" name="branch_name" value="{{ old('branch_name', $gasto->branch_name ?? 'Principal') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="Principal">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Factura o recibo</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $gasto->invoice_number ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="FAC-00123">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Numero de comprobante</label>
                    <input type="text" name="receipt_number" value="{{ old('receipt_number', $gasto->receipt_number ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="REC-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Referencia</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number', $gasto->reference_number ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="Transferencia / cheque">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Comprobante</label>
                    <input type="file" name="receipt_image" accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @if(isset($gasto) && $gasto?->receipt_image)
                    <a href="{{ asset('storage/' . $gasto->receipt_image) }}" target="_blank" class="mt-2 inline-flex text-sm font-medium text-primary-600 hover:text-primary-700">
                        Ver comprobante actual
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Recurrencia y notas</h2>
            <div class="space-y-4">
                <label class="flex items-center space-x-3 rounded-lg border border-gray-200 px-3 py-2">
                    <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ $isRecurring ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Este gasto es recurrente</span>
                </label>
                <div id="recurring_fields" class="{{ $isRecurring ? '' : 'hidden' }} space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Frecuencia</label>
                        <select name="recurring_period" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="">Selecciona...</option>
                            @foreach($periodosRecurrentes as $value => $label)
                            <option value="{{ $value }}" {{ old('recurring_period', $gasto->recurring_period ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Proximo vencimiento</label>
                        <input type="date" name="next_due_date" value="{{ old('next_due_date', isset($gasto) && $gasto?->next_due_date ? $gasto->next_due_date->format('Y-m-d') : '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Fecha pagado</label>
                    <input type="date" name="paid_date" value="{{ old('paid_date', isset($gasto) && $gasto?->paid_date ? $gasto->paid_date->format('Y-m-d') : '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea name="notes" rows="5" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="Detalles, soporte, observaciones internas...">{{ old('notes', $gasto->notes ?? '') }}</textarea>
                </div>
                @if(isset($gasto) && $gasto)
                <label class="flex items-center space-x-3 rounded-lg border border-gray-200 px-3 py-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $gasto->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Registro activo</span>
                </label>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');
        const recurringCheckbox = document.getElementById('is_recurring');
        const recurringFields = document.getElementById('recurring_fields');

        function syncSubcategories() {
            const categoryId = categorySelect.value;
            Array.from(subcategorySelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const visible = option.dataset.parent === categoryId;
                option.hidden = !visible;

                if (!visible && option.selected) {
                    subcategorySelect.value = '';
                }
            });
        }

        function syncRecurringFields() {
            recurringFields.classList.toggle('hidden', !recurringCheckbox.checked);
        }

        categorySelect.addEventListener('change', syncSubcategories);
        recurringCheckbox.addEventListener('change', syncRecurringFields);

        syncSubcategories();
        syncRecurringFields();
    });
</script>
@endpush
