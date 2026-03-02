<?php

namespace App\Filament\Resources\Discounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DiscountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed',
                    }),

                TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(fn ($record) => $record->type === 'percentage'
                        ? $record->value . '%'
                        : number_format($record->value, 2) . ' EGP')
                    ->sortable(),

                TextColumn::make('usage_stats')
                    ->label('Usage')
                    ->formatStateUsing(fn ($record) => $record->usage_limit
                        ? "{$record->used_count} / {$record->usage_limit}"
                        : "{$record->used_count}")
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('used_count', $direction);
                    }),

                TextColumn::make('starts_at')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('Expiry Date')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($state) => $state && now()->isAfter($state) ? 'danger' : null),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),

                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now()))
                    ->label('Expired'),

                Filter::make('active_now')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('starts_at')
                                ->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                        })
                    )
                    ->label('Currently Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
