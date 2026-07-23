# Step 50: Vendor Storefront Page

**Goal:** Create public pages for listing all approved vendors and a dedicated storefront page for individual vendors.
**Depends On:** Step 13 (Vendor model), Step 16 (Product model), Step 42 (Frontend layout)
**Next Step:** Step 51 (PricingService)

---

## Goal Explanation

Customers value knowing which verified artisan or vendor crafted their jewellery.
This step introduces:
1. A public vendors directory (`/vendors`) listing all active, approved vendors with their store logo, store name, description, and link to their shop.
2. A beautiful vendor storefront page (`/vendors/{slug}`) featuring the vendor's banner, logo, and a paginated product grid showing only that vendor's active products.

---

## Files to Create / Modify

### New Files:
- `app/Http/Controllers/Frontend/VendorController.php`
- `resources/views/vendors/index.blade.php`
- `resources/views/vendors/show.blade.php`
- `app/Filament/Vendor/Pages/ManageStorefront.php`
- `resources/views/filament/vendor/pages/manage-storefront.blade.php`

### Modified Files:
- `routes/web.php`
- `app/Providers/Filament/VendorPanelProvider.php`

---

## Complete PHP & Blade Code

### `app/Http/Controllers/Frontend/VendorController.php`

```php
<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of all active, approved vendors.
     */
    public function index()
    {
        $vendors = Vendor::where('status', 'approved')
            ->orderBy('store_name', 'asc')
            ->paginate(12);

        return view('vendors.index', compact('vendors'));
    }

    /**
     * Display the storefront of a specific vendor.
     */
    public function show(string $slug)
    {
        $vendor = Vendor::where('store_slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        $isOwner = auth()->check() && auth()->user()->vendor && auth()->user()->vendor->id === $vendor->id;
        if (! $vendor->hasActiveSubscription() && ! $isOwner) {
            abort(404, 'This shop is temporarily unavailable.');
        }

        // Paginate active products belonging to this vendor
        $products = $vendor->products()
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('vendors.show', compact('vendor', 'products'));
    }
}
```

---

### `resources/views/vendors/index.blade.php`

```html
@extends('layouts.app')

@section('title', 'Artisan & Jewellery Vendors')

@section('content')
<!-- Hero Header -->
<div class="bg-dark-card/50 py-16 border-b border-border-gold">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-extrabold text-text-light tracking-tight sm:text-5xl font-primary">Our Jewellery Partners</h1>
        <p class="mt-4 max-w-2xl mx-auto text-lg text-text-muted">Explore verified workshops, local artisans, and premium jewellery brands selling directly to you.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($vendors->isEmpty())
        <div class="text-center py-20 bg-dark-card border border-border-gold rounded-3xl shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gold/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h3 class="mt-4 text-lg font-bold text-text-light font-primary">No active vendors</h3>
            <p class="mt-2 text-sm text-text-muted">Check back later as we verify and onboard new jewellery designers.</p>
        </div>
    @else
        <!-- Vendors Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($vendors as $vendor)
                <div class="group bg-dark-card border border-border-gold rounded-3xl overflow-hidden shadow-sm hover:shadow-gold/20 hover:shadow-md transition duration-300 flex flex-col justify-between">
                    
                    <!-- Card Banner -->
                    <div class="h-32 bg-dark-bg relative overflow-hidden shrink-0">
                        @if($vendor->store_banner)
                            <img src="{{ Storage::url($vendor->store_banner) }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-500" alt="{{ $vendor->store_name }} banner">
                        @else
                            <div class="w-full h-full bg-gradient-to-r from-gold/10 to-gold-dark/25"></div>
                        @endif
                    </div>

                    <!-- Store Logo and Info -->
                    <div class="p-6 relative flex-grow flex flex-col items-center text-center">
                        
                        <!-- Logo badge overlapping the banner -->
                        <div class="w-20 h-20 bg-dark-surface border-2 border-gold rounded-2xl overflow-hidden shadow-sm -mt-16 mb-4 flex items-center justify-center shrink-0">
                            @if($vendor->store_logo)
                                <img src="{{ Storage::url($vendor->store_logo) }}" class="object-cover w-full h-full" alt="{{ $vendor->store_name }} logo">
                            @else
                                <span class="text-2xl font-black text-gold font-primary">{{ substr($vendor->store_name, 0, 1) }}</span>
                            @endif
                        </div>

                        <h3 class="text-xl font-bold text-text-light leading-tight mb-2 font-primary">
                            <a href="{{ route('vendors.show', $vendor->store_slug) }}" class="hover:text-gold transition">{{ $vendor->store_name }}</a>
                        </h3>
                        <p class="text-sm text-text-muted line-clamp-3 mb-6">
                            {{ $vendor->store_description ?? 'No description provided by this vendor.' }}
                        </p>
                    </div>

                    <!-- View Store CTA -->
                    <div class="px-6 pb-6 pt-2">
                        <a href="{{ route('vendors.show', $vendor->store_slug) }}" class="btn btn-outline-gold w-full justify-center">
                            Visit Storefront
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $vendors->links() }}
        </div>
    @endif
</div>
@endsection
```


---

### `resources/views/vendors/show.blade.php`

```html
@extends('layouts.app')

@section('title', $vendor->store_name . ' - Storefront')

@section('content')
<!-- Hero Banner Header -->
<div class="relative bg-dark-surface h-64 sm:h-80 overflow-hidden">
    @if($vendor->store_banner)
        <img src="{{ Storage::url($vendor->store_banner) }}" class="object-cover w-full h-full" alt="{{ $vendor->store_name }} banner">
    @else
        <div class="w-full h-full bg-gradient-to-r from-gold/10 via-gold-dark/20 to-gold/10"></div>
    @endif
    <div class="absolute inset-0 bg-black/10"></div>
</div>

<!-- Profile Info Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24 sm:-mt-32 relative z-10 pb-6 mb-10">
    <div class="bg-dark-card border border-border-gold rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row gap-6 md:items-end justify-between">
        
        <div class="flex flex-col sm:flex-row items-center sm:items-end gap-6 text-center sm:text-left">
            <!-- Logo badge -->
            <div class="w-32 h-32 bg-dark-surface border-4 border-border-gold rounded-3xl overflow-hidden shadow-md flex items-center justify-center shrink-0">
                @if($vendor->store_logo)
                    <img src="{{ Storage::url($vendor->store_logo) }}" class="object-cover w-full h-full" alt="{{ $vendor->store_name }} logo">
                @else
                    <span class="text-5xl font-black text-gold font-primary">{{ substr($vendor->store_name, 0, 1) }}</span>
                @endif
            </div>

            <div>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                    <h1 class="text-3xl font-extrabold text-text-light tracking-tight font-primary">{{ $vendor->store_name }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-400 border border-green-500/20">
                        Verified Vendor
                    </span>
                </div>
                <p class="text-text-muted mt-2 max-w-xl text-sm leading-relaxed">
                    {{ $vendor->store_description ?? 'Welcome to our storefront. Explore our exclusive range of gold, silver, and diamond designs.' }}
                </p>
            </div>
        </div>

        <div class="flex justify-center shrink-0">
            <div class="bg-dark-bg border border-border-gold rounded-2xl px-6 py-4 text-center">
                <span class="text-xs font-bold text-text-muted uppercase tracking-wide">Collection Size</span>
                <p class="text-3xl font-black text-gold mt-0.5">{{ $products->total() }} Products</p>
            </div>
        </div>

    </div>
</div>

<!-- Page Builder Blocks -->
@if($vendor->pageBuilderBlocks && $vendor->pageBuilderBlocks->isNotEmpty())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-16 space-y-12">
        @foreach($vendor->pageBuilderBlocks as $block)
            @php
                $blockClass = $block->block_type;
                $view = $blockClass::getView();
            @endphp
            @if($view)
                @include($view, ['block' => $block->data])
            @endif
        @endforeach
    </div>
@endif

<!-- Products Grid Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 mb-16">
    <h2 class="text-2xl font-extrabold text-text-light tracking-tight mb-8 font-primary">Products in Stock</h2>
    
    @if($products->isEmpty())
        <div class="text-center py-20 border border-dashed border-border-gold/50 rounded-3xl">
            <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="mt-4 text-text-muted font-medium">This vendor has not posted any products yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="group bg-dark-card border border-border-gold rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:shadow-gold/10 transition duration-200 flex flex-col h-full">
                    
                    <!-- Product Image -->
                    <div class="relative aspect-square bg-dark-bg overflow-hidden">
                        <img src="{{ $product->primary_image_url }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-300" alt="{{ $product->name }}">
                        @if($product->discount_percent > 0)
                            <span class="absolute top-3 left-3 bg-red-500 text-white text-xs font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                {{ number_format($product->discount_percent, 0) }}% Off
                            </span>
                        @endif
                    </div>

                    <!-- Product Details -->
                    <div class="p-5 flex flex-col justify-between flex-grow">
                        <div>
                            <h3 class="font-bold text-text-light text-sm line-clamp-2 h-10 mb-2 leading-tight font-primary">
                                <a href="{{ route('products.show', $product->slug) }}" class="hover:text-gold transition">{{ $product->name }}</a>
                            </h3>
                            <div class="mb-4 flex items-baseline gap-2">
                                @if($product->is_price_on_request)
                                    <span class="text-sm font-semibold text-text-muted">Price on Request</span>
                                @else
                                    <span class="text-lg font-black text-gold">₹{{ number_format($product->calculated_price, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-gold w-full justify-center">
                                View Product
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
```

---

### `routes/web.php` (Partial Addition)

```php
// Vendor storefront routes
Route::get('/vendors', [App\Http\Controllers\Frontend\VendorController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{slug}', [App\Http\Controllers\Frontend\VendorController::class, 'show'])->name('vendors.show');
```

---

### `app/Filament/Vendor/Pages/ManageStorefront.php`

```php
<?php

namespace App\Filament\Vendor\Pages;

use App\Models\Vendor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Redberry\PageBuilderPlugin\Components\Forms\PageBuilder;

class ManageStorefront extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'My Storefront';
    protected static ?string $title = 'Manage Storefront';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.vendor.pages.manage-storefront';

    public ?array $data = [];
    public ?Vendor $record = null;

    public function mount(): void
    {
        $this->record = auth()->user()->vendor;
        if (! $this->record) {
            abort(403, 'Vendor profile not found.');
        }

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        $indianStates = [
            'AP' => 'Andhra Pradesh',
            'AR' => 'Arunachal Pradesh',
            'AS' => 'Assam',
            'BR' => 'Bihar',
            'CG' => 'Chhattisgarh',
            'GA' => 'Goa',
            'GJ' => 'Gujarat',
            'HR' => 'Haryana',
            'HP' => 'Himachal Pradesh',
            'JH' => 'Jharkhand',
            'KA' => 'Karnataka',
            'KL' => 'Kerala',
            'MP' => 'Madhya Pradesh',
            'MH' => 'Maharashtra',
            'MN' => 'Manipur',
            'ML' => 'Meghalaya',
            'MZ' => 'Mizoram',
            'NL' => 'Nagaland',
            'OD' => 'Odisha',
            'PB' => 'Punjab',
            'RJ' => 'Rajasthan',
            'SK' => 'Sikkim',
            'TN' => 'Tamil Nadu',
            'TS' => 'Telangana',
            'TR' => 'Tripura',
            'UP' => 'Uttar Pradesh',
            'UK' => 'Uttarakhand',
            'WB' => 'West Bengal',
            'AN' => 'Andaman and Nicobar Islands',
            'CH' => 'Chandigarh',
            'DN' => 'Dadra and Nagar Haveli and Daman and Diu',
            'DL' => 'Delhi',
            'JK' => 'Jammu and Kashmir',
            'LA' => 'Ladakh',
            'LD' => 'Lakshadweep',
            'PY' => 'Puducherry',
        ];

        return $schema
            ->components([
                TextInput::make('store_name')
                    ->label('Store Name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Store Description')
                    ->rows(4)
                    ->maxLength(2000),

                FileUpload::make('logo')
                    ->label('Store Logo')
                    ->image()
                    ->disk('public')
                    ->directory('vendors/logos')
                    ->maxSize(2048),

                FileUpload::make('banner')
                    ->label('Store Banner')
                    ->image()
                    ->disk('public')
                    ->directory('vendors/banners')
                    ->maxSize(5120),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(15),

                TextInput::make('email')
                    ->label('Business Email')
                    ->email()
                    ->maxLength(255),

                Textarea::make('address')
                    ->label('Street Address')
                    ->rows(2),

                TextInput::make('city')
                    ->label('City')
                    ->maxLength(100),

                Select::make('state')
                    ->label('State')
                    ->options($indianStates)
                    ->searchable(),

                TextInput::make('pincode')
                    ->label('Pincode')
                    ->maxLength(6)
                    ->numeric(),

                PageBuilder::make('pageBuilderBlocks')
                    ->label('Custom Homepage Blocks')
                    ->blocks([
                        \App\Filament\Blocks\Hero::class,
                        \App\Filament\Blocks\VermiCompost::class,
                    ])
                    ->reorderable()
                    ->columnSpanFull(),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('preview')
                ->label('Preview Storefront')
                ->icon('heroicon-o-eye')
                ->url(fn () => route('vendors.show', $this->record->store_slug), shouldOpenInNewTab: true)
                ->color('gray'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);
        $this->form->model($this->record)->saveRelationships();

        Notification::make()
            ->title('Storefront Updated')
            ->body('Your storefront settings and homepage blocks have been updated successfully.')
            ->success()
            ->send();
    }
}
```

---

### `resources/views/filament/vendor/pages/manage-storefront.blade.php`

```html
<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-4 pt-4">
            <x-filament::button type="submit" size="sm">
                Save Changes
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
```

---

## Notes

- **Aesthetics**: Responsive store layout with a parallax-capable hero banner, overlapping store logo card, and shadow hover highlights.
- **Page Builder integration**: Uses the polymorphic `page_builder_blocks` relationship via the `HasPageBuilder` trait and registers blocks natively under the new custom `ManageStorefront` Filament page.
- **Subscription checks & preview**: Blocks public access to a vendor's page if they have no active subscription, while letting the vendor owner view and preview their own storefront (via bypass logic).

