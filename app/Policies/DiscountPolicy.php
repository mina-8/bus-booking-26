<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Discount;
use Illuminate\Auth\Access\HandlesAuthorization;

class DiscountPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_discount');
    }

    public function view(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('view_discount');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_discount');
    }

    public function update(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('update_discount');
    }

    public function delete(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('delete_discount');
    }

    public function restore(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('restore_discount');
    }

    public function forceDelete(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('force_delete_discount');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_discount');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_discount');
    }

    public function replicate(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('replicate_discount');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_discount');
    }

}