<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;
use App\Enums\RoleEnum;
use Spatie\Permission\Models\Permission;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // جلب كل الصلاحيات من قاعدة البيانات (التي تم إنشاؤها بواسطة shield:generate)
        $allPermissions = Permission::where('guard_name', 'web')->pluck('name')->toArray();

        if (empty($allPermissions)) {
            $this->command->warn('⚠️  No permissions found! Run: php artisan shield:generate --all --panel=admin');
            return;
        }

        $rolesWithPermissions = [];
        foreach (RoleEnum::cases() as $role) {
            $permissions = $this->getPermissionsForRole($role, $allPermissions);
            $rolesWithPermissions[] = [
                'name' => $role->value,
                'guard_name' => 'web',
                'permissions' => $permissions,
            ];
        }

        static::makeRolesWithPermissions($rolesWithPermissions);

        $this->command->info('✅ Shield Seeding Completed.');
        $this->command->info('📊 Total Permissions: ' . count($allPermissions));
        foreach (RoleEnum::cases() as $role) {
            $count = count($this->getPermissionsForRole($role, $allPermissions));
            $this->command->info("   - {$role->value}: {$count} permissions");
        }
    }

    protected static function makeRolesWithPermissions(array $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = $rolesWithPermissions)) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    protected function getPermissionsForRole(RoleEnum $role, array $allPermissions): array
    {
        return match($role) {
            // Super Admin يحصل على كل الصلاحيات تلقائياً
            RoleEnum::SUPER_ADMIN => $allPermissions,


            // CRM يحصل فقط على الصلاحيات المحددة في PermissionsEnum
            RoleEnum::USER => PermissionsEnum::getUserPermissions($allPermissions),
        };
    }
}
