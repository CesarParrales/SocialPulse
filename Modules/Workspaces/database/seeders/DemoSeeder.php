<?php

namespace Modules\Workspaces\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Dashboard\Database\Seeders\DemoAnalyticsSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $agency = Agency::query()->firstOrCreate(
            ['name' => 'Agencia Demo LATAM'],
            [
                'plan' => AgencyPlan::Agency,
                'billing_email' => 'billing@agenciademo.test',
            ],
        );

        $workspaceA = Workspace::query()->firstOrCreate(
            ['agency_id' => $agency->id, 'name' => 'Cliente — Marca Alfa'],
            [
                'industry_category' => 'Retail',
                'timezone' => 'America/Guayaquil',
            ],
        );

        $workspaceB = Workspace::query()->firstOrCreate(
            ['agency_id' => $agency->id, 'name' => 'Cliente — Marca Beta'],
            [
                'industry_category' => 'Tecnología',
                'timezone' => 'America/Mexico_City',
            ],
        );

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'super@socialpulse.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'agency_id' => null,
                'email_verified_at' => now(),
            ],
        );
        $superAdmin->syncRoles([SystemRole::SuperAdmin->value]);

        $agencyAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@agenciademo.test'],
            [
                'name' => 'Admin Agencia',
                'password' => Hash::make('password'),
                'agency_id' => $agency->id,
                'email_verified_at' => now(),
            ],
        );
        $agencyAdmin->syncRoles([SystemRole::AgencyAdmin->value]);

        $operator = User::query()->updateOrCreate(
            ['email' => 'operador@agenciademo.test'],
            [
                'name' => 'Operador Demo',
                'password' => Hash::make('password'),
                'agency_id' => $agency->id,
                'email_verified_at' => now(),
            ],
        );
        $operator->syncRoles([SystemRole::Operator->value]);

        $operator->workspaces()->sync([
            $workspaceA->id => ['role' => WorkspaceMemberRole::Operator->value],
        ]);

        $client = User::query()->updateOrCreate(
            ['email' => 'cliente@agenciademo.test'],
            [
                'name' => 'Cliente Demo',
                'password' => Hash::make('password'),
                'agency_id' => $agency->id,
                'email_verified_at' => now(),
            ],
        );
        $client->syncRoles([SystemRole::ClientReadonly->value]);

        $client->workspaces()->sync([
            $workspaceA->id => ['role' => WorkspaceMemberRole::ClientReadonly->value],
        ]);

        $this->call(DemoAnalyticsSeeder::class);
    }
}
