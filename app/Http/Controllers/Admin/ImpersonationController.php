<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    public function start(User $user)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back();
        }

        try {
            session([
                'impersonator_id' => auth()->id(),
                'impersonator_redirect' => url()->previous(),
            ]);

            ActivityLogger::log('impersonate_start', $user, "Impersonating user {$user->name}.");
            Auth::login($user);

            return redirect('/')
                ->with([
                    'message' => 'You are now logged in as '.$user->name,
                    'status' => 'success',
                ]);
        } catch (Exception $e) {
            Log::error('Impersonation Start Error', [
                'impersonator_id' => auth()->id(),
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return back()->with([
                'message' => 'Something went wrong while starting impersonation.',
                'status' => 'error',
            ]);
        }
    }

    public function stop()
    {
        if (! session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        try {
            $admin = User::findOrFail(
                session('impersonator_id')
            );

            $redirectUrl = session('impersonator_redirect');

            session()->forget([
                'impersonator_id',
                'impersonator_redirect',
            ]);

            Auth::login($admin);
            ActivityLogger::log('impersonate_stop', null, 'Stopped impersonating.');

            return redirect($redirectUrl ?: route('dashboard'))
                ->with([
                    'message' => 'Returned to your account',
                    'status' => 'success',
                ]);
        } catch (Exception $e) {
            Log::error('Impersonation Stop Error', [
                'impersonator_id' => session('impersonator_id'),
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return redirect()->route('dashboard')->with([
                'message' => 'Something went wrong while stopping impersonation.',
                'status' => 'error',
            ]);
        }
    }
}
