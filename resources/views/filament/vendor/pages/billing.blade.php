<x-filament-panels::page>
    <div class="space-y-8">
        
        <!-- Status Card -->
        <div class="max-w-4xl">
            <x-filament::section>
                <x-slot name="heading">
                    Current Subscription Status
                </x-slot>

                <div class="space-y-4">
                    @if ($status === 'trialing' && $trialDays > 0)
                        <div class="p-4 bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-900 rounded-xl flex items-center gap-4">
                            <div class="p-3 bg-amber-500 rounded-lg text-white shrink-0">
                                <x-filament::icon icon="heroicon-o-gift" class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-amber-900 dark:text-amber-100">Free Trial Active</h3>
                                <p class="text-sm text-amber-700 dark:text-amber-300">You have <strong>{{ $trialDays }} days</strong> remaining on your free trial. All portal features and your public store are fully active.</p>
                            </div>
                        </div>
                    @elseif ($status === 'active')
                        <div class="p-4 bg-success-50 dark:bg-success-950 border border-success-200 dark:border-success-900 rounded-xl flex items-center gap-4">
                            <div class="p-3 bg-success-500 rounded-lg text-white shrink-0">
                                <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-success-900 dark:text-success-100">Subscription Active</h3>
                                <p class="text-sm text-success-700 dark:text-success-300">Your annual subscription is active. It will expire on <strong>{{ $endsAt ? $endsAt->format('d M Y') : 'N/A' }}</strong>.</p>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-danger-50 dark:bg-danger-950 border border-danger-200 dark:border-danger-900 rounded-xl flex items-center gap-4">
                            <div class="p-3 bg-danger-500 rounded-lg text-white shrink-0">
                                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-danger-900 dark:text-danger-100">Subscription Expired / Inactive</h3>
                                <p class="text-sm text-danger-700 dark:text-danger-300">
                                    Your trial/subscription is not active. Your public storefront is hidden from customers, and you cannot view orders or manage customers. Please subscribe below to activate your store.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-gray-100 dark:border-gray-800 pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Subscription ID</span>
                            <span class="font-mono text-gray-900 dark:text-gray-100">{{ auth()->user()->vendor->subscription->stripe_subscription_id ?? 'None' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Valid Until</span>
                            <span class="text-gray-900 dark:text-gray-100">
                                @if($status === 'trialing')
                                    {{ auth()->user()->vendor->subscription->trial_ends_at ? auth()->user()->vendor->subscription->trial_ends_at->format('d M Y') : 'N/A' }} (Trial)
                                @else
                                    {{ $endsAt ? $endsAt->format('d M Y') : 'Expired / None' }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Plans Section -->
        <div class="space-y-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Available Subscription Plans</h2>
            
            @if (count($plans) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($plans as $plan)
                        <x-filament::section class="flex flex-col h-full justify-between">
                            <div class="space-y-4 text-center py-4 flex-grow">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ $plan['name'] }}
                                </h3>
                                @if (!empty($plan['description']))
                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">
                                        {{ $plan['description'] }}
                                    </p>
                                @endif
                                <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 my-4">
                                    ₹{{ number_format($plan['price'], 2) }}
                                    <span class="text-sm font-normal text-gray-500">/ Year</span>
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                    Gain complete access to the Pinora vendor portal: list products, manage customer orders, view payout reports, and showcase your storefront.
                                </p>
                            </div>
                            
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                @if ($isStripeConfigured)
                                    <x-filament::button
                                        wire:click="subscribe({{ $plan['id'] }})"
                                        size="lg"
                                        class="w-full"
                                        icon="heroicon-m-credit-card"
                                    >
                                        Subscribe Now
                                    </x-filament::button>
                                @else
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-center">
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            Stripe online checkout is disabled. Please contact the administrator to activate this plan.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>
            @else
                <div class="p-6 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-center">
                    <p class="text-gray-600 dark:text-gray-400">
                        No active subscription plans are currently configured. Please contact the administrator.
                    </p>
                </div>
            @endif
        </div>
        
    </div>
</x-filament-panels::page>
