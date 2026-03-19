<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public ?string $roleToAssign = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Prellenar el campo 'role' con la(s) rol(es) actuales del usuario.
        $record = $this->getRecord();

        $roles = method_exists($record, 'getRoleNames') ? $record->getRoleNames() : collect();

        $data['role'] = $roles[0] ?? null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleToAssign = $data['role'] ?? null;

        // Evita que el form intente guardar una columna que no existe.
        unset($data['role']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (! filled($this->roleToAssign)) {
            return;
        }

        $this->getRecord()->syncRoles([$this->roleToAssign]);
    }
}
