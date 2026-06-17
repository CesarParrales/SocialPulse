<?php

namespace Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationCredentialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'meta_app_id' => ['nullable', 'string', 'max:255'],
            'meta_app_secret' => ['nullable', 'string', 'max:500'],
            'meta_api_version' => ['nullable', 'string', 'max:32'],
            'meta_system_user_id' => ['nullable', 'string', 'max:255'],
            'meta_system_user_access_token' => ['nullable', 'string', 'max:2000'],
            'meta_business_id' => ['nullable', 'string', 'max:255'],
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
            'google_developer_token' => ['nullable', 'string', 'max:500'],
            'tiktok_client_key' => ['nullable', 'string', 'max:255'],
            'tiktok_client_secret' => ['nullable', 'string', 'max:500'],
            'linkedin_client_id' => ['nullable', 'string', 'max:255'],
            'linkedin_client_secret' => ['nullable', 'string', 'max:500'],
            'youtube_client_id' => ['nullable', 'string', 'max:255'],
            'youtube_client_secret' => ['nullable', 'string', 'max:500'],
        ];
    }
}
