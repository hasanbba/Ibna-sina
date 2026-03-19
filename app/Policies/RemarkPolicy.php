<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Remark;
use Illuminate\Auth\Access\HandlesAuthorization;

class RemarkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Remark');
    }

    public function view(AuthUser $authUser, Remark $remark): bool
    {
        return $authUser->can('View:Remark');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Remark');
    }

    public function update(AuthUser $authUser, Remark $remark): bool
    {
        return $authUser->can('Update:Remark');
    }

    public function delete(AuthUser $authUser, Remark $remark): bool
    {
        return $authUser->can('Delete:Remark');
    }

    public function restore(AuthUser $authUser, Remark $remark): bool
    {
        return $authUser->can('Restore:Remark');
    }

    public function forceDelete(AuthUser $authUser, Remark $remark): bool
    {
        return $authUser->can('ForceDelete:Remark');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Remark');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Remark');
    }

    public function replicate(AuthUser $authUser, Remark $remark): bool
    {
        return $authUser->can('Replicate:Remark');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Remark');
    }

}