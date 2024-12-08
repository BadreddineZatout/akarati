<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()->hasRole('promoteur')) {
            $data['promoter_id'] = auth()->id();
        }
        if (auth()->user()->hasRole('chef chantier')) {
            $data['chef_id'] = auth()->id();
        }

        return $data;
    }
}
