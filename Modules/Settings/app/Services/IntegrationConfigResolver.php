<?php

namespace Modules\Settings\Services;

use Modules\Settings\Models\IntegrationCredentialSet;

class IntegrationConfigResolver
{
    /**
     * @return array<string, mixed>
     */
    public function meta(?int $agencyId = null): array
    {
        $env = config('connections.meta', []);
        $platform = IntegrationCredentialSet::platform()->metaFields();
        $agency = $agencyId !== null
            ? IntegrationCredentialSet::forAgency($agencyId)->metaFields()
            : [];

        return [
            'app_id' => $this->pick($agency, $platform, $env, 'app_id'),
            'app_secret' => $this->pick($agency, $platform, $env, 'app_secret'),
            'api_version' => $this->pick($agency, $platform, $env, 'api_version') ?? 'v22.0',
            'redirect_uri' => $env['redirect_uri'] ?? url('/connections/meta/callback'),
            'scopes' => $env['scopes'] ?? [],
            'system_user_id' => $this->pick($agency, $platform, $env, 'system_user_id'),
            'system_user_access_token' => $this->pick($agency, $platform, $env, 'system_user_access_token'),
            'business_id' => $this->pick($agency, $platform, $env, 'business_id'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function google(?int $agencyId = null): array
    {
        $env = config('connections.google', []);
        $platform = IntegrationCredentialSet::platform()->googleFields();
        $agency = $agencyId !== null
            ? IntegrationCredentialSet::forAgency($agencyId)->googleFields()
            : [];

        return [
            'client_id' => $this->pick($agency, $platform, $env, 'client_id'),
            'client_secret' => $this->pick($agency, $platform, $env, 'client_secret'),
            'developer_token' => $this->pick($agency, $platform, $env, 'developer_token'),
            'redirect_uri' => $env['redirect_uri'] ?? url('/connections/google/callback'),
            'scopes' => $env['scopes'] ?? ['https://www.googleapis.com/auth/adwords'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function tiktok(?int $agencyId = null): array
    {
        $env = config('connections.tiktok', []);
        $platform = IntegrationCredentialSet::platform()->tiktokFields();
        $agency = $agencyId !== null
            ? IntegrationCredentialSet::forAgency($agencyId)->tiktokFields()
            : [];

        return [
            'client_key' => $this->pick($agency, $platform, $env, 'client_key'),
            'client_secret' => $this->pick($agency, $platform, $env, 'client_secret'),
            'redirect_uri' => $env['redirect_uri'] ?? url('/connections/tiktok/callback'),
            'scopes' => $env['scopes'] ?? ['user.info.basic', 'video.list'],
        ];
    }

    public function isMetaOAuthConfigured(?int $agencyId = null): bool
    {
        $meta = $this->meta($agencyId);

        return filled($meta['app_id'] ?? null) && filled($meta['app_secret'] ?? null);
    }

    public function isMetaSystemUserConfigured(?int $agencyId = null): bool
    {
        $meta = $this->meta($agencyId);

        return filled($meta['system_user_access_token'] ?? null)
            && filled($meta['business_id'] ?? null);
    }

    public function isGoogleConfigured(?int $agencyId = null): bool
    {
        $google = $this->google($agencyId);

        return filled($google['client_id'] ?? null)
            && filled($google['client_secret'] ?? null)
            && filled($google['developer_token'] ?? null);
    }

    public function isGoogleOAuthConfigured(?int $agencyId = null): bool
    {
        $google = $this->google($agencyId);

        return filled($google['client_id'] ?? null)
            && filled($google['client_secret'] ?? null);
    }

    public function isTikTokOAuthConfigured(?int $agencyId = null): bool
    {
        $tiktok = $this->tiktok($agencyId);

        return filled($tiktok['client_key'] ?? null)
            && filled($tiktok['client_secret'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public function linkedin(?int $agencyId = null): array
    {
        $env = config('connections.linkedin', []);
        $platform = IntegrationCredentialSet::platform()->linkedinFields();
        $agency = $agencyId !== null
            ? IntegrationCredentialSet::forAgency($agencyId)->linkedinFields()
            : [];

        return [
            'client_id' => $this->pick($agency, $platform, $env, 'client_id'),
            'client_secret' => $this->pick($agency, $platform, $env, 'client_secret'),
            'redirect_uri' => $env['redirect_uri'] ?? url('/connections/linkedin/callback'),
            'scopes' => $env['scopes'] ?? ['r_organization_admin', 'r_organization_social'],
            'api_version' => $env['api_version'] ?? '202405',
        ];
    }

    public function isLinkedInOAuthConfigured(?int $agencyId = null): bool
    {
        $linkedin = $this->linkedin($agencyId);

        return filled($linkedin['client_id'] ?? null)
            && filled($linkedin['client_secret'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public function youtube(?int $agencyId = null): array
    {
        $env = config('connections.youtube', []);
        $platform = IntegrationCredentialSet::platform()->youtubeFields();
        $agency = $agencyId !== null
            ? IntegrationCredentialSet::forAgency($agencyId)->youtubeFields()
            : [];

        return [
            'client_id' => $this->pick($agency, $platform, $env, 'client_id'),
            'client_secret' => $this->pick($agency, $platform, $env, 'client_secret'),
            'redirect_uri' => $env['redirect_uri'] ?? url('/connections/youtube/callback'),
            'scopes' => $env['scopes'] ?? ['https://www.googleapis.com/auth/youtube.readonly'],
        ];
    }

    public function isYouTubeOAuthConfigured(?int $agencyId = null): bool
    {
        $youtube = $this->youtube($agencyId);

        return filled($youtube['client_id'] ?? null)
            && filled($youtube['client_secret'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public function status(?int $agencyId = null): array
    {
        $meta = $this->meta($agencyId);
        $google = $this->google($agencyId);
        $tiktok = $this->tiktok($agencyId);
        $linkedin = $this->linkedin($agencyId);
        $youtube = $this->youtube($agencyId);

        return [
            'meta' => [
                'configured' => $this->isMetaOAuthConfigured($agencyId),
                'system_user_configured' => $this->isMetaSystemUserConfigured($agencyId),
                'api_version' => $meta['api_version'] ?? null,
                'scopes' => $meta['scopes'] ?? [],
                'oauth_source' => $this->metaOAuthSource($agencyId),
                'system_user_source' => $this->metaSystemUserSource($agencyId),
            ],
            'google' => [
                'configured' => $this->isGoogleConfigured($agencyId),
                'scopes' => $google['scopes'] ?? [],
                'developer_token_configured' => filled($google['developer_token'] ?? null),
                'source' => $this->googleSource($agencyId),
            ],
            'tiktok' => [
                'configured' => $this->isTikTokOAuthConfigured($agencyId),
                'scopes' => $tiktok['scopes'] ?? [],
                'source' => $this->tiktokSource($agencyId),
            ],
            'linkedin' => [
                'configured' => $this->isLinkedInOAuthConfigured($agencyId),
                'scopes' => $linkedin['scopes'] ?? [],
                'source' => $this->linkedinSource($agencyId),
            ],
            'youtube' => [
                'configured' => $this->isYouTubeOAuthConfigured($agencyId),
                'scopes' => $youtube['scopes'] ?? [],
                'source' => $this->youtubeSource($agencyId),
            ],
        ];
    }

    public function metaOAuthSource(?int $agencyId): string
    {
        return $this->resolveSource($agencyId, 'meta', ['app_id', 'app_secret']);
    }

    public function metaSystemUserSource(?int $agencyId): string
    {
        return $this->resolveSource($agencyId, 'meta', ['system_user_access_token', 'business_id']);
    }

    public function googleSource(?int $agencyId): string
    {
        return $this->resolveSource($agencyId, 'google', ['client_id', 'client_secret', 'developer_token']);
    }

    public function tiktokSource(?int $agencyId): string
    {
        return $this->resolveSource($agencyId, 'tiktok', ['client_key', 'client_secret']);
    }

    public function linkedinSource(?int $agencyId): string
    {
        return $this->resolveSource($agencyId, 'linkedin', ['client_id', 'client_secret']);
    }

    public function youtubeSource(?int $agencyId): string
    {
        return $this->resolveSource($agencyId, 'youtube', ['client_id', 'client_secret']);
    }

    /**
     * @param  list<string>  $fields
     */
    private function resolveSource(?int $agencyId, string $provider, array $fields): string
    {
        if ($agencyId !== null) {
            $agencySet = IntegrationCredentialSet::forAgency($agencyId);
            $agencyValues = match ($provider) {
                'meta' => $agencySet->metaFields(),
                'google' => $agencySet->googleFields(),
                'tiktok' => $agencySet->tiktokFields(),
                'linkedin' => $agencySet->linkedinFields(),
                'youtube' => $agencySet->youtubeFields(),
                default => [],
            };

            if ($this->fieldsFilled($agencyValues, $fields)) {
                return 'agency';
            }
        }

        $platformSet = IntegrationCredentialSet::platform();
        $platformValues = match ($provider) {
            'meta' => $platformSet->metaFields(),
            'google' => $platformSet->googleFields(),
            'tiktok' => $platformSet->tiktokFields(),
            'linkedin' => $platformSet->linkedinFields(),
            'youtube' => $platformSet->youtubeFields(),
            default => [],
        };

        if ($this->fieldsFilled($platformValues, $fields)) {
            return 'platform';
        }

        return 'env';
    }

    /**
     * @param  array<string, string|null>  $values
     * @param  list<string>  $fields
     */
    private function fieldsFilled(array $values, array $fields): bool
    {
        foreach ($fields as $field) {
            if (! filled($values[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string|null>  $agency
     * @param  array<string, string|null>  $platform
     * @param  array<string, mixed>  $env
     */
    private function pick(array $agency, array $platform, array $env, string $key): ?string
    {
        $value = $agency[$key] ?? $platform[$key] ?? $env[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
