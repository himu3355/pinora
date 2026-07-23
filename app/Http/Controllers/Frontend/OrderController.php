<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function confirmation(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->with(['items.product.primaryImage', 'items.vendor'])
            ->firstOrFail();

        // Send confirmation email (only if not already sent)
        if (! $order->confirmation_email_sent_at) {
            try {
                Mail::to($order->user->email)->queue(new OrderConfirmationMail($order));
                $order->update(['confirmation_email_sent_at' => now()]);
            } catch (\Exception $e) {
                report($e);
            }
        }

        return view('orders.confirmation', compact('order'));
    }

    public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->with(['items.product.primaryImage', 'items.variant', 'items.vendor'])
            ->firstOrFail();

        return view('account.order-detail', compact('order'));
    }
}
