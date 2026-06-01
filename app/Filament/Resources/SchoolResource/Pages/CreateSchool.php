<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateSchool extends CreateRecord
{
    protected static string $resource = SchoolResource::class;

    protected function afterCreate(): void
    {
        $school = $this->record;

        try {
            $adminEmail = $school->email ?: 'admin.' . Str::slug($school->name) . '@' . Str::slug($school->name) . '.com';
            $defaultPassword = 'password123';

            // Buat user admin sekolah
            $adminUser = User::create([
                'name' => 'Admin ' . $school->name,
                'email' => $adminEmail,
                'password' => Hash::make($defaultPassword),
                'school_id' => $school->id,
                'status' => 'active',
            ]);

            // Assign role admin
            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => 'web'
            ]);
            $adminUser->assignRole($adminRole);



            \Filament\Notifications\Notification::make()
                ->title('✅ Admin sekolah berhasil dibuat!')
                ->body("Email: {$adminEmail}\nPassword: {$defaultPassword}\nRole: admin")
                ->success()
                ->persistent()
                ->send();

        } catch (\Exception $e) {
            Log::error('Gagal membuat admin sekolah: ' . $e->getMessage());

            \Filament\Notifications\Notification::make()
                ->title('⚠️ Gagal membuat admin sekolah')
                ->body('Sekolah berhasil dibuat, tetapi admin sekolah gagal dibuat secara otomatis. Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}