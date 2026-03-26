<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Nombre o empresa</label>
        <input type="text" name="name" value="{{ old('name', $proveedor->name ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" required>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Contacto</label>
        <input type="text" name="contact_name" value="{{ old('contact_name', $proveedor->contact_name ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">NIT o documento</label>
        <input type="text" name="nit_rut" value="{{ old('nit_rut', $proveedor->nit_rut ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Correo</label>
        <input type="email" name="email" value="{{ old('email', $proveedor->email ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Telefono principal</label>
        <input type="text" name="phone" value="{{ old('phone', $proveedor->phone ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Telefono secundario</label>
        <input type="text" name="phone_secondary" value="{{ old('phone_secondary', $proveedor->phone_secondary ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Ciudad</label>
        <input type="text" name="city" value="{{ old('city', $proveedor->city ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Categoria frecuente</label>
        <select name="frequent_category_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Selecciona...</option>
            @foreach($categorias as $categoriaItem)
            <option value="{{ $categoriaItem->id }}" {{ old('frequent_category_id', $proveedor->frequent_category_id ?? '') == $categoriaItem->id ? 'selected' : '' }}>{{ $categoriaItem->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Direccion</label>
        <input type="text" name="address" value="{{ old('address', $proveedor->address ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Sitio web</label>
        <input type="text" name="website" value="{{ old('website', $proveedor->website ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Observaciones</label>
        <textarea name="notes" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('notes', $proveedor->notes ?? '') }}</textarea>
    </div>
</div>

<label class="flex items-center space-x-3 rounded-lg border border-gray-200 px-4 py-3">
    <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($proveedor) ? $proveedor->is_active : true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
    <span class="text-sm text-gray-700">Proveedor activo</span>
</label>
