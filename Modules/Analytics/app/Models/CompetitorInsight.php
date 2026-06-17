<?php

namespace Modules\Analytics\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Workspaces\Models\Workspace;

class CompetitorInsight extends Model
{
    protected $fillable = [
        'workspace_id',
        'prompt_text',
        'ai_draft_text',
        'reviewed_text',
        'updated_by',
        'prompt_generated_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'prompt_generated_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reportText(): ?string
    {
        if (is_string($this->reviewed_text) && trim($this->reviewed_text) !== '') {
            return trim($this->reviewed_text);
        }

        if (is_string($this->ai_draft_text) && trim($this->ai_draft_text) !== '') {
            return trim($this->ai_draft_text);
        }

        return null;
    }

    public function isReviewed(): bool
    {
        return is_string($this->reviewed_text) && trim($this->reviewed_text) !== '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontend(): array
    {
        return [
            'prompt_text' => $this->prompt_text,
            'ai_draft_text' => $this->ai_draft_text,
            'reviewed_text' => $this->reviewed_text,
            'is_reviewed' => $this->isReviewed(),
            'prompt_generated_at' => $this->prompt_generated_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
        ];
    }
}
