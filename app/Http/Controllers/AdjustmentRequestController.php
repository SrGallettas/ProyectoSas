<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdjustmentRequest;
use App\Models\AdjustmentRequest;
use App\Models\AuditLog;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdjustmentRequestController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $requests = $business->adjustmentRequests()->where('status', AdjustmentRequest::STATUS_PENDING)->with(['sale', 'requestedBy'])->oldest()->get();

        return view('adjustment-requests.index', compact('business', 'requests'));
    }

    public function store(StoreAdjustmentRequest $request): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $sale = $business->sales()->findOrFail($request->validated('sale_id'));
        $payload = $request->safe()->only(['reason', 'payment_method', 'lines']);
        if ($request->validated('type') === 'refund' && collect($payload['lines'] ?? [])->filter()->isEmpty()) {
            throw ValidationException::withMessages(['lines' => 'Selecciona al menos una unidad.']);
        }
        $adjustment = $business->adjustmentRequests()->create(['sale_id' => $sale->id, 'requested_by_user_id' => $request->user()->id, 'type' => $request->validated('type'), 'payload' => $payload, 'status' => AdjustmentRequest::STATUS_PENDING]);
        AuditLog::record($request, $business, 'adjustment.requested', $adjustment, null, ['type' => $adjustment->type, 'sale_id' => $sale->id, 'reason' => $payload['reason']]);

        return back()->with('status', 'Solicitud enviada para revisión.');
    }

    public function reject(Request $request, int $adjustment): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $model = $business->adjustmentRequests()->where('status', AdjustmentRequest::STATUS_PENDING)->findOrFail($adjustment);
        $request->validate(['review_note' => ['required', 'string', 'min:3', 'max:255']]);
        $model->update(['status' => 'rejected', 'reviewed_by_user_id' => $request->user()->id, 'review_note' => $request->string('review_note'), 'reviewed_at' => now()]);
        AuditLog::record($request, $business, 'adjustment.rejected', $model, null, ['type' => $model->type, 'sale_id' => $model->sale_id, 'reason' => $model->review_note]);

        return back()->with('status', 'Solicitud rechazada.');
    }
}
