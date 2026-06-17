<?php

namespace Modules\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Workspaces\Models\Workspace;

class StoreCompetitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Workspace $workspace */
        $workspace = $this->route('workspace');

        return $this->user()?->can('customizeDashboard', $workspace) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'tiktok', 'other'])],
            'handle' => ['nullable', 'string', 'max:120'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'avg_reach' => ['nullable', 'numeric', 'min:0'],
            'avg_engagement_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'data_source_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'handle' => is_string($this->input('handle')) ? trim($this->input('handle')) : $this->input('handle'),
            'platform' => $this->input('platform') === '' ? null : $this->input('platform'),
            'followers_count' => $this->input('followers_count') === '' ? null : $this->input('followers_count'),
            'avg_reach' => $this->input('avg_reach') === '' ? null : $this->input('avg_reach'),
            'avg_engagement_rate' => $this->input('avg_engagement_rate') === '' ? null : $this->input('avg_engagement_rate'),
            'data_source_note' => $this->input('data_source_note') === '' ? null : $this->input('data_source_note'),
            'notes' => $this->input('notes') === '' ? null : $this->input('notes'),
        ]);
    }
}
