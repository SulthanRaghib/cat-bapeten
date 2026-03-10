<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamParticipant;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamParticipantPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamParticipant');
    }

    public function view(AuthUser $authUser, ExamParticipant $examParticipant): bool
    {
        return $authUser->can('View:ExamParticipant');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamParticipant');
    }

    public function update(AuthUser $authUser, ExamParticipant $examParticipant): bool
    {
        return $authUser->can('Update:ExamParticipant');
    }

    public function delete(AuthUser $authUser, ExamParticipant $examParticipant): bool
    {
        return $authUser->can('Delete:ExamParticipant');
    }

    public function restore(AuthUser $authUser, ExamParticipant $examParticipant): bool
    {
        return $authUser->can('Restore:ExamParticipant');
    }

    public function forceDelete(AuthUser $authUser, ExamParticipant $examParticipant): bool
    {
        return $authUser->can('ForceDelete:ExamParticipant');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExamParticipant');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExamParticipant');
    }

    public function replicate(AuthUser $authUser, ExamParticipant $examParticipant): bool
    {
        return $authUser->can('Replicate:ExamParticipant');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExamParticipant');
    }

}