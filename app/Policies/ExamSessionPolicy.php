<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamSession;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamSessionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamSession');
    }

    public function view(AuthUser $authUser, ExamSession $examSession): bool
    {
        return $authUser->can('View:ExamSession');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamSession');
    }

    public function update(AuthUser $authUser, ExamSession $examSession): bool
    {
        return $authUser->can('Update:ExamSession');
    }

    public function delete(AuthUser $authUser, ExamSession $examSession): bool
    {
        return $authUser->can('Delete:ExamSession');
    }

    public function restore(AuthUser $authUser, ExamSession $examSession): bool
    {
        return $authUser->can('Restore:ExamSession');
    }

    public function forceDelete(AuthUser $authUser, ExamSession $examSession): bool
    {
        return $authUser->can('ForceDelete:ExamSession');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExamSession');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExamSession');
    }

    public function replicate(AuthUser $authUser, ExamSession $examSession): bool
    {
        return $authUser->can('Replicate:ExamSession');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExamSession');
    }

}