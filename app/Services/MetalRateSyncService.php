<?php

namespace App\Services;

use App\Models\MetalRate;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetalRateSyncService
{
    protected ?string $lastError = null;

    /**
     * Get the last error message from the sync attempt.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Check if rates have already been synced from GoldAPI today.
     */
    public function hasSyncedToday(): bool
    {
        $lastDate = Setting::get('goldapi_last_synced_date');

        if ($lastDate === today()->toDateString()) {
            return true;
        }

        // Fallback check on metal_rates records for today
        return MetalRate::whereDate('effective_date', today())
            ->where('notes', 'like', 'Synced via GoldAPI%')
            ->exists();
    }

    /**
     * Get the timestamp of the last successful sync today.
     */
    public function getLastSyncedAt(): ?string
    {
        $settingTime = Setting::get('goldapi_last_synced_at');

        if ($settingTime) {
            return $settingTime;
        }

        $record = MetalRate::whereDate('effective_date', today())
            ->where('notes', 'like', 'Synced via GoldAPI%')
            ->latest('updated_at')
            ->first();

        return $record?->updated_at?->toDateTimeString();
    }

    /**
     * Build the HTTP client with required headers and local SSL handling.
     */
    protected function createClient(string $apiKey)
    {
        $client = Http::withHeaders([
            'x-access-token' => $apiKey,
            'Content-Type'   => 'application/json',
        ])->timeout(12);

        // Bypass cURL error 60 SSL certificate issues in local/development environments
        if (app()->environment('local') || config('app.debug')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Fetch daily metal rates from GoldAPI and store them.
     * Enforces once-daily syncing to stay within the 100 requests/month limit unless $force is true.
     */
    public function sync(?string $overrideApiKey = null, bool $force = false): bool
    {
        $this->lastError = null;

        // Guard: sync only once per day unless explicitly forced
        if (!$force && $this->hasSyncedToday()) {
            $lastTime = $this->getLastSyncedAt();
            $timeText = $lastTime ? Carbon::parse($lastTime)->format('h:i A') : 'earlier today';
            $this->lastError = "Today's rates were already synced from GoldAPI at {$timeText}. Sync is limited to once daily to stay within your 100 requests/month limit (3 reqs/day = 90 reqs/mo).";
            Log::info('MetalRateSyncService: Prevented duplicate sync for ' . today()->toDateString());
            return false;
        }

        try {
            $apiKey = $overrideApiKey
                ?: Setting::get('goldapi_key')
                ?: config('services.goldapi.key')
                ?: env('GOLDAPI_KEY');

            if (blank($apiKey)) {
                $this->lastError = 'GoldAPI token is not configured. Please enter your GoldAPI token in Site Settings, in the sync prompt, or in your .env file.';
                Log::warning('MetalRateSyncService: ' . $this->lastError);
                return false;
            }

            $client = $this->createClient($apiKey);
            $syncedAny = false;

            // 1. Sync Gold (XAU) 24K in INR (1 request)
            $goldResponse = $client->get('https://www.goldapi.io/api/XAU/INR');

            if ($goldResponse->successful()) {
                $goldData = $goldResponse->json();
                $rate24K = $goldData['price_gram_24k'] ?? null;

                if ($rate24K && (float) $rate24K > 0) {
                    $rate24KFloat = (float) $rate24K;
                    // Save 24K rate
                    $this->updateRate('gold', '24K', $rate24KFloat);
                    // Calculate standard purity ratios
                    $this->updateRate('gold', '22K', $rate24KFloat * 0.9167);
                    $this->updateRate('gold', '18K', $rate24KFloat * 0.7500);
                    $this->updateRate('gold', '14K', $rate24KFloat * 0.5833);
                    $syncedAny = true;
                }
            } else {
                $status = $goldResponse->status();
                $bodyMsg = $goldResponse->json('error') ?? $goldResponse->json('message') ?? null;

                if ($status === 401 || $status === 403) {
                    $detail = $bodyMsg ?: 'Invalid API Key';
                    $this->lastError = "GoldAPI Error ({$detail}). Please verify your API token at https://www.goldapi.io.";
                    Log::warning('MetalRateSyncService: ' . $this->lastError);
                    return false;
                } elseif ($status === 429) {
                    $this->lastError = 'GoldAPI monthly rate limit reached. Please upgrade your GoldAPI plan or enter rates manually.';
                    Log::warning('MetalRateSyncService: ' . $this->lastError);
                    return false;
                } else {
                    $this->lastError = "Gold price fetch failed with HTTP response {$status}." . ($bodyMsg ? " ({$bodyMsg})" : '');
                    Log::warning('MetalRateSyncService: ' . $this->lastError);
                }
            }

            // 2. Sync Silver (XAG) in INR (1 request)
            $silverResponse = $client->get('https://www.goldapi.io/api/XAG/INR');

            if ($silverResponse->successful()) {
                $silverData = $silverResponse->json();
                $ratePureSilver = $silverData['price_gram_24k'] ?? null;

                if ($ratePureSilver && (float) $ratePureSilver > 0) {
                    $rateSilverFloat = (float) $ratePureSilver;
                    $this->updateRate('silver', '999', $rateSilverFloat);
                    $this->updateRate('silver', '925', $rateSilverFloat * 0.9250);
                    $syncedAny = true;
                }
            } else {
                Log::warning('MetalRateSyncService: Silver price fetch failed with response ' . $silverResponse->status());
            }

            // 3. Sync Platinum (XPT) in INR (1 request, optional)
            try {
                $platResponse = $client->get('https://www.goldapi.io/api/XPT/INR');

                if ($platResponse->successful()) {
                    $platData = $platResponse->json();
                    $ratePlat = $platData['price_gram_24k'] ?? null;
                    if ($ratePlat && (float) $ratePlat > 0) {
                        $this->updateRate('platinum', '950', (float) $ratePlat * 0.9500);
                        $syncedAny = true;
                    }
                }
            } catch (\Throwable $e) {
                // Non-fatal if XPT is unavailable
            }

            if (!$syncedAny) {
                if (empty($this->lastError)) {
                    $this->lastError = 'Could not retrieve market rates from GoldAPI response.';
                }
                return false;
            }

            // Record successful sync timestamp to prevent duplicate daily requests
            Setting::set('goldapi_last_synced_date', today()->toDateString());
            Setting::set('goldapi_last_synced_at', now()->toDateTimeString());

            return true;
        } catch (\Exception $e) {
            $this->lastError = 'Connection error: ' . $e->getMessage();
            Log::error('MetalRateSyncService exception: ' . $e->getMessage());
            return false;
        }
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
                'notes'         => 'Synced via GoldAPI on ' . now()->toDateTimeString(),
            ]
        );

        MetalRate::flushRateCache($metalType, $purity);
    }
}
