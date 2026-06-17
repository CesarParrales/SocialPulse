<?php

namespace Modules\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Workspaces\Models\Workspace;

class SaveCompetitorInsightRequest extends FormRequest
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
            'ai_draft_text' => ['nullable', 'string', 'max:20000'],
            'reviewed_text' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
