<?php

namespace Modules\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Analytics\Models\CompetitorAccount;

class UpdateCompetitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CompetitorAccount $competitor */
        $competitor = $this->route('competitor');

        return $this->user()?->can('update', $competitor) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'other'])],
            'handle' => ['nullable', 'string', 'max:120'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'avg_reach' => ['nullable', 'numeric', 'min:0'],
            'avg_engagement_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'data_source_note' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
