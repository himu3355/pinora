<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category Details')
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
                        ->maxLength(1000),

                    Grid::make(2)->schema([
                        FileUpload::make('image')
                            ->label('Category Image')
                            ->image()
                            ->disk('public')
                            ->directory('categories/images')
                            ->maxSize(2048),

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
}
