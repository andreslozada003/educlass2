<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
        <input type="text" name="name" value="{{ old('name', $categoria->name ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" required>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Categoria padre</label>
        <select name="parent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Categoria principal</option>
            @foreach($padres as $padre)
            <option value="{{ $padre->id }}" {{ old('parent_id', $categoria->parent_id ?? '') == $padre->id ? 'selected' : '' }}>{{ $padre->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Grupo</label>
        <select name="expense_group" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Selecciona...</option>
            @foreach($grupos as $value => $label)
            <option value="{{ $value }}" {{ old('expense_group', $categoria->expense_group ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Presupuesto mensual</label>
        <input type="number" step="0.01" min="0" name="monthly_budget" value="{{ old('monthly_budget', $categoria->monthly_budget ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Color</label>
        <select name="color" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Selecciona...</option>
            @foreach($colores as $color)
            <option value="{{ $color }}" {{ old('color', $categoria->color ?? '') === $color ? 'selected' : '' }}>{{ $color }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Icono</label>
        <select name="icon" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Selecciona...</option>
            @foreach($iconos as $icono)
            <option value="{{ $icono }}" {{ old('icon', $categoria->icon ?? '') === $icono ? 'selected' : '' }}>{{ $icono }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700">Descripcion</label>
        <textarea name="description" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('description', $categoria->description ?? '') }}</textarea>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <label class="flex items-center space-x-3 rounded-lg border border-gray-200 px-4 py-3">
        <input type="checkbox" name="requires_approval" value="1" {{ old('requires_approval', $categoria->requires_approval ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
        <span class="text-sm text-gray-700">Requiere aprobacion</span>
    </label>
    <label class="flex items-center space-x-3 rounded-lg border border-gray-200 px-4 py-3">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($categoria) ? $categoria->is_active : true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
        <span class="text-sm text-gray-700">Categoria activa</span>
    </label>
</div>
