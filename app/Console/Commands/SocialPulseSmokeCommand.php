<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Workspaces\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

class SocialPulseSmokeCommand extends Command
{
    protected $signature = 'socialpulse:smoke
                            {--auth : Incluir rutas autenticadas con usuario demo}
                            {--oauth : Probar redirects OAuth (requiere --auth y credenciales configuradas)}
                            {--email=admin@agenciademo.test : Email del usuario demo}';

    protected $description = 'Smoke test E2E de rutas críticas (launch checklist)';

    /** @var list<string> */
    private array $failures = [];

    public function handle(Kernel $kernel): int
    {
        $this->info('SocialPulse smoke test');
        $this->newLine();

        $this->checkRoute($kernel, 'GET', '/', 'Welcome', [200]);
        $this->checkRoute($kernel, 'GET', '/up', 'Liveness', [200]);
        $this->checkRoute($kernel, 'GET', '/health', 'Health JSON', [200]);
        $this->checkRoute($kernel, 'GET', route('legal.privacy'), 'Privacy policy', [200]);
        $this->checkRoute($kernel, 'GET', route('legal.terms'), 'Terms of service', [200]);
        $this->checkRoute($kernel, 'GET', route('login'), 'Login', [200]);

        if ($this->option('oauth') && ! $this->option('auth')) {
            $this->failures[] = 'La opción --oauth requiere --auth';
        } elseif ($this->option('auth')) {
            $this->runAuthenticatedChecks($kernel);
        }

        $this->newLine();

        if ($this->failures !== []) {
            $this->error('Smoke test FAILED:');
            foreach ($this->failures as $failure) {
                $this->line("  - {$failure}");
            }

            return self::FAILURE;
        }

        $this->info('Smoke test passed.');

        return self::SUCCESS;
    }

    private function runAuthenticatedChecks(Kernel $kernel): void
    {
        $email = (string) $this->option('email');
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->failures[] = "Usuario demo no encontrado: {$email} (ejecuta DemoSeeder)";

            return;
        }

        Auth::login($user);

        $this->checkRoute($kernel, 'GET', route('dashboard'), 'Dashboard home', [200, 302]);
        $this->checkRoute($kernel, 'GET', route('workspaces.index'), 'Workspaces index', [200]);
        $this->checkRoute($kernel, 'GET', route('settings.index'), 'Settings hub', [200]);

        $workspace = Workspace::query()
            ->when($user->agency_id !== null, fn ($query) => $query->where('agency_id', $user->agency_id))
            ->orderBy('id')
            ->first();

        if ($workspace === null) {
            $this->failures[] = 'No hay workspace para smoke autenticado (ejecuta DemoSeeder)';

            Auth::logout();

            return;
        }

        $this->checkRoute(
            $kernel,
            'GET',
            route('workspaces.dashboard', $workspace),
            'Workspace dashboard',
            [200],
        );
        $this->checkRoute(
            $kernel,
            'GET',
            route('workspaces.connections.index', $workspace),
            'Workspace connections',
            [200],
        );
        $this->checkRoute(
            $kernel,
            'GET',
            route('workspaces.reports.index', $workspace),
            'Workspace reports',
            [200],
        );
        $this->checkRoute(
            $kernel,
            'GET',
            route('workspaces.content.index', $workspace),
            'Content calendar',
            [200],
        );
        $this->checkRoute(
            $kernel,
            'GET',
            route('workspaces.benchmarks', $workspace),
            'Benchmarks',
            [200],
        );

        if ($this->option('oauth')) {
            $this->runOAuthRedirectChecks($kernel, $workspace, $user->agency_id);
        }

        Auth::logout();
    }

    private function runOAuthRedirectChecks(Kernel $kernel, Workspace $workspace, ?int $agencyId): void
    {
        $resolver = app(IntegrationConfigResolver::class);

        $checks = [
            [
                'label' => 'Meta OAuth redirect',
                'configured' => $resolver->isMetaOAuthConfigured($agencyId),
                'uri' => route('workspaces.connections.meta.redirect', $workspace),
            ],
            [
                'label' => 'Google OAuth redirect',
                'configured' => $resolver->isGoogleOAuthConfigured($agencyId),
                'uri' => route('workspaces.connections.google.redirect', $workspace),
            ],
            [
                'label' => 'TikTok OAuth redirect',
                'configured' => $resolver->isTikTokOAuthConfigured($agencyId),
                'uri' => route('workspaces.connections.tiktok.redirect', $workspace),
            ],
            [
                'label' => 'LinkedIn OAuth redirect',
                'configured' => $resolver->isLinkedInOAuthConfigured($agencyId),
                'uri' => route('workspaces.connections.linkedin.redirect', $workspace),
            ],
            [
                'label' => 'YouTube OAuth redirect',
                'configured' => $resolver->isYouTubeOAuthConfigured($agencyId),
                'uri' => route('workspaces.connections.youtube.redirect', $workspace),
            ],
        ];

        $ranAny = false;

        foreach ($checks as $check) {
            if (! $check['configured']) {
                $this->line("<comment>○</comment> {$check['label']} (omitido — sin credenciales)");

                continue;
            }

            $ranAny = true;
            $this->checkRoute($kernel, 'GET', $check['uri'], $check['label'], [302]);
        }

        if (! $ranAny) {
            $this->line('<comment>○</comment> OAuth redirects: ninguna plataforma configurada');
        }
    }

    /**
     * @param  list<int>  $expectedStatuses
     */
    private function checkRoute(
        Kernel $kernel,
        string $method,
        string $uri,
        string $label,
        array $expectedStatuses,
    ): void {
        $request = Request::create($uri, $method);
        $request->headers->set('Accept', 'text/html,application/json');

        /** @var Response $response */
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $status = $response->getStatusCode();

        if (in_array($status, $expectedStatuses, true)) {
            $this->line("<info>✓</info> {$label} ({$status})");

            return;
        }

        $this->failures[] = "{$label}: expected ".implode('/', $expectedStatuses).", got {$status}";
        $this->line("<error>✗</error> {$label} ({$status})");
    }
}
