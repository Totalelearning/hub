<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MagicLinkMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class MagicLinkController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $key = 'magic-link|' . $request->ip() . '|' . strtolower($request->email);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'email' => __('Too many requests. Please try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        RateLimiter::hit($key, 60);

        $user = User::where('email', strtolower($request->email))
            ->whereNull('suspended_at')
            ->first();

        if ($user) {
            $url = URL::temporarySignedRoute(
                'magic-link.verify',
                now()->addMinutes(15),
                ['user' => $user->id]
            );

            Mail::to($user)->send(new MagicLinkMail($url));
        }

        return redirect()->route('login')->with(
            'status',
            'If that email is registered, a login link has been sent. Check your inbox.'
        );
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        if ($user->suspended_at) {
            return redirect()->route('login')->withErrors([
                'email' => 'This account has been suspended.',
            ]);
        }

        Auth::login($user);

        $user->forceFill(['last_login_at' => now()])->save();

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $request->session()->regenerate();

        $defaultRoute = $user->isParent()
            ? route('app.parent.dashboard', absolute: false)
            : route('app.feed', absolute: false);

        return redirect()->intended($defaultRoute);
    }
}
