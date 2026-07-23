<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Redberry\PageBuilderPlugin\Models\GlobalBlockConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class GlobalBlockConfigPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GlobalBlockConfig');
    }

    public function view(AuthUser $authUser, GlobalBlockConfig $globalBlockConfig): bool
    {
        return $authUser->can('View:GlobalBlockConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GlobalBlockConfig');
    }

    public function update(AuthUser $authUser, GlobalBlockConfig $globalBlockConfig): bool
    {
        return $authUser->can('Update:GlobalBlockConfig');
    }

    public function delete(AuthUser $authUser, GlobalBlockConfig $globalBlockConfig): bool
    {
        return $authUser->can('Delete:GlobalBlockConfig');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GlobalBlockConfig');
    }

    public function restore(AuthUser $authUser, GlobalBlockConfig $globalBlockConfig): bool
    {
        return $authUser->can('Restore:GlobalBlockConfig');
    }

    public function forceDelete(AuthUser $authUser, GlobalBlockConfig $globalBlockConfig): bool
    {
        return $authUser->can('ForceDelete:GlobalBlockConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GlobalBlockConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GlobalBlockConfig');
    }

    public function replicate(AuthUser $authUser, GlobalBlockConfig $globalBlockConfig): bool
    {
        return $authUser->can('Replicate:GlobalBlockConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GlobalBlockConfig');
    }

}