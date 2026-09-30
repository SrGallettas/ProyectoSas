<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $categories = $activeBusiness->categories()->withCount('products')->orderBy('name')->get();

        return view('categories.index', compact('activeBusiness', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');

        return view('categories.create', compact('activeBusiness'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $activeBusiness->categories()->create($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoría creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Request $request, Category $category): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');

        return view('categories.edit', compact('activeBusiness', 'category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Categoría eliminada correctamente.');
    }
}
