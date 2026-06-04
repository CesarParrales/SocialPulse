<?php

namespace Modules\Workspaces\Database\Seeders;

use Illuminate\Database\Seeder;

class WorkspacesDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
