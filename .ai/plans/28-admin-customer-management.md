# Step 28: Admin Panel — Customer Management Resource

## Goal
Create a Filament 5 Resource for managing customers. Includes profile editing, jewellery size preferences, customer tagging, account status management, and a session-based impersonation feature that lets admins browse the storefront as a customer.

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Filament/Resources/CustomerResource.php` | Main resource class |
| `app/Filament/Resources/CustomerResource/Pages/ListCustomers.php` | List page |
| `app/Filament/Resources/CustomerResource/Pages/CreateCustomer.php` | Create page |
| `app/Filament/Resources/CustomerResource/Pages/EditCustomer.php` | Edit page |
| `app/Filament/Resources/CustomerResource/Pages/ViewCustomer.php` | View page with sub-tables |
| `app/Http/Middleware/CheckImpersonation.php` | Middleware for frontend impersonation banner |

---

## PHP Code

### `app/Filament/Resources/CustomerResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Actions\Action;
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

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Only show users who have the 'customer' role OR have at least one order.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $q) {
                $q->whereHas('roles', fn(Builder $r) => $r->where('name', 'customer'))
                  ->orWhereHas('orders');
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ── Section 1: Personal Info ───────────────────────────────
            Section::make('Personal Information')
                ->icon('heroicon-o-user')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email', ignoreRecord: true),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(15),

                        Select::make('gender')
                            ->label('Gender')
                            ->options([
                                'male'   => 'Male',
                                'female' => 'Female',
                                'other'  => 'Other / Prefer not to say',
                            ])
                            ->nullable(),
                    ]),

                    Grid::make(2)->schema([
                        DatePicker::make('birthday')
                            ->label('Date of Birth')
                            ->maxDate(today()->subYears(13)->toDateString()),

                        DatePicker::make('anniversary_date')
                            ->label('Anniversary Date'),
                    ]),

                    FileUpload::make('avatar')
                        ->label('Profile Picture')
                        ->image()
                        ->disk('public')
                        ->directory('users/avatars')
                        ->maxSize(2048)
                        ->imageEditor()
                        ->circular(),
                ]),

            // ── Section 2: Jewellery Preferences ──────────────────────
            Section::make('Jewellery Preferences')
                ->icon('heroicon-o-sparkles')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('ring_size')
                            ->label('Ring Size')
                            ->options(array_combine(
                                range(1, 30),
                                array_map(fn($s) => "Size $s", range(1, 30))
                            ))
                            ->nullable()
                            ->searchable(),

                        Select::make('bangle_size')
                            ->label('Bangle Size')
                            ->options([
                                '2/2'  => '2/2 (XS)',
                                '2/4'  => '2/4 (S)',
                                '2/6'  => '2/6 (M)',
                                '2/8'  => '2/8 (L)',
                                '2/10' => '2/10 (XL)',
                                '2/12' => '2/12 (XXL)',
                            ])
                            ->nullable(),
                    ]),
                ]),

            // ── Section 3: Account Management ─────────────────────────
            Section::make('Account Management')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('customer_tag')
                            ->label('Customer Tag')
                            ->options([
                                'regular'  => 'Regular',
                                'vip'      => 'VIP',
                                'premium'  => 'Premium',
                                'new'      => 'New',
                                'at_risk'  => 'At Risk',
                                'churned'  => 'Churned',
                            ])
                            ->default('regular')
                            ->helperText('Tags help segment customers for marketing.'),

                        Select::make('status')
                            ->label('Account Status')
                            ->options([
                                'active'    => 'Active',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->disk('public'),

                TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—'),

                BadgeColumn::make('customer_tag')
                    ->label('Tag')
                    ->colors([
                        'gray'    => 'regular',
                        'warning' => 'vip',
                        'success' => 'premium',
                        'info'    => 'new',
                        'danger'  => 'at_risk',
                        'primary' => 'churned',
                    ])
                    ->placeholder('—'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'suspended',
                    ]),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->getStateUsing(fn(User $record) =>
                        $record->orders()->where('payment_status', 'paid')->sum('total_amount')
                    )
                    ->money('INR')
                    ->sortable(false),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('customer_tag')
                    ->label('Tag')
                    ->options([
                        'regular'  => 'Regular',
                        'vip'      => 'VIP',
                        'premium'  => 'Premium',
                        'new'      => 'New',
                        'at_risk'  => 'At Risk',
                        'churned'  => 'Churned',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'active'    => 'Active',
                        'suspended' => 'Suspended',
                    ]),

                Filter::make('joined')
                    ->form([
                        DatePicker::make('from')->label('Joined From'),
                        DatePicker::make('until')->label('Joined Until'),
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

                Action::make('change_tag')
                    ->label('Change Tag')
                    ->icon('heroicon-o-tag')
                    ->color('gray')
                    ->form([
                        Select::make('customer_tag')
                            ->label('New Tag')
                            ->options([
                                'regular'  => 'Regular',
                                'vip'      => 'VIP',
                                'premium'  => 'Premium',
                                'new'      => 'New',
                                'at_risk'  => 'At Risk',
                                'churned'  => 'Churned',
                            ])
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update(['customer_tag' => $data['customer_tag']]);
                        \Filament\Notifications\Notification::make()
                            ->title('Tag updated')
                            ->success()
                            ->send();
                    }),

                Action::make('toggle_status')
                    ->label(fn(User $record) => $record->status === 'active' ? 'Suspend' : 'Activate')
                    ->icon(fn(User $record) => $record->status === 'active' ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn(User $record) => $record->status === 'active' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $newStatus = $record->status === 'active' ? 'suspended' : 'active';
                        $record->update(['status' => $newStatus]);
                        \Filament\Notifications\Notification::make()
                            ->title('Status updated to ' . $newStatus)
                            ->success()
                            ->send();
                    }),

                Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Impersonate Customer')
                    ->modalDescription(fn(User $record) => "You will be logged in as {$record->name} on the storefront. A banner will allow you to exit.")
                    ->action(function (User $record) {
                        // Store the real admin's ID so we can restore them later
                        session()->put('impersonating_admin_id', auth()->id());
                        // Switch auth to the customer
                        auth()->login($record);
                        return redirect()->route('home');
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Tables\Actions\BulkAction::make('bulk_tag')
                        ->label('Tag Selected')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('customer_tag')
                                ->label('Tag')
                                ->options([
                                    'regular'  => 'Regular',
                                    'vip'      => 'VIP',
                                    'premium'  => 'Premium',
                                    'at_risk'  => 'At Risk',
                                    'churned'  => 'Churned',
                                ])
                                ->required(),
                        ])
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records, array $data) =>
                            $records->each(fn(User $u) => $u->update(['customer_tag' => $data['customer_tag']]))
                        ),
                ]),
            ])
            ->emptyStateHeading('No customers found')
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view'   => Pages\ViewCustomer::route('/{record}'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
```

---

### `app/Filament/Resources/CustomerResource/Pages/ListCustomers.php`

```php
<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

---

### `app/Filament/Resources/CustomerResource/Pages/CreateCustomer.php`

```php
<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = bcrypt(\Illuminate\Support\Str::random(16));
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->assignRole('customer');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

### `app/Filament/Resources/CustomerResource/Pages/EditCustomer.php`

```php
<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
```

---

### `app/Filament/Resources/CustomerResource/Pages/ViewCustomer.php`

```php
<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Customer Profile')
                ->schema([
                    Grid::make(3)->schema([
                        ImageEntry::make('avatar')
                            ->label('Avatar')
                            ->circular()
                            ->disk('public'),

                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('email')->label('Email')->copyable(),
                        TextEntry::make('phone')->label('Phone')->placeholder('—'),
                        TextEntry::make('gender')->label('Gender')->badge()->placeholder('—'),
                        TextEntry::make('birthday')->label('Birthday')->date('d M Y')->placeholder('—'),
                        TextEntry::make('anniversary_date')->label('Anniversary')->date('d M Y')->placeholder('—'),
                        TextEntry::make('ring_size')->label('Ring Size')->placeholder('—'),
                        TextEntry::make('bangle_size')->label('Bangle Size')->placeholder('—'),
                        TextEntry::make('customer_tag')->label('Tag')->badge(),
                        TextEntry::make('status')->label('Status')->badge()
                            ->color(fn($state) => $state === 'active' ? 'success' : 'danger'),
                        TextEntry::make('created_at')->label('Joined')->date('d M Y'),
                    ]),
                ]),
        ]);
    }
}
```

---

### `app/Http/Middleware/CheckImpersonation.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('impersonating_admin_id')) {
            // Share the impersonation state with all views
            view()->share('impersonating', true);
            view()->share('impersonated_name', auth()->user()->name ?? 'Unknown');
        }

        return $next($request);
    }
}
```

Add this middleware to `bootstrap/app.php` (Laravel 13 style):

```php
->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->appendToGroup('web', \App\Http\Middleware\CheckImpersonation::class);
})
```

---

### Frontend impersonation banner (Blade snippet)

Add to your frontend layout `resources/views/layouts/app.blade.php`:

```blade
@if(session()->has('impersonating_admin_id'))
<div class="impersonation-banner" style="background:#ef4444;color:white;padding:8px 16px;text-align:center;position:sticky;top:0;z-index:9999;">
    <strong>⚠ You are impersonating {{ auth()->user()->name }}</strong>
    &mdash;
    <a href="{{ route('admin.impersonation.exit') }}" style="color:white;text-decoration:underline;">Exit Impersonation</a>
</div>
@endif
```

---

### Exit Impersonation Route + Controller

In `routes/web.php`:

```php
Route::get('/admin/exit-impersonation', function () {
    $adminId = session()->pull('impersonating_admin_id');
    if ($adminId) {
        auth()->loginUsingId($adminId);
    }
    return redirect()->route('filament.admin.pages.dashboard');
})->name('admin.impersonation.exit')->middleware('auth');
```

---

## Artisan Commands

```bash
# Generate resource scaffold
php artisan make:filament-resource Customer --generate --model=User

# Generate FilamentShield policies
php artisan shield:generate --resource=CustomerResource

# Clear cache
php artisan filament:cache-components
```

---

## Notes

- **`getEloquentQuery()` override**: Filters the `User` model to only show customers (role=customer OR has orders). This prevents admin/vendor users from appearing in this list.
- **`status` column**: Requires a `status` column on the `users` table (add via migration if not present). Default: `active`.
- **`customer_tag` column**: Requires a `customer_tag` string column on the `users` table.
- **Impersonation security**: The `impersonating_admin_id` key is stored in the session (server-side). The exit route restores the original admin. Always verify the admin is re-authenticated on exit. Consider adding a `can:impersonate_customer` gate check.
- **`ring_size` / `bangle_size`**: Require columns on the `users` table or a separate `customer_profiles` table (depending on your Step 3 migration decisions).
- **CreateCustomer**: Auto-assigns `customer` role after creation and generates a random password. The customer should receive an email to set their password — integrate with Laravel's password reset flow.
