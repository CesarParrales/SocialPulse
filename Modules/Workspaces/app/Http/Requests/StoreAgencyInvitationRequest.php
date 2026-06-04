<?php

namespace Modules\Workspaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Workspaces\Enums\SystemRole;

class StoreAgencyInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->isSuperAdmin() || $user->isAgencyAdmin());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => [
                'required',
                Rule::enum(SystemRole::class),
                Rule::notIn([SystemRole::SuperAdmin->value]),
            ],
        ];
    }
}
