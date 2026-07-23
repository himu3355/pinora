@php
    use App\Filament\Components\PricingCalculatorSection;

    // Read live form values from the Livewire component's data
    $formData = $this->data ?? [];

    $metalType = data_get($formData, 'metal_type');
    $purity = data_get($formData, 'purity');
    $weightGrams = data_get($formData, 'weight_grams');
    $makingCharges = data_get($formData, 'making_charges');
    $makingChargesType = data_get($formData, 'making_charges_type', 'fixed');
    $basePrice = data_get($formData, 'base_price');
    $discountPercent = data_get($formData, 'discount_percent', 0);
    $isPriceOnRequest = (bool) data_get($formData, 'is_price_on_request', false);

    $breakdown = PricingCalculatorSection::calculateFromFormData(
        metalType: $metalType,
        purity: $purity,
        weightGrams: $weightGrams,
        makingCharges: $makingCharges,
        makingChargesType: $makingChargesType,
        basePrice: $basePrice,
        discountPercent: $discountPercent,
        isPriceOnRequest: $isPriceOnRequest,
    );

    $chargesTypeLabels = [
        'fixed' => 'Fixed',
        'per_gram' => 'Per Gram',
        'percentage' => 'Percentage',
    ];

    $metalLabels = [
        'gold' => 'Gold',
        'silver' => 'Silver',
        'platinum' => 'Platinum',
        'other' => 'Other',
    ];
@endphp

<div class="space-y-4">
    {{-- Price On Request State --}}
    @if ($breakdown['is_price_on_request'])
        <div class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800">
            <div class="p-2 bg-amber-500 rounded-lg text-white shrink-0">
                <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-5 w-5" />
            </div>
            <div>
                <p class="font-semibold text-amber-900 dark:text-amber-100">Price On Request</p>
                <p class="text-sm text-amber-700 dark:text-amber-300">This product will display a "Get Quote" button instead of a price.</p>
            </div>
        </div>
    @elseif ($breakdown['total_price'] <= 0 && !$breakdown['use_override'])
        {{-- No data yet --}}
        <div class="flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="p-2 bg-gray-400 dark:bg-gray-600 rounded-lg text-white shrink-0">
                <x-filament::icon icon="heroicon-o-calculator" class="h-5 w-5" />
            </div>
            <div>
                <p class="font-semibold text-gray-700 dark:text-gray-300">Fill in pricing fields</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Enter metal type, purity, weight, and other details to see the calculated price.</p>
            </div>
        </div>
    @else
        {{-- Calculation Breakdown --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

            {{-- Header --}}
            <div class="px-4 py-3 bg-primary-50 dark:bg-primary-950/30 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-calculator" class="h-5 w-5 text-primary-500" />
                        <span class="font-semibold text-sm text-primary-700 dark:text-primary-300">
                            @if ($breakdown['use_override'])
                                Base Price Override Mode
                            @else
                                Live Metal Rate Calculation
                            @endif
                        </span>
                    </div>
                    @if (!$breakdown['use_override'] && $breakdown['metal_rate_used'] > 0)
                        <span class="text-xs font-medium px-2 py-1 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                            {{ $metalLabels[$breakdown['metal_type']] ?? ucfirst($breakdown['metal_type'] ?? '') }}
                            {{ $breakdown['purity'] ?? '' }}
                            · ₹{{ number_format($breakdown['metal_rate_used'], 2) }}/g
                        </span>
                    @endif
                </div>
            </div>

            {{-- Line Items --}}
            <div class="divide-y divide-gray-100 dark:divide-gray-800">

                @if ($breakdown['use_override'])
                    {{-- Override mode: simple --}}
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Base Price (Fixed)</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">₹{{ number_format($breakdown['raw_cost'], 2) }}</span>
                    </div>
                @else
                    {{-- Formula mode: detailed breakdown --}}
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Metal Cost
                            <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">
                                ({{ number_format($breakdown['weight_grams'], 3) }}g × ₹{{ number_format($breakdown['metal_rate_used'], 2) }}/g)
                            </span>
                        </span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">₹{{ number_format($breakdown['metal_cost'], 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Making Charges
                            <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">
                                ({{ $chargesTypeLabels[$breakdown['making_charges_type']] ?? 'Fixed' }}
                                @if ($breakdown['making_charges_type'] === 'percentage')
                                    · {{ $breakdown['making_charges_rate'] }}%)
                                @elseif ($breakdown['making_charges_type'] === 'per_gram')
                                    · ₹{{ number_format($breakdown['making_charges_rate'], 2) }}/g)
                                @else
                                    · ₹{{ number_format($breakdown['making_charges_rate'], 2) }})
                                @endif
                            </span>
                        </span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">₹{{ number_format($breakdown['making_charges'], 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-gray-800/50">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Raw Cost</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">₹{{ number_format($breakdown['raw_cost'], 2) }}</span>
                    </div>
                @endif

                @if ($breakdown['discount_percent'] > 0)
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm text-green-600 dark:text-green-400">
                            <x-filament::icon icon="heroicon-m-tag" class="h-4 w-4 inline-block mr-1" />
                            Discount ({{ $breakdown['discount_percent'] }}%)
                        </span>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">− ₹{{ number_format($breakdown['discount_amount'], 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-gray-800/50">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Subtotal (after discount)</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">₹{{ number_format($breakdown['subtotal'], 2) }}</span>
                    </div>
                @endif

            </div>

            {{-- Total --}}
            <div class="px-4 py-3 bg-primary-600 dark:bg-primary-800">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-white">Product Price (excl. GST)</span>
                    <span class="text-lg font-extrabold text-white">₹{{ number_format($breakdown['total_price'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Note about metal rates --}}
        @if (!$breakdown['use_override'])
            <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                <x-filament::icon icon="heroicon-m-information-circle" class="h-3.5 w-3.5 shrink-0" />
                Metal rates are fetched from the latest available rate. The final customer-facing price may vary if rates change.
            </p>
        @endif
    @endif
</div>
