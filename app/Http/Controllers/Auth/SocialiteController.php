<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    protected const PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS), 404);

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Throwable $e) {
            // Credentials not configured yet — friendly fallback instead of a 500
            return redirect()->route('login')->withErrors([
                'email' => ucfirst($provider).' login is not configured yet. Please use email to sign in.',
            ]);
        }
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Could not sign you in with '.ucfirst($provider).'. Please try again or use email.',
            ]);
        }

        $email = $socialUser->getEmail();
        if (!$email) {
            return redirect()->route('login')->withErrors([
                'email' => ucfirst($provider).' did not provide an email address. Please use email login instead.',
            ]);
        }

        // Find existing user by email, else create a new one
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: ucfirst($provider).' User',
                'email' => $email,
                'password' => null, // social-only account
                'provider' => $provider,
                'provider_id' => (string) $socialUser->getId(),
                'role' => 'buyer', // social sign-ups default to buyer
                'email_verified_at' => now(), // provider already verified the email
            ]);
        } else {
            // Existing user — link the provider if not already linked
            if (!$user->provider_id) {
                $user->forceFill([
                    'provider' => $provider,
                    'provider_id' => (string) $socialUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }
}
