<?php

namespace Modules\Content\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Enums\ContentDraftStatus;
use Modules\Content\Models\ContentCalendarEntry;
use Modules\Content\Models\ContentDraft;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_content_calendar_page(): void
    {
        [$workspace, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->get(route('workspaces.content.index', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Content/Calendar')
                ->has('calendarEntries')
                ->has('drafts')
            );
    }

    public function test_admin_can_create_calendar_entry_and_draft(): void
    {
        [$workspace, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->post(route('workspaces.content.entries.store', $workspace), [
                'title' => 'Reel lanzamiento',
                'scheduled_at' => now()->addDays(3)->toDateString(),
                'channel' => 'instagram',
                'content_type' => 'reel',
            ])
            ->assertRedirect();

        $entry = ContentCalendarEntry::query()->first();
        $this->assertNotNull($entry);

        $this->actingAs($admin)
            ->post(route('workspaces.content.drafts.store', $workspace), [
                'title' => 'Reel lanzamiento',
                'caption' => 'Copy del reel',
                'channel' => 'instagram',
                'content_type' => 'reel',
                'calendar_entry_id' => $entry->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('content_drafts', [
            'workspace_id' => $workspace->id,
            'calendar_entry_id' => $entry->id,
            'status' => ContentDraftStatus::Draft->value,
        ]);
    }

    public function test_approval_workflow(): void
    {
        [$workspace, $admin, $client] = $this->adminAndClientContext();

        $draft = ContentDraft::query()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Post aprobación',
            'caption' => 'Texto inicial',
            'channel' => 'facebook',
            'content_type' => 'feed',
            'status' => ContentDraftStatus::Draft,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('workspaces.content.drafts.submit', [$workspace, $draft]))
            ->assertRedirect();

        $draft->refresh();
        $this->assertSame(ContentDraftStatus::PendingReview, $draft->status);

        $this->actingAs($client)
            ->post(route('workspaces.content.drafts.review', [$workspace, $draft]), [
                'action' => 'approve',
            ])
            ->assertRedirect();

        $draft->refresh();
        $this->assertSame(ContentDraftStatus::Approved, $draft->status);
        $this->assertSame($client->id, $draft->reviewed_by);
    }

    public function test_client_cannot_create_content(): void
    {
        [$workspace, , $client] = $this->adminAndClientContext();

        $this->actingAs($client)
            ->post(route('workspaces.content.drafts.store', $workspace), [
                'title' => 'Intento cliente',
                'caption' => 'No permitido',
                'channel' => 'instagram',
                'content_type' => 'feed',
            ])
            ->assertRedirect($client->clientHomeUrl());
    }

    public function test_client_can_access_content_calendar(): void
    {
        [, $workspace, $client] = $this->adminAndClientContext();

        $this->actingAs($client)
            ->get(route('workspaces.content.index', $workspace))
            ->assertOk();
    }

    /**
     * @return array{0: Workspace, 1: User}
     */
    private function adminContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Content',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Content',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        return [$workspace, $admin];
    }

    /**
     * @return array{0: Workspace, 1: User, 2: User}
     */
    private function adminAndClientContext(): array
    {
        [$workspace, $admin] = $this->adminContext();

        $client = User::factory()->create(['agency_id' => $workspace->agency_id]);
        $client->assignRole(SystemRole::ClientReadonly->value);
        $client->workspaces()->attach($workspace->id, [
            'role' => WorkspaceMemberRole::ClientReadonly->value,
        ]);

        return [$workspace, $admin, $client];
    }
}
