<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google and log them in.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google authentication failed. Please try again.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            Auth::login($user, true);
            return redirect()->intended('/');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);

            Auth::login($user, true);
            return redirect()->intended('/');
        }

        $newUser = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'password' => Hash::make(Str::random(24)),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $newUser->assignRole('customer');
        Auth::login($newUser, true);

        return redirect()->intended('/');
    }
}
