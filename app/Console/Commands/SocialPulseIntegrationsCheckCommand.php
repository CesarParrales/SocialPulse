<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Workspaces\Models\Agency;

class SocialPulseIntegrationsCheckCommand extends Command
{
    protected $signature = 'socialpulse:integrations:check
                            {--agency= : ID de agencia para resolver cascada agencia → plataforma → .env}
                            {--require= : Plataformas obligatorias separadas por coma: meta,google,tiktok,linkedin,youtube}';

    protected $description = 'Verifica credenciales OAuth/API configuradas (staging / pre-launch)';

    public function handle(IntegrationConfigResolver $resolver): int
    {
        $agencyId = $this->option('agency') !== null
            ? (int) $this->option('agency')
            : null;

        if ($agencyId !== null && ! Agency::query()->whereKey($agencyId)->exists()) {
            $this->error("Agencia {$agencyId} no encontrada.");

            return self::FAILURE;
        }

        $this->info('SocialPulse — estado de integraciones');
        $this->line('APP_URL: '.config('app.url'));

        if ($agencyId !== null) {
            $this->line("Agencia: {$agencyId}");
        } else {
            $this->line('Agencia: (solo plataforma + .env)');
        }

        $this->newLine();

        $status = $resolver->status($agencyId);
        $rows = [];

        $rows[] = $this->row(
            'Meta OAuth',
            $status['meta']['configured'],
            $status['meta']['oauth_source'] ?? 'env',
            config('connections.meta.redirect_uri') ?? url('/connections/meta/callback'),
        );

        $rows[] = $this->row(
            'Meta System User',
            $status['meta']['system_user_configured'] ?? false,
            $status['meta']['system_user_source'] ?? 'env',
            '—',
        );

        $rows[] = $this->row(
            'Google Ads',
            $status['google']['configured'],
            $status['google']['source'] ?? 'env',
            config('connections.google.redirect_uri') ?? url('/connections/google/callback'),
            ($status['google']['developer_token_configured'] ?? false) ? 'dev token OK' : 'sin dev token',
        );

        $rows[] = $this->row(
            'TikTok',
            $status['tiktok']['configured'] ?? false,
            $status['tiktok']['source'] ?? 'env',
            config('connections.tiktok.redirect_uri') ?? url('/connections/tiktok/callback'),
        );

        $rows[] = $this->row(
            'LinkedIn',
            $status['linkedin']['configured'] ?? false,
            $status['linkedin']['source'] ?? 'env',
            config('connections.linkedin.redirect_uri') ?? url('/connections/linkedin/callback'),
        );

        $rows[] = $this->row(
            'YouTube',
            $status['youtube']['configured'] ?? false,
            $status['youtube']['source'] ?? 'env',
            config('connections.youtube.redirect_uri') ?? url('/connections/youtube/callback'),
        );

        $this->table(
            ['Integración', 'Configurada', 'Fuente', 'Redirect / notas'],
            $rows,
        );

        $this->printMissingEnvHints($status);

        $missing = $this->missingRequired($status);
        $required = $this->requiredPlatforms();

        if ($required !== []) {
            $this->newLine();
            $this->line('Requeridas: '.implode(', ', $required));

            if ($missing !== []) {
                $this->error('Faltan: '.implode(', ', $missing));

                return self::FAILURE;
            }

            $this->info('Todas las integraciones requeridas están configuradas.');
        } else {
            $configuredCount = collect($rows)->where('1', '✓')->count();
            $this->newLine();
            $this->line("Configuradas: {$configuredCount}/".count($rows));
            $this->line('Usa --require=meta,tiktok para fallar si falta alguna.');
        }

        $this->newLine();
        $this->line('Guía QA manual: docs/staging-oauth-qa.md');
        $this->line('Importar .env → BD: php artisan socialpulse:integrations:import-env --platform');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function requiredPlatforms(): array
    {
        $raw = (string) $this->option('require');

        if ($raw === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn (string $value) => strtolower(trim($value)))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $status
     * @return list<string>
     */
    private function missingRequired(array $status): array
    {
        $missing = [];

        foreach ($this->requiredPlatforms() as $platform) {
            $configured = match ($platform) {
                'meta' => $status['meta']['configured'] ?? false,
                'google' => $status['google']['configured'] ?? false,
                'tiktok' => $status['tiktok']['configured'] ?? false,
                'linkedin' => $status['linkedin']['configured'] ?? false,
                'youtube' => $status['youtube']['configured'] ?? false,
                default => null,
            };

            if ($configured === null) {
                $this->warn("Plataforma desconocida en --require: {$platform}");

                continue;
            }

            if (! $configured) {
                $missing[] = $platform;
            }
        }

        return $missing;
    }

    /**
     * @return list<string>
     */
    private function row(
        string $label,
        bool $configured,
        string $source,
        string $redirect,
        ?string $extra = null,
    ): array {
        $notes = $redirect;

        if ($extra !== null) {
            $notes = $redirect === '—' ? $extra : "{$redirect} · {$extra}";
        }

        return [
            $label,
            $configured ? '✓' : '✗',
            $source,
            $notes,
        ];
    }

    /**
     * @param  array<string, mixed>  $status
     */
    private function printMissingEnvHints(array $status): void
    {
        $hints = [
            'Meta OAuth' => [
                'configured' => $status['meta']['configured'] ?? false,
                'env' => ['META_APP_ID', 'META_APP_SECRET'],
                'redirect' => config('connections.meta.redirect_uri'),
            ],
            'Meta System User' => [
                'configured' => $status['meta']['system_user_configured'] ?? false,
                'env' => ['META_SYSTEM_USER_ACCESS_TOKEN', 'META_BUSINESS_ID'],
                'redirect' => null,
            ],
            'Google Ads' => [
                'configured' => $status['google']['configured'] ?? false,
                'env' => ['GOOGLE_ADS_CLIENT_ID', 'GOOGLE_ADS_CLIENT_SECRET', 'GOOGLE_ADS_DEVELOPER_TOKEN'],
                'redirect' => config('connections.google.redirect_uri'),
            ],
            'TikTok' => [
                'configured' => $status['tiktok']['configured'] ?? false,
                'env' => ['TIKTOK_CLIENT_KEY', 'TIKTOK_CLIENT_SECRET'],
                'redirect' => config('connections.tiktok.redirect_uri'),
            ],
            'LinkedIn' => [
                'configured' => $status['linkedin']['configured'] ?? false,
                'env' => ['LINKEDIN_CLIENT_ID', 'LINKEDIN_CLIENT_SECRET'],
                'redirect' => config('connections.linkedin.redirect_uri'),
            ],
            'YouTube' => [
                'configured' => $status['youtube']['configured'] ?? false,
                'env' => ['YOUTUBE_CLIENT_ID', 'YOUTUBE_CLIENT_SECRET'],
                'redirect' => config('connections.youtube.redirect_uri'),
            ],
        ];

        $missingBlocks = [];

        foreach ($hints as $label => $hint) {
            if ($hint['configured']) {
                continue;
            }

            $lines = implode(', ', $hint['env']);

            if ($hint['redirect'] !== null) {
                $lines .= " · redirect: {$hint['redirect']}";
            }

            $missingBlocks[] = "  {$label}: {$lines}";
        }

        if ($missingBlocks === []) {
            return;
        }

        $this->newLine();
        $this->line('Variables .env faltantes (o Settings → credenciales):');
        $this->line(implode("\n", $missingBlocks));
    }
}
