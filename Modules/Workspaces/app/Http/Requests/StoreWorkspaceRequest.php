<?php

namespace Modules\Workspaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Workspaces\Models\Workspace;

class StoreWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Workspace::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'industry_category' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'timezone:all'],
        ];

        if ($this->user()?->isSuperAdmin()) {
            $rules['agency_id'] = ['required', 'integer', 'exists:agencies,id'];
        }

        return $rules;
    }
}
