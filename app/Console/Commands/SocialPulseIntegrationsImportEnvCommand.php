<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Settings\Models\IntegrationCredentialSet;
use Modules\Settings\Services\IntegrationEnvImporter;
use Modules\Workspaces\Models\Agency;

class SocialPulseIntegrationsImportEnvCommand extends Command
{
    protected $signature = 'socialpulse:integrations:import-env
                            {--platform : Importar al set global de plataforma (default)}
                            {--agency= : ID de agencia destino}
                            {--force : Sobrescribir credenciales ya guardadas en BD}
                            {--dry-run : Mostrar qué se importaría sin guardar}';

    protected $description = 'Importa credenciales OAuth desde .env/config hacia IntegrationCredentialSet (BD cifrada)';

    public function handle(IntegrationEnvImporter $importer): int
    {
        if (! $importer->hasEnvValues()) {
            $this->warn('No hay credenciales OAuth en .env / config. Completa META_*, GOOGLE_ADS_*, etc.');

            return self::FAILURE;
        }

        $agencyId = $this->option('agency') !== null
            ? (int) $this->option('agency')
            : null;

        if ($this->option('platform') && $agencyId !== null) {
            $this->error('Usa solo --platform o --agency=ID, no ambos.');

            return self::FAILURE;
        }

        if ($agencyId !== null && ! Agency::query()->whereKey($agencyId)->exists()) {
            $this->error("Agencia {$agencyId} no encontrada.");

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $credentials = $agencyId !== null
            ? IntegrationCredentialSet::forAgency($agencyId)
            : IntegrationCredentialSet::platform();

        $label = $agencyId !== null ? "agencia {$agencyId}" : 'plataforma';

        $pending = $importer->pendingEnvFields($credentials, $force);

        if ($pending === []) {
            $this->info("Nada que importar a {$label}. Usa --force para sobrescribir valores existentes.");

            return self::SUCCESS;
        }

        $this->line("Destino: {$label}");
        $this->line('Campos: '.implode(', ', $pending));

        if ($dryRun) {
            $this->comment('Dry-run: no se guardó nada.');

            return self::SUCCESS;
        }

        $result = $agencyId !== null
            ? $importer->importAgency($agencyId, $force)
            : $importer->importPlatform($force);

        if ($result['imported'] !== []) {
            $this->info('Importados: '.implode(', ', $result['imported']));
        }

        if ($result['skipped'] !== []) {
            $this->line('Omitidos (ya en BD): '.implode(', ', $result['skipped']));
        }

        $this->newLine();
        $this->line('Verifica: php artisan socialpulse:integrations:check');

        return self::SUCCESS;
    }
}
