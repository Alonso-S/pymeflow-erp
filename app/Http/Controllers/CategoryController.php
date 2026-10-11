<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::withCount('products')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        $category = Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', "Categoría '{$category->name}' creada exitosamente.");
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', "Categoría '{$category->name}' actualizada correctamente.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Regla de integridad: No permitir eliminar categorías con productos asociados
        if ($category->products()->exists()) {
            return redirect()->back()->with('error', "No se puede eliminar la categoría '{$category->name}' porque contiene productos asociados. Desvincule o reasigne los productos primero.");
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "La categoría fue eliminada con éxito.");
    }
}
