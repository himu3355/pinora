<?php

namespace App\Services;

use App\Models\MetalRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetalRateSyncService
{
    /**
     * Fetch daily metal rates from GoldAPI and store them.
     */
    public function sync(): bool
    {
        try {
            $apiKey = config('services.goldapi.key');
            if (blank($apiKey)) {
                Log::warning('MetalRateSyncService: GoldAPI token is not configured.');
                return false;
            }

            // 1. Sync Gold (XAU) 24K in INR
            $goldResponse = Http::withHeaders([
                'x-access-token' => $apiKey
            ])->get('https://www.goldapi.io/api/XAU/INR');

            if ($goldResponse->successful()) {
                $goldData = $goldResponse->json();
                $rate24K = $goldData['price_gram_24k'] ?? null;
                
                if ($rate24K) {
                    // Save 24K rate
                    $this->updateRate('gold', '24K', $rate24K);
                    // Calculate 22K (91.67%), 18K (75%), 14K (58.33%) standard purity ratios
                    $this->updateRate('gold', '22K', $rate24K * 0.9167);
                    $this->updateRate('gold', '18K', $rate24K * 0.75);
                    $this->updateRate('gold', '14K', $rate24K * 0.5833);
                }
            } else {
                Log::warning('MetalRateSyncService: Gold price fetch failed with response ' . $goldResponse->status());
            }

            // 2. Sync Silver (XAG) in INR
            $silverResponse = Http::withHeaders([
                'x-access-token' => $apiKey
            ])->get('https://www.goldapi.io/api/XAG/INR');

            if ($silverResponse->successful()) {
                $silverData = $silverResponse->json();
                // GoldAPI returns price per gram for silver 24k (pure silver) under price_gram_24k
                $ratePureSilver = $silverData['price_gram_24k'] ?? null;

                if ($ratePureSilver) {
                    $this->updateRate('silver', '999', $ratePureSilver);
                    $this->updateRate('silver', '925', $ratePureSilver * 0.925);
                }
            } else {
                Log::warning('MetalRateSyncService: Silver price fetch failed with response ' . $silverResponse->status());
            }

            return true;
        } catch (\Exception $e) {
            Log::error('MetalRateSyncService exception: ' . $e->getMessage());
            return false;
        }
    }

    protected function updateRate(string $metalType, string $purity, float $ratePerGram): void
    {
        MetalRate::updateOrCreate(
            [
                'metal_type' => $metalType,
                'purity' => $purity,
                'effective_date' => today()->toDateString(),
            ],
            [
                'rate_per_gram' => round($ratePerGram, 2),
                'notes' => 'Synced via GoldAPI on ' . now()->toDateTimeString(),
            ]
        );
    }
}
