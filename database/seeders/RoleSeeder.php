<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    public const ROLES = ['admin', 'seller', 'customer'];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }

        foreach (['satici' => 'seller', 'musteri' => 'customer'] as $legacyName => $roleName) {
            $legacyRole = Role::query()
                ->where('name', $legacyName)
                ->where('guard_name', 'web')
                ->first();

            if ($legacyRole === null) {
                continue;
            }

            $role = Role::findByName($roleName, 'web');

            foreach ($legacyRole->users as $user) {
                $user->assignRole($role);
                $user->removeRole($legacyRole);
            }

            $legacyRole->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
