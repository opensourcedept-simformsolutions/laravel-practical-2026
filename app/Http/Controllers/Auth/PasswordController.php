<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        try {
            $user = $request->user();
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            ActivityLogger::log('update', $user, "Changed account password.");

            return back()->with('status', 'password-updated');
        } catch (Exception $e) {
            Log::error('Password Update Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return back()->withErrors(['password' => 'Something went wrong while updating your password.'], 'updatePassword');
        }
    }
}
