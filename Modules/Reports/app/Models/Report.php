<?php

namespace Modules\Reports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Reports\Enums\ReportStatus;
use Modules\Workspaces\Models\Workspace;

class Report extends Model
{
    protected $fillable = [
        'workspace_id',
        'created_by',
        'name',
        'period_start',
        'period_end',
        'config',
        'status',
        'file_path',
        'error_message',
        'generated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'config' => 'array',
            'status' => ReportStatus::class,
            'generated_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isReady(): bool
    {
        return $this->status === ReportStatus::Ready && $this->file_path !== null;
    }
}
