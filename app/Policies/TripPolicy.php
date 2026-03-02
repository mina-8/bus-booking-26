<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Trip;
use Illuminate\Auth\Access\HandlesAuthorization;

class TripPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_trip');
    }

    public function view(AuthUser $authUser, Trip $trip): bool
    {
        return $authUser->can('view_trip');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_trip');
    }

    public function update(AuthUser $authUser, Trip $trip): bool
    {
        return $authUser->can('update_trip');
    }

    public function delete(AuthUser $authUser, Trip $trip): bool
    {
        return $authUser->can('delete_trip');
    }

    public function restore(AuthUser $authUser, Trip $trip): bool
    {
        return $authUser->can('restore_trip');
    }

    public function forceDelete(AuthUser $authUser, Trip $trip): bool
    {
        return $authUser->can('force_delete_trip');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_trip');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_trip');
    }

    public function replicate(AuthUser $authUser, Trip $trip): bool
    {
        return $authUser->can('replicate_trip');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_trip');
    }

}