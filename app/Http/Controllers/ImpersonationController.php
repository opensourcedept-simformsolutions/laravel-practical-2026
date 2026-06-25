<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

        session([
            'impersonator_id' => auth()->id(),
            'impersonator_redirect' => url()->previous(),
        ]);

        Auth::login($user);

        return redirect('/')
            ->with([
                'message' => 'You are now logged in as ' . $user->name,
                'status' => 'success',
            ]);
    }

    public function stop()
    {
        if (! session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $admin = User::findOrFail(
            session('impersonator_id')
        );

        $redirectUrl = session('impersonator_redirect');

        session()->forget([
            'impersonator_id',
            'impersonator_redirect',
        ]);

        Auth::login($admin);

        return redirect($redirectUrl ?: route('dashboard'))
            ->with([
                'message' => 'Returned to your account',
                'status' => 'success',
            ]);
    }
}
