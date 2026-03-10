<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamPackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamPackagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamPackage');
    }

    public function view(AuthUser $authUser, ExamPackage $examPackage): bool
    {
        return $authUser->can('View:ExamPackage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamPackage');
    }

    public function update(AuthUser $authUser, ExamPackage $examPackage): bool
    {
        return $authUser->can('Update:ExamPackage');
    }

    public function delete(AuthUser $authUser, ExamPackage $examPackage): bool
    {
        return $authUser->can('Delete:ExamPackage');
    }

    public function restore(AuthUser $authUser, ExamPackage $examPackage): bool
    {
        return $authUser->can('Restore:ExamPackage');
    }

    public function forceDelete(AuthUser $authUser, ExamPackage $examPackage): bool
    {
        return $authUser->can('ForceDelete:ExamPackage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExamPackage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExamPackage');
    }

    public function replicate(AuthUser $authUser, ExamPackage $examPackage): bool
    {
        return $authUser->can('Replicate:ExamPackage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExamPackage');
    }

}