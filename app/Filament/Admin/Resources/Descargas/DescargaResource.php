<?php

namespace App\Filament\Admin\Resources\Descargas;

use App\Filament\Admin\Resources\Descargas\Pages\CreateDescarga;
use App\Filament\Admin\Resources\Descargas\Pages\EditDescarga;
use App\Filament\Admin\Resources\Descargas\Pages\ListDescargas;
use App\Filament\Admin\Resources\Descargas\Pages\ViewDescarga;
use App\Filament\Admin\Resources\Descargas\Schemas\DescargaForm;
use App\Filament\Admin\Resources\Descargas\Schemas\DescargaInfolist;
use App\Filament\Admin\Resources\Descargas\Tables\DescargasTable;
use App\Models\Descarga;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DescargaResource extends Resource
{
    protected static ?string $model = Descarga::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DescargaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DescargaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DescargasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDescargas::route('/'),
            'create' => CreateDescarga::route('/create'),
            'view' => ViewDescarga::route('/{record}'),
            'edit' => EditDescarga::route('/{record}/edit'),
        ];
    }
}
