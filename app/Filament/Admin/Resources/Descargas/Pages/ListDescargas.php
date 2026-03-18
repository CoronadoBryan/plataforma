<?php

namespace App\Filament\Admin\Resources\Descargas\Pages;

use App\Filament\Admin\Resources\Descargas\DescargaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDescargas extends ListRecords
{
    protected static string $resource = DescargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
