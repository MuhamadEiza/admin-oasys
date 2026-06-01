<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationProgressResource\Pages; // <-- Bagian ini yang direvisi
use App\Models\DonationProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DonationProgressResource extends Resource
{
    protected static ?string $model = DonationProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Progres Donasi';
    protected static ?string $modelLabel = 'Progres Donasi';
    protected static ?string $pluralModelLabel = 'Progres Donasi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pendanaan')
                    ->description('Perbarui nominal donasi yang akan ditampilkan di halaman depan.')
                    ->schema([
                        Forms\Components\TextInput::make('current_amount')
                            ->label('Nominal Terkumpul')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('target_amount')
                            ->label('Target Donasi')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('current_amount')
                    ->label('Terkumpul')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Target')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Persentase')
                    ->getStateUsing(function (DonationProgress $record): string {
                        if ($record->target_amount > 0) {
                            $percentage = ($record->current_amount / $record->target_amount) * 100;
                            return round($percentage, 1) . '%';
                        }
                        return '0%';
                    })
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]); // Dikosongkan agar admin tidak menghapus data secara massal
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDonationProgress::route('/'),
        ];
    }
    
    // Membatasi agar Admin hanya bisa mengedit, tidak bisa menambah baris baru jika sudah ada 1 data
    public static function canCreate(): bool
    {
        return DonationProgress::query()->count('id') === 0;
    }
}