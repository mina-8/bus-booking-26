<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\City;
use Illuminate\Auth\Access\HandlesAuthorization;

class CityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_city');
    }

    public function view(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('view_city');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_city');
    }

    public function update(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('update_city');
    }

    public function delete(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('delete_city');
    }

    public function restore(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('restore_city');
    }

    public function forceDelete(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('force_delete_city');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_city');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_city');
    }

    public function replicate(AuthUser $authUser, City $city): bool
    {
        return $authUser->can('replicate_city');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_city');
    }

}