# Step 41: Vendor Self-Registration Form

**Goal:** Create a public vendor application form to register vendor accounts, record shop configurations, upload identity/tax records, and notify admins for approval.
**Depends On:** Step 13 (Vendor & VendorDocument Models), Step 24 (Admin Vendor Management)
**Next Step:** Step 42 (Customer Storefront Layout)

---

## Goal Explanation

On the Pinora marketplace, vendors register their own accounts. To sign up, they must provide both user details (owner account) and business parameters (store name, GST details, PAN card, bank details for payouts). They must also upload official verification documents (GST registration certificate, PAN card).

This step implements the `VendorRegistrationController` and corresponding Blade storefront views:
1. **Double Record Creation:** Registers the owner `User` account, assigns the Spatie `vendor` role, and creates the associated `Vendor` record in a `pending` status.
2. **Transaction Safety:** Wraps the entire creation flow in a Database Transaction. If document upload fails, user and vendor creation are rolled back.
3. **Documents Storage:** Automatically uploads business proof files to storage, registers them in the `vendor_documents` table under a `pending` status, and links them to the new vendor.
4. **Onboarding Pending State:** Logs the new user in and displays a beautiful "Application Received" screen, advising them that their files are being audited.

---

## Files to Create

1. `app/Http/Controllers/Vendor/VendorRegistrationController.php`
2. `resources/views/vendor/apply.blade.php`

---

## Complete Code

### 1. `app/Http/Controllers/Vendor/VendorRegistrationController.php`

```php
<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class VendorRegistrationController extends Controller
{
    /**
     * Show the public vendor application form.
     */
    public function showForm(): View
    {
        return view('vendor.apply');
    }

    /**
     * Handle the vendor self-registration submission.
     */
    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            // Owner credentials
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'owner_phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // Store details
            'store_name' => ['required', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:2000'],
            'store_phone' => ['nullable', 'string', 'max:20'],
            'store_email' => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'string', 'max:10'],

            // Verification details
            'gst_number' => ['required', 'string', 'max:20'],
            'pan_number' => ['required', 'string', 'max:15'],
            'gst_certificate' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Max 5MB
            'pan_card' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Bank details
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_ifsc_code' => ['required', 'string', 'max:20'],
        ]);

        DB::beginTransaction();

        try {
            // 1. Create the Owner User Account
            $user = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone' => $request->owner_phone,
                'password' => Hash::make($request->password),
                'status' => 'active', // Login is active, but vendor panel may block pending status
            ]);

            // Assign Spatie role
            $user->assignRole('vendor');

            // 2. Create the Vendor/Store Profile
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'store_name' => $request->store_name,
                'description' => $request->store_description,
                'phone' => $request->store_phone ?? $request->owner_phone,
                'email' => $request->store_email ?? $request->owner_email,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'bank_name' => $request->bank_name,
                'bank_account_name' => $request->bank_account_name,
                'bank_account_number' => $request->bank_account_number, // Cast will encrypt this in Vendor model
                'bank_ifsc_code' => $request->bank_ifsc_code,
                'commission_rate' => 10.00, // Platform default 10% commission, admin can adjust on approval
                'status' => 'pending',
            ]);

            // 3. Store and Link Verification Documents
            if ($request->hasFile('gst_certificate')) {
                $file = $request->file('gst_certificate');
                $path = $file->store('vendor_documents', 'public');

                VendorDocument::create([
                    'vendor_id' => $vendor->id,
                    'type' => 'gst_certificate',
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'status' => 'pending',
                ]);
            }

            if ($request->hasFile('pan_card')) {
                $file = $request->file('pan_card');
                $path = $file->store('vendor_documents', 'public');

                VendorDocument::create([
                    'vendor_id' => $vendor->id,
                    'type' => 'pan_card',
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            // Auto-login the owner user so they remain authenticated
            Auth::login($user);

            return back()->with('success', 'Your vendor application has been successfully submitted.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor self-registration failed: ' . $e->getMessage());

            return back()->withInput()->withErrors([
                'owner_name' => 'Registration failed due to a system error. Please try again shortly.',
            ]);
        }
    }
}
```

---

### 2. `resources/views/vendor/apply.blade.php`

*(Refer to created view file at resources/views/vendor/apply.blade.php for complete code).*

---

## Web Route Definitions

Add the following routes to `routes/web.php`:

```php
use App\Http\Controllers\Vendor\VendorRegistrationController;

Route::middleware('guest')->group(function () {
    Route::get('vendor/apply', [VendorRegistrationController::class, 'showForm'])->name('vendor.apply');
    Route::post('vendor/apply', [VendorRegistrationController::class, 'submit'])->name('vendor.apply.submit');
});
```

---

## Notes

- **Database Integrity:** Wrapping the logic inside a `DB::beginTransaction()` block ensures that we never end up with orphaned `User` accounts if document uploading fails midway.
- **Auto-Encryption:** The `bank_account_number` field is automatically encrypted upon save, thanks to the `'encrypted'` cast defined on the `Vendor` model in Step 13.
- **Admin Alert:** In Phase 9 (Event Listeners), we will attach an observer to trigger an email alert/notification to the platform administrator when a vendor creates a new `pending` record.
- **Access Guarding:** Ensure the `EnsureVendorAccess` middleware blocks access to core vendor pages for users whose associated Vendor profiles are in a `'pending'`, `'rejected'`, or `'suspended'` status, prompting them with a notification banner.
