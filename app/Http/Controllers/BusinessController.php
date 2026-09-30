<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(Request $request): View
    {
        $businesses = $request->user()
            ->businesses()
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('businesses.index', [
            'businesses' => $businesses,
            'activeBusinessId' => $request->session()->get('active_business_id'),
        ]);
    }

    public function create(): View
    {
        return view('businesses.create');
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $business = $request->user()->businesses()->create($request->validated());
        $request->session()->put('active_business_id', $business->id);

        return redirect()
            ->route('businesses.index')
            ->with('status', 'Comercio creado correctamente.');
    }
}
