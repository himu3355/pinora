<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\OrderItemResource\Pages\ListOrderItems;
use App\Filament\Vendor\Resources\OrderItemResource\Pages\ViewOrderItem;
use App\Models\OrderItem;
use App\Services\VendorContext;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Schemas\Components\Grid as InfoGrid;
use Filament\Schemas\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $navigationLabel = 'My Orders';

    protected static ?string $pluralLabel = 'My Orders';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $vendor = auth()->user()->vendor;
        return $vendor && $vendor->hasActiveSubscription();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['order.user'])
            ->where('vendor_id', VendorContext::currentId());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('variant_name')
                    ->label('Variant')
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),

                TextColumn::make('total_price')
                    ->label('Total (Inc. Tax)')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('fulfillment_status')
                    ->label('Fulfillment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'accepted' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled', 'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? ''))
                    ->sortable(),

                TextColumn::make('order.shipping_name')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('order.shipping_phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('fulfillment_status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('From Date'),
                        DatePicker::make('created_until')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'From ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'To ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Select::make('fulfillment_status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'accepted' => 'Accepted',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default(fn (OrderItem $record): string => $record->fulfillment_status),
                    ])
                    ->action(function (OrderItem $record, array $data): void {
                        $updateData = ['fulfillment_status' => $data['fulfillment_status']];

                        if ($data['fulfillment_status'] === 'shipped') {
                            $updateData['shipped_at'] = now();
                        } elseif ($data['fulfillment_status'] === 'delivered') {
                            $updateData['delivered_at'] = now();
                        }

                        $record->update($updateData);
                    }),

                Action::make('addTracking')
                    ->label('Add Tracking')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->form([
                        TextInput::make('courier_name')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (OrderItem $record): ?string => $record->courier_name),
                        TextInput::make('tracking_number')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (OrderItem $record): ?string => $record->tracking_number),
                        TextInput::make('tracking_url')
                            ->url()
                            ->maxLength(500)
                            ->default(fn (OrderItem $record): ?string => $record->tracking_url),
                    ])
                    ->action(function (OrderItem $record, array $data): void {
                        $record->update([
                            'courier_name' => $data['courier_name'],
                            'tracking_number' => $data['tracking_number'],
                            'tracking_url' => $data['tracking_url'],
                            'fulfillment_status' => 'shipped',
                            'shipped_at' => $record->shipped_at ?? now(),
                        ]);
                    }),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                InfoGrid::make(3)
                    ->schema([
                        InfoSection::make('Order & Status')
                            ->columnSpan(2)
                            ->schema([
                                InfoGrid::make(3)->schema([
                                    TextEntry::make('order.order_number')
                                        ->label('Order Number')
                                        ->weight('bold'),
                                    TextEntry::make('created_at')
                                        ->label('Placed At')
                                        ->dateTime('d M Y h:i A'),
                                    TextEntry::make('fulfillment_status')
                                        ->label('Fulfillment Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'pending' => 'gray',
                                            'accepted' => 'info',
                                            'processing' => 'warning',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),
                                ]),
                            ]),

                        InfoSection::make('Customer Info')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('customer_name')
                                    ->label('Customer Name')
                                    ->state(fn($record) => $record->order?->user?->name ?? $record->order?->shipping_name),
                                TextEntry::make('customer_phone')
                                    ->label('Customer Phone')
                                    ->state(fn($record) => $record->order?->user?->phone ?? $record->order?->shipping_phone),
                                TextEntry::make('customer_email')
                                    ->label('Customer Email')
                                    ->state(fn($record) => $record->order?->user?->email ?? $record->order?->guest_email)
                                    ->placeholder('—'),
                            ]),
                    ]),

                InfoGrid::make(3)
                    ->schema([
                        InfoSection::make('Product Details')
                            ->columnSpan(2)
                            ->schema([
                                InfoGrid::make(3)->schema([
                                    TextEntry::make('product_name')
                                        ->label('Product Name')
                                        ->weight('bold'),
                                    TextEntry::make('variant_name')
                                        ->label('Variant')
                                        ->placeholder('—'),
                                    TextEntry::make('product_sku')
                                        ->label('SKU')
                                        ->placeholder('—'),
                                    TextEntry::make('quantity')
                                        ->label('Quantity'),
                                    TextEntry::make('metal_type')
                                        ->label('Metal Type')
                                        ->badge()
                                        ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),
                                    TextEntry::make('purity')
                                        ->label('Purity'),
                                    TextEntry::make('weight_grams')
                                        ->label('Weight (grams)')
                                        ->suffix(' g'),
                                    TextEntry::make('metal_rate_used')
                                        ->label('Metal Rate')
                                        ->money('INR'),
                                    TextEntry::make('making_charges')
                                        ->label('Making Charges')
                                        ->money('INR'),
                                ]),
                            ]),

                        InfoSection::make('Shipping Address')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('order.shipping_address_line_1')
                                    ->label('Address'),
                                TextEntry::make('order.shipping_address_line_2')
                                    ->label('Address Line 2')
                                    ->placeholder('—'),
                                TextEntry::make('order.shipping_city')
                                    ->label('City'),
                                TextEntry::make('order.shipping_state')
                                    ->label('State'),
                                TextEntry::make('order.shipping_pincode')
                                    ->label('Pincode'),
                                TextEntry::make('order.shipping_country')
                                    ->label('Country'),
                            ]),
                    ]),

                InfoGrid::make(3)
                    ->schema([
                        InfoSection::make('Financial Summary (GST Breakdown)')
                            ->columnSpan(2)
                            ->schema([
                                InfoGrid::make(4)->schema([
                                    TextEntry::make('unit_price')
                                        ->label('Unit Price (Pre-GST)')
                                        ->money('INR'),
                                    TextEntry::make('subtotal')
                                        ->label('Subtotal')
                                        ->money('INR'),
                                    TextEntry::make('cgst_amount')
                                        ->label('CGST')
                                        ->state(fn (OrderItem $record): string => "₹{$record->cgst_amount} ({$record->cgst_rate}%)"),
                                    TextEntry::make('sgst_amount')
                                        ->label('SGST')
                                        ->state(fn (OrderItem $record): string => "₹{$record->sgst_amount} ({$record->sgst_rate}%)"),
                                    TextEntry::make('igst_amount')
                                        ->label('IGST')
                                        ->state(fn (OrderItem $record): string => "₹{$record->igst_amount} ({$record->igst_rate}%)"),
                                    TextEntry::make('total_price')
                                        ->label('Final Total')
                                        ->money('INR')
                                        ->weight('bold'),
                                ]),
                            ]),

                        InfoSection::make('Tracking Details')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('courier_name')
                                    ->label('Courier')
                                    ->placeholder('—'),
                                TextEntry::make('tracking_number')
                                    ->label('Tracking No.')
                                    ->placeholder('—'),
                                TextEntry::make('tracking_url')
                                    ->label('Tracking URL')
                                    ->url()
                                    ->placeholder('—'),
                                TextEntry::make('shipped_at')
                                    ->label('Shipped At')
                                    ->dateTime('d M Y h:i A')
                                    ->placeholder('—'),
                                TextEntry::make('delivered_at')
                                    ->label('Delivered At')
                                    ->dateTime('d M Y h:i A')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderItems::route('/'),
            'view' => ViewOrderItem::route('/{record}'),
        ];
    }
}
