<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Log Aktivitas';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Pelaku')
                    ->description(fn ($record) => $record->ip_address)
                    ->searchable(),
                
                
                Tables\Columns\TextColumn::make('activity_type')
                    ->label('Aktivitas')
                    ->badge() 
                    ->color(fn (string $state): string => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        'login' => 'info',
                        default => 'gray',
                    }),
                // ------------------------------
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->wrap()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type')
                    ->options([
                        'create' => 'Pembuatan Data',
                        'update' => 'Pembaruan Data',
                        'delete' => 'Penghapusan Data',
                        'login' => 'Login Sistem',
                    ])
                    ->label('Jenis Aktivitas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['old_data'] = json_encode(json_decode($data['old_data'] ?? '{}'), JSON_PRETTY_PRINT);
                        $data['new_data'] = json_encode(json_decode($data['new_data'] ?? '{}'), JSON_PRETTY_PRINT);
                        return $data;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}