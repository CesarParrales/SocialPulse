<?php

namespace Modules\Workspaces\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Workspaces\Enums\SystemRole;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard');

        foreach (SystemRole::cases() as $role) {
            Role::findOrCreate($role->value, $guard);
        }
    }
}
