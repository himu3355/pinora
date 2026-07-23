# Step 34: Vendor Panel — Product Management

**Goal:** Create a Filament Resource for vendors to manage their own products.
**Depends On:** Step 33 (Vendor Panel Setup), Step 15 (Product model & migrations)
**Next Step:** Step 35 (Vendor Order Management)

---

## Files to Create

### `app/Filament/Vendor/Resources/ProductResource.php`

```php
<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Services\VendorContext;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model           = Product::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'My Products';
    protected static ?string $pluralLabel     = 'My Products';
    protected static ?int    $navigationSort  = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_id', VendorContext::currentId());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state) =>
                            $set('slug', str($state)->slug())
                        ),

                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Product::class, 'slug', ignoreRecord: true),

                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->options(
                            Category::whereNull('parent_id')
                                ->with('children')
                                ->get()
                                ->flatMap(fn ($cat) => array_merge(
                                    [$cat->id => $cat->name],
                                    $cat->children->mapWithKeys(fn ($c) => [$c->id => '— ' . $c->name])->toArray()
                                ))
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Metal & Stone Details')
                ->schema([
                    Forms\Components\Select::make('metal_type')
                        ->options([
                            'gold'     => 'Gold',
                            'silver'   => 'Silver',
                            'platinum' => 'Platinum',
                            'other'    => 'Other',
                        ])
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('purity')
                        ->options([
                            '24K' => '24K',
                            '22K' => '22K',
                            '18K' => '18K',
                            '14K' => '14K',
                            '925' => '925 Sterling Silver',
                            '950' => '950 Platinum',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('weight_grams')
                        ->label('Weight (grams)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01),

                    Forms\Components\TextInput::make('making_charges')
                        ->label('Making Charges (₹)')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('₹'),

                    Forms\Components\TextInput::make('base_price')
                        ->label('Fixed Base Price (₹) — overrides metal rate calculation')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('₹')
                        ->helperText('Leave empty to auto-calculate from live metal rate'),

                    Forms\Components\TextInput::make('discount_percent')
                        ->label('Discount (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),

                    Forms\Components\Select::make('stone_type')
                        ->options([
                            'diamond'  => 'Diamond',
                            'ruby'     => 'Ruby',
                            'emerald'  => 'Emerald',
                            'sapphire' => 'Sapphire',
                            'pearl'    => 'Pearl',
                            'none'     => 'None / Plain',
                        ]),

                    Forms\Components\TextInput::make('stone_weight_carats')
                        ->label('Stone Weight (carats)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01),
                ])->columns(2),

            Forms\Components\Section::make('Inventory & Status')
                ->schema([
                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->maxLength(100)
                        ->unique(Product::class, 'sku', ignoreRecord: true),

                    Forms\Components\TextInput::make('stock_quantity')
                        ->label('Stock Quantity')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),

                    Forms\Components\Select::make('status')
                        ->options([
                            'draft'    => 'Draft',
                            'active'   => 'Active',
                            'inactive' => 'Inactive',
                        ])
                        ->default('draft')
                        ->required(),

                    Forms\Components\Toggle::make('is_new_arrival')
                        ->label('Mark as New Arrival'),

                    Forms\Components\Toggle::make('is_customizable')
                        ->label('Accept Custom Orders'),
                ])->columns(2),

            Forms\Components\Section::make('Certification')
                ->schema([
                    Forms\Components\Select::make('certification_type')
                        ->options([
                            'bis_hallmark' => 'BIS Hallmark',
                            'gia'          => 'GIA',
                            'igi'          => 'IGI',
                            'none'         => 'None',
                        ]),

                    Forms\Components\TextInput::make('certification_number')
                        ->maxLength(100),
                ])->columns(2),

            Forms\Components\Section::make('Images')
                ->schema([
                    Forms\Components\Repeater::make('images')
                        ->relationship('images')
                        ->schema([
                            Forms\Components\FileUpload::make('image_path')
                                ->label('Image')
                                ->image()
                                ->directory('products')
                                ->required(),

                            Forms\Components\Toggle::make('is_primary')
                                ->label('Primary Image'),

                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->defaultItems(1),
                ]),

            Forms\Components\Section::make('Variants')
                ->schema([
                    Forms\Components\Repeater::make('variants')
                        ->relationship('variants')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->placeholder('e.g. Size 12, Medium'),

                            Forms\Components\TextInput::make('sku')
                                ->label('Variant SKU'),

                            Forms\Components\TextInput::make('base_price')
                                ->label('Base Price Override (₹)')
                                ->numeric()
                                ->prefix('₹'),

                            Forms\Components\TextInput::make('weight_grams')
                                ->label('Weight (g)')
                                ->numeric(),

                            Forms\Components\TextInput::make('stock_quantity')
                                ->label('Stock')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3),
                ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primaryImage.image_path')
                    ->label('Image')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('metal_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gold'     => 'warning',
                        'silver'   => 'gray',
                        'platinum' => 'info',
                        default    => 'primary',
                    }),

                Tables\Columns\TextColumn::make('weight_grams')
                    ->label('Weight (g)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'draft'    => 'gray',
                        'inactive' => 'danger',
                        default    => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'    => 'Draft',
                        'active'   => 'Active',
                        'inactive' => 'Inactive',
                    ]),

                Tables\Filters\SelectFilter::make('metal_type')
                    ->options([
                        'gold'     => 'Gold',
                        'silver'   => 'Silver',
                        'platinum' => 'Platinum',
                        'other'    => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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

### `app/Filament/Vendor/Resources/ProductResource/Pages/ListProducts.php`

```php
<?php

namespace App\Filament\Vendor\Resources\ProductResource\Pages;

use App\Filament\Vendor\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
```

---

### `app/Filament/Vendor/Resources/ProductResource/Pages/CreateProduct.php`

```php
<?php

namespace App\Filament\Vendor\Resources\ProductResource\Pages;

use App\Filament\Vendor\Resources\ProductResource;
use App\Services\VendorContext;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['vendor_id'] = VendorContext::currentId();
        return $data;
    }
}
```

---

### `app/Filament/Vendor/Resources/ProductResource/Pages/EditProduct.php`

```php
<?php

namespace App\Filament\Vendor\Resources\ProductResource\Pages;

use App\Filament\Vendor\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
```

---

## Notes

- `vendor_id` is **never** shown in the vendor panel form — it is injected automatically in `CreateProduct::mutateFormDataBeforeCreate()`.
- `getEloquentQuery()` scopes all queries to the current vendor — vendors can only see and edit their own products.
- `is_featured` is a read-only icon column. Vendors can see if admin has featured their product but cannot change it.
- Status `active` can be set by the vendor directly in V1. For admin-approval flow, change default to `draft` and restrict the `active` option.
