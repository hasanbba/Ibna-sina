<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InOut;
use Illuminate\Auth\Access\HandlesAuthorization;

class InOutPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InOut');
    }

    public function view(AuthUser $authUser, InOut $inOut): bool
    {
        return $authUser->can('View:InOut');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InOut');
    }

    public function update(AuthUser $authUser, InOut $inOut): bool
    {
        return $authUser->can('Update:InOut');
    }

    public function delete(AuthUser $authUser, InOut $inOut): bool
    {
        return $authUser->can('Delete:InOut');
    }

    public function restore(AuthUser $authUser, InOut $inOut): bool
    {
        return $authUser->can('Restore:InOut');
    }

    public function forceDelete(AuthUser $authUser, InOut $inOut): bool
    {
        return $authUser->can('ForceDelete:InOut');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InOut');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InOut');
    }

    public function replicate(AuthUser $authUser, InOut $inOut): bool
    {
        return $authUser->can('Replicate:InOut');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InOut');
    }

}