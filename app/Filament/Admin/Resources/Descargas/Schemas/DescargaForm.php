<?php

namespace App\Filament\Admin\Resources\Descargas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DescargaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('archivo')
                    ->label('Archivo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->maxLength(255),
                Select::make('estado')
                    ->options([
                        'pendiente' => 'pendiente',
                        'procesando' => 'procesando',
                        'completado' => 'completado',
                        'requiere_verificacion' => 'requiere_verificacion',
                        'error' => 'error',
                    ])
                    ->required()
                    ->default('pendiente'),
            ]);
    }
}
