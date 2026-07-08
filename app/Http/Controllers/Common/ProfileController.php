<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileUpdateRequest;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->can('is-resident')) {
            $user->load([
                'resident.flat',
            ]);
        }

        return view('profile.index', compact('user'));
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's password change form.
     */
    public function passwordEdit(Request $request): View
    {
        return view('profile.password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();

            $emailChanged = $user->email !== $request->email;

            $user->fill($request->validated());

            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();

            if ($emailChanged) {
                $user->sendEmailVerificationNotification();
                ActivityLogger::log('update', $user, 'Updated profile email and triggered verification.');

                return Redirect::route('profile.edit')->with('status', 'verification-link-sent');
            }

            ActivityLogger::log('update', $user, 'Updated personal profile information.');

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (Exception $e) {
            Log::error('Profile Update Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return Redirect::route('profile.edit')->with('error', 'Something went wrong while updating the profile.');
        }
    }
}
