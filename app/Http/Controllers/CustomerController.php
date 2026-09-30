<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Business;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $customers = $activeBusiness->customers()
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20);

        return view('customers.index', compact('activeBusiness', 'customers'));
    }

    public function create(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');

        return view('customers.create', compact('activeBusiness'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $activeBusiness->customers()->create($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('status', 'Cliente creado correctamente.');
    }

    public function edit(Request $request, Customer $customer): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');

        return view('customers.edit', compact('activeBusiness', 'customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('status', 'Cliente actualizado correctamente.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('status', 'Cliente eliminado correctamente.');
    }
}
