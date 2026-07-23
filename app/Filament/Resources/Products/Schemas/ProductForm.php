<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Components\PricingCalculatorSection;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
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
                    Select::make('vendor_id')
                        ->label('Vendor / Store')
                        ->options(fn() => Vendor::pluck('store_name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->default(fn() => Vendor::first()?->id)
                        ->helperText('Select the vendor store that owns this product.'),

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
                    Grid::make(3)->schema([
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
                                    'gold'     => ['24K' => '24K', '22K' => '22K', '18K' => '18K', '14K' => '14K'],
                                    'silver'   => ['999' => '999', '925' => '925'],
                                    'platinum' => ['950' => '950'],
                                    default    => [],
                                };
                            })
                            ->live(onBlur: true)
                            ->disabled(fn(Get $get) => blank($get('metal_type'))),

                        TextInput::make('weight_grams')
                            ->label('Weight (grams)')
                            ->numeric()
                            ->step('0.001')
                            ->suffix('g')
                            ->minValue(0)
                            ->live(onBlur: true),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('loss')
                            ->label('Loss')
                            ->numeric()
                            ->step('0.001')
                            ->suffix('g')
                            ->minValue(0)
                            ->placeholder('0.000')
                            ->helperText('Weight loss during making'),

                        TextInput::make('making_charges')
                            ->label('Making Charges')
                            ->numeric()
                            ->prefix('₹')
                            ->minValue(0)
                            ->live(onBlur: true),

                        Hidden::make('making_charges_type')
                            ->default('fixed'),
                    ]),

                    // Grid::make(3)->schema([
                    //     TextInput::make('stone_type')
                    //         ->label('Stone Type')
                    //         ->maxLength(100)
                    //         ->placeholder('Diamond, Ruby, Emerald…'),

                    //     TextInput::make('stone_weight_carats')
                    //         ->label('Stone Weight (carats)')
                    //         ->numeric()
                    //         ->step('0.001')
                    //         ->suffix('ct')
                    //         ->minValue(0),

                    //     TextInput::make('stone_quality')
                    //         ->label('Stone Quality')
                    //         ->maxLength(100)
                    //         ->placeholder('VS1, VVS2, F, G…'),
                    // ]),

                    Grid::make(3)->schema([
                        TextInput::make('base_price')
                            ->label('Base Price (₹)')
                            ->numeric()
                            ->prefix('₹')
                            ->minValue(0)
                            ->helperText('Leave empty to calculate from metal rate formula.')
                            ->live(onBlur: true),

                        TextInput::make('discount_percent')
                            ->label('Discount')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->live(onBlur: true),

                        // Toggle::make('is_price_on_request')
                        //     ->label('Price On Request')
                        //     ->helperText('Hides price and shows a "Get Quote" CTA.')
                        //     ->live(),
                    ]),

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
                                ->default('draft')
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
