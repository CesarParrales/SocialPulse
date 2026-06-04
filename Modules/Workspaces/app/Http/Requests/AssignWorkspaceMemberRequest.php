<?php

namespace Modules\Workspaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Models\Workspace;

class AssignWorkspaceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && $this->user()?->can('assignMember', $workspace);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', Rule::enum(WorkspaceMemberRole::class)],
        ];
    }
}
