<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use Redberry\PageBuilderPlugin\Models\PageBuilderBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class VendorRegistrationController extends Controller
{
    /**
     * Show the vendor registration form.
     */
    public function showRegistrationForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole(['vendor', 'vendor_staff'])) {
                return redirect('/vendor');
            }
            return redirect('/');
        }

        return view('auth.register-vendor');
    }

    /**
     * Handle the vendor registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'store_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:15'],
            'description' => ['nullable', 'string'],
        ]);

        // 1. Create the User account
        $user = User::create([
            'name' => $request->owner_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active', // User account is active, vendor profile is pending
        ]);

        // Assign Spatie Role 'vendor'
        $user->assignRole('vendor');

        // 2. Generate a unique store slug
        $slug = Str::slug($request->store_name);
        $originalSlug = $slug;
        $count = 1;
        while (Vendor::where('store_slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        // 3. Create the Vendor record (Auto-approved)
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => $request->store_name,
            'store_slug' => $slug,
            'phone' => $request->phone,
            'email' => $request->email,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
            'description' => $request->description,
            'status' => 'approved', // Auto-approved
        ]);

        // 4. Create Free Trial Subscription (14 days)
        VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // 5. Create default homepage Hero block
        PageBuilderBlock::create([
            'block_type' => \App\Filament\Blocks\Hero::class,
            'page_builder_blockable_type' => Vendor::class,
            'page_builder_blockable_id' => $vendor->id,
            'order' => 1,
            'data' => [
                'badge_text' => 'Grand Opening',
                'heading' => 'Welcome to ' . $vendor->store_name,
                'subheading' => 'Discover our unique collection of gold, silver, and diamond designs.',
                'image' => null,
            ],
        ]);

        // 6. Log the user in
        Auth::login($user);

        return redirect('/vendor')->with('success', 'Your partner account has been successfully created and your 14-day free trial has started!');
    }
}
