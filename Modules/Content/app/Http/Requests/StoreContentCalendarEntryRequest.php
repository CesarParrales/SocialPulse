<?php

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Content\Enums\ContentChannel;
use Modules\Content\Enums\ContentType;
use Modules\Content\Models\ContentDraft;

class StoreContentCalendarEntryRequest extends FormRequest
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
            'scheduled_at' => ['required', 'date'],
            'channel' => ['required', 'string', Rule::enum(ContentChannel::class)],
            'content_type' => ['required', 'string', Rule::enum(ContentType::class)],
        ];
    }
}
