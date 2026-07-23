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
    public function showLogin(Request $request): View|RedirectResponse
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
    public function showRegister(Request $request): View|RedirectResponse
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
