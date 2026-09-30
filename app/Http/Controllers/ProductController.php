<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $products = $activeBusiness->products()
            ->with('category')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20);

        return view('products.index', compact('activeBusiness', 'products'));
    }

    public function create(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $categories = $activeBusiness->categories()->orderBy('name')->get();

        return view('products.create', compact('activeBusiness', 'categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $activeBusiness->products()->create($data);

        return redirect()
            ->route('products.index')
            ->with('status', 'Producto creado correctamente.');
    }

    public function edit(Request $request, Product $product): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $categories = $activeBusiness->categories()->orderBy('name')->get();

        return view('products.edit', compact('activeBusiness', 'categories', 'product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $oldImagePath = $product->image_path;
            $data['image_path'] = $request->file('image')->store('products', 'public');
            $product->update($data);

            if ($oldImagePath !== null) {
                Storage::disk('public')->delete($oldImagePath);
            }
        } else {
            $product->update($data);
        }

        return redirect()
            ->route('products.index')
            ->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $imagePath = $product->image_path;
        $product->delete();

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()
            ->route('products.index')
            ->with('status', 'Producto eliminado correctamente.');
    }
}
