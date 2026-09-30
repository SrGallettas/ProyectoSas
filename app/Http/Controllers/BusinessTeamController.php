<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessInvitationRequest;
use App\Http\Requests\UpdateBusinessMemberRequest;
use App\Models\Business;
use App\Models\BusinessInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
        $email = Str::lower($request->validated('email'));

        if ($business->members()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Esta persona ya pertenece al equipo.']);
        }

        $business->invitations()->where('email', $email)->whereNull('accepted_at')->delete();

        $business->invitations()->create([
            ...$request->validated(),
            'email' => $email,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
        ]);

        return back()->with('status', 'Invitación creada.')->with('invitation_url', route('team.invitations.accept', $token));
    }

    public function update(UpdateBusinessMemberRequest $request, int $user): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $member = $business->members()->findOrFail($user);
        abort_if($member->pivot->role === Business::ROLE_OWNER, 422, 'No se puede modificar al propietario.');
        $business->members()->updateExistingPivot($member->id, $request->validated());

        return back()->with('status', 'Acceso del empleado actualizado.');
    }

    public function regenerateInvitation(Request $request, int $invitation): RedirectResponse
    {
        $invitationModel = $this->invitationForActiveBusiness($request, $invitation);
        $token = Str::random(64);
        $invitationModel->update(['token' => hash('sha256', $token), 'expires_at' => now()->addDays(7)]);

        return back()->with('status', 'Invitación regenerada.')->with('invitation_url', route('team.invitations.accept', $token));
    }

    public function destroyInvitation(Request $request, int $invitation): RedirectResponse
    {
        $this->invitationForActiveBusiness($request, $invitation)->delete();

        return back()->with('status', 'Invitación cancelada.');
    }

    private function invitationForActiveBusiness(Request $request, int $invitation): BusinessInvitation
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');

        return $business->invitations()->whereNull('accepted_at')->findOrFail($invitation);
    }
}
