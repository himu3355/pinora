<?php

namespace App\Services;

use App\Models\MetalRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetalRateSyncService
{
    /**
     * Standard conversion factor: 1 Troy Ounce = 31.1034768 Grams.
     */
    public const TROY_OUNCE_IN_GRAMS = 31.1034768;

    /**
     * Cache duration in seconds.
     * Gold-API.com requires caching prices for at least 30 seconds to prevent IP blocking.
     * We use 60 seconds to provide an extra safety margin against spam or rapid clicks.
     */
    public const CACHE_TTL_SECONDS = 60;

    protected ?string $lastError = null;

    /**
     * Indicates whether the most recent sync operation was fulfilled from cache.
     */
    protected bool $servedFromCache = false;

    /**
     * Get the last error message from the sync attempt.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Check if the last sync operation used cached prices (IP anti-spam protection).
     */
    public function wasServedFromCache(): bool
    {
        return $this->servedFromCache;
    }

    /**
     * Check if rates have already been synced from Gold-API.com today.
     */
    public function hasSyncedToday(): bool
    {
        return MetalRate::whereDate('effective_date', today())
            ->where('notes', 'like', 'Synced via Gold-API.com%')
            ->exists();
    }

    /**
     * Get the timestamp of the last successful sync today.
     */
    public function getLastSyncedAt(): ?string
    {
        $record = MetalRate::whereDate('effective_date', today())
            ->where('notes', 'like', 'Synced via Gold-API.com%')
            ->latest('updated_at')
            ->first();

        return $record?->updated_at?->toDateTimeString();
    }

    /**
     * Build the HTTP client with custom User-Agent, timeout, and local SSL handling.
     */
    protected function createClient()
    {
        $client = Http::withHeaders([
            'User-Agent' => 'PinoraJewellery/1.0 (https://pinora.in; metal-rate-sync)',
            'Accept'     => 'application/json',
        ])->timeout(12);

        // Bypass cURL error 60 SSL certificate issues in local/development environments
        if (app()->environment('local') || config('app.debug')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Fetch a symbol's price in currency with mandatory 60s caching and request spacing.
     * Prevents any possibility of IP blocking from rapid or repetitive calls.
     */
    public function fetchSymbolPrice(string $symbol, string $currency = 'INR', ?bool &$fromCache = null): ?float
    {
        $cacheKey = "gold_api_price_{$symbol}_{$currency}";

        // 1. Check Cache first (Gold-API requests at least 30s cache; we maintain 60s)
        if (Cache::has($cacheKey)) {
            $cachedPrice = Cache::get($cacheKey);
            if ($cachedPrice !== null && (float) $cachedPrice > 0) {
                $fromCache = true;
                return (float) $cachedPrice;
            }
        }

        $fromCache = false;

        // 2. Micro-delay of 250ms before outbound call to prevent burst traffic
        usleep(250000);

        try {
            $client = $this->createClient();
            $response = $client->get("https://api.gold-api.com/price/{$symbol}/{$currency}");

            if ($response->successful()) {
                $data = $response->json();
                $price = $data['price'] ?? null;

                if ($price && (float) $price > 0) {
                    $floatPrice = (float) $price;
                    Cache::put($cacheKey, $floatPrice, now()->addSeconds(self::CACHE_TTL_SECONDS));
                    return $floatPrice;
                }
            } else {
                $status = $response->status();
                if ($status === 429) {
                    $this->lastError = 'Gold-API rate limit hit. IP anti-spam protection activated.';
                } else {
                    $this->lastError = "Gold-API price fetch for {$symbol} returned HTTP status {$status}.";
                }
                Log::warning("MetalRateSyncService: Failed fetching {$symbol}/{$currency} with response {$status}");
            }
        } catch (\Throwable $e) {
            $this->lastError = 'Connection error: ' . $e->getMessage();
            Log::error("MetalRateSyncService exception fetching {$symbol}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch daily metal rates from Gold-API.com and store them.
     * Strictly safeguards the server's IP address:
     * - Uses 60-second response caching for every symbol (exceeds Gold-API 30s anti-spam rule)
     * - Prevents race conditions / concurrent rapid clicks using an atomic cache lock
     * - Paces distinct symbol requests with 250ms spacing
     */
    public function sync(): bool
    {
        $this->lastError = null;
        $this->servedFromCache = false;

        // Prevent parallel concurrent executions hitting the external endpoint
        $lock = Cache::lock('gold_api_sync_lock', 15);

        return (bool) $lock->get(function () {
            try {
                $syncedAny = false;
                $allFromCache = true;

                // 1. Sync Gold (XAU in INR)
                $goldFromCache = false;
                $goldOzPrice = $this->fetchSymbolPrice('XAU', 'INR', $goldFromCache);

                if ($goldOzPrice && $goldOzPrice > 0) {
                    $rate24K = $goldOzPrice / self::TROY_OUNCE_IN_GRAMS;

                    $this->updateRate('gold', '24K', $rate24K);
                    $this->updateRate('gold', '22K', $rate24K * 0.9167);
                    $this->updateRate('gold', '18K', $rate24K * 0.7500);
                    $this->updateRate('gold', '14K', $rate24K * 0.5833);
                    $syncedAny = true;
                    if (!$goldFromCache) {
                        $allFromCache = false;
                    }
                }

                // 2. Sync Silver (XAG in INR)
                $silverFromCache = false;
                $silverOzPrice = $this->fetchSymbolPrice('XAG', 'INR', $silverFromCache);

                if ($silverOzPrice && $silverOzPrice > 0) {
                    $rate999 = $silverOzPrice / self::TROY_OUNCE_IN_GRAMS;

                    $this->updateRate('silver', '999', $rate999);
                    $this->updateRate('silver', '925', $rate999 * 0.9250);
                    $syncedAny = true;
                    if (!$silverFromCache) {
                        $allFromCache = false;
                    }
                }

                // 3. Sync Platinum (XPT in INR, optional)
                try {
                    $platFromCache = false;
                    $platOzPrice = $this->fetchSymbolPrice('XPT', 'INR', $platFromCache);

                    if ($platOzPrice && $platOzPrice > 0) {
                        $ratePlat = ($platOzPrice / self::TROY_OUNCE_IN_GRAMS) * 0.9500;
                        $this->updateRate('platinum', '950', $ratePlat);
                        $syncedAny = true;
                        if (!$platFromCache) {
                            $allFromCache = false;
                        }
                    }
                } catch (\Throwable $e) {
                    // Non-fatal if XPT fails
                }

                $this->servedFromCache = $syncedAny && $allFromCache;

                if (!$syncedAny) {
                    if (empty($this->lastError)) {
                        $this->lastError = 'Could not retrieve market rates from Gold-API.com.';
                    }
                    return false;
                }

                return true;
            } catch (\Exception $e) {
                $this->lastError = 'Connection error: ' . $e->getMessage();
                Log::error('MetalRateSyncService exception: ' . $e->getMessage());
                return false;
            }
        });
    }

    protected function updateRate(string $metalType, string $purity, float $ratePerGram): void
    {
        MetalRate::updateOrCreate(
            [
                'metal_type'     => $metalType,
                'purity'         => $purity,
                'effective_date' => today()->toDateString(),
            ],
            [
                'rate_per_gram' => round($ratePerGram, 2),
                'notes'         => 'Synced via Gold-API.com on ' . now()->toDateTimeString(),
            ]
        );

        MetalRate::flushRateCache($metalType, $purity);
    }
}
