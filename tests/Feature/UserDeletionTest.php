<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_deletion_soft_deletes_user_and_vendor(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => 'Test Store',
            'store_slug' => 'test-store',
            'status' => 'approved',
        ]);

        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
    }

    public function test_user_restoration_restores_vendor(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => 'Test Store 2',
            'store_slug' => 'test-store-2',
            'status' => 'approved',
        ]);

        $user->delete();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);

        $user->restore();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'deleted_at' => null]);
    }

    public function test_force_deleting_user_with_order_items_is_blocked(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => 'Test Store 3',
            'store_slug' => 'test-store-3',
            'status' => 'approved',
        ]);

        $customer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'ORD-12345',
            'user_id' => $customer->id,
            'shipping_name' => 'John Doe',
            'shipping_phone' => '1234567890',
            'shipping_address_line_1' => '123 Street',
            'shipping_city' => 'City',
            'shipping_state' => 'State',
            'shipping_pincode' => '123456',
            'subtotal' => 100,
            'total_amount' => 100,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'product_name' => 'Gold Ring',
            'unit_price' => 100,
            'subtotal' => 100,
            'total_price' => 100,
        ]);

        $this->expectException(\Exception::class);
        $user->forceDelete();
    }
}
