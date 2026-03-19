<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Consultant;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsultantPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Consultant');
    }

    public function view(AuthUser $authUser, Consultant $consultant): bool
    {
        return $authUser->can('View:Consultant');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Consultant');
    }

    public function update(AuthUser $authUser, Consultant $consultant): bool
    {
        return $authUser->can('Update:Consultant');
    }

    public function delete(AuthUser $authUser, Consultant $consultant): bool
    {
        return $authUser->can('Delete:Consultant');
    }

    public function restore(AuthUser $authUser, Consultant $consultant): bool
    {
        return $authUser->can('Restore:Consultant');
    }

    public function forceDelete(AuthUser $authUser, Consultant $consultant): bool
    {
        return $authUser->can('ForceDelete:Consultant');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Consultant');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Consultant');
    }

    public function replicate(AuthUser $authUser, Consultant $consultant): bool
    {
        return $authUser->can('Replicate:Consultant');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Consultant');
    }

}