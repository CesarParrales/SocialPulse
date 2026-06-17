<?php

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Content\Enums\ContentChannel;
use Modules\Content\Enums\ContentType;
use Modules\Content\Models\ContentDraft;

class StoreContentDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', [ContentDraft::class, $this->route('workspace')]) ?? false;
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
            'calendar_entry_id' => ['nullable', 'integer', 'exists:content_calendar_entries,id'],
            'media_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
