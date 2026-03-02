<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ScheduleWork;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduleWorkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_schedule_work');
    }

    public function view(AuthUser $authUser, ScheduleWork $scheduleWork): bool
    {
        return $authUser->can('view_schedule_work');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_schedule_work');
    }

    public function update(AuthUser $authUser, ScheduleWork $scheduleWork): bool
    {
        return $authUser->can('update_schedule_work');
    }

    public function delete(AuthUser $authUser, ScheduleWork $scheduleWork): bool
    {
        return $authUser->can('delete_schedule_work');
    }

    public function restore(AuthUser $authUser, ScheduleWork $scheduleWork): bool
    {
        return $authUser->can('restore_schedule_work');
    }

    public function forceDelete(AuthUser $authUser, ScheduleWork $scheduleWork): bool
    {
        return $authUser->can('force_delete_schedule_work');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_schedule_work');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_schedule_work');
    }

    public function replicate(AuthUser $authUser, ScheduleWork $scheduleWork): bool
    {
        return $authUser->can('replicate_schedule_work');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_schedule_work');
    }

}