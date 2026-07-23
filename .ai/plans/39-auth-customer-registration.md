# Step 39: Customer Registration & Guest Checkout

**Goal:** Build the customer-facing registration and login system using Laravel Controllers and Blade templates, and configure guest checkout redirection logic.
**Depends On:** Step 12 (Update User Model), Step 23 (Seed Roles & Permissions)
**Next Step:** Step 40 (Google OAuth Login)

---

## Goal Explanation

Customers need to register and log in to manage wishlists, save delivery addresses, and track order histories. This step implements standard email-and-password auth flows specifically styled for the customer frontend:

1. **Registration Form:** Collects `name`, `email`, `phone`, `password`, and `password_confirmation`. Upon submission, a user record is created, the Spatie role `customer` is assigned, and they are automatically logged in.
2. **Login Form:** Auths via `email` and `password`. If successful, the user is redirected dynamically:
   - Admins (`super_admin` role) go to `/admin`.
   - Vendors (`vendor` role) go to `/vendor`.
   - Customers go to the homepage or their intended page (e.g., checkout).
3. **Guest Checkout Guard:** If a customer attempts to access the checkout route while not authenticated, we redirect them to the registration page first (with a helper message encouraging them to create an account), but also support passing checkout query params to preserve their session.

To ensure the storefront looks premium and modern, the views are styled using custom, responsive Vanilla CSS inside a layout block.

---

## Files to Create

1. `app/Http/Controllers/Auth/CustomerAuthController.php`
2. `resources/views/auth/register.blade.php`
3. `resources/views/auth/login.blade.php`

---

## Complete Code

### 1. `app/Http/Controllers/Auth/CustomerAuthController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    /**
     * Display the customer login view.
     */
    public function showLogin(Request $request): View
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }

        return view('auth.login', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    /**
     * Handle an authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($request->filled('redirect') && $request->input('redirect') === 'checkout') {
                return redirect()->to('/checkout');
            }

            return $this->redirectUserByRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Display the customer registration view.
     */
    public function showRegister(Request $request): View
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.register', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        // Assign customer role
        $user->assignRole('customer');

        Auth::login($user);

        if ($request->filled('redirect') && $request->input('redirect') === 'checkout') {
            return redirect()->to('/checkout');
        }

        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Helper to route users to correct panels based on roles.
     */
    protected function redirectUserByRole(User $user): RedirectResponse
    {
        if ($user->hasRole('super_admin')) {
            return redirect()->intended('/admin');
        }

        if ($user->hasRole(['vendor', 'vendor_staff'])) {
            return redirect()->intended('/vendor');
        }

        return redirect()->intended('/');
    }
}
```

---

### 2. `resources/views/auth/register.blade.php`

*(Refer to created view file at resources/views/auth/register.blade.php for complete code).*

---

### 3. `resources/views/auth/login.blade.php`

*(Refer to created view file at resources/views/auth/login.blade.php for complete code).*

---

## Web Route Definitions

Add the following routes to `routes/web.php`:

```php
use App\Http\Controllers\Auth\CustomerAuthController;

Route::middleware('guest')->group(function () {
    Route::get('register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [CustomerAuthController::class, 'register']);

    Route::get('login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [CustomerAuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [CustomerAuthController::class, 'logout'])->name('logout');
});
```

---

## Notes

- **Role Assignment:** A Spatie permission observer or manual command binds the `'customer'` role to all registrants out of the box.
- **Dynamic Intended Redirects:** Dynamic routing automatically resolves vendor staff to `/vendor`, administrators to `/admin`, and standard clients back to their checkout carts or account profiles.
- **Guest Checkout Flow:** Placing a query param of `?redirect=checkout` on links to register or login forwards the customer directly back to the checkout process immediately after successful authentication.
