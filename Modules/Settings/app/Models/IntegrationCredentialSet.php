<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Workspaces\Models\Agency;

class IntegrationCredentialSet extends Model
{
    protected $fillable = [
        'agency_id',
        'meta_app_id',
        'meta_app_secret',
        'meta_api_version',
        'meta_system_user_id',
        'meta_system_user_access_token',
        'meta_business_id',
        'google_client_id',
        'google_client_secret',
        'google_developer_token',
        'tiktok_client_key',
        'tiktok_client_secret',
        'linkedin_client_id',
        'linkedin_client_secret',
        'youtube_client_id',
        'youtube_client_secret',
    ];

    protected function casts(): array
    {
        return [
            'meta_app_secret' => 'encrypted',
            'meta_system_user_access_token' => 'encrypted',
            'google_client_secret' => 'encrypted',
            'google_developer_token' => 'encrypted',
            'tiktok_client_secret' => 'encrypted',
            'linkedin_client_secret' => 'encrypted',
            'youtube_client_secret' => 'encrypted',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public static function platform(): self
    {
        return static::query()->firstOrCreate(['agency_id' => null]);
    }

    public static function forAgency(int $agencyId): self
    {
        return static::query()->firstOrCreate(['agency_id' => $agencyId]);
    }

    /**
     * @return array<string, string|null>
     */
    public function metaFields(): array
    {
        return [
            'app_id' => $this->meta_app_id,
            'app_secret' => $this->meta_app_secret,
            'api_version' => $this->meta_api_version,
            'system_user_id' => $this->meta_system_user_id,
            'system_user_access_token' => $this->meta_system_user_access_token,
            'business_id' => $this->meta_business_id,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function googleFields(): array
    {
        return [
            'client_id' => $this->google_client_id,
            'client_secret' => $this->google_client_secret,
            'developer_token' => $this->google_developer_token,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function tiktokFields(): array
    {
        return [
            'client_key' => $this->tiktok_client_key,
            'client_secret' => $this->tiktok_client_secret,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function linkedinFields(): array
    {
        return [
            'client_id' => $this->linkedin_client_id,
            'client_secret' => $this->linkedin_client_secret,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function youtubeFields(): array
    {
        return [
            'client_id' => $this->youtube_client_id,
            'client_secret' => $this->youtube_client_secret,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toFormPayload(): array
    {
        return [
            'meta_app_id' => $this->meta_app_id ?? '',
            'meta_api_version' => $this->meta_api_version ?? '',
            'meta_system_user_id' => $this->meta_system_user_id ?? '',
            'meta_business_id' => $this->meta_business_id ?? '',
            'google_client_id' => $this->google_client_id ?? '',
            'tiktok_client_key' => $this->tiktok_client_key ?? '',
            'linkedin_client_id' => $this->linkedin_client_id ?? '',
            'youtube_client_id' => $this->youtube_client_id ?? '',
            'has_meta_app_secret' => filled($this->meta_app_secret),
            'has_meta_system_user_access_token' => filled($this->meta_system_user_access_token),
            'has_google_client_secret' => filled($this->google_client_secret),
            'has_google_developer_token' => filled($this->google_developer_token),
            'has_tiktok_client_secret' => filled($this->tiktok_client_secret),
            'has_linkedin_client_secret' => filled($this->linkedin_client_secret),
            'has_youtube_client_secret' => filled($this->youtube_client_secret),
        ];
    }
}
