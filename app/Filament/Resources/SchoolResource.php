<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Filament\Resources\SchoolResource\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\SchoolResource\RelationManagers\TeachersRelationManager;
use App\Filament\Resources\SchoolResource\Widgets\SchoolAttendanceChart;
use Illuminate\Support\Str;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Manajemen Sekolah';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Tabs::make('Tabs')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Informasi Dasar')
                                    ->icon('heroicon-m-building-library')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Sekolah')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('npsn')
                                                    ->label('NPSN')
                                                    ->maxLength(20)
                                                    ->unique(ignoreRecord: true),
                                                Forms\Components\TextInput::make('nss')
                                                    ->label('NSS')
                                                    ->maxLength(20),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('education_level')
                                                    ->label('Jenjang Pendidikan')
                                                    ->options([
                                                        'SD' => 'SD',
                                                        'SMP' => 'SMP',
                                                        'SMA' => 'SMA',
                                                        'SMK' => 'SMK',
                                                        'Mixed' => 'Campuran',
                                                    ])
                                                    ->default('Mixed'),
                                                Forms\Components\Select::make('accreditation')
                                                    ->label('Akreditasi')
                                                    ->options([
                                                        'A' => 'A (Unggul)',
                                                        'B' => 'B (Baik)',
                                                        'C' => 'C (Cukup)',
                                                        'Belum' => 'Belum Terakreditasi',
                                                    ])
                                                    ->default('Belum'),
                                                Forms\Components\TextInput::make('accreditation_year')
                                                    ->label('Tahun Akreditasi')
                                                    ->numeric()
                                                    ->minValue(1900)
                                                    ->maxValue(date('Y')),
                                            ]),
                                    ]),

                                Forms\Components\Tabs\Tab::make('Alamat & Kontak')
                                    ->icon('heroicon-m-map-pin')
                                    ->schema([
                                        Forms\Components\Textarea::make('address')
                                            ->label('Alamat Lengkap')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('city')->label('Kota'),
                                                Forms\Components\TextInput::make('province')->label('Provinsi'),
                                                Forms\Components\TextInput::make('postal_code')->label('Kode Pos'),
                                                Forms\Components\TextInput::make('phone')->label('Telepon')->tel(),
                                            ]),
                                        Forms\Components\TextInput::make('email')->label('Email Sekolah')->email(),
                                        Forms\Components\TextInput::make('website')->label('Website')->url(),
                                    ]),

                                Forms\Components\Tabs\Tab::make('Data Kepala Sekolah')
                                    ->icon('heroicon-m-user-circle')
                                    ->schema([
                                        Forms\Components\TextInput::make('principal_name')->label('Nama Kepala Sekolah'),
                                        Forms\Components\TextInput::make('principal_nip')->label('NIP Kepala Sekolah'),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('principal_phone')->label('Telepon'),
                                                Forms\Components\TextInput::make('principal_email')->label('Email'),
                                            ]),
                                    ]),
                            ]),

                        // --- BAGIAN PEMBUATAN AKUN ADMIN (HANYA MUNCUL SAAT CREATE) ---
                        Forms\Components\Section::make('Pembuatan Akun Admin Sekolah')
                            ->description('Akun ini akan digunakan oleh pengelola sekolah untuk masuk ke Oasys School.')
                            ->icon('heroicon-m-user-plus')
                            ->schema([
                                Forms\Components\TextInput::make('admin_name')
                                    ->label('Nama Lengkap Admin')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('admin_email')
                                    ->label('Alamat Email Login')
                                    ->email()
                                    ->unique('users', 'email')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('admin_password')
                                    ->label('Kata Sandi')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(false),
                            ])
                            ->visible(fn (string $operation): bool => $operation === 'create'),
                    ])
                    ->columnSpan(['lg' => 2]), 

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status Operasional')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status Sekolah')
                                    ->options([
                                        'active' => 'Aktif',
                                        'inactive' => 'Tidak Aktif',
                                        'pending' => 'Menunggu',
                                        'suspended' => 'Ditangguhkan',
                                    ])
                                    ->required()
                                    ->default('pending'),
                            ]),

                        Forms\Components\Section::make('Paket Langganan')
                            ->schema([
                                Forms\Components\Select::make('subscription_status')
                                    ->label('Status Langganan')
                                    ->options([
                                        'trial' => 'Trial',
                                        'active' => 'Aktif',
                                        'expired' => 'Kadaluarsa',
                                        'cancelled' => 'Dibatalkan',
                                    ])
                                    ->default('trial'),
                                Forms\Components\DatePicker::make('subscription_start_date')
                                    ->label('Mulai Langganan'),
                                Forms\Components\DatePicker::make('subscription_expiry_date')
                                    ->label('Kadaluarsa Langganan')
                                    ->after('subscription_start_date'),
                            ]),
                            
                        Forms\Components\Section::make('Pengaturan Kuota')
                            ->collapsed() 
                            ->schema([
                                Forms\Components\TextInput::make('max_students')->label('Maks. Siswa')->numeric()->default(500),
                                Forms\Components\TextInput::make('max_teachers')->label('Maks. Guru')->numeric()->default(50),
                                Forms\Components\TextInput::make('max_classes')->label('Maks. Kelas')->numeric()->default(30),
                            ]),

                        Forms\Components\Section::make('Visual')
                            ->schema([
                                Forms\Components\FileUpload::make('logo')
                                    ->label('Logo Sekolah')
                                    ->image()
                                    ->directory('schools/logos'),
                                Forms\Components\ColorPicker::make('theme_color')
                                    ->label('Warna Tema Utama')
                                    ->default('#8CC63F'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=random&color=fff'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Sekolah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('npsn')
                    ->label('NPSN')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Kota'),

                // --- PEMBARUAN PADA KOLOM STATUS ---
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'inactive' => 'danger',
                        'suspended' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        'pending' => 'Menunggu',
                        'suspended' => 'Ditangguhkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('subscription_status')
                    ->label('Langganan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'trial' => 'warning',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'trial' => 'Trial',
                        'expired' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('subscription_expiry_date')
                    ->label('Kadaluarsa')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        'pending' => 'Menunggu',
                        'suspended' => 'Ditangguhkan',
                    ]),
                Tables\Filters\SelectFilter::make('subscription_status')
                    ->options([
                        'active' => 'Aktif',
                        'trial' => 'Trial',
                        'expired' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Akan Kadaluarsa (30 hari)')
                    ->query(fn($query) => $query->where('subscription_expiry_date', '<=', now()->addDays(30))
                        ->where('subscription_expiry_date', '>', now())
                        ->where('status', 'active')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('activate')
                        ->label('Aktifkan')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(fn($record) => $record->update(['status' => 'active']))
                        ->visible(fn($record) => $record->status !== 'active'),
                    Tables\Actions\Action::make('suspend')
                        ->label('Tangguhkan')
                        ->color('danger')
                        ->icon('heroicon-o-no-symbol')
                        ->requiresConfirmation()
                        ->action(fn($record) => $record->update(['status' => 'suspended']))
                        ->visible(fn($record) => $record->status === 'active'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Menu Detail Sekolah')
                    ->tabs([
                        // 1. Tab Grafik Kehadiran Siswa
                        Infolists\Components\Tabs\Tab::make('Grafik Kehadiran Siswa')
                            ->icon('heroicon-m-chart-bar')
                            ->schema([
                                Infolists\Components\Livewire::make(SchoolAttendanceChart::class)
                                    ->columnSpanFull(),
                            ]),

                        // 2. Tab Informasi Dasar
                        Infolists\Components\Tabs\Tab::make('Informasi Dasar')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Infolists\Components\ImageEntry::make('logo')
                                    ->label('Logo')
                                    ->circular()
                                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=8CC63F&color=fff'),
                                Infolists\Components\TextEntry::make('name')->label('Nama Sekolah')->weight('bold'),
                                Infolists\Components\TextEntry::make('npsn')->label('NPSN'),
                                Infolists\Components\TextEntry::make('nss')->label('NSS'),
                                Infolists\Components\TextEntry::make('education_level')->label('Jenjang Pendidikan'),
                                Infolists\Components\TextEntry::make('accreditation')->label('Akreditasi'),
                                Infolists\Components\TextEntry::make('accreditation_year')->label('Tahun Akreditasi'),
                            ])->columns(2),

                        // 3. Tab Alamat & Kontak
                        Infolists\Components\Tabs\Tab::make('Alamat & Kontak')
                            ->icon('heroicon-m-map-pin')
                            ->schema([
                                Infolists\Components\TextEntry::make('address')->label('Alamat')->columnSpanFull(),
                                Infolists\Components\TextEntry::make('city')->label('Kota'),
                                Infolists\Components\TextEntry::make('province')->label('Provinsi'),
                                Infolists\Components\TextEntry::make('postal_code')->label('Kode Pos'),
                                Infolists\Components\TextEntry::make('phone')->label('Telepon'),
                                Infolists\Components\TextEntry::make('email')->label('Email'),
                                Infolists\Components\TextEntry::make('website')->label('Website')->url(fn ($record) => $record->website)->openUrlInNewTab(),
                            ])->columns(2),

                        // 4. Tab Data Kepala Sekolah
                        Infolists\Components\Tabs\Tab::make('Data Kepala Sekolah')
                            ->icon('heroicon-m-user-circle')
                            ->schema([
                                Infolists\Components\TextEntry::make('principal_name')->label('Nama Kepala Sekolah'),
                                Infolists\Components\TextEntry::make('principal_nip')->label('NIP Kepala Sekolah'),
                                Infolists\Components\TextEntry::make('principal_phone')->label('Telepon'),
                                Infolists\Components\TextEntry::make('principal_email')->label('Email'),
                            ])->columns(2),

                        // 5. Tab Status & Langganan
                        Infolists\Components\Tabs\Tab::make('Status Langganan')
                            ->icon('heroicon-m-check-badge')
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status Sekolah')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'pending' => 'warning',
                                        'inactive' => 'danger',
                                        'suspended' => 'gray',
                                        default => 'primary',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match($state) {
                                        'active' => 'Aktif',
                                        'inactive' => 'Tidak Aktif',
                                        'pending' => 'Menunggu',
                                        'suspended' => 'Ditangguhkan',
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('subscription_status')
                                    ->label('Status Langganan')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'trial' => 'warning',
                                        'expired' => 'danger',
                                        'cancelled' => 'gray',
                                        default => 'primary',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match($state) {
                                        'active' => 'Aktif',
                                        'trial' => 'Trial',
                                        'expired' => 'Kadaluarsa',
                                        'cancelled' => 'Dibatalkan',
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('subscription_start_date')->label('Mulai Langganan')->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('subscription_expiry_date')->label('Kadaluarsa')->date('d/m/Y'),
                                Infolists\Components\ColorEntry::make('theme_color')->label('Warna Tema'),
                            ])->columns(2),

                        // 6. Tab Statistik
                        Infolists\Components\Tabs\Tab::make('Statistik')
                            ->icon('heroicon-m-presentation-chart-line')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('students_count')
                                            ->label('Total Siswa')
                                            ->state(fn($record) => $record->students()->count())
                                            ->color('success')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        Infolists\Components\TextEntry::make('teachers_count')
                                            ->label('Total Guru')
                                            ->state(fn($record) => $record->teachers()->count())
                                            ->color('warning')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        Infolists\Components\TextEntry::make('classes_count')
                                            ->label('Total Kelas')
                                            ->state(fn($record) => $record->classes()->count())
                                            ->color('info')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        Infolists\Components\TextEntry::make('max_students')->label('Kuota Maksimal Siswa')->numeric(),
                                        Infolists\Components\TextEntry::make('max_teachers')->label('Kuota Maksimal Guru')->numeric(),
                                        Infolists\Components\TextEntry::make('max_classes')->label('Kuota Maksimal Kelas')->numeric(),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'view' => Pages\ViewSchool::route('/{record}'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }


    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\SchoolResource\RelationManagers\StudentsRelationManager::class,
            \App\Filament\Resources\SchoolResource\RelationManagers\TeachersRelationManager::class,
        ];
    }
}