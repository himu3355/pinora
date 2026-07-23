# Step 27: Admin Panel — Product Management Resource

## Goal
Create a full Filament 5 Resource for managing ALL products across all vendors from the admin panel. Includes rich form sections for metal/jewellery details, stone info, certification, pricing, inventory, image repeater, variant repeater, and SEO.

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Filament/Resources/ProductResource.php` | Main resource class |
| `app/Filament/Resources/ProductResource/Pages/ListProducts.php` | List page |
| `app/Filament/Resources/ProductResource/Pages/CreateProduct.php` | Create page |
| `app/Filament/Resources/ProductResource/Pages/EditProduct.php` | Edit page |

---

## PHP Code

### `app/Filament/Resources/ProductResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ── Section 1: Basic Info ──────────────────────────────────
            Section::make('Basic Information')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->options(
                                Vendor::where('status', 'approved')
                                    ->orderBy('store_name')
                                    ->pluck('store_name', 'id')
                            )
                            ->required()
                            ->searchable(),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(
                                Category::where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable(),
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
                        ->maxLength(500)
                        ->columnSpanFull(),

                    RichEditor::make('description')
                        ->label('Full Description')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'bulletList', 'orderedList', 'blockquote',
                            'h2', 'h3', 'link', 'redo', 'undo',
                        ])
                        ->columnSpanFull(),
                ]),

            // ── Section 2: Metal & Jewellery Details ──────────────────
            Section::make('Metal & Jewellery Details')
                ->icon('heroicon-o-sparkles')
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
                            ->options(function (\Filament\Schemas\Components\Utilities\Get $get): array {
                                return match ($get('metal_type')) {
                                    'gold'     => ['24K' => '24K', '22K' => '22K', '18K' => '18K'],
                                    'silver'   => ['999' => '999', '925' => '925'],
                                    'platinum' => ['950' => '950'],
                                    default    => [],
                                };
                            })
                            ->disabled(fn(\Filament\Schemas\Components\Utilities\Get $get) => blank($get('metal_type'))),

                        TextInput::make('weight_grams')
                            ->label('Weight (grams)')
                            ->numeric()
                            ->step('0.001')
                            ->suffix('g')
                            ->minValue(0),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('making_charges')
                            ->label('Making Charges')
                            ->numeric()
                            ->prefix('₹')
                            ->minValue(0),

                        Select::make('making_charges_type')
                            ->label('Making Charges Type')
                            ->options([
                                'flat'       => 'Flat (₹ per piece)',
                                'per_gram'   => 'Per Gram (₹/g)',
                                'percentage' => 'Percentage (%)',
                            ])
                            ->default('flat'),
                    ]),
                ]),

            // ── Section 3: Stone Details ───────────────────────────────
            Section::make('Stone Details')
                ->icon('heroicon-o-gem') // use available icon
                ->collapsible()
                ->collapsed()
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('stone_type')
                            ->label('Stone Type')
                            ->maxLength(100)
                            ->placeholder('Diamond, Ruby, Emerald…'),

                        TextInput::make('stone_weight_carats')
                            ->label('Stone Weight (carats)')
                            ->numeric()
                            ->step('0.01')
                            ->suffix('ct')
                            ->minValue(0),

                        TextInput::make('stone_quality')
                            ->label('Stone Quality')
                            ->maxLength(100)
                            ->placeholder('VS1, VVS2, F, G…'),
                    ]),
                ]),

            // ── Section 4: Certification ───────────────────────────────
            Section::make('Certification')
                ->icon('heroicon-o-shield-check')
                ->collapsible()
                ->collapsed()
                ->schema([
                    CheckboxList::make('certifications')
                        ->label('Product Certifications & Badges')
                        ->options([
                            'bis_hallmark'        => 'BIS Hallmark',
                            'certified_diamond'   => 'Certified Diamond Jewellery',
                            'certified_jewellery' => '100% Certified Jewellery',
                            'lifetime_exchange'  => 'Lifetime Exchange and Buy Back',
                        ])
                        ->columns(2)
                        ->gridDirection('row')
                        ->helperText('Select the certifications applicable to this product. Corresponding logos will be displayed on the product page (upload logo images to public/images/certifications/).'),
                ]),

            // ── Section 5: Pricing ─────────────────────────────────────
            Section::make('Pricing')
                ->icon('heroicon-o-currency-rupee')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('base_price')
                            ->label('Base Price (₹)')
                            ->numeric()
                            ->prefix('₹')
                            ->minValue(0)
                            ->helperText('Leave empty to calculate from metal rate formula.'),

                        TextInput::make('discount_percent')
                            ->label('Discount')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0),

                        Toggle::make('is_price_on_request')
                            ->label('Price On Request')
                            ->helperText('Hides price and shows a "Get Quote" CTA.'),
                    ]),
                ]),

            // ── Section 6: Inventory & Status ─────────────────────────
            Section::make('Inventory & Status')
                ->icon('heroicon-o-cube')
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
                                'published' => 'Published',
                                'archived'  => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),

                    Grid::make(4)->schema([
                        Toggle::make('is_featured')
                            ->label('Featured'),

                        Toggle::make('is_new_arrival')
                            ->label('New Arrival'),

                        Toggle::make('is_customizable')
                            ->label('Customizable')
                            ->live(),

                        Toggle::make('is_price_on_request')
                            ->label('Price On Request'),
                    ]),

                    Textarea::make('customization_notes')
                        ->label('Customization Notes')
                        ->rows(2)
                        ->maxLength(1000)
                        ->visible(fn(\Filament\Schemas\Components\Utilities\Get $get) => (bool) $get('is_customizable'))
                        ->columnSpanFull(),
                ]),

            // ── Section 7: Product Images ──────────────────────────────
            Section::make('Product Images')
                ->icon('heroicon-o-photo')
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
                                ->imageEditor()
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
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ]),

            // ── Section 8: Variants ────────────────────────────────────
            Section::make('Product Variants')
                ->icon('heroicon-o-adjustments-horizontal')
                ->collapsible()
                ->collapsed()
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
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ]),

            // ── Section 9: SEO ─────────────────────────────────────────
            Section::make('SEO')
                ->icon('heroicon-o-magnifying-glass')
                ->collapsible()
                ->collapsed()
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primaryImage.path')
                    ->label('')
                    ->size(50)
                    ->disk('public'),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->limit(40),

                TextColumn::make('vendor.store_name')
                    ->label('Vendor')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                BadgeColumn::make('metal_type')
                    ->label('Metal')
                    ->colors([
                        'warning' => 'gold',
                        'gray'    => 'silver',
                        'info'    => 'platinum',
                    ])
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                TextColumn::make('purity')
                    ->label('Purity')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('calculated_price')
                    ->label('Price (₹)')
                    ->getStateUsing(fn(Product $record) => $record->calculated_price ?? $record->base_price)
                    ->money('INR')
                    ->sortable(false),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray'    => 'draft',
                        'success' => 'published',
                        'danger'  => 'archived',
                    ]),

                ToggleColumn::make('is_featured')
                    ->label('Featured'),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Published',
                        'archived'  => 'Archived',
                    ]),

                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->options(
                        Vendor::where('status', 'approved')
                            ->orderBy('store_name')
                            ->pluck('store_name', 'id')
                    )
                    ->searchable(),

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(
                        Category::where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable(),

                SelectFilter::make('metal_type')
                    ->options([
                        'gold'     => 'Gold',
                        'silver'   => 'Silver',
                        'platinum' => 'Platinum',
                        'other'    => 'Other',
                    ]),

                TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Created From'),
                        DatePicker::make('until')->label('Created Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['from'],
                                fn(Builder $q, $d) => $q->whereDate('created_at', '>=', $d)
                            )
                            ->when($data['until'],
                                fn(Builder $q, $d) => $q->whereDate('created_at', '<=', $d)
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('toggle_featured')
                    ->label(fn(Product $record) => $record->is_featured ? 'Unfeature' : 'Feature')
                    ->icon(fn(Product $record) => $record->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn(Product $record) => $record->is_featured ? 'warning' : 'gray')
                    ->action(fn(Product $record) => $record->update(['is_featured' => !$record->is_featured])),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    \Filament\Tables\Actions\BulkAction::make('publish_selected')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) =>
                            $records->each(fn(Product $p) => $p->update(['status' => 'published']))
                        ),

                    \Filament\Tables\Actions\BulkAction::make('archive_selected')
                        ->label('Archive Selected')
                        ->icon('heroicon-o-archive-box')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) =>
                            $records->each(fn(Product $p) => $p->update(['status' => 'archived']))
                        ),
                ]),
            ])
            ->emptyStateHeading('No products found')
            ->emptyStateIcon('heroicon-o-squares-2x2');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
```

---

### `app/Filament/Resources/ProductResource/Pages/ListProducts.php`

```php
<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

---

### `app/Filament/Resources/ProductResource/Pages/CreateProduct.php`

```php
<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

### `app/Filament/Resources/ProductResource/Pages/EditProduct.php`

```php
<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

## Artisan Commands

```bash
# Generate resource scaffold
php artisan make:filament-resource Product --generate

# Generate FilamentShield policies
php artisan shield:generate --resource=ProductResource

# Clear cache
php artisan filament:cache-components
```

---

## Notes

- **`primaryImage` relationship**: The `Product` model needs a `primaryImage()` hasOne relationship on `product_images` where `is_primary = true`. Use `ImageColumn::make('primaryImage.path')` to display it.
- **`calculated_price` accessor**: Define a `getCalculatedPriceAttribute()` accessor on the `Product` model that computes price from metal rate + weight + making charges if `base_price` is null.
- **`images` repeater**: Uses `->relationship('images')` — requires the `ProductImage` model and a `hasMany` relationship on `Product`.
- **`variants` repeater**: Uses `->relationship('variants')` — requires the `ProductVariant` model and a `hasMany` relationship on `Product`.
- **Stone/Certification sections**: Collapsed by default to keep the form clean for products without stones.
- **`ToggleColumn`**: The `is_featured` column is an inline toggle — admins can feature/unfeature directly from the table.
- **Vendor filter**: Only shows approved vendors in the filter dropdown.
