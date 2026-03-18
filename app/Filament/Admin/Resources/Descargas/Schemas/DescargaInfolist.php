<?php

namespace App\Filament\Admin\Resources\Descargas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DescargaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Usuario'),
                TextEntry::make('archivo')
                    ->label('Archivo'),
                TextEntry::make('url')
                    ->label('URL')
                    ->placeholder('-'),
                TextEntry::make('estado')
                    ->label('Estado'),
                TextEntry::make('archivo_local')
                    ->label('Archivo local')
                    ->placeholder('-'),
                TextEntry::make('error_detalle')
                    ->label('Error')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Fecha de descarga')
                    ->dateTime(),
            ]);
    }
}
