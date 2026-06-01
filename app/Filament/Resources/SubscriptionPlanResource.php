<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Manajemen Langganan';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Paket')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Paket (cth: basic, premium)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Tidak Aktif',
                            ])
                            ->default('active')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Batasan Kuota')
                    ->schema([
                        Forms\Components\TextInput::make('max_schools')
                            ->label('Maksimal Sekolah')
                            ->numeric()
                            ->default(1),
                        Forms\Components\TextInput::make('max_students_per_school')
                            ->label('Maksimal Siswa per Sekolah')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('max_teachers_per_school')
                            ->label('Maksimal Guru per Sekolah')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('max_classes_per_school')
                            ->label('Maksimal Kelas per Sekolah')
                            ->numeric()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Fitur Tambahan')
                    ->schema([
                        Forms\Components\Toggle::make('has_qr_attendance')
                            ->label('Fitur QR Absensi')
                            ->default(false),
                        Forms\Components\Toggle::make('has_report')
                            ->label('Fitur Laporan')
                            ->default(false),
                        Forms\Components\Toggle::make('has_parent_app')
                            ->label('Aplikasi Orang Tua')
                            ->default(false),
                        Forms\Components\Toggle::make('has_api_access')
                            ->label('Akses API')
                            ->default(false),
                    ])->columns(4),

                Forms\Components\Section::make('Harga')
                    ->schema([
                        Forms\Components\TextInput::make('price_monthly')
                            ->label('Harga Bulanan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\TextInput::make('price_yearly')
                            ->label('Harga Tahunan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->badge(),
                Tables\Columns\TextColumn::make('price_monthly')
                    ->label('Bulanan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_yearly')
                    ->label('Tahunan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_qr_attendance')
                    ->label('QR Absensi')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
