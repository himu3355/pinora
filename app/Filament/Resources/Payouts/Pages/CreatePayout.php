<?php

namespace App\Filament\Resources\Payouts\Pages;

use App\Filament\Resources\Payouts\PayoutResource;
use App\Models\OrderItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePayout extends CreateRecord
{
    protected static string $resource = PayoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate payout reference
        $data['payout_reference'] = 'PAY-' . date('Y') . '-' . strtoupper(Str::random(5));
        $data['status'] = 'draft'; // Model default status

        // Query matching order items
        $orderItems = OrderItem::query()
            ->where('fulfillment_status', '!=', 'cancelled')
            ->where('vendor_id', $data['vendor_id'])
            ->whereDate('created_at', '>=', $data['period_from'])
            ->whereDate('created_at', '<=', $data['period_to']);

        $totalAmount = (float) $orderItems->sum('total_price');
        $data['total_orders_amount']   = $totalAmount;
        $data['total_vendor_earnings'] = $totalAmount; // 100% of order totals

        $adjustments = (float) ($data['adjustments'] ?? 0);
        $data['final_payout_amount'] = $data['total_vendor_earnings'] + $adjustments;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
