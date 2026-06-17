<?php

namespace Modules\Settings\Services;

use Modules\Settings\Models\IntegrationCredentialSet;

class IntegrationEnvImporter
{
    /**
     * @return list<string>
     */
    public function envFieldMap(): array
    {
        return [
            'meta_app_id' => 'connections.meta.app_id',
            'meta_app_secret' => 'connections.meta.app_secret',
            'meta_api_version' => 'connections.meta.api_version',
            'meta_system_user_id' => 'connections.meta.system_user_id',
            'meta_system_user_access_token' => 'connections.meta.system_user_access_token',
            'meta_business_id' => 'connections.meta.business_id',
            'google_client_id' => 'connections.google.client_id',
            'google_client_secret' => 'connections.google.client_secret',
            'google_developer_token' => 'connections.google.developer_token',
            'tiktok_client_key' => 'connections.tiktok.client_key',
            'tiktok_client_secret' => 'connections.tiktok.client_secret',
            'linkedin_client_id' => 'connections.linkedin.client_id',
            'linkedin_client_secret' => 'connections.linkedin.client_secret',
            'youtube_client_id' => 'connections.youtube.client_id',
            'youtube_client_secret' => 'connections.youtube.client_secret',
        ];
    }

    public function hasEnvValues(): bool
    {
        foreach ($this->envFieldMap() as $configKey) {
            if (filled(config($configKey))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function pendingEnvFields(IntegrationCredentialSet $credentials, bool $force = false): array
    {
        $pending = [];

        foreach ($this->envFieldMap() as $field => $configKey) {
            $envValue = config($configKey);

            if (! filled($envValue)) {
                continue;
            }

            $current = $credentials->{$field};

            if ($force || ! filled($current)) {
                $pending[] = $field;
            }
        }

        return $pending;
    }

    /**
     * @return array{imported: list<string>, skipped: list<string>}
     */
    public function import(IntegrationCredentialSet $credentials, bool $force = false): array
    {
        $imported = [];
        $skipped = [];

        foreach ($this->envFieldMap() as $field => $configKey) {
            $envValue = config($configKey);

            if (! filled($envValue)) {
                continue;
            }

            if (! $force && filled($credentials->{$field})) {
                $skipped[] = $field;

                continue;
            }

            $credentials->{$field} = is_string($envValue) ? $envValue : (string) $envValue;
            $imported[] = $field;
        }

        if ($imported !== []) {
            $credentials->save();
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
        ];
    }

    public function importPlatform(bool $force = false): array
    {
        return $this->import(IntegrationCredentialSet::platform(), $force);
    }

    public function importAgency(int $agencyId, bool $force = false): array
    {
        return $this->import(IntegrationCredentialSet::forAgency($agencyId), $force);
    }
}
