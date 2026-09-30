<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessInvitationRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessTeamController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');

        return view('team.index', [
            'activeBusiness' => $business,
            'members' => $business->members()->orderBy('name')->get(),
            'invitations' => $business->invitations()->whereNull('accepted_at')->where('expires_at', '>', now())->latest()->get(),
        ]);
    }

    public function store(StoreBusinessInvitationRequest $request): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $token = Str::random(64);

        $business->invitations()->create([
            ...$request->validated(),
            'email' => Str::lower($request->validated('email')),
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
        ]);

        return back()->with('status', 'Invitación creada.')->with('invitation_url', route('team.invitations.accept', $token));
    }
}
