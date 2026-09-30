<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdjustmentReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $voidedSales = $business->sales()->whereNotNull('voided_at')->with(['user', 'voidedBy'])->latest('voided_at')->paginate(20, ['*'], 'voids');
        $refunds = $business->refunds()->with(['sale', 'user', 'lines'])->latest('refunded_at')->paginate(20, ['*'], 'refunds');

        return view('adjustments.index', compact('business', 'voidedSales', 'refunds'));
    }
}
