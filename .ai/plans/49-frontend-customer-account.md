# Step 49: My Account Page

**Goal:** Create a comprehensive customer account panel for profile editing, address CRUD, order history, and wishlist management.
**Depends On:** Step 12 (User model), Step 18 (Address model), Step 19 (Wishlist model), Step 20 (Order/OrderItem models)
**Next Step:** Step 50 (Vendor Storefront Page)

---

## Goal Explanation

Customers need a secure dashboard to manage their personal details, track orders, manage shipping addresses, and review their wishlist. 
This step builds the auth-protected `/account` namespace, including:
1. An account dashboard with a summary of recent activity.
2. An order history list with pagination and detailed order lookups.
3. A address manager with full CRUD capabilities (supporting a single "default" address).
4. A wishlist manager where customers can view and remove saved items.
5. A profile editor for updating name, email, phone, and password.

All views share a common sidebar layout for account navigation.

---

## Files to Create / Modify

### New Files:
- `app/Http/Controllers/Frontend/AccountController.php`
- `resources/views/account/dashboard.blade.php`
- `resources/views/account/orders.blade.php`
- `resources/views/account/order-detail.blade.php`
- `resources/views/account/wishlist.blade.php`
- `resources/views/account/addresses.blade.php`
- `resources/views/account/profile.blade.php`

### Modified Files:
- `routes/web.php`

---

## Complete PHP & Blade Code

### `app/Http/Controllers/Frontend/AccountController.php`

```php
<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Show the account dashboard.
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        $recentOrders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $defaultAddress = Address::where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        $wishlistCount = Wishlist::where('user_id', $user->id)->count();

        return view('account.dashboard', compact('user', 'recentOrders', 'defaultAddress', 'wishlistCount'));
    }

    /**
     * Show the list of orders.
     */
    public function orders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('account.orders', compact('orders'));
    }

    /**
     * Show detailed view of a specific order.
     */
    public function orderDetail(Order $order)
    {
        // Abort if order doesn't belong to current user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['items.product', 'items.variant', 'items.vendor']);

        return view('account.order-detail', compact('order'));
    }

    /**
     * Show wishlist manager.
     */
    public function wishlist()
    {
        $wishlistItems = Wishlist::where('user_id', auth()->id())
            ->with(['product.primaryImage', 'product.vendor'])
            ->get();

        return view('account.wishlist', compact('wishlistItems'));
    }

    /**
     * Show address manager.
     */
    public function addresses()
    {
        $addresses = Address::where('user_id', auth()->id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $states = Address::INDIAN_STATES;

        return view('account.addresses', compact('addresses', 'states'));
    }

    /**
     * Store a new shipping address.
     */
    public function addressStore(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:100'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', Rule::in(array_keys(Address::INDIAN_STATES))],
            'pincode' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = isset($validated['is_default']) && $validated['is_default'];

        if ($isDefault) {
            // Set all other user addresses default status to false
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        // If this is the user's first address, make it default automatically
        $hasAddresses = Address::where('user_id', auth()->id())->exists();
        if (!$hasAddresses) {
            $isDefault = true;
        }

        $label = empty($validated['label']) ? ucfirst($validated['type']) : $validated['label'];

        Address::create([
            'user_id' => auth()->id(),
            'type' => 'shipping',
            'label' => $label,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'],
            'landmark' => $validated['landmark'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'],
            'is_default' => $isDefault,
        ]);

        return redirect()->route('account.addresses')->with('success', 'Address added successfully.');
    }

    /**
     * Update an existing address.
     */
    public function addressUpdate(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:100'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', Rule::in(array_keys(Address::INDIAN_STATES))],
            'pincode' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = isset($validated['is_default']) && $validated['is_default'];

        if ($isDefault) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $label = empty($validated['label']) ? ucfirst($validated['type']) : $validated['label'];

        $address->update([
            'type' => 'shipping',
            'label' => $label,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'],
            'landmark' => $validated['landmark'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'],
            'is_default' => $isDefault,
        ]);

        return redirect()->route('account.addresses')->with('success', 'Address updated successfully.');
    }

    /**
     * Delete an address.
     */
    public function addressDestroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // If we deleted the default, make the next latest address default
        if ($wasDefault) {
            $nextAddress = Address::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->first();
            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        return redirect()->route('account.addresses')->with('success', 'Address deleted successfully.');
    }

    /**
     * Set default address manually.
     */
    public function addressSetDefault(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        Address::where('user_id', auth()->id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return redirect()->route('account.addresses')->with('success', 'Default address updated.');
    }

    /**
     * Show profile editor.
     */
    public function profile()
    {
        $user = auth()->user();
        return view('account.profile', compact('user'));
    }

    /**
     * Update customer profile.
     */
    public function profileUpdate(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'The provided current password does not match.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return redirect()->route('account.profile')->with('success', 'Profile updated successfully.');
    }
}
```

---

### `resources/views/account/dashboard.blade.php`

```html
@extends('layouts.app')

@section('title', 'My Account Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'dashboard'])
        </div>

        <!-- Main Panel Content -->
        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Welcome, {{ $user->name }}</h1>
                <p class="text-text-muted mt-1">From your dashboard you can track recent orders, manage addresses, and edit profile details.</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Orders</span>
                    <span class="text-3xl font-black text-gold mt-2">{{ $recentOrders->count() }}</span>
                </div>
                <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">Wishlist Items</span>
                    <span class="text-3xl font-black text-gold mt-2">{{ $wishlistCount }}</span>
                </div>
                <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">Phone</span>
                    <span class="text-md font-bold text-text-light mt-2 overflow-hidden truncate">{{ $user->phone ?? 'Not set' }}</span>
                </div>
            </div>

            <!-- Split Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Recent Orders -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-text-light">Recent Orders</h2>
                        <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-gold hover:text-gold-light transition">View All</a>
                    </div>
                    @if($recentOrders->isEmpty())
                        <div class="border border-dashed border-border-gold/50 rounded-xl p-8 text-center text-text-muted text-sm">
                            No orders placed yet.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($recentOrders as $order)
                                <div class="flex items-center justify-between p-4 border border-border-gold/50 rounded-xl hover:bg-gold/5 transition">
                                    <div>
                                        <p class="text-sm font-bold text-text-light">{{ $order->order_number }}</p>
                                        <p class="text-xs text-text-muted">{{ $order->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gold">₹{{ number_format($order->total_amount, 2) }}</p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gold/10 text-gold uppercase">
                                            {{ $order->status }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Default Address -->
                <div>
                    <h2 class="text-lg font-bold text-text-light mb-4">Default Shipping Address</h2>
                    @if(!$defaultAddress)
                        <div class="border border-dashed border-border-gold/50 rounded-xl p-8 text-center text-text-muted text-sm mb-4">
                            No default address saved.
                        </div>
                        <a href="{{ route('account.addresses') }}" class="btn btn-outline-gold w-full justify-center">
                            Add Address
                        </a>
                    @else
                        <div class="border border-border-gold p-5 rounded-2xl bg-dark-card shadow-sm relative">
                            <span class="absolute top-4 right-4 bg-gold/10 text-gold text-xs font-bold px-2.5 py-1 rounded-full uppercase">
                                Default
                            </span>
                            <p class="font-bold text-text-light text-base mb-1">{{ $defaultAddress->full_name }}</p>
                            <p class="text-sm text-gold uppercase tracking-wide font-medium mb-3">{{ $defaultAddress->type }}</p>
                            <p class="text-sm text-text-muted">{{ $defaultAddress->address_line_1 }}</p>
                            @if($defaultAddress->address_line_2)
                                <p class="text-sm text-text-muted">{{ $defaultAddress->address_line_2 }}</p>
                            @endif
                            <p class="text-sm text-text-muted">{{ $defaultAddress->city }}, {{ $defaultAddress->state }} - {{ $defaultAddress->pincode }}</p>
                            <p class="text-sm text-text-muted mt-2"><span class="font-semibold text-text-light">Phone:</span> {{ $defaultAddress->phone }}</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
`````

---

### `resources/views/account/orders.blade.php`

```html
@extends('layouts.app')

@section('title', 'My Order History')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'orders'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Order History</h1>
                <p class="text-text-muted mt-1">Review status and details of all your past and pending orders.</p>
            </div>

            @if($orders->isEmpty())
                <div class="text-center py-16 border border-dashed border-border-gold/50 rounded-2xl">
                    <p class="text-text-muted text-lg">You haven't placed any orders yet.</p>
                    <a href="/" class="btn btn-gold mt-4">
                        Start Shopping
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-gold/50">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Order No.</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Payment</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Total</th>
                                <th scope="col" class="relative px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-gold/20 bg-transparent">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gold/5 transition">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-text-light">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-text-muted">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase 
                                            @if($order->status === 'delivered') bg-green-500/10 text-green-400
                                            @elseif($order->status === 'cancelled') bg-red-500/10 text-red-400
                                            @elseif($order->status === 'pending') bg-yellow-500/10 text-yellow-400
                                            @else bg-blue-500/10 text-blue-400 @endif">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-text-muted">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            @if($order->payment_status === 'paid') bg-green-500/10 text-green-400
                                            @elseif($order->payment_status === 'failed') bg-red-500/10 text-red-400
                                            @else bg-gold/10 text-gold @endif">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-extrabold text-gold text-right">
                                        ₹{{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('account.orders.show', $order->id) }}" class="text-gold hover:text-gold-light font-bold transition">View Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
```
```

---

### `resources/views/account/order-detail.blade.php`

```html
@extends('layouts.app')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'orders'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center pb-6 border-b border-border-gold/50 mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Order {{ $order->order_number }}</h1>
                    <p class="text-text-muted mt-1">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-gold/10 text-gold uppercase">
                        Order Status: {{ $order->status }}
                    </span>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase 
                        @if($order->payment_status === 'paid') bg-green-500/10 text-green-400
                        @else bg-yellow-500/10 text-yellow-400 @endif">
                        Payment: {{ $order->payment_status }}
                    </span>
                </div>
            </div>

            <!-- Items summary -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-text-light mb-4">Ordered Items</h2>
                <div class="divide-y divide-border-gold/50 border border-border-gold rounded-2xl overflow-hidden">
                    @foreach($order->items as $item)
                        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center gap-6 bg-transparent">
                            <!-- Product Image Placeholder or real -->
                            <div class="w-20 h-20 bg-dark-bg/50 border border-border-gold rounded-xl overflow-hidden shrink-0 flex items-center justify-center">
                                <img src="{{ $item->product ? $item->product->primary_image_url : asset('images/product-placeholder.png') }}" class="object-cover w-full h-full" alt="{{ $item->product_name }}">
                            </div>

                            <div class="flex-grow">
                                <h3 class="font-bold text-text-light text-base leading-snug">{{ $item->product_name }}</h3>
                                @if($item->variant_name)
                                    <p class="text-xs text-text-muted mt-0.5">Variant: {{ $item->variant_name }}</p>
                                @endif
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-text-muted font-medium">
                                    @if($item->metal_type)
                                        <span>Metal: <span class="text-gold font-semibold">{{ ucfirst($item->metal_type) }} ({{ $item->purity }})</span></span>
                                    @endif
                                    @if($item->weight_grams)
                                        <span>Weight: <span class="text-gold font-semibold">{{ number_format($item->weight_grams, 3) }}g</span></span>
                                    @endif
                                    <span>Vendor: <span class="text-gold font-semibold">{{ $item->vendor->store_name }}</span></span>
                                </div>
                            </div>

                            <div class="flex flex-col items-start sm:items-end w-full sm:w-auto shrink-0 mt-4 sm:mt-0 pt-4 sm:pt-0 border-t sm:border-t-0 border-border-gold/50">
                                <p class="text-sm text-text-muted font-medium">₹{{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }}</p>
                                <p class="text-base font-extrabold text-gold mt-0.5">₹{{ number_format($item->subtotal, 2) }}</p>
                                <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-gold/10 text-gold">
                                    Fulfillment: {{ $item->fulfillment_status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Two Column Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Shipping Details -->
                <div>
                    <h2 class="text-xl font-bold text-text-light mb-4">Shipping Information</h2>
                    <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50">
                        <p class="font-extrabold text-text-light text-lg mb-2">{{ $order->shipping_name }}</p>
                        <p class="text-sm text-text-muted leading-relaxed">{{ $order->shipping_address_line_1 }}</p>
                        @if($order->shipping_address_line_2)
                            <p class="text-sm text-text-muted leading-relaxed">{{ $order->shipping_address_line_2 }}</p>
                        @endif
                        @if($order->shipping_landmark)
                            <p class="text-sm text-text-muted leading-relaxed">Landmark: {{ $order->shipping_landmark }}</p>
                        @endif
                        <p class="text-sm text-text-muted leading-relaxed">{{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}</p>
                        <p class="text-sm text-text-muted leading-relaxed mt-1">{{ $order->shipping_country }}</p>
                        <p class="text-sm text-text-light font-semibold mt-4">Phone: {{ $order->shipping_phone }}</p>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div>
                    <h2 class="text-xl font-bold text-text-light mb-4">Financial Summary</h2>
                    <div class="bg-dark-bg/50 p-6 rounded-2xl border border-border-gold/50 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-text-muted font-medium">Subtotal</span>
                            <span class="text-text-light font-bold">₹{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-sm text-red-450 font-semibold">
                                <span>Discount</span>
                                <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->cgst_amount > 0)
                            <div class="flex justify-between text-sm text-text-muted">
                                <span>CGST</span>
                                <span class="text-text-light font-semibold">₹{{ number_format($order->cgst_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->sgst_amount > 0)
                            <div class="flex justify-between text-sm text-text-muted">
                                <span>SGST</span>
                                <span class="text-text-light font-semibold">₹{{ number_format($order->sgst_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->igst_amount > 0)
                            <div class="flex justify-between text-sm text-text-muted">
                                <span>IGST (3%)</span>
                                <span class="text-text-light font-semibold">₹{{ number_format($order->igst_amount, 2) }}</span>
                            </div>
                        @endif
                        
                        <div class="border-t border-border-gold/50 pt-4 flex justify-between items-baseline">
                            <span class="text-base font-extrabold text-text-light">Grand Total</span>
                            <span class="text-2xl font-black text-gold">₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
```
```

---

### `resources/views/account/wishlist.blade.php`

```html
@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'wishlist'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-extrabold text-text-light tracking-tight">My Wishlist</h1>
                <p class="text-text-muted mt-1">Keep track of your favorite jewellery pieces and add them directly to your cart.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if($wishlistItems->isEmpty())
                <div class="text-center py-16 border border-dashed border-border-gold/50 rounded-2xl">
                    <p class="text-text-muted text-lg">Your wishlist is currently empty.</p>
                    <a href="/" class="btn btn-gold mt-4">
                        Explore Products
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($wishlistItems as $item)
                        @php $product = $item->product; @endphp
                        @if($product)
                            <div class="group bg-dark-card border border-border-gold/50 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition duration-200 flex flex-col h-full">
                                
                                <!-- Product Image & Remove Action -->
                                <div class="relative aspect-square bg-dark-bg/50 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $product->primary_image_url }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-300" alt="{{ $product->name }}">
                                    
                                    <form action="{{ route('account.wishlist.remove', $product->id) }}" method="POST" class="absolute top-3 right-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-dark-bg/85 backdrop-blur-sm p-2 rounded-full text-red-500 hover:bg-dark-bg hover:scale-110 transition shadow-sm" title="Remove from Wishlist">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </form>
                                </div>

                                <!-- Product Info -->
                                <div class="p-5 flex flex-col justify-between flex-grow">
                                    <div>
                                        <p class="text-xs text-gold font-semibold uppercase tracking-wider mb-1">{{ $product->vendor->store_name }}</p>
                                        <h3 class="font-bold text-text-light text-sm line-clamp-2 h-10 mb-2 leading-tight">
                                            <a href="{{ route('product.show', $product->slug) }}" class="hover:text-gold transition">{{ $product->name }}</a>
                                        </h3>
                                        <div class="mb-4">
                                            @if($product->is_price_on_request)
                                                <span class="text-sm font-semibold text-text-muted">Price on Request</span>
                                            @else
                                                <span class="text-lg font-black text-gold">₹{{ number_format($product->calculated_price, 2) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        @if($product->stock_quantity > 0 && !$product->is_price_on_request)
                                            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-gold hover:bg-gold-light text-dark-bg font-bold text-xs rounded-xl shadow-sm transition">
                                                    Add To Cart
                                                </button>
                                            </form>
                                        @else
                                            <button disabled class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-dark-bg/50 text-text-muted font-bold text-xs rounded-xl cursor-not-allowed border border-border-gold/50">
                                                {{ $product->is_price_on_request ? 'Contact Vendor' : 'Out of Stock' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
```

---

### `resources/views/account/addresses.blade.php`

```html
@extends('layouts.app')

@section('title', 'Manage Addresses')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'addresses'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Saved Addresses</h1>
                    <p class="text-text-muted mt-1">Manage shipping locations for convenient one-click checkouts.</p>
                </div>
                <div>
                    <button onclick="toggleForm('create')" class="btn btn-gold">
                        Add New Address
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Address Form (Initially Hidden) -->
            <div id="address-form-container" class="hidden mb-8 p-6 bg-dark-bg border border-border-gold rounded-2xl">
                <h3 id="form-title" class="text-lg font-bold text-text-light mb-6">Add a New Address</h3>
                
                <form id="address-form" method="POST" action="{{ route('account.addresses.store') }}">
                    @csrf
                    <input type="hidden" id="form-method" name="_method" value="POST">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label for="full_name" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Recipient Name *</label>
                            <input type="text" name="full_name" id="full_name" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Phone Number *</label>
                            <input type="text" name="phone" id="phone" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                        <div>
                            <label for="type" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Address Type *</label>
                            <select name="type" id="type" required class="w-full bg-dark-bg border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition cursor-pointer">
                                <option value="home">Home</option>
                                <option value="work">Work / Office</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="label" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Optional Label (e.g. Mom's House)</label>
                            <input type="text" name="label" id="label" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="address_line_1" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Street Address *</label>
                        <input type="text" name="address_line_1" id="address_line_1" required placeholder="House No, Apartment, Street" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition placeholder:text-text-muted">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label for="address_line_2" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Address Line 2</label>
                            <input type="text" name="address_line_2" id="address_line_2" placeholder="Suite, Unit, Area (Optional)" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition placeholder:text-text-muted">
                        </div>
                        <div>
                            <label for="landmark" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Landmark</label>
                            <input type="text" name="landmark" id="landmark" placeholder="e.g. Near City Mall (Optional)" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition placeholder:text-text-muted">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
                        <div>
                            <label for="city" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">City *</label>
                            <input type="text" name="city" id="city" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="state" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">State / UT *</label>
                            <select name="state" id="state" required class="w-full bg-dark-bg border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition cursor-pointer">
                                <option value="">Select State</option>
                                @foreach($states as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="pincode" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Pincode *</label>
                            <input type="text" name="pincode" id="pincode" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>

                    <div class="flex items-center mb-6">
                        <input type="checkbox" name="is_default" id="is_default" value="1" class="h-4.5 w-4.5 text-gold focus:ring-gold border-border-gold bg-transparent rounded accent-gold">
                        <label for="is_default" class="ml-2.5 text-sm font-semibold text-text-muted">Set as default shipping address</label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="toggleForm('hide')" class="px-5 py-2.5 border border-border-gold text-text-muted font-bold text-sm rounded-xl hover:bg-gold/5 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-gold hover:bg-gold-light text-dark-bg font-bold text-sm rounded-xl shadow-sm transition">
                            Save Address
                        </button>
                    </div>
                </form>
            </div>

            <!-- Addresses List -->
            @if($addresses->isEmpty())
                <div class="text-center py-16 border border-dashed border-border-gold/55 rounded-2xl">
                    <p class="text-text-muted text-base">You haven't saved any addresses yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($addresses as $address)
                        <div class="border border-border-gold/55 p-6 rounded-2xl bg-dark-card shadow-sm relative flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <span class="px-2.5 py-1 bg-gold/10 border border-border-gold/50 text-gold text-xs font-bold rounded-full uppercase tracking-wider">
                                        {{ $address->label ?? ucfirst($address->type) }}
                                    </span>
                                    @if($address->is_default)
                                        <span class="bg-gold/10 text-gold text-xs font-bold px-2.5 py-1 rounded-full uppercase">
                                            Default
                                        </span>
                                    @endif
                                </div>
                                <h3 class="font-extrabold text-text-light text-lg mb-1">{{ $address->full_name }}</h3>
                                <p class="text-sm text-text-muted leading-relaxed">{{ $address->address_line_1 }}</p>
                                @if($address->address_line_2)
                                    <p class="text-sm text-text-muted leading-relaxed">{{ $address->address_line_2 }}</p>
                                @endif
                                @if($address->landmark)
                                    <p class="text-sm text-text-muted/80 leading-relaxed italic mt-0.5">Near: {{ $address->landmark }}</p>
                                @endif
                                <p class="text-sm text-text-muted leading-relaxed mt-0.5">{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                                <p class="text-sm text-text-muted mt-3 font-medium"><span class="text-text-muted/65">Phone:</span> {{ $address->phone }}</p>
                            </div>

                            <div class="border-t border-border-gold/30 pt-4 mt-6 flex justify-between items-center">
                                <div class="flex gap-3">
                                    <button onclick="editAddress({{ json_encode($address) }})" class="text-xs font-bold text-text-muted hover:text-gold transition">
                                        Edit
                                    </button>
                                    
                                    <form action="{{ route('account.addresses.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>

                                @if(!$address->is_default)
                                    <form action="{{ route('account.addresses.default', $address->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-gold hover:text-gold-light transition">
                                            Set as Default
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

<script>
    function toggleForm(action) {
        const container = document.getElementById('address-form-container');
        if (action === 'create') {
            document.getElementById('form-title').innerText = 'Add a New Address';
            document.getElementById('address-form').action = "{{ route('account.addresses.store') }}";
            document.getElementById('form-method').value = "POST";
            document.getElementById('address-form').reset();
            container.classList.remove('hidden');
            container.scrollIntoView({ behavior: 'smooth' });
        } else if (action === 'hide') {
            container.classList.add('hidden');
        }
    }

    function editAddress(address) {
        const container = document.getElementById('address-form-container');
        document.getElementById('form-title').innerText = 'Edit Address';
        
        let actionUrl = "{{ route('account.addresses.update', ':id') }}";
        actionUrl = actionUrl.replace(':id', address.id);
        
        document.getElementById('address-form').action = actionUrl;
        document.getElementById('form-method').value = "PUT";
        
        // Resolve type and label based on DB label value
        let typeVal = 'other';
        let labelVal = address.label || '';
        
        const lowerLabel = labelVal.toLowerCase().trim();
        if (lowerLabel === 'home') {
            typeVal = 'home';
            labelVal = '';
        } else if (lowerLabel === 'work' || lowerLabel === 'office') {
            typeVal = 'work';
            labelVal = '';
        }
        
        // Populate inputs
        document.getElementById('full_name').value = address.full_name;
        document.getElementById('phone').value = address.phone;
        document.getElementById('type').value = typeVal;
        document.getElementById('label').value = labelVal;
        document.getElementById('address_line_1').value = address.address_line_1;
        document.getElementById('address_line_2').value = address.address_line_2 || '';
        document.getElementById('landmark').value = address.landmark || '';
        document.getElementById('city').value = address.city;
        document.getElementById('state').value = address.state;
        document.getElementById('pincode').value = address.pincode;
        document.getElementById('is_default').checked = address.is_default;
        
        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth' });
    }
</script>
@endsection
```

---

### `resources/views/account/profile.blade.php`

```html
@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'profile'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Profile Details</h1>
                <p class="text-text-muted mt-1">Manage your contact information and account security password.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl text-sm font-semibold">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('account.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Personal Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-text-light mb-4 pb-2 border-b border-border-gold/30">Personal Info</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Full Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="email" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Email Address *</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Password Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-text-light mb-2 pb-2 border-b border-border-gold/30">Change Password</h3>
                    <p class="text-text-muted text-xs mb-4">Leave fields blank if you do not wish to update your password.</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <label for="current_password" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="new_password" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-border-gold/30">
                    <button type="submit" class="btn btn-gold">
                        Update Account
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
```
```

---

### `resources/views/account/partials/sidebar.blade.php`

```html
<div class="bg-dark-card border border-border-gold rounded-2xl p-4 space-y-1.5 shadow-sm">
    <a href="{{ route('account.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'dashboard' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    
    <a href="{{ route('account.orders') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'orders' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        Order History
    </a>
    
    <a href="{{ route('account.wishlist') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'wishlist' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        Wishlist
    </a>
    
    <a href="{{ route('account.addresses') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'addresses' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Addresses
    </a>
    
    <a href="{{ route('account.profile') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ $active === 'profile' ? 'bg-gold/10 text-gold' : 'text-text-muted hover:bg-gold/5' }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Profile Settings
    </a>

    <div class="pt-4 mt-4 border-t border-border-gold/30">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-bold text-red-500 rounded-xl hover:bg-red-500/10 transition duration-150">
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </button>
        </form>
    </div>
</div>
```

---

### `routes/web.php` (Partial Addition)

```php
// Account panel routes (auth-protected)
Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', [App\Http\Controllers\Frontend\AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [App\Http\Controllers\Frontend\AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [App\Http\Controllers\Frontend\AccountController::class, 'orderDetail'])->name('orders.show');
    Route::get('/wishlist', [App\Http\Controllers\Frontend\AccountController::class, 'wishlist'])->name('wishlist');
    
    Route::get('/addresses', [App\Http\Controllers\Frontend\AccountController::class, 'addresses'])->name('addresses');
    Route::post('/addresses', [App\Http\Controllers\Frontend\AccountController::class, 'addressStore'])->name('addresses.store');
    Route::put('/addresses/{address}', [App\Http\Controllers\Frontend\AccountController::class, 'addressUpdate'])->name('addresses.update');
    Route::delete('/addresses/{address}', [App\Http\Controllers\Frontend\AccountController::class, 'addressDestroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [App\Http\Controllers\Frontend\AccountController::class, 'addressSetDefault'])->name('addresses.default');
    
    Route::get('/profile', [App\Http\Controllers\Frontend\AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\Frontend\AccountController::class, 'profileUpdate'])->name('profile.update');
});
```

---

## Notes

- **Tailwind v4 / Modern Styling**: High-quality visual markup is used utilizing HSL-style color palettes, rounded borders (`rounded-2xl`), shadow utilities, hover scaling effects, and clean sidebar layout structure.
- **CSRF & Method Spoofing**: HTML forms contain `@csrf` and appropriate `@method('PUT')` / `@method('DELETE')` helpers.
- **Security Check**: The controller strictly verifies ownership: `if ($order->user_id !== auth()->id()) { abort(403); }` to prevent ID-harvesting vulnerability.
- **Input Validation**: Custom checking via `Rule::in(array_keys(Address::INDIAN_STATES))` guarantees that customers only provide validated Indian states.
