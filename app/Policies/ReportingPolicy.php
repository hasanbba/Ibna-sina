<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Reporting;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Reporting');
    }

    public function view(AuthUser $authUser, Reporting $reporting): bool
    {
        return $authUser->can('View:Reporting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Reporting');
    }

    public function update(AuthUser $authUser, Reporting $reporting): bool
    {
        return $authUser->can('Update:Reporting');
    }

    public function delete(AuthUser $authUser, Reporting $reporting): bool
    {
        return $authUser->can('Delete:Reporting');
    }

    public function restore(AuthUser $authUser, Reporting $reporting): bool
    {
        return $authUser->can('Restore:Reporting');
    }

    public function forceDelete(AuthUser $authUser, Reporting $reporting): bool
    {
        return $authUser->can('ForceDelete:Reporting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Reporting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Reporting');
    }

    public function replicate(AuthUser $authUser, Reporting $reporting): bool
    {
        return $authUser->can('Replicate:Reporting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Reporting');
    }

}