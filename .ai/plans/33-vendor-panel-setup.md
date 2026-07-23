# Step 33: Create Vendor Filament Panel

**Goal:** Register a new Filament panel at `/vendor` for vendor users.
**Depends On:** Step 01 (Filament install), Step 10 (User roles), Step 12 (Vendor model)
**Next Step:** Step 34 (Vendor Product Management)

---

## Files to Create

### 1. `app/Providers/Filament/VendorPanelProvider.php`

```php
<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureVendorAccess;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class VendorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendor')
            ->path('vendor')
            ->login()
            ->colors([
                'primary' => Color::Rose,
            ])
            ->discoverResources(
                in: app_path('Filament/Vendor/Resources'),
                for: 'App\\Filament\\Vendor\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Vendor/Pages'),
                for: 'App\\Filament\\Vendor\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Vendor/Widgets'),
                for: 'App\\Filament\\Vendor\\Widgets'
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureVendorAccess::class,
            ]);
    }
}
```

---

### 2. `app/Http/Middleware/EnsureVendorAccess.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('filament.vendor.auth.login');
        }

        $hasVendorRole   = $user->hasRole(['vendor', 'vendor_staff']);
        $isActingAsVendor = session()->has('acting_as_vendor_id');

        if (! $hasVendorRole && ! $isActingAsVendor) {
            session()->flash('error', 'You do not have access to the vendor panel.');
            return redirect('/admin');
        }

        return $next($request);
    }
}
```

---

### 3. `app/Services/VendorContext.php`

```php
<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class VendorContext
{
    /**
     * Resolve the current vendor from the authenticated user or
     * the admin impersonation session.
     */
    public static function current(): Vendor
    {
        if (Session::has('acting_as_vendor_id')) {
            return Vendor::findOrFail(Session::get('acting_as_vendor_id'));
        }

        $user = Auth::user();

        if (! $user) {
            abort(403, 'No authenticated user found.');
        }

        $vendor = $user->vendor;

        if (! $vendor) {
            abort(403, 'No vendor record associated with this account.');
        }

        return $vendor;
    }

    /**
     * Return the current vendor's ID without loading the full model.
     */
    public static function currentId(): int
    {
        if (Session::has('acting_as_vendor_id')) {
            return (int) Session::get('acting_as_vendor_id');
        }

        $user = Auth::user();

        if (! $user || ! $user->vendor) {
            abort(403, 'No vendor context available.');
        }

        return $user->vendor->id;
    }

    /**
     * Set the admin to act as a specific vendor (impersonation).
     */
    public static function actingAs(int $vendorId): void
    {
        Session::put('acting_as_vendor_id', $vendorId);
    }

    /**
     * Stop admin impersonation.
     */
    public static function stopActing(): void
    {
        Session::forget('acting_as_vendor_id');
    }

    /**
     * Check if the current session is an admin impersonating a vendor.
     */
    public static function isImpersonating(): bool
    {
        return Session::has('acting_as_vendor_id');
    }
}
```

---

### 4. Register in `bootstrap/providers.php`

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\VendorPanelProvider::class, // <-- Add this line
];
```

---

### 5. Create Vendor Dashboard Page

Create manually at `app/Filament/Vendor/Pages/Dashboard.php`:

```php
<?php

namespace App\Filament\Vendor\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title           = 'Vendor Dashboard';
    protected static ?int    $navigationSort  = 0;
}
```

---

## Notes

- The vendor panel uses the same `web` guard and `users` table as the admin panel.
- `VendorContext::current()` is the single source of truth for which vendor is active — always call it in Filament resources to scope queries.
- When an admin uses "Act as Vendor" (Step 28), it calls `VendorContext::actingAs($vendorId)` and redirects to `/vendor`.
- Colors are Rose to visually distinguish the vendor panel from the admin panel (Amber).
- `EnsureVendorAccess` runs **after** `Authenticate`, so `auth()->user()` is always available inside it.
