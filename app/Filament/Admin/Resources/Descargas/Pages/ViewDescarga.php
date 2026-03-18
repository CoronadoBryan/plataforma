<?php

namespace App\Filament\Admin\Resources\Descargas\Pages;

use App\Filament\Admin\Resources\Descargas\DescargaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDescarga extends ViewRecord
{
    protected static string $resource = DescargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
