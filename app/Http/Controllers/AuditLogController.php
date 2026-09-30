<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');

        return view('audit-logs.index', [
            'activeBusiness' => $business,
            'logs' => $business->auditLogs()->with('user')->latest('created_at')->latest('id')->paginate(30),
        ]);
    }
}
