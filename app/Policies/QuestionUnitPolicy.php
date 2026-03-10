<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\QuestionUnit;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionUnitPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:QuestionUnit');
    }

    public function view(AuthUser $authUser, QuestionUnit $questionUnit): bool
    {
        return $authUser->can('View:QuestionUnit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:QuestionUnit');
    }

    public function update(AuthUser $authUser, QuestionUnit $questionUnit): bool
    {
        return $authUser->can('Update:QuestionUnit');
    }

    public function delete(AuthUser $authUser, QuestionUnit $questionUnit): bool
    {
        return $authUser->can('Delete:QuestionUnit');
    }

    public function restore(AuthUser $authUser, QuestionUnit $questionUnit): bool
    {
        return $authUser->can('Restore:QuestionUnit');
    }

    public function forceDelete(AuthUser $authUser, QuestionUnit $questionUnit): bool
    {
        return $authUser->can('ForceDelete:QuestionUnit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:QuestionUnit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:QuestionUnit');
    }

    public function replicate(AuthUser $authUser, QuestionUnit $questionUnit): bool
    {
        return $authUser->can('Replicate:QuestionUnit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QuestionUnit');
    }

}