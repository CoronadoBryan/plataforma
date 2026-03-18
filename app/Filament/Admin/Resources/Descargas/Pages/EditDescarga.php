<?php

namespace App\Filament\Admin\Resources\Descargas\Pages;

use App\Filament\Admin\Resources\Descargas\DescargaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDescarga extends EditRecord
{
    protected static string $resource = DescargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
