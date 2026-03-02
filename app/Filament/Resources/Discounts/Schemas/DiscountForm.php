<?php

namespace App\Filament\Resources\Discounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Discount Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('كود الخصم الذي سيستخدمه العملاء'),

                Textarea::make('description')
                    ->label('Description')
                    ->maxLength(500)
                    ->rows(3)
                    ->helperText('وصف مختصر للخصم'),

                Select::make('type')
                    ->label('Discount Type')
                    ->options([
                        'percentage' => 'Percentage (%)',
                        'fixed' => 'Fixed Amount',
                    ])
                    ->default('percentage')
                    ->required()
                    ->live()
                    ->helperText('نوع الخصم: نسبة مئوية أو مبلغ ثابت'),

                TextInput::make('value')
                    ->label('Discount Value')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : 'EGP')
                    ->helperText(fn ($get) => $get('type') === 'percentage'
                        ? 'قيمة الخصم بالنسبة المئوية (مثال: 10 لـ 10%)'
                        : 'قيمة الخصم بالجنيه المصري'),

                TextInput::make('min_amount')
                    ->label('Minimum Amount')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('EGP')
                    ->helperText('الحد الأدنى للمبلغ لتطبيق الخصم (اختياري)'),

                TextInput::make('max_discount')
                    ->label('Maximum Discount')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('EGP')
                    ->visible(fn ($get) => $get('type') === 'percentage')
                    ->helperText('الحد الأقصى للخصم (للنسبة المئوية فقط)'),

                TextInput::make('usage_limit')
                    ->label('Usage Limit')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('عدد مرات الاستخدام المسموح (اختياري)'),

                DateTimePicker::make('starts_at')
                    ->label('Start Date')
                    ->helperText('تاريخ بداية تفعيل الخصم (اختياري)'),

                DateTimePicker::make('expires_at')
                    ->label('Expiry Date')
                    ->after('starts_at')
                    ->helperText('تاريخ انتهاء الخصم (اختياري)'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('تفعيل أو إيقاف الخصم'),
            ]);
    }
}
