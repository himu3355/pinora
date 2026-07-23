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
            ->whereHas('product', function ($query) {
                $query->active();
            })
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

    /**
     * Toggle wishlist status of a product (AJAX).
     */
    public function toggleWishlist(int $productId)
    {
        $userId = auth()->id();
        $wishlist = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();

        if ($wishlist) {
            $wishlist->delete();
            $wishlisted = false;
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            $wishlisted = true;
        }

        $recent = Wishlist::where('user_id', $userId)
            ->latest()
            ->take(3)
            ->with('product.primaryImage')
            ->get()
            ->map(function ($w) {
                if (!$w->product) return null;
                return [
                    'product_id' => $w->product->id,
                    'name' => $w->product->name,
                    'slug' => $w->product->slug,
                    'image_url' => $w->product->primary_image_url,
                    'is_price_on_request' => $w->product->is_price_on_request,
                    'price' => $w->product->calculated_price,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'wishlisted' => $wishlisted,
            'count' => Wishlist::where('user_id', $userId)->count(),
            'recent' => $recent,
        ]);
    }

    /**
     * Remove a product from wishlist (Form submit).
     */
    public function wishlistDestroy(int $productId)
    {
        Wishlist::where('user_id', auth()->id())->where('product_id', $productId)->delete();
        return redirect()->route('account.wishlist')->with('success', 'Item removed from wishlist.');
    }
}
