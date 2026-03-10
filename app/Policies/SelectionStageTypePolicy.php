<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SelectionStageType;
use Illuminate\Auth\Access\HandlesAuthorization;

class SelectionStageTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SelectionStageType');
    }

    public function view(AuthUser $authUser, SelectionStageType $selectionStageType): bool
    {
        return $authUser->can('View:SelectionStageType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SelectionStageType');
    }

    public function update(AuthUser $authUser, SelectionStageType $selectionStageType): bool
    {
        return $authUser->can('Update:SelectionStageType');
    }

    public function delete(AuthUser $authUser, SelectionStageType $selectionStageType): bool
    {
        return $authUser->can('Delete:SelectionStageType');
    }

    public function restore(AuthUser $authUser, SelectionStageType $selectionStageType): bool
    {
        return $authUser->can('Restore:SelectionStageType');
    }

    public function forceDelete(AuthUser $authUser, SelectionStageType $selectionStageType): bool
    {
        return $authUser->can('ForceDelete:SelectionStageType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SelectionStageType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SelectionStageType');
    }

    public function replicate(AuthUser $authUser, SelectionStageType $selectionStageType): bool
    {
        return $authUser->can('Replicate:SelectionStageType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SelectionStageType');
    }

}