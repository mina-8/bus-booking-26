<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use App\Enums\RoleEnum;
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'users', column: 'email', ignoreRecord: true),
                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->required()
                    ->visibleOn('create')
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                    ->revealable()
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label('تأكيد كلمة المرور')
                    ->visibleOn('create')
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrated(false),
                Select::make('roles')
                    ->label('الصلاحيات')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn($record) => RoleEnum::tryFrom($record->name)->label()),
            ]);
    }
}
