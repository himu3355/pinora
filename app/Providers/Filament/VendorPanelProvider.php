<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureVendorAccess;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class VendorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendor')
            ->path('vendor')
            ->login()
            ->colors([
                'primary' => Color::Rose,
            ])
            ->plugins([
                \Redberry\PageBuilderPlugin\GlobalBlocksPlugin::make()
                    ->enableGlobalBlocks(false),
            ])
            ->discoverResources(
                in: app_path('Filament/Vendor/Resources'),
                for: 'App\\Filament\\Vendor\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Vendor/Pages'),
                for: 'App\\Filament\\Vendor\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Vendor/Widgets'),
                for: 'App\\Filament\\Vendor\\Widgets'
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureVendorAccess::class,
            ]);
    }
}
