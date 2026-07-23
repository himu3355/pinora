# Step 58 — Temporary Vendor Freeze & Reversion Instructions

**Phase:** System Modification / Feature Freeze  
**Depends on:** Step 24 (Vendor Management), Step 27 (Product Management), Step 41 (Vendor Registration)  

---

## 🎯 Overview

Documentation of temporary changes applied to freeze vendor functionality (disabling vendor registration, restricting vendor/vendor-staff user creation, hiding vendor selection in product management, and auto-defaulting products to the first vendor).

---

## 🔒 Applied Temporary Changes

| Feature | Change Applied | Affected File(s) |
|---------|----------------|------------------|
| **Vendor Self-Registration** | Disabled GET and POST routes `/register/vendor` by redirecting to `/login` | `routes/web.php` |
| **Vendor & Staff User Creation** | Filtered `roles` selection in Admin User Form to exclude `vendor` and `vendor_staff` roles | `app/Filament/Resources/Users/Schemas/UserForm.php` |
| **Admin Default Vendor Setting** | Hidden "Default Vendor (for Product Creation)" field from User Admin Form | `app/Filament/Resources/Users/Schemas/UserForm.php` |
| **Product Form Vendor Selection** | Hidden "Vendor" dropdown from Product Form schema and converted to `Hidden::make('vendor_id')` defaulting to `Vendor::first()?->id` | `app/Filament/Resources/Products/Schemas/ProductForm.php` |

---

## 🔄 Step-by-Step Reversion Instructions (How to Re-Enable Multi-Vendor Features)

When you are ready to re-enable multi-vendor functionality, follow these exact steps to revert the changes:

### Step 1: Re-enable Vendor Self-Registration Routes
In `routes/web.php`, restore the original registration controller routes:
```php
// Revert in routes/web.php:
Route::get('register/vendor', [VendorRegistrationController::class, 'showRegistrationForm'])->name('vendor.apply');
Route::post('register/vendor', [VendorRegistrationController::class, 'register'])->name('vendor.apply.submit');
```

### Step 2: Re-enable Vendor & Vendor Staff Role Creation & Admin Default Vendor Field
In `app/Filament/Resources/Users/Schemas/UserForm.php`:
1. Remove the `whereNotIn('name', ['vendor', 'vendor_staff'])` query filter on `roles`:
```php
Select::make('roles')
    ->relationship('roles', 'name')
    ->multiple()
    ->preload(),
```
2. Restore the `default_vendor_id` field:
```php
Select::make('default_vendor_id')
    ->label('Default Vendor (for Product Creation)')
    ->options(
        \App\Models\Vendor::orderBy('store_name')->pluck('store_name', 'id')
    )
    ->searchable()
    ->nullable()
    ->helperText('Pre-selects this vendor when this Admin creates a product.'),
```

### Step 3: Re-enable Vendor Selection in Product Form
In `app/Filament/Resources/Products/Schemas/ProductForm.php`:
Replace `Hidden::make('vendor_id')` with the original `Select::make('vendor_id')` field:
```php
Select::make('vendor_id')
    ->label('Vendor')
    ->options(
        Vendor::where('status', 'approved')
            ->orderBy('store_name')
            ->pluck('store_name', 'id')
    )
    ->default(fn() => auth()->user()?->default_vendor_id)
    ->required()
    ->searchable()
    ->createOptionForm([
        TextInput::make('store_name')->label('Store / Vendor Name')->required()->maxLength(255)->live(onBlur: true)->afterStateUpdated(fn(Set $set, ?string $state) => $set('store_slug', Str::slug($state ?? ''))),
        TextInput::make('store_slug')->label('Store Slug')->required()->maxLength(255),
        TextInput::make('email')->label('Contact Email')->email(),
        TextInput::make('phone')->label('Contact Phone')->tel(),
    ])
    ->createOptionUsing(function (array $data): int {
        $data['user_id'] = auth()->id();
        $data['status']  = 'approved';
        $vendor = Vendor::create($data);
        return $vendor->id;
    }),
```

---

## 📝 Verification After Reverting

1. Visit `/register/vendor` -> Confirm registration page renders.
2. Visit `/admin/users/create` -> Confirm `vendor` and `vendor_staff` options appear in Roles select and "Default Vendor" dropdown is visible.
3. Visit `/admin/products/create` -> Confirm "Vendor" dropdown appears.
