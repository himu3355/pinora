<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateSubCategory;
use App\Filament\Resources\Categories\Pages\EditSubCategory;
use App\Filament\Resources\Categories\Pages\ListSubCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SubCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $slug = 'sub-categories';

    protected static ?string $navigationLabel = 'Sub-Categories';

    protected static ?string $modelLabel = 'Sub-Category';

    protected static ?string $pluralModelLabel = 'Sub-Categories';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static \UnitEnum|string|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 21;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('parent_id');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sub-Category Details')
                ->schema([
                    Select::make('parent_id')
                        ->label('Parent Category')
                        ->required()
                        ->options(
                            Category::whereNull('parent_id')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->helperText('Select the main category for this sub-category.'),

                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Sub-Category Name')
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
                        ->maxLength(1000),

                    Grid::make(2)->schema([
                        FileUpload::make('image')
                            ->label('Sub-Category Image')
                            ->image()
                            ->disk('public')
                            ->directory('categories/images')
                            ->maxSize(2048),

                        TextInput::make('icon')
                            ->label('Heroicon Name')
                            ->placeholder('heroicon-o-sparkles')
                            ->maxLength(100),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->size(40)
                    ->circular(),

                TextColumn::make('name')
                    ->label('Sub-Category')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('parent.name')
                    ->label('Parent Category')
                    ->sortable()
                    ->badge()
                    ->color('amber'),

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

                SelectFilter::make('parent_id')
                    ->label('Parent Category')
                    ->options(
                        Category::whereNull('parent_id')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    ),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('toggle_active')
                    ->label(fn(Category $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn(Category $record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Category $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn(Category $record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate_selected')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn(Collection $records) =>
                            $records->each(fn(Category $c) => $c->update(['is_active' => true]))
                        ),

                    BulkAction::make('deactivate_selected')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn(Collection $records) =>
                            $records->each(fn(Category $c) => $c->update(['is_active' => false]))
                        ),
                ]),
            ])
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSubCategories::route('/'),
            'create' => CreateSubCategory::route('/create'),
            'edit'   => EditSubCategory::route('/{record}/edit'),
        ];
    }
}
