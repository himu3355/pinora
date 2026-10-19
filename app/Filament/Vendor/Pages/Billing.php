<?php

namespace App\Filament\Vendor\Pages;

use App\Models\SubscriptionPlan;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Billing extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Billing';
    protected static ?string $title = 'Billing & Subscriptions';
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.vendor.pages.billing';

    public $status = '';
    public $trialDays = 0;
    public $endsAt = null;
    public $plans = [];

    public function mount()
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $sub = $vendor->subscription;
        if ($sub) {
            $this->status = $sub->status;
            $this->trialDays = $vendor->trialDaysRemaining();
            $this->endsAt = $sub->ends_at;
        }

        $this->plans = SubscriptionPlan::where('is_active', true)->get()->toArray();
    }

    public function subscribe($planId)
    {
        $plan = SubscriptionPlan::find($planId);

        if (!$plan || !$plan->is_active) {
            Notification::make()
                ->title('Invalid Plan')
                ->body('The selected subscription plan is not active or does not exist.')
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Subscription Request Received')
            ->body('Online vendor subscription checkout is currently offline. Please contact the administrator to activate or renew this plan.')
            ->info()
            ->send();
    }
}
