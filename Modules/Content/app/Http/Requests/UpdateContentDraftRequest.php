<?php

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Content\Enums\ContentChannel;
use Modules\Content\Enums\ContentType;
use Modules\Content\Models\ContentDraft;

class UpdateContentDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $draft = $this->route('draft');

        return $draft instanceof ContentDraft
            && ($this->user()?->can('update', $draft) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'caption' => ['nullable', 'string', 'max:5000'],
            'channel' => ['required', 'string', Rule::enum(ContentChannel::class)],
            'content_type' => ['required', 'string', Rule::enum(ContentType::class)],
            'scheduled_at' => ['nullable', 'date'],
            'media_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
