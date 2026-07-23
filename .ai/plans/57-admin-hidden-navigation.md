# Step 57 — Admin Navigation Visibility & Hidden Menus

**Phase:** Admin Panel Customization  
**Depends on:** Step 24 (Vendor Management), Step 31 (Payout Management), Shield Plugin  

---

## 🎯 Overview

Documentation of navigation configuration and hidden sidebar menu items in the Filament Admin Panel (`/admin`).

---

## 🚫 Hidden Sidebar Menu Items & Configurations

| Menu Item / Resource | Class / Component | Method Used to Hide | File Location |
|----------------------|-------------------|--------------------|---------------|
| **Vendors** | `VendorResource` | `protected static bool $shouldRegisterNavigation = false;` | `app/Filament/Resources/Vendors/VendorResource.php` |
| **Subscription Plans** | `SubscriptionPlanResource` | `protected static bool $shouldRegisterNavigation = false;` | `app/Filament/Resources/SubscriptionPlanResource.php` |
| **Vendor Subscriptions** | `VendorSubscriptionResource` | `protected static bool $shouldRegisterNavigation = false;` | `app/Filament/Resources/VendorSubscriptionResource.php` |
| **Payouts** | `PayoutResource` | `protected static bool $shouldRegisterNavigation = false;` | `app/Filament/Resources/Payouts/PayoutResource.php` |
| **Roles** | `RoleResource` | Registered custom `RoleResource` before `plugins()` in `AdminPanelProvider`, and declared `public static function shouldRegisterNavigation(): bool { return false; }` | `app/Filament/Resources/Roles/RoleResource.php` & `app/Providers/Filament/AdminPanelProvider.php` |
| **Global Blocks** | `GlobalBlocksPlugin` | `GlobalBlocksPlugin::make()->enableGlobalBlocks(false)` in `AdminPanelProvider` | `app/Providers/Filament/AdminPanelProvider.php` |
| **Commissions** | `CommissionResource` | Completely removed model, table, resource, and menu from codebase | Removed |

---

## ⚙️ Implementation Technical Details

### 1. Resource Property Method (`$shouldRegisterNavigation`)
For standard Filament resources, navigation is suppressed by setting:
```php
protected static bool $shouldRegisterNavigation = false;
```

### 2. Overriding Trait Methods (Roles Resource)
Filament Shield uses dynamic traits that override the `$shouldRegisterNavigation` property. To hide **Roles**, the custom resource `RoleResource` is registered in `AdminPanelProvider.php` prior to `plugins()`, overriding the method:
```php
public static function shouldRegisterNavigation(): bool
{
    return false;
}
```

### 3. Plugin Feature Toggles (Global Blocks)
Global Blocks plugin resource is disabled at the plugin initialization level in `AdminPanelProvider.php`:
```php
GlobalBlocksPlugin::make()->enableGlobalBlocks(false)
```

---

## 📝 Verification

- Verify routes remain accessible via direct URL for admins with proper permissions (e.g. `/admin/shield/roles`).
- Confirm navigation bar on `/admin` hides the above resources cleanly.
