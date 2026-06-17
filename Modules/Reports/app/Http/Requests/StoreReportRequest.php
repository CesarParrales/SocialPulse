<?php

namespace Modules\Reports\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Reports\Support\ReportConfig;

class StoreReportRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'period' => ['required', Rule::in(DashboardPeriod::PRESETS)],
            'from' => ['required_if:period,custom', 'nullable', 'date'],
            'to' => ['required_if:period,custom', 'nullable', 'date', 'after_or_equal:from'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['boolean'],
            'metrics' => ['nullable', 'array'],
            'metrics.*' => ['boolean'],
        ];
    }

    /**
     * @return array{start: Carbon, end: Carbon}
     */
    public function periodDates(): array
    {
        $preset = $this->string('period')->value();

        if ($preset === 'custom') {
            return [
                'start' => Carbon::parse($this->string('from')->value())->startOfDay(),
                'end' => Carbon::parse($this->string('to')->value())->endOfDay(),
            ];
        }

        $days = (int) rtrim($preset, 'd');

        return [
            'start' => now()->subDays($days - 1)->startOfDay(),
            'end' => now()->endOfDay(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reportConfig(?string $logoPath = null): array
    {
        $sectionsInput = [];

        $defaultSections = [
            'overview' => true,
            'organic' => true,
            'paid' => true,
            'top_content' => true,
            'comparisons' => false,
        ];

        foreach (ReportConfig::SECTIONS as $section) {
            $sectionsInput[$section] = $this->has("sections.{$section}")
                ? $this->boolean("sections.{$section}")
                : ($defaultSections[$section] ?? false);
        }

        $metricsInput = [];

        foreach (ReportConfig::METRICS as $metric) {
            $metricsInput[$metric] = $this->boolean("metrics.{$metric}", true);
        }

        return ReportConfig::normalize([
            'title' => $this->string('title')->value(),
            'primary_color' => $this->input('primary_color'),
            'secondary_color' => $this->input('secondary_color'),
            'logo_path' => $logoPath,
            'sections' => $sectionsInput,
            'metrics' => $metricsInput,
        ]);
    }
}
