<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $title = 'Site & General Settings';
    protected static \UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $all = Setting::all()->pluck('value', 'key')->toArray();

        $this->form->fill([
            // General
            'site_name' => $all['site_name'] ?? 'Pinora',
            'site_title' => $all['site_title'] ?? 'Pinora — Fine Gold Jewellery & Nose Pins',
            'navbar_tagline' => $all['navbar_tagline'] ?? 'TINY JEWEL. BIG STYLE.',
            'specialist_title' => $all['specialist_title'] ?? "Rajkot's Nose Pin Specialist",
            'header_logo' => $all['header_logo'] ?? null,
            'footer_logo' => $all['footer_logo'] ?? null,
            'favicon' => $all['favicon'] ?? null,

            // Announcement
            'announcement_enabled' => filter_var($all['announcement_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'announcement_text' => $all['announcement_text'] ?? "Today's 22K Gold Rate: ₹7,850/g | Free Insured Shipping Across India | 100% Certified & BIS Hallmarked",
            'announcement_link' => $all['announcement_link'] ?? null,

            // Contact & WhatsApp
            'support_phone' => $all['support_phone'] ?? '+91 98765 43210',
            'support_email' => $all['support_email'] ?? 'support@pinora.com',
            'whatsapp_number' => $all['whatsapp_number'] ?? '919876543210',
            'whatsapp_enabled' => filter_var($all['whatsapp_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'store_address' => $all['store_address'] ?? 'Palace Road, Soni Bazar, Rajkot, Gujarat 360001, India',
            'working_hours' => $all['working_hours'] ?? 'Mon - Sat: 10:00 AM - 8:00 PM',

            // Social links
            'instagram_url' => $all['instagram_url'] ?? 'https://instagram.com/pinora_jewels',
            'facebook_url' => $all['facebook_url'] ?? 'https://facebook.com/pinorajewels',
            'youtube_url' => $all['youtube_url'] ?? 'https://youtube.com/@pinorajewels',
            'pinterest_url' => $all['pinterest_url'] ?? 'https://pinterest.com/pinorajewels',

            // SEO & Social Share
            'default_meta_title' => $all['default_meta_title'] ?? 'Pinora — Certified 22K Gold Nose Pins Handcrafted in Rajkot',
            'default_meta_description' => $all['default_meta_description'] ?? 'Discover certified 22K gold nose pins handcrafted by master artisans in Rajkot. Daily wear, floral designs, studded solitaires, and heritage bridal pins.',
            'default_meta_keywords' => $all['default_meta_keywords'] ?? 'gold nose pins, 22K gold jewellery, Rajkot jewellery, certified nose studs, bridal nose pin',
            'og_image' => $all['og_image'] ?? null,

            // Footer & Regulatory
            'footer_about_text' => $all['footer_about_text'] ?? "Rajkot's premier specialist for handcrafted 22K gold nose pins. Hallmarked, guaranteed, and delivered with insured transit across India.",
            'bis_hallmark_text' => $all['bis_hallmark_text'] ?? '100% Certified 22K BIS Hallmarked Gold',
            'copyright_text' => $all['copyright_text'] ?? 'PINORA. All Rights Reserved. Rajkot, Gujarat, India.',

            // Live Bullion API (GoldAPI)
            'goldapi_key' => $all['goldapi_key'] ?? env('GOLDAPI_KEY', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('SettingsTabs')->tabs([

                // Tab 1: General & Branding
                Tab::make('General & Branding')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('site_name')
                                ->label('Site Name / Brand')
                                ->required(),
                            TextInput::make('site_title')
                                ->label('Site Title (Browser Tab)')
                                ->required(),
                            TextInput::make('navbar_tagline')
                                ->label('Navbar Tagline')
                                ->helperText('Displayed under logo, e.g. TINY JEWEL. BIG STYLE.'),
                            TextInput::make('specialist_title')
                                ->label('Specialist Subtitle')
                                ->helperText("e.g. Rajkot's Nose Pin Specialist"),
                        ]),
                        Grid::make(3)->schema([
                            FileUpload::make('header_logo')
                                ->label('Header Logo Image')
                                ->image()
                                ->disk('public')
                                ->directory('settings')
                                ->helperText('Optional: if not set, SVG branding is used.'),
                            FileUpload::make('footer_logo')
                                ->label('Footer Logo Image')
                                ->image()
                                ->disk('public')
                                ->directory('settings'),
                            FileUpload::make('favicon')
                                ->label('Favicon')
                                ->image()
                                ->disk('public')
                                ->directory('settings'),
                        ]),
                    ]),

                // Tab 2: Top Announcement Bar
                Tab::make('Announcement Bar')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Toggle::make('announcement_enabled')
                            ->label('Show Top Announcement Bar')
                            ->default(true),
                        TextInput::make('announcement_text')
                            ->label('Announcement Text')
                            ->helperText('Top strip text across desktop and mobile.'),
                        TextInput::make('announcement_link')
                            ->label('Optional Announcement Link URL')
                            ->placeholder('https://... or /shop'),
                    ]),

                // Tab 3: Contact & WhatsApp
                Tab::make('Contact & Support')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('support_phone')
                                ->label('Customer Support Phone')
                                ->tel(),
                            TextInput::make('support_email')
                                ->label('Customer Support Email')
                                ->email(),
                            TextInput::make('whatsapp_number')
                                ->label('WhatsApp Business Number (with country code)')
                                ->helperText('e.g. 919876543210 (digits only, no + or spaces)'),
                            Toggle::make('whatsapp_enabled')
                                ->label('Enable Floating WhatsApp Chat Button')
                                ->default(true),
                        ]),
                        Textarea::make('store_address')
                            ->label('Physical Store / Showroom Address in Rajkot')
                            ->rows(2),
                        TextInput::make('working_hours')
                            ->label('Store / Support Hours')
                            ->helperText('e.g. Mon - Sat: 10:00 AM - 8:00 PM'),
                    ]),

                // Tab 4: Social Media
                Tab::make('Social Links')
                    ->icon('heroicon-o-share')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('instagram_url')
                                ->label('Instagram Profile URL')
                                ->url(),
                            TextInput::make('facebook_url')
                                ->label('Facebook Page URL')
                                ->url(),
                            TextInput::make('youtube_url')
                                ->label('YouTube Channel URL')
                                ->url(),
                            TextInput::make('pinterest_url')
                                ->label('Pinterest Profile URL')
                                ->url(),
                        ]),
                    ]),

                // Tab 5: SEO
                Tab::make('SEO & Sharing')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        TextInput::make('default_meta_title')
                            ->label('Default Meta Title'),
                        Textarea::make('default_meta_description')
                            ->label('Default Meta Description')
                            ->rows(3),
                        TextInput::make('default_meta_keywords')
                            ->label('Default Meta Keywords'),
                        FileUpload::make('og_image')
                            ->label('OpenGraph / Social Share Image')
                            ->image()
                            ->disk('public')
                            ->directory('settings')
                            ->helperText('Recommended: 1200x630 px for WhatsApp, Facebook, Twitter previews.'),
                    ]),

                // Tab 6: Footer & Regulatory
                Tab::make('Footer & Regulatory')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('footer_about_text')
                            ->label('Footer Short About Description')
                            ->rows(2),
                        Grid::make(2)->schema([
                            TextInput::make('bis_hallmark_text')
                                ->label('Hallmark Guarantee Badge Text')
                                ->default('100% Certified 22K BIS Hallmarked Gold'),
                            TextInput::make('copyright_text')
                                ->label('Footer Copyright Statement')
                                ->default('PINORA. All Rights Reserved. Rajkot, Gujarat, India.'),
                        ]),
                    ]),

                // Tab 7: Live Bullion API (GoldAPI.io)
                Tab::make('Bullion API (GoldAPI)')
                    ->icon('heroicon-o-key')
                    ->schema([
                        TextInput::make('goldapi_key')
                            ->label('GoldAPI Access Token')
                            ->password()
                            ->revealable()
                            ->placeholder('e.g. goldapi-xxxxxxxxxxxx-io')
                            ->helperText('Token from https://www.goldapi.io to fetch live daily market bullion rates in INR. Can also be set via GOLDAPI_KEY in .env.'),
                    ]),

            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Settings updated successfully')
            ->success()
            ->send();
    }
}
