<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Investigation;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvestigationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Investigation');
    }

    public function view(AuthUser $authUser, Investigation $investigation): bool
    {
        return $authUser->can('View:Investigation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Investigation');
    }

    public function update(AuthUser $authUser, Investigation $investigation): bool
    {
        return $authUser->can('Update:Investigation');
    }

    public function delete(AuthUser $authUser, Investigation $investigation): bool
    {
        return $authUser->can('Delete:Investigation');
    }

    public function restore(AuthUser $authUser, Investigation $investigation): bool
    {
        return $authUser->can('Restore:Investigation');
    }

    public function forceDelete(AuthUser $authUser, Investigation $investigation): bool
    {
        return $authUser->can('ForceDelete:Investigation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Investigation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Investigation');
    }

    public function replicate(AuthUser $authUser, Investigation $investigation): bool
    {
        return $authUser->can('Replicate:Investigation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Investigation');
    }

}