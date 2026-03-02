<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Bus;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_bus');
    }

    public function view(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('view_bus');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_bus');
    }

    public function update(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('update_bus');
    }

    public function delete(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('delete_bus');
    }

    public function restore(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('restore_bus');
    }

    public function forceDelete(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('force_delete_bus');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_bus');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_bus');
    }

    public function replicate(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('replicate_bus');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_bus');
    }

}