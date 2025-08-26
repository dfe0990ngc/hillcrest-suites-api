<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Helpers\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  string  $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! URL::hasValidSignature($request)) {
            abort(403, 'Invalid signature.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid hash.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(config('app.frontend_url').'/email-verified');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return redirect()->intended(config('app.frontend_url').'/email-verified');
        }

        return redirect()->intended(config('app.frontend_url').'/email-unverified');
    }
}
