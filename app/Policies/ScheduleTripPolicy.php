<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ScheduleTrip;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduleTripPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_schedule_trip');
    }

    public function view(AuthUser $authUser, ScheduleTrip $scheduleTrip): bool
    {
        return $authUser->can('view_schedule_trip');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_schedule_trip');
    }

    public function update(AuthUser $authUser, ScheduleTrip $scheduleTrip): bool
    {
        return $authUser->can('update_schedule_trip');
    }

    public function delete(AuthUser $authUser, ScheduleTrip $scheduleTrip): bool
    {
        return $authUser->can('delete_schedule_trip');
    }

    public function restore(AuthUser $authUser, ScheduleTrip $scheduleTrip): bool
    {
        return $authUser->can('restore_schedule_trip');
    }

    public function forceDelete(AuthUser $authUser, ScheduleTrip $scheduleTrip): bool
    {
        return $authUser->can('force_delete_schedule_trip');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_schedule_trip');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_schedule_trip');
    }

    public function replicate(AuthUser $authUser, ScheduleTrip $scheduleTrip): bool
    {
        return $authUser->can('replicate_schedule_trip');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_schedule_trip');
    }

}