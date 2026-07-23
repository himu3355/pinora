<?php

namespace Database\Seeders;

use App\Models\MetalRate;
use Illuminate\Database\Seeder;

class MetalRateSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();

        $rates = [
            // Gold
            ['metal_type' => 'gold', 'purity' => '24K', 'rate_per_gram' => 7200.00],
            ['metal_type' => 'gold', 'purity' => '22K', 'rate_per_gram' => 6600.00],
            ['metal_type' => 'gold', 'purity' => '18K', 'rate_per_gram' => 5400.00],
            ['metal_type' => 'gold', 'purity' => '14K', 'rate_per_gram' => 4200.00],
            // Silver
            ['metal_type' => 'silver', 'purity' => '999', 'rate_per_gram' => 90.00],
            ['metal_type' => 'silver', 'purity' => '925', 'rate_per_gram' => 83.25],
            // Platinum
            ['metal_type' => 'platinum', 'purity' => '950', 'rate_per_gram' => 3800.00],
        ];

        foreach ($rates as $rate) {
            MetalRate::updateOrCreate(
                [
                    'metal_type'     => $rate['metal_type'],
                    'purity'         => $rate['purity'],
                    'effective_date' => $today,
                ],
                [
                    'rate_per_gram' => $rate['rate_per_gram'],
                    'notes'         => 'Default seeded rate'
                ]
            );
        }
    }
}
