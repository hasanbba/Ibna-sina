<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MonthlyReportSummary;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonthlyReportSummaryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MonthlyReportSummary');
    }

    public function view(AuthUser $authUser, MonthlyReportSummary $monthlyReportSummary): bool
    {
        return $authUser->can('View:MonthlyReportSummary');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MonthlyReportSummary');
    }

    public function update(AuthUser $authUser, MonthlyReportSummary $monthlyReportSummary): bool
    {
        return $authUser->can('Update:MonthlyReportSummary');
    }

    public function delete(AuthUser $authUser, MonthlyReportSummary $monthlyReportSummary): bool
    {
        return $authUser->can('Delete:MonthlyReportSummary');
    }

    public function restore(AuthUser $authUser, MonthlyReportSummary $monthlyReportSummary): bool
    {
        return $authUser->can('Restore:MonthlyReportSummary');
    }

    public function forceDelete(AuthUser $authUser, MonthlyReportSummary $monthlyReportSummary): bool
    {
        return $authUser->can('ForceDelete:MonthlyReportSummary');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MonthlyReportSummary');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MonthlyReportSummary');
    }

    public function replicate(AuthUser $authUser, MonthlyReportSummary $monthlyReportSummary): bool
    {
        return $authUser->can('Replicate:MonthlyReportSummary');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MonthlyReportSummary');
    }

}