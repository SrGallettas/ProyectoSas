<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActiveBusinessController extends Controller
{
    public function __invoke(Request $request, int $business): RedirectResponse
    {
        $selectedBusiness = $request->user()
            ->memberBusinesses()
            ->wherePivot('is_active', true)
            ->findOrFail($business);

        $request->session()->put('active_business_id', $selectedBusiness->id);

        return redirect()
            ->route('businesses.index')
            ->with('status', "Ahora estás trabajando con {$selectedBusiness->name}.");
    }
}
