<?php

namespace App\Filament\Resources\DonationProgressResource\Pages;

use App\Filament\Resources\DonationProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDonationProgress extends ManageRecords
{
    protected static string $resource = DonationProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
