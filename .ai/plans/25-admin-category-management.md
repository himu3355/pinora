# Step 25: Admin Panel — Category Management Resource

## Goal
Create a Filament 5 Resource for managing product categories. Supports one level of parent/child nesting, slug auto-generation, SEO fields, and active/inactive toggling.

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Filament/Resources/CategoryResource.php` | Main resource class |
| `app/Filament/Resources/CategoryResource/Pages/ListCategories.php` | List page |
| `app/Filament/Resources/CategoryResource/Pages/CreateCategory.php` | Create page |
| `app/Filament/Resources/CategoryResource/Pages/EditCategory.php` | Edit page |

---

## PHP Code

### `app/Filament/Resources/CategoryResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Category Details')
                ->icon('heroicon-o-tag')
                ->schema([
                    Select::make('parent_id')
                        ->label('Parent Category')
                        ->options(
                            Category::whereNull('parent_id')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->placeholder('— Top Level Category —')
                        ->searchable()
                        ->nullable()
                        ->helperText('Only one level of nesting is supported.'),

                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Category Name')
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
                            ->unique(Category::class, 'slug', ignoreRecord: true)
                            ->helperText('Auto-populated from name. Must be unique.'),
                    ]),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        FileUpload::make('image')
                            ->label('Category Image')
                            ->image()
                            ->disk('public')
                            ->directory('categories/images')
                            ->maxSize(2048)
                            ->imageEditor(),

                        TextInput::make('icon')
                            ->label('Heroicon Name')
                            ->placeholder('heroicon-o-sparkles')
                            ->maxLength(100)
                            ->helperText('Use any Heroicon name (e.g. heroicon-o-sparkles)'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive categories are hidden from the storefront.'),
                    ]),
                ]),

            Section::make('SEO')
                ->icon('heroicon-o-magnifying-glass')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->helperText('Recommended: 50–60 characters'),

                        TextInput::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(160)
                            ->helperText('Recommended: 150–160 characters'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->size(40)
                    ->circular(),

                TextColumn::make('name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('— Top Level —')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Filter::make('is_active')
                    ->label('Active Only')
                    ->query(fn(Builder $query) => $query->where('is_active', true))
                    ->toggle(),

                Filter::make('top_level')
                    ->label('Top Level Only')
                    ->query(fn(Builder $query) => $query->whereNull('parent_id'))
                    ->toggle(),

                SelectFilter::make('parent_id')
                    ->label('Under Parent')
                    ->options(
                        Category::whereNull('parent_id')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->placeholder('All'),
            ])
            ->actions([
                EditAction::make(),

                \Filament\Actions\Action::make('toggle_active')
                    ->label(fn(Category $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn(Category $record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Category $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn(Category $record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    \Filament\Tables\Actions\BulkAction::make('activate_selected')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) =>
                            $records->each(fn(Category $c) => $c->update(['is_active' => true]))
                        ),

                    \Filament\Tables\Actions\BulkAction::make('deactivate_selected')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) =>
                            $records->each(fn(Category $c) => $c->update(['is_active' => false]))
                        ),
                ]),
            ])
            ->reorderable('sort_order')
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Start by creating your first product category.')
            ->emptyStateIcon('heroicon-o-tag');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
```

---

### `app/Filament/Resources/CategoryResource/Pages/ListCategories.php`

```php
<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

---

### `app/Filament/Resources/CategoryResource/Pages/CreateCategory.php`

```php
<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

### `app/Filament\Resources\CategoryResource\Pages\EditCategory.php`

```php
<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (Category $record, DeleteAction $action) {
                    if ($record->products()->exists()) {
                        \Filament\Notifications\Notification::make()
                            ->title('Cannot delete category')
                            ->body('This category has products assigned to it.')
                            ->danger()
                            ->send();
                        $action->cancel();
                    }
                }),
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
php artisan make:filament-resource Category --generate

# Generate FilamentShield policies
php artisan shield:generate --resource=CategoryResource

# Clear Filament component cache
php artisan filament:cache-components
```

---

## Notes

- **One level nesting only**: The parent `Select` is filtered to `whereNull('parent_id')`, enforcing a max depth of 1. Sub-categories cannot be used as parents.
- **Table reordering**: `->reorderable('sort_order')` enables drag-and-drop sorting of categories directly in the table. Requires the `sort_order` column to be an integer.
- **`products_count`**: Uses `->counts('products')` — ensure the `Category` model has a `products()` hasMany relationship.
- **Guard against deletion with products**: The `EditCategory` delete action checks for existing products before allowing deletion.
- **`meta_title` and `meta_description`**: Stored as columns on the `categories` table (ensure these exist in migrations from Step 5/6).
- **`icon` field**: Stores a Heroicon identifier string. The storefront renders this using Blade's `x-heroicon` component or Filament's icon helper.
