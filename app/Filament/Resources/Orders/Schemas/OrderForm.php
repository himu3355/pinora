<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order Summary')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending'    => 'Pending',
                                'confirmed'  => 'Confirmed',
                                'processing' => 'Processing',
                                'shipped'    => 'Shipped',
                                'delivered'  => 'Delivered',
                                'cancelled'  => 'Cancelled',
                                'refunded'   => 'Refunded',
                            ])
                            ->required(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending'  => 'Pending',
                                'paid'     => 'Paid',
                                'failed'   => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'razorpay' => 'Razorpay',
                                'cod'      => 'Cash on Delivery',
                                'bank'     => 'Bank Transfer',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->maxLength(255),

                        DatePicker::make('paid_at')
                            ->label('Paid At'),
                    ]),

                    Textarea::make('notes')
                        ->label('Admin Notes')
                        ->rows(2)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),

            Section::make('Customer')
                ->icon('heroicon-o-user')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->formatStateUsing(fn(?Order $record) => $record?->user?->name ?? 'Guest')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('customer_email')
                            ->label('Customer Email')
                            ->formatStateUsing(fn(?Order $record) => $record?->user?->email ?? $record?->guest_email)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('customer_phone')
                            ->label('Customer Phone')
                            ->formatStateUsing(fn(?Order $record) => $record?->user?->phone ?? $record?->guest_phone)
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                ]),

            Section::make('Shipping Address (Snapshot)')
                ->icon('heroicon-o-map-pin')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('shipping_name')
                            ->label('Recipient Name')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_phone')
                            ->label('Phone')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    TextInput::make('shipping_address_line_1')
                        ->label('Address Line 1')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    TextInput::make('shipping_address_line_2')
                        ->label('Address Line 2')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    Grid::make(4)->schema([
                        TextInput::make('shipping_city')
                            ->label('City')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_state')
                            ->label('State')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_pincode')
                            ->label('Pincode')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_country')
                            ->label('Country')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                ]),

            Section::make('Order Items')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->label('Items')
                        ->schema([
                            Grid::make(4)->schema([
                                TextInput::make('product_name')
                                    ->label('Product')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('variant_name')
                                    ->label('Variant')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('—'),

                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('unit_price')
                                    ->label('Unit Price (₹)')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                            Grid::make(4)->schema([
                                TextInput::make('total_price')
                                    ->label('Line Total (₹)')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('fulfillment_status')
                                    ->label('Fulfillment Status')
                                    ->options([
                                        'pending'   => 'Pending',
                                        'packed'    => 'Packed',
                                        'shipped'   => 'Shipped',
                                        'delivered' => 'Delivered',
                                        'returned'  => 'Returned',
                                    ]),

                                TextInput::make('courier_name')
                                    ->label('Courier')
                                    ->maxLength(100),

                                TextInput::make('tracking_number')
                                    ->label('Tracking No.')
                                    ->maxLength(100),
                            ]),
                        ])
                        ->deletable(false)
                        ->addable(false)
                        ->columnSpanFull(),
                ]),

            Section::make('Financials')
                ->icon('heroicon-o-currency-rupee')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('discount_amount')
                            ->label('Discount')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_amount')
                            ->label('Shipping')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('cgst_amount')
                            ->label('CGST')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('sgst_amount')
                            ->label('SGST')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('igst_amount')
                            ->label('IGST')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    TextInput::make('total_amount')
                        ->label('Grand Total')
                        ->prefix('₹')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false)
                        ->extraInputAttributes(['class' => 'font-bold text-lg']),
                ]),
        ]);
    }
}
