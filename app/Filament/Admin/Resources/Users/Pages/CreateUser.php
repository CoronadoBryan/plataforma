<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public ?string $roleToAssign = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleToAssign = $data['role'] ?? null;

        // Evita que Eloquent intente llenar un atributo que no existe en la tabla.
        unset($data['role']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! filled($this->roleToAssign) || ! $this->record) {
            return;
        }

        // Spatie\Permission: asigna la(s) rol(es) por nombre.
        $this->record->syncRoles([$this->roleToAssign]);
    }
}
