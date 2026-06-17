<?php

namespace Modules\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Analytics\Enums\ComparisonType;

class WorkspaceComparisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('workspace')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::in(array_column(ComparisonType::cases(), 'value'))],
            'period' => ['sometimes', 'string', Rule::in(['7d', '14d', '30d', '90d'])],
            'left_start' => ['required_if:type,period_vs_period', 'date'],
            'left_end' => ['required_if:type,period_vs_period', 'date', 'after_or_equal:left_start'],
            'right_start' => ['required_if:type,period_vs_period', 'date'],
            'right_end' => ['required_if:type,period_vs_period', 'date', 'after_or_equal:right_start'],
            'asset_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }
}
