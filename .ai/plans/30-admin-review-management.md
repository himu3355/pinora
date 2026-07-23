# Step 30: Admin Panel — Review Approval Resource

## Goal
Create a Filament 5 Resource for approving or rejecting product reviews. Features a default "pending first" sort, inline approve/reject actions with modal for rejection notes, bulk actions, and a navigation badge showing the pending review count.

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Filament/Resources/ReviewResource.php` | Main resource class |
| `app/Filament/Resources/ReviewResource/Pages/ListReviews.php` | List page |
| `app/Filament/Resources/ReviewResource/Pages/ViewReview.php` | View/Edit page |

---

## PHP Code

### `app/Filament/Resources/ReviewResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = 'Review';

    protected static ?string $pluralModelLabel = 'Reviews';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Review Details')
                ->icon('heroicon-o-star')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('product.name')
                            ->label('Product')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('user.name')
                            ->label('Customer')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('rating')
                            ->label('Rating')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('/ 5'),

                        TextInput::make('title')
                            ->label('Review Title')
                            ->maxLength(255),
                    ]),

                    Textarea::make('body')
                        ->label('Review Body')
                        ->rows(4)
                        ->maxLength(2000)
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'  => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),

                        TextInput::make('admin_note')
                            ->label('Admin Note')
                            ->maxLength(500)
                            ->helperText('Reason for rejection (if applicable).'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->weight('semibold'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(function (int $state): string {
                        $filled  = str_repeat('★', $state);
                        $empty   = str_repeat('☆', 5 - $state);
                        return $filled . $empty;
                    })
                    ->color(fn(int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default     => 'danger',
                    }),

                TextColumn::make('title')
                    ->label('Title')
                    ->limit(40)
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ]),

                IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip('Verified Purchase'),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort(function (Builder $query): Builder {
                // Pending reviews first, then by newest
                return $query
                    ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
                    ->orderBy('created_at', 'desc');
            })
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable(),

                SelectFilter::make('rating')
                    ->options([
                        '1' => '1 Star',
                        '2' => '2 Stars',
                        '3' => '3 Stars',
                        '4' => '4 Stars',
                        '5' => '5 Stars',
                    ]),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Submitted From'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Submitted Until'),
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

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Review')
                    ->modalDescription('This review will be published on the product page.')
                    ->visible(fn(Review $record) => $record->status !== 'approved')
                    ->action(function (Review $record): void {
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Review approved')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Review $record) => $record->status !== 'rejected')
                    ->form([
                        Textarea::make('admin_note')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Why is this review being rejected?'),
                    ])
                    ->action(function (Review $record, array $data): void {
                        $record->update([
                            'status'     => 'rejected',
                            'admin_note' => $data['admin_note'],
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Review rejected')
                            ->danger()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Review $review): void {
                                if ($review->status !== 'approved') {
                                    $review->update([
                                        'status'      => 'approved',
                                        'approved_at' => now(),
                                        'approved_by' => auth()->id(),
                                    ]);
                                }
                            });
                            \Filament\Notifications\Notification::make()
                                ->title(count($records) . ' reviews approved')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Textarea::make('admin_note')
                                ->label('Rejection Reason (applies to all selected)')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Review $review) use ($data): void {
                                if ($review->status !== 'rejected') {
                                    $review->update([
                                        'status'     => 'rejected',
                                        'admin_note' => $data['admin_note'],
                                    ]);
                                }
                            });
                            \Filament\Notifications\Notification::make()
                                ->title(count($records) . ' reviews rejected')
                                ->danger()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No reviews to moderate')
            ->emptyStateIcon('heroicon-o-star');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view'  => Pages\ViewReview::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
```

---

### `app/Filament/Resources/ReviewResource/Pages/ListReviews.php`

```php
<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        // Reviews are submitted by customers; admins don't create them
        return [];
    }
}
```

---

### `app/Filament/Resources/ReviewResource/Pages/ViewReview.php`

```php
<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class ViewReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    public function getTitle(): string
    {
        return 'Review: ' . ($this->record->title ?: 'No Title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve Review')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->status !== 'approved')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update([
                        'status'      => 'approved',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                    ]);
                    $this->refreshFormData(['status', 'approved_at']);
                    \Filament\Notifications\Notification::make()
                        ->title('Review approved')
                        ->success()
                        ->send();
                }),

            Action::make('reject')
                ->label('Reject Review')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->status !== 'rejected')
                ->form([
                    Textarea::make('admin_note')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status'     => 'rejected',
                        'admin_note' => $data['admin_note'],
                    ]);
                    $this->refreshFormData(['status', 'admin_note']);
                    \Filament\Notifications\Notification::make()
                        ->title('Review rejected')
                        ->danger()
                        ->send();
                }),

            Action::make('save')
                ->label('Save Changes')
                ->action(fn() => $this->save()),
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
php artisan make:filament-resource Review --generate

# Generate FilamentShield policies
php artisan shield:generate --resource=ReviewResource

# Clear cache
php artisan filament:cache-components
```

---

## Notes

- **Default sort — pending first**: Uses `->defaultSort(fn(Builder $query) => ...)` with a raw `FIELD()` SQL expression for MySQL. For PostgreSQL, use a `CASE WHEN` expression instead.
- **`approved_at` / `approved_by` columns**: Require these columns on the `reviews` table (ensure they exist from Step 8/9 migrations).
- **`admin_note` column**: Requires a `admin_note` text column on the `reviews` table.
- **`is_verified_purchase` column**: Should be set automatically when an order containing the product is delivered. The `OrderObserver` or an event listener should set this flag.
- **Star display**: The `rating` column uses Unicode stars (★/☆) for visual display. This is a simple, dependency-free approach.
- **Bulk rejection**: Uses a single `admin_note` for all selected — acceptable for bulk operations. Individual rejection via the `reject` action allows per-review notes.
- **No create**: Reviews are submitted by customers. Admins can only moderate, not create reviews.
- **`refreshFormData`**: Called after inline approve/reject actions on the view page to re-render the status field without a full page refresh.
