# Step 24: Admin Panel — Vendor Management Resource

## Goal
Create a full Filament 5 Resource for managing vendors in the admin panel, including list, create, edit, and view pages. Supports status management (approve/suspend/reject) with modal confirmations, and includes a "Manage as Vendor" impersonation shortcut.

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Filament/Resources/VendorResource.php` | Main resource class |
| `app/Filament/Resources/VendorResource/Pages/ListVendors.php` | List page |
| `app/Filament/Resources/VendorResource/Pages/CreateVendor.php` | Create page |
| `app/Filament/Resources/VendorResource/Pages/EditVendor.php` | Edit page |
| `app/Filament/Resources/VendorResource/Pages/ViewVendor.php` | View page |

---

## PHP Code

### `app/Filament/Resources/VendorResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Vendors';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'store_name';

    public static function form(Form $form): Form
    {
        $indianStates = [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
            'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka',
            'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram',
            'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu',
            'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
            'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu',
            'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
        ];

        return $form->schema([
            Section::make('Store Information')
                ->icon('heroicon-o-building-storefront')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('store_name')
                            ->label('Store Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) =>
                                $set('store_slug', Str::slug($state ?? ''))
                            ),

                        TextInput::make('store_slug')
                            ->label('Store Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Vendor::class, 'store_slug', ignoreRecord: true)
                            ->helperText('Auto-populated from store name. Must be unique.'),
                    ]),

                    Textarea::make('description')
                        ->label('Store Description')
                        ->rows(4)
                        ->maxLength(2000)
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        FileUpload::make('logo')
                            ->label('Store Logo')
                            ->image()
                            ->disk('public')
                            ->directory('vendors/logos')
                            ->maxSize(2048)
                            ->imageEditor(),

                        FileUpload::make('banner')
                            ->label('Store Banner')
                            ->image()
                            ->disk('public')
                            ->directory('vendors/banners')
                            ->maxSize(5120)
                            ->imageEditor(),
                    ]),
                ]),

            Section::make('Contact Information')
                ->icon('heroicon-o-phone')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(15),

                        TextInput::make('email')
                            ->label('Business Email')
                            ->email()
                            ->maxLength(255),
                    ]),

                    Textarea::make('address')
                        ->label('Street Address')
                        ->rows(2)
                        ->columnSpanFull(),

                    Grid::make(3)->schema([
                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(100),

                        Select::make('state')
                            ->label('State')
                            ->options(array_combine($indianStates, $indianStates))
                            ->searchable(),

                        TextInput::make('pincode')
                            ->label('Pincode')
                            ->maxLength(6)
                            ->numeric(),
                    ]),
                ]),

            Section::make('Business Details')
                ->icon('heroicon-o-briefcase')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('gst_number')
                            ->label('GST Number')
                            ->maxLength(15)
                            ->placeholder('22AAAAA0000A1Z5'),

                        TextInput::make('pan_number')
                            ->label('PAN Number')
                            ->maxLength(10)
                            ->placeholder('ABCDE1234F'),
                    ]),
                ]),

            Section::make('Commission & Status')
                ->icon('heroicon-o-percent-badge')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('commission_rate')
                            ->label('Commission Rate')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(10)
                            ->required(),

                        Select::make('status')
                            ->label('Vendor Status')
                            ->options([
                                'pending'   => 'Pending',
                                'approved'  => 'Approved',
                                'suspended' => 'Suspended',
                                'rejected'  => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
                ]),

            Section::make('Bank Details')
                ->icon('heroicon-o-building-library')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('bank_account_name')
                            ->label('Account Holder Name')
                            ->maxLength(255),

                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255),

                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(20),

                        TextInput::make('bank_ifsc_code')
                            ->label('IFSC Code')
                            ->maxLength(11)
                            ->placeholder('SBIN0001234'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('')
                    ->circular()
                    ->size(40),

                TextColumn::make('store_name')
                    ->label('Store Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->url(fn(Vendor $record) => VendorResource::getUrl('edit', ['record' => $record])),

                TextColumn::make('store_slug')
                    ->label('Slug')
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->suffix('%')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'suspended',
                        'gray'    => 'rejected',
                    ]),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable(),

                TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->money('INR')
                    ->getStateUsing(fn(Vendor $record) => $record->commissions()->sum('order_amount'))
                    ->sortable(false),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'approved'  => 'Approved',
                        'suspended' => 'Suspended',
                        'rejected'  => 'Rejected',
                    ]),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Registered From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Registered Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['created_from'],
                                fn(Builder $q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when($data['created_until'],
                                fn(Builder $q, $date) => $q->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Vendor')
                    ->modalDescription('Are you sure you want to approve this vendor? They will gain access to the vendor panel.')
                    ->visible(fn(Vendor $record) => $record->status !== 'approved')
                    ->action(function (Vendor $record): void {
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Vendor approved successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Suspend Vendor')
                    ->modalDescription('This vendor will lose access to the vendor panel.')
                    ->visible(fn(Vendor $record) => $record->status === 'approved')
                    ->action(function (Vendor $record): void {
                        $record->update(['status' => 'suspended']);
                        \Filament\Notifications\Notification::make()
                            ->title('Vendor suspended')
                            ->warning()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn(Vendor $record) => !in_array($record->status, ['rejected']))
                    ->action(function (Vendor $record, array $data): void {
                        $record->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Vendor rejected')
                            ->danger()
                            ->send();
                    }),

                Action::make('manage_as_vendor')
                    ->label('Manage as Vendor')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('gray')
                    ->visible(fn(Vendor $record) => $record->status === 'approved')
                    ->action(function (Vendor $record) {
                        session()->put('admin_managing_vendor_id', $record->id);
                        return redirect()->route('vendor.dashboard');
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn(Vendor $vendor) => $vendor->update([
                                'status'      => 'approved',
                                'approved_at' => now(),
                            ]));
                            \Filament\Notifications\Notification::make()
                                ->title(count($records) . ' vendors approved')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('suspend_selected')
                        ->label('Suspend Selected')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn(Vendor $vendor) => $vendor->update(['status' => 'suspended']));
                            \Filament\Notifications\Notification::make()
                                ->title(count($records) . ' vendors suspended')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('No vendors yet')
            ->emptyStateDescription('Vendors who apply will appear here for review.')
            ->emptyStateIcon('heroicon-o-building-storefront');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'view'   => Pages\ViewVendor::route('/{record}'),
            'edit'   => Pages\EditVendor::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
```

---

### `app/Filament/Resources/VendorResource/Pages/ListVendors.php`

```php
<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

---

### `app/Filament/Resources/VendorResource/Pages/CreateVendor.php`

```php
<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendor extends CreateRecord
{
    protected static string $resource = VendorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $vendor = $this->record;

        if (! $vendor->subscription) {
            \App\Models\VendorSubscription::create([
                'vendor_id'     => $vendor->id,
                'status'        => 'trialing',
                'trial_ends_at' => now()->addDays(14),
            ]);
        }
    }
}
```

---

### `app/Filament/Resources/VendorResource/Pages/EditVendor.php`

```php
<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
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

### `app/Filament/Resources/VendorResource/Pages/ViewVendor.php`

```php
<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVendor extends ViewRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
```

---

## Artisan Commands

```bash
# Generate resource scaffold (if building from scratch)
php artisan make:filament-resource Vendor --generate

# Clear Filament cache after adding resource
php artisan filament:cache-components

# Generate FilamentShield policies for this resource
php artisan shield:generate --resource=VendorResource
```

---

## Notes

- **`store_slug` auto-generation**: Handled via `->live(onBlur: true)` + `afterStateUpdated` on `store_name`. Only fires on blur to avoid slug-changes mid-typing.
- **`products_count`**: Uses Eloquent `withCount('products')` — ensure `Vendor` model has a `products()` hasMany relationship.
- **`total_sales`**: Computed from `commissions` relationship sum — not a DB column, so `sortable(false)`.
- **Navigation badge**: Shows count of pending vendors in red — drawn from `getNavigationBadge()`.
- **"Manage as Vendor"**: Stores `admin_managing_vendor_id` in session, then redirects to the vendor panel route. The vendor panel middleware should check this session key and switch context accordingly.
- **`rejection_reason` column**: Ensure this column exists on the `vendors` table migration (Step 12).
- **`approved_at` column**: Ensure this column exists on the `vendors` table migration.
