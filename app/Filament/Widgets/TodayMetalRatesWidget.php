<?php

namespace App\Filament\Widgets;

use App\Models\MetalRate;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayMetalRatesWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $today = today()->toDateString();

        $rates = MetalRate::whereDate('effective_date', $today)
            ->orderBy('metal_type')
            ->orderBy('purity')
            ->get()
            ->keyBy(fn(MetalRate $r) => $r->metal_type . '_' . $r->purity);

        $format = fn(?MetalRate $rate): string =>
            $rate ? '₹' . number_format((float) $rate->rate_per_gram, 2) . ' / g' : 'Not set';

        $gold24 = $rates->get('gold_24K');
        $gold22 = $rates->get('gold_22K');
        $gold18 = $rates->get('gold_18K');
        $gold14 = $rates->get('gold_14K');
        $silver999 = $rates->get('silver_999');
        $silver925 = $rates->get('silver_925');
        $platinum950 = $rates->get('platinum_950');

        $stats = [];

        if ($gold24 || $gold22 || $gold18 || $gold14) {
            if ($gold24) {
                $stats[] = Stat::make('Gold 24K', $format($gold24))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('warning');
            }
            if ($gold22) {
                $stats[] = Stat::make('Gold 22K', $format($gold22))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('warning');
            }
            if ($gold18) {
                $stats[] = Stat::make('Gold 18K', $format($gold18))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('warning');
            }
        }

        if ($silver999 || $silver925) {
            if ($silver999) {
                $stats[] = Stat::make('Silver 999', $format($silver999))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('gray');
            }
            if ($silver925) {
                $stats[] = Stat::make('Silver 925', $format($silver925))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('gray');
            }
        }

        if ($platinum950) {
            $stats[] = Stat::make('Platinum 950', $format($platinum950))
                ->description('Today — ' . today()->format('d M Y'))
                ->color('info');
        }

        if (empty($stats)) {
            $stats[] = Stat::make("Today's Rates", 'Not yet entered')
                ->description('Add today\'s rates using the button above.')
                ->color('danger');
        }

        return $stats;
    }
}
