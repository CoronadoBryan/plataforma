<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Descarga;
use Illuminate\Auth\Access\HandlesAuthorization;

class DescargaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Descarga');
    }

    public function view(AuthUser $authUser, Descarga $descarga): bool
    {
        return $authUser->can('View:Descarga');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Descarga');
    }

    public function update(AuthUser $authUser, Descarga $descarga): bool
    {
        return $authUser->can('Update:Descarga');
    }

    public function delete(AuthUser $authUser, Descarga $descarga): bool
    {
        return $authUser->can('Delete:Descarga');
    }

    public function restore(AuthUser $authUser, Descarga $descarga): bool
    {
        return $authUser->can('Restore:Descarga');
    }

    public function forceDelete(AuthUser $authUser, Descarga $descarga): bool
    {
        return $authUser->can('ForceDelete:Descarga');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Descarga');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Descarga');
    }

    public function replicate(AuthUser $authUser, Descarga $descarga): bool
    {
        return $authUser->can('Replicate:Descarga');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Descarga');
    }

}