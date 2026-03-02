<?php

namespace App\Enums;

enum RoleEnum : string
{
    case SUPER_ADMIN = 'super_admin';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'مشرف',
            self::USER => 'مستخدم',
        };
    }
    // public static function options(): array
    // {
    //     $out = [];
    //     foreach (self::cases() as $case) {
    //         // $out[$case->value] = $case->name;
    //         $out[$case->value] = __('filament-panels::resources/pages/admin.roles.' . $case->value);
    //     }

    //     return $out;
    // }
}
