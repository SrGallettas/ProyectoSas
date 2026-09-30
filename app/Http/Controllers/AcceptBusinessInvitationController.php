<?php

namespace App\Http\Controllers;

use App\Models\BusinessInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcceptBusinessInvitationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $invitation = BusinessInvitation::query()
            ->where('token', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        abort_unless(strcasecmp($request->user()->email, $invitation->email) === 0, 403);

        $invitation->business->members()->syncWithoutDetaching([
            $request->user()->id => ['role' => $invitation->role, 'is_active' => true],
        ]);
        $invitation->update(['accepted_at' => now()]);
        $request->session()->put('active_business_id', $invitation->business_id);

        return redirect()->route('dashboard')->with('status', 'Te has unido al comercio correctamente.');
    }
}
