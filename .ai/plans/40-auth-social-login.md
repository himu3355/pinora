# Step 40: Google OAuth Login

**Goal:** Implement Google OAuth social authentication for customer accounts using the `laravel/socialite` package.
**Depends On:** Step 39 (Customer Registration & Guest Checkout)
**Next Step:** Step 41 (Vendor Self-Registration Form)

---

## Goal Explanation

To simplify customer onboarding, we offer one-click Google OAuth login. If a user already exists with the matching email address but has never used Google login, we link their Google profile to their existing account by saving their `google_id`. If they are new, we register them as a customer and immediately log them in.

This step installs `laravel/socialite` (package config included) and implements the `SocialAuthController` and corresponding routes.

---

## Files to Create/Update

1. **Create:** `app/Http/Controllers/Auth/SocialAuthController.php`
2. **Update:** `config/services.php`
3. **Update:** `routes/web.php`

---

## Complete Code & Configuration

### 1. `app/Http/Controllers/Auth/SocialAuthController.php`

```php
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

        // 1. Search for existing user with this google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            Auth::login($user, true);
            return redirect()->intended('/');
        }

        // 2. Search for user with matching email address
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link Google account to this existing email
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);

            Auth::login($user, true);
            return redirect()->intended('/');
        }

        // 3. Create a new user if no match found
        $newUser = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'password' => Hash::make(Str::random(24)), // Generate secure random password
            'status' => 'active',
            'email_verified_at' => now(), // OAuth verified emails are verified by default
        ]);

        // Assign customer role
        $newUser->assignRole('customer');

        Auth::login($newUser, true);

        return redirect()->intended('/');
    }
}
```

---

### 2. Update `config/services.php`

Append the Google client credentials under the service providers array:

```php
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
```

---

### 3. Update `routes/web.php`

Append the following routes for social authentication:

```php
use App\Http\Controllers\Auth\SocialAuthController;

Route::middleware('guest')->group(function () {
    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});
```

---

## Installation Commands

Run the following composer command to install Socialite:

```bash
composer require laravel/socialite
```

Add these environment variables to your `.env` file:

```env
GOOGLE_CLIENT_ID="your-google-client-id.apps.googleusercontent.com"
GOOGLE_CLIENT_SECRET="GOCSPX-your-client-secret"
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

---

## Notes

- **Password Generation:** A random 24-character password is set for accounts registered via OAuth. Users can request a password reset later to configure standard email-password credentials.
- **Auto Email Verification:** Google verified emails bypass manual verification flows. The controller sets `email_verified_at = now()`.
- **Linking Accounts:** Prioritizing the email search ensures that customers who manually register first can link their Google logins seamlessly without creating duplicate accounts.
