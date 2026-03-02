<?php

namespace App\Enums;

enum PermissionsEnum
{
    public static function getUserPermissions(array $allPermissions): array
    {
        return array_values(array_filter($allPermissions, fn($perm) =>
            str_starts_with($perm, 'view_trip') ||
            str_starts_with($perm, 'create_trip') ||
            str_starts_with($perm, 'update_trip') ||
            str_starts_with($perm, 'delete_trip') ||
            str_starts_with($perm, 'view_booking') ||
            str_starts_with($perm, 'create_booking') ||
            str_starts_with($perm, 'update_booking') ||
            str_starts_with($perm, 'delete_booking')
        ));
    }
}
