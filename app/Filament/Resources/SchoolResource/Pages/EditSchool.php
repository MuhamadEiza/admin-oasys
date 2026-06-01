<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class EditSchool extends EditRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $school = $this->record;

        try {

            $adminUser = User::where('school_id', $school->id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'admin');
                })
                ->first();

            if ($adminUser) {

                if (!$adminUser->hasRole('admin')) {
                    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
                    $adminUser->assignRole($adminRole);
                }


                $newAdminEmail = $school->email ?: 'admin.' . str()->slug($school->name) . '@' . str()->slug($school->name) . '.com';

                if ($adminUser->email !== $newAdminEmail) {
                    $adminUser->update([
                        'email' => $newAdminEmail,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('📧 Email admin diperbarui')
                        ->body("Email admin sekolah diubah menjadi: {$newAdminEmail}")
                        ->info()
                        ->send();
                }


                $newAdminName = 'Admin ' . $school->name;
                if ($adminUser->name !== $newAdminName) {
                    $adminUser->update([
                        'name' => $newAdminName,
                    ]);
                }
            } else {

                $adminEmail = $school->email ?: 'admin.' . str()->slug($school->name) . '@' . str()->slug($school->name) . '.com';
                $defaultPassword = 'password123';

                $newAdmin = User::create([
                    'name' => 'Admin ' . $school->name,
                    'email' => $adminEmail,
                    'password' => bcrypt($defaultPassword),
                    'school_id' => $school->id,
                    'status' => 'active',
                ]);

                $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
                $newAdmin->assignRole($adminRole);

                \Filament\Notifications\Notification::make()
                    ->title('✅ Admin sekolah baru dibuat!')
                    ->body("Email: {$adminEmail}\nPassword: {$defaultPassword}")
                    ->success()
                    ->send();
            }

        } catch (\Exception $e) {
            Log::error('Gagal update admin sekolah: ' . $e->getMessage());

            \Filament\Notifications\Notification::make()
                ->title('⚠️ Gagal update admin sekolah')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Sekolah berhasil diperbarui')
            ->body('Data sekolah telah berhasil diupdate.')
            ->send();
    }
}