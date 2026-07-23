<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Components\PricingCalculatorSection;
use App\Filament\Vendor\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Vendor\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Vendor\Resources\ProductResource\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Services\VendorContext;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'My Products';

    protected static ?string $pluralLabel = 'My Products';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_id', VendorContext::currentId());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // ── 1. Basic Information ──────────────────────────────────
            Section::make('Basic Information')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) =>
                                $set('slug', str($state)->slug())
                            ),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                    ]),

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

                    Textarea::make('description')
                        ->rows(4),
                ]),

            // ── 2. Metal, Stone & Pricing Calculations ────────────────
            Section::make('Metal, Stone & Pricing Calculations')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('metal_type')
                            ->options([
                                'gold'     => 'Gold',
                                'silver'   => 'Silver',
                                'platinum' => 'Platinum',
                                'other'    => 'Other',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('purity', null)),

                        Select::make('purity')
                            ->options(fn (Get $get) => match ($get('metal_type')) {
                                'gold'     => ['24K' => '24K', '22K' => '22K', '18K' => '18K', '14K' => '14K'],
                                'silver'   => ['999' => '999', '925' => '925'],
                                'platinum' => ['950' => '950'],
                                default    => [],
                            })
                            ->live(onBlur: true)
                            ->disabled(fn (Get $get) => blank($get('metal_type'))),

                        TextInput::make('weight_grams')
                            ->label('Weight (grams)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->live(onBlur: true),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('loss')
                            ->label('Loss')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->suffix('g')
                            ->placeholder('0.000')
                            ->helperText('Weight loss during making'),

                        TextInput::make('making_charges')
                            ->label('Making Charges (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->live(onBlur: true),

                        Hidden::make('making_charges_type')
                            ->default('fixed'),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('stone_type')
                            ->options([
                                'diamond'  => 'Diamond',
                                'ruby'     => 'Ruby',
                                'emerald'  => 'Emerald',
                                'sapphire' => 'Sapphire',
                                'pearl'    => 'Pearl',
                                'none'     => 'None / Plain',
                            ]),

                        TextInput::make('stone_weight_carats')
                            ->label('Stone Weight (carats)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('base_price')
                            ->label('Fixed Base Price (₹) — overrides metal rate calculation')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->helperText('Leave empty to auto-calculate from live metal rate')
                            ->live(onBlur: true),

                        TextInput::make('discount_percent')
                            ->label('Discount (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(onBlur: true),
                    ]),

                    PricingCalculatorSection::make(),
                ]),

            // ── 3. Inventory, Status & Certifications (2-col Grid) ─────
                Section::make('Inventory & Status')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('sku')
                                ->label('SKU')
                                ->maxLength(100)
                                ->unique(Product::class, 'sku', ignoreRecord: true),

                            TextInput::make('stock_quantity')
                                ->label('Stock Quantity')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            Select::make('status')
                                ->options([
                                    'draft'    => 'Draft',
                                    'active'   => 'Active',
                                    'inactive' => 'Inactive',
                                ])
                                ->default('active')
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            Toggle::make('is_new_arrival')
                                ->label('Mark as New Arrival'),

                            Toggle::make('is_customizable')
                                ->label('Accept Custom Orders'),
                        ]),
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
            Section::make('Images')
                ->schema([
                    Repeater::make('images')
                        ->relationship('images')
                        ->schema([
                            FileUpload::make('path')
                                ->label('Image')
                                ->image()
                                ->disk('public')
                                ->directory('products/images')
                                ->required(),

                            Toggle::make('is_primary')
                                ->label('Primary Image'),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->defaultItems(0),
                ]),

            Section::make('Variants')
                ->schema([
                    Repeater::make('variants')
                        ->relationship('variants')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->placeholder('e.g. Size 12, Medium'),

                            TextInput::make('sku')
                                ->label('Variant SKU'),

                            TextInput::make('base_price')
                                ->label('Base Price Override (₹)')
                                ->numeric()
                                ->prefix('₹'),

                            TextInput::make('weight_grams')
                                ->label('Weight (g)')
                                ->numeric(),

                            TextInput::make('stock_quantity')
                                ->label('Stock')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(5),
                ])->collapsible()->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->state(fn(Product $record) => $record->primaryImage?->path ?? $record->images->first()?->path)
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('metal_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gold'     => 'warning',
                        'silver'   => 'gray',
                        'platinum' => 'info',
                        default    => 'primary',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                TextColumn::make('weight_grams')
                    ->label('Weight (g)')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'draft'    => 'gray',
                        'inactive' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'    => 'Draft',
                        'active'   => 'Active',
                        'inactive' => 'Inactive',
                    ]),

                SelectFilter::make('metal_type')
                    ->options([
                        'gold'     => 'Gold',
                        'silver'   => 'Silver',
                        'platinum' => 'Platinum',
                        'other'    => 'Other',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }
}
