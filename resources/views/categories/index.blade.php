@extends('layouts.app', ['title' => 'Gestión de Categorías', 'header' => 'Catálogo / Categorías'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Categorías de Productos</h2>
            <p class="text-xs text-slate-500">Clasificación taxonómica de los artículos del inventario</p>
        </div>
        <button type="button" onclick="openCreateModal()" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            + Nueva Categoría
        </button>
    </div>

    <!-- Search input -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('categories.index') }}" class="flex items-center space-x-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre de categoría..."
                   class="w-full max-w-sm px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                Buscar
            </button>
            @if(request('search'))
            <a href="{{ route('categories.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                Limpiar
            </a>
            @endif
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Categoría</th>
                        <th class="px-5 py-3.5">Slug</th>
                        <th class="px-5 py-3.5">Descripción</th>
                        <th class="px-5 py-3.5 text-center">Productos Vinculados</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-4 font-bold text-slate-900">
                            {{ $category->name }}
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-500">
                            {{ $category->slug }}
                        </td>
                        <td class="px-5 py-4 text-slate-600 max-w-xs truncate">
                            {{ $category->description ?? 'Sin descripción' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $category->products_count > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $category->products_count }} {{ Str::plural('producto', $category->products_count) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right space-x-2">
                            <button type="button" onclick="openEditModal({{ json_encode($category) }})"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 text-xs font-semibold transition">
                                Editar
                            </button>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Está seguro de eliminar esta categoría?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 transition"
                                        {{ $category->products_count > 0 ? 'title=Contiene_productos_asociados' : '' }}>
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                            No se encontraron categorías registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Crear Categoría -->
<div id="createCategoryModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-scale-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Nueva Categoría</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form action="{{ route('categories.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label for="cat_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre <span class="text-rose-500">*</span></label>
                <input type="text" id="cat_name" name="name" required placeholder="Ej. Accesorios y Periféricos"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <div>
                <label for="cat_desc" class="block text-xs font-bold text-slate-700 uppercase mb-1">Descripción</label>
                <textarea id="cat_desc" name="description" rows="3" placeholder="Detalle de la categoría..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancelar</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Categoría -->
<div id="editCategoryModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-scale-in">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Editar Categoría</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="editCategoryForm" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_cat_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_cat_name" name="name" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <div>
                <label for="edit_cat_desc" class="block text-xs font-bold text-slate-700 uppercase mb-1">Descripción</label>
                <textarea id="edit_cat_desc" name="description" rows="3"
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancelar</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createCategoryModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createCategoryModal').classList.add('hidden');
    }
    function openEditModal(category) {
        document.getElementById('editCategoryForm').action = `/categories/${category.id}`;
        document.getElementById('edit_cat_name').value = category.name;
        document.getElementById('edit_cat_desc').value = category.description || '';
        document.getElementById('editCategoryModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editCategoryModal').classList.add('hidden');
    }
</script>
@endsection
