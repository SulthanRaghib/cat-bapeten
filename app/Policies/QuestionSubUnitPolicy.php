<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\QuestionSubUnit;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionSubUnitPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:QuestionSubUnit');
    }

    public function view(AuthUser $authUser, QuestionSubUnit $questionSubUnit): bool
    {
        return $authUser->can('View:QuestionSubUnit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:QuestionSubUnit');
    }

    public function update(AuthUser $authUser, QuestionSubUnit $questionSubUnit): bool
    {
        return $authUser->can('Update:QuestionSubUnit');
    }

    public function delete(AuthUser $authUser, QuestionSubUnit $questionSubUnit): bool
    {
        return $authUser->can('Delete:QuestionSubUnit');
    }

    public function restore(AuthUser $authUser, QuestionSubUnit $questionSubUnit): bool
    {
        return $authUser->can('Restore:QuestionSubUnit');
    }

    public function forceDelete(AuthUser $authUser, QuestionSubUnit $questionSubUnit): bool
    {
        return $authUser->can('ForceDelete:QuestionSubUnit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:QuestionSubUnit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:QuestionSubUnit');
    }

    public function replicate(AuthUser $authUser, QuestionSubUnit $questionSubUnit): bool
    {
        return $authUser->can('Replicate:QuestionSubUnit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QuestionSubUnit');
    }

}