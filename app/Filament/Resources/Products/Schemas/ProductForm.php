<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Components\PricingCalculatorSection;
use App\Models\Category;
use App\Models\MetalRate;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // ── 1. Basic Information ──────────────────────────────────
            Section::make('Basic Information')
                ->schema([
                    Hidden::make('vendor_id')
                        ->default(fn() => Vendor::first()?->id),

                    Grid::make(2)->schema([

                        Select::make('parent_category_id')
                            ->label('Main Category')
                            ->options(
                                Category::whereNull('parent_id')
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('category_id', null))
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Select $component, ?Product $record) {
                                if ($record && $record->category) {
                                    $component->state($record->category->parent_id ?? $record->category_id);
                                }
                            }),

                        Select::make('category_id')
                            ->label('Sub Category')
                            ->options(function (Get $get) {
                                $parentId = $get('parent_category_id');
                                if ($parentId) {
                                    return Category::where('parent_id', $parentId)
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                }

                                return Category::whereNotNull('parent_id')
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Select sub-category'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) =>
                                $set('slug', Str::slug($state ?? ''))
                            ),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                    ]),

                    Textarea::make('short_description')
                        ->label('Short Description')
                        ->rows(2)
                        ->maxLength(500),

                    RichEditor::make('description')
                        ->label('Full Description')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'bulletList', 'orderedList', 'blockquote',
                            'h2', 'h3', 'link', 'redo', 'undo',
                        ]),
                ]),

            // ── 2. Metal, Stone, Pricing & Live Calculator ────────────
            Section::make('Metal, Stone & Pricing Calculations')
                ->schema([
                    // Row 1: Product type + metal + purity
                    Grid::make(3)->schema([
                        Select::make('product_type')
                            ->label('Product / Pricing Type')
                            ->options([
                                'jewellery'  => '💍 Jewellery',
                                'coin_bar'   => '🪙 Coin / Bar',
                                'mrp_fixed'  => '🏷️ MRP Fixed',
                                'mrp_linked' => '🔗 MRP Linked (Rate-Based)',
                            ])
                            ->default('jewellery')
                            ->required()
                            ->live()
                            ->helperText('Controls which pricing formula is used.'),

                        Select::make('metal_type')
                            ->label('Metal Type')
                            ->options([
                                'gold'     => 'Gold',
                                'silver'   => 'Silver',
                                'platinum' => 'Platinum',
                                'other'    => 'Other',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('purity', null)),

                        Select::make('purity')
                            ->label('Purity')
                            ->options(function (Get $get): array {
                                return match ($get('metal_type')) {
                                    'gold'     => ['24K' => '24K (999)', '22K' => '22K (916)', '18K' => '18K (750)', '14K' => '14K (585)'],
                                    'silver'   => ['999' => '999 (Fine)', '925' => '925 (Sterling)'],
                                    'platinum' => ['950' => '950'],
                                    default    => [],
                                };
                            })
                            ->live()
                            ->disabled(fn(Get $get) => blank($get('metal_type')))
                            ->helperText(function (Get $get): ?string {
                                $metal = $get('metal_type');
                                $purity = $get('purity');
                                if (!$metal || !$purity) {
                                    return null;
                                }
                                $rate = MetalRate::getLatestRate($metal, $purity);
                                if (!$rate) {
                                    return 'No live rate found for this metal & purity';
                                }
                                $date = $rate->effective_date
                                    ? Carbon::parse($rate->effective_date)->format('d M Y')
                                    : 'N/A';
                                return "Live Rate: ₹" . number_format((float) $rate->rate_per_gram, 2) . "/g (Effective: {$date})";
                            }),
                    ]),

                    // Row 2: Gross weight + loss + making type
                    Grid::make(3)->schema([
                        TextInput::make('weight_grams')
                            ->label('Gross Weight (g)')
                            ->numeric()
                            ->step('0.001')
                            ->suffix('g')
                            ->minValue(0)
                            ->live(onBlur: true),

                        TextInput::make('loss')
                            ->label('Less Weight / Loss (g)')
                            ->numeric()
                            ->step('0.001')
                            ->suffix('g')
                            ->minValue(0)
                            ->default(0)
                            ->placeholder('0.000')
                            ->helperText('Deducted from gross to get net weight')
                            ->live(onBlur: true),

                        Select::make('making_charges_type')
                            ->label('Labour / Making Type')
                            ->options([
                                'fixed'      => 'Fixed Amount (₹)',
                                'per_gram'   => 'Per Gram (₹/g)',
                                'percentage' => 'Percentage (%) of Metal Value',
                            ])
                            ->default('percentage')
                            ->required()
                            ->live(onBlur: true),
                    ]),

                    // Row 3: Labour amount + base price / MRP + pieces
                    Grid::make(3)->schema([
                        TextInput::make('making_charges')
                            ->label(fn (Get $get) => match ($get('making_charges_type')) {
                                'per_gram'   => 'Labour (₹/g)',
                                'percentage' => 'Labour (%)',
                                default      => 'Labour (₹ fixed)',
                            })
                            ->numeric()
                            ->prefix(fn (Get $get) => $get('making_charges_type') === 'percentage' ? null : '₹')
                            ->suffix(fn (Get $get) => match ($get('making_charges_type')) {
                                'per_gram'   => '/g',
                                'percentage' => '%',
                                default      => null,
                            })
                            ->minValue(0)
                            ->live(onBlur: true),

                        TextInput::make('base_price')
                            ->label(fn (Get $get) => $get('product_type') === 'mrp_fixed'
                                ? 'Fixed MRP (₹)'
                                : 'Base Price Override (₹)')
                            ->numeric()
                            ->prefix('₹')
                            ->minValue(0)
                            ->helperText(fn (Get $get) => $get('product_type') === 'mrp_fixed'
                                ? 'This IS the final consumer MRP (GST-inclusive by law).'
                                : 'Leave empty to auto-calculate from metal rate.')
                            ->live(onBlur: true),

                        TextInput::make('mrp_pieces')
                            ->label('No. of Pieces (Batch)')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Total batch cost ÷ pieces = per-piece MRP')
                            ->visible(fn (Get $get) => $get('product_type') === 'mrp_linked')
                            ->live(onBlur: true),
                    ]),

                    // Row 4: Discount (only for jewellery / coin_bar)
                    Grid::make(3)->schema([
                        TextInput::make('discount_percent')
                            ->label('Discount (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->visible(fn (Get $get) => in_array($get('product_type') ?? 'jewellery', ['jewellery', 'coin_bar']))
                            ->live(onBlur: true),

                        // Toggle::make('is_price_on_request')
                        //     ->label('Price On Request')
                        //     ->helperText('Hides price and shows a "Get Quote" CTA.')
                        //     ->live(),
                    ]),

                    // Stone fields (currently hidden — uncomment to enable)
                    // Grid::make(3)->schema([
                    //     TextInput::make('stone_type')->label('Stone Type')->maxLength(100)->placeholder('Diamond, Ruby, Emerald…'),
                    //     TextInput::make('stone_weight_carats')->label('Stone Weight (carats)')->numeric()->step('0.001')->suffix('ct')->minValue(0),
                    //     TextInput::make('stone_quality')->label('Stone Quality')->maxLength(100)->placeholder('VS1, VVS2, F, G…'),
                    // ]),

                    PricingCalculatorSection::make(),
                ]),

            // ── 3. Inventory, Status & Certifications (2-col Grid) ─────
                Section::make('Inventory & Status')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('stock_quantity')
                                ->label('Stock Quantity')
                                ->numeric()
                                ->minValue(0)
                                ->default(1),

                            TextInput::make('min_order_quantity')
                                ->label('Min. Order Qty')
                                ->numeric()
                                ->minValue(1)
                                ->default(1),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft'     => 'Draft',
                                    'active' => 'Published/Active',
                                    'inactive'  => 'Inactive',
                                ])
                                ->default('active')
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            Toggle::make('is_featured')
                                ->label('Featured'),

                            Toggle::make('is_new_arrival')
                                ->label('New Arrival'),

                            Toggle::make('is_customizable')
                                ->label('Customizable')
                                ->live(),
                        ]),

                        Textarea::make('customization_notes')
                            ->label('Customization Notes')
                            ->rows(2)
                            ->maxLength(1000)
                            ->visible(fn(Get $get) => (bool) $get('is_customizable')),
                    ]),

                Section::make('Certification')
                    ->schema([
                        CheckboxList::make('certifications')
                            ->label('Product Certifications & Badges')
                            ->options([
                                'bis_hallmark'        => 'BIS Hallmark',
                                'certified_diamond'   => 'Certified Diamond Jewellery',
                                'certified_jewellery' => '100% Certified Jewellery',
                                'lifetime_exchange'  => 'Lifetime Exchange and Buy Back',
                            ])
                            ->columns(2),
                    ]),

            // ── 4. Images & Variants ──────────────────────────────────
            Section::make('Product Images')
                ->schema([
                    Repeater::make('images')
                        ->relationship('images')
                        ->label('Images')
                        ->schema([
                            FileUpload::make('path')
                                ->label('Image')
                                ->image()
                                ->disk('public')
                                ->directory('products/images')
                                ->maxSize(5120)
                                ->required(),

                            Grid::make(2)->schema([
                                TextInput::make('alt_text')
                                    ->label('Alt Text')
                                    ->maxLength(255),

                                Toggle::make('is_primary')
                                    ->label('Primary Image')
                                    ->helperText('Only one image should be primary.'),
                            ]),
                        ])
                        ->addActionLabel('Add Image')
                        ->reorderable()
                        ->collapsible()
                        ->defaultItems(0),
                ]),

            Section::make('Product Variants')
                ->collapsible()
                ->schema([
                    Repeater::make('variants')
                        ->relationship('variants')
                        ->label('Variants')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('name')
                                    ->label('Variant Name')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('e.g., 22K / 4g / Size 16'),

                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->maxLength(100)
                                    ->unique('product_variants', 'sku', ignoreRecord: true),

                                TextInput::make('weight_grams')
                                    ->label('Weight (g)')
                                    ->numeric()
                                    ->step('0.001')
                                    ->suffix('g'),
                            ]),

                            Grid::make(3)->schema([
                                TextInput::make('making_charges')
                                    ->label('Making Charges')
                                    ->numeric()
                                    ->prefix('₹'),

                                TextInput::make('base_price')
                                    ->label('Base Price')
                                    ->numeric()
                                    ->prefix('₹'),

                                TextInput::make('stock_quantity')
                                    ->label('Stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                            ]),
                        ])
                        ->addActionLabel('Add Variant')
                        ->reorderable()
                        ->collapsible()
                        ->defaultItems(0),
                ]),

            Section::make('SEO')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60),

                        TextInput::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(160),
                    ]),
                ]),
        ]);
    }
}
