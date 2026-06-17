<?php

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Content\Models\ContentDraft;

class ReviewContentDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $draft = $this->route('draft');

        return $draft instanceof ContentDraft
            && ($this->user()?->can('review', $draft) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:approve,reject'],
            'review_notes' => ['required_if:action,reject', 'nullable', 'string', 'max:2000'],
        ];
    }
}
