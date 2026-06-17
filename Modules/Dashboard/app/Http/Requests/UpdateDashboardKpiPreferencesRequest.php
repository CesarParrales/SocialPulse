<?php

namespace Modules\Dashboard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Dashboard\Support\DashboardKpiPreferences;
use Modules\Workspaces\Models\Workspace;

class UpdateDashboardKpiPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && ($this->user()?->can('customizeDashboard', $workspace) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'visible_kpis' => ['required', 'array', 'min:1'],
            'visible_kpis.*' => ['required', 'string', Rule::in(DashboardKpiPreferences::METRICS)],
        ];
    }
}
