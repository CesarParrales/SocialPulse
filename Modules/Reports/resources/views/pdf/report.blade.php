<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        :root {
            --primary: {{ $branding['primary_color'] }};
            --secondary: {{ $branding['secondary_color'] }};
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #111827;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
        }
        .page { padding: 24px 32px; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            color: var(--primary);
        }
        .header .meta { text-align: right; color: #6b7280; font-size: 11px; }
        .logo { max-height: 48px; max-width: 160px; object-fit: contain; }
        .section { margin-bottom: 28px; page-break-inside: avoid; }
        .section h2 {
            font-size: 16px;
            color: var(--primary);
            border-left: 4px solid var(--secondary);
            padding-left: 10px;
            margin: 0 0 14px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .kpi-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
        }
        .kpi-card .label { color: #6b7280; font-size: 11px; margin-bottom: 4px; }
        .kpi-card .value { font-size: 22px; font-weight: 700; color: #111827; }
        .kpi-card .delta { font-size: 10px; margin-top: 4px; }
        .delta-up { color: #059669; }
        .delta-down { color: #dc2626; }
        .delta-neutral { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; }
        th { background: var(--primary); color: #fff; font-weight: 600; }
        tr:nth-child(even) { background: #f9fafb; }
        .footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 10px;
            text-align: center;
        }
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .channel-block {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .channel-block h3 {
            margin: 0 0 12px;
            font-size: 14px;
            color: var(--secondary);
        }
        .subheading {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin: 14px 0 8px;
        }
        .post-preview { color: #374151; max-width: 280px; }
        .narrative-block {
            background: #f8fafc;
            border-left: 4px solid var(--primary);
            border-radius: 0 8px 8px 0;
            padding: 14px 16px;
            margin-top: 16px;
        }
        .narrative-block p {
            margin: 0 0 8px;
            color: #374151;
            line-height: 1.55;
        }
        .narrative-block p:last-child { margin-bottom: 0; }
        .narrative-signals { margin-top: 10px; }
        .signal {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
            margin: 0 6px 6px 0;
        }
        .signal-positive { background: #d1fae5; color: #065f46; }
        .signal-warning { background: #fef3c7; color: #92400e; }
        .signal-negative { background: #fee2e2; color: #991b1b; }
        .signal-neutral { background: #e5e7eb; color: #374151; }
        .narrative-bullets {
            margin: 8px 0 0;
            padding-left: 18px;
            color: #4b5563;
        }
        .narrative-bullets li { margin-bottom: 4px; }
        .appendix-table { font-size: 10px; }
        .appendix-table th, .appendix-table td { padding: 6px 8px; }
        .page-break { page-break-before: always; }
        body.deck { background: #ffffff; }
        .slide {
            page-break-after: always;
            min-height: 190mm;
            padding: 28px 36px 48px;
            position: relative;
        }
        .slide:last-child { page-break-after: auto; }
        .slide-cover {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .slide-cover-inner { max-width: 70%; }
        .cover-kicker {
            margin: 0 0 8px;
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.85;
        }
        .cover-title {
            margin: 0 0 12px;
            font-size: 42px;
            line-height: 1.1;
            font-weight: 700;
        }
        .cover-brand {
            margin: 0 0 28px;
            font-size: 20px;
            opacity: 0.95;
        }
        .cover-logo {
            max-height: 64px;
            max-width: 200px;
            object-fit: contain;
            margin-bottom: 24px;
            filter: brightness(0) invert(1);
        }
        .cover-meta {
            display: grid;
            gap: 8px;
            font-size: 13px;
        }
        .cover-meta span {
            display: inline-block;
            min-width: 120px;
            opacity: 0.75;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.08em;
        }
        .slide-divider {
            background: var(--primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .slide-divider-inner { max-width: 80%; }
        .divider-kicker {
            margin: 0 0 8px;
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            opacity: 0.8;
        }
        .slide-divider h2 {
            margin: 0;
            font-size: 34px;
            line-height: 1.15;
            color: #ffffff;
            border: none;
            padding: 0;
        }
        .divider-subtitle {
            margin: 12px 0 0;
            font-size: 14px;
            opacity: 0.85;
        }
        .content-slide .slide-header {
            border-bottom: 2px solid var(--primary);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .content-slide .slide-header h2 {
            margin: 0;
            font-size: 22px;
            color: var(--primary);
            border: none;
            padding: 0;
        }
        .content-slide .slide-header p {
            margin: 6px 0 0;
            color: #6b7280;
            font-size: 11px;
        }
        .slide-footer-bar {
            position: absolute;
            left: 36px;
            right: 36px;
            bottom: 16px;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            color: #9ca3af;
            font-size: 10px;
        }
        .slide-footer-bar--light {
            border-top-color: rgba(255, 255, 255, 0.25);
            color: rgba(255, 255, 255, 0.75);
        }
        .meta-summary-block {
            margin-top: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }
        .meta-summary-header {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: #fff;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
        }
        .meta-summary-body { padding: 16px; }
        .meta-leaders {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }
        .meta-leader-chip {
            background: #eef2ff;
            color: #3730a3;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 600;
        }
    </style>
</head>
<body class="deck">
@php
    use Modules\Connections\Enums\AssetType;
    use Modules\Reports\Support\MetaMetricLabels;

    $kpis = $analytics['kpis'] ?? [];
    $metricMap = [
        'reach' => ['label' => 'Alcance total', 'data' => $kpis['reach'] ?? null, 'format' => 'number'],
        'impressions' => ['label' => 'Impresiones', 'data' => $kpis['impressions'] ?? null, 'format' => 'number'],
        'engagement_rate' => ['label' => 'Engagement rate', 'data' => $kpis['engagement_rate'] ?? null, 'format' => 'percent'],
        'spend' => ['label' => 'Inversión pagada', 'data' => $kpis['spend'] ?? null, 'format' => 'currency'],
        'follower_growth' => ['label' => 'Crecimiento seguidores', 'data' => $kpis['follower_growth'] ?? null, 'format' => 'number'],
        'posts_published' => ['label' => 'Posts publicados', 'data' => $kpis['posts_published'] ?? null, 'format' => 'number'],
    ];
    $selectedMetrics = $metrics ?? array_keys($metricMap);

    $formatValue = function ($value, $format) {
        if ($format === 'currency') return '$' . number_format((float) $value, 2);
        if ($format === 'percent') return number_format((float) $value, 2) . '%';
        return number_format((float) $value, 0, '.', ',');
    };

    $formatDelta = function ($delta) use ($formatValue) {
        if (! ($delta['comparable'] ?? false)) return 'Sin histórico previo';
        $pct = $delta['change_pct'] ?? null;
        if ($pct === null) return '—';
        $sign = $pct >= 0 ? '+' : '';
        return $sign . number_format($pct, 1) . '% vs período anterior';
    };
@endphp

@include('reports::pdf.partials.slide-cover')

    @if ($sections['overview'] ?? false)
        @include('reports::pdf.partials.slide-divider', [
            'title' => 'Resumen ejecutivo',
            'kicker' => 'Overview',
            'subtitle' => $report['period']['from'].' — '.$report['period']['to'],
        ])
        <section class="slide content-slide">
            @include('reports::pdf.partials.slide-footer')
            <div class="slide-header">
                <h2>Resumen ejecutivo</h2>
                <p>KPIs del período vs histórico comparable</p>
            </div>
        <div class="section">
            <div class="kpi-grid">
                @foreach ($selectedMetrics as $metricKey)
                    @php $metric = $metricMap[$metricKey] ?? null; @endphp
                    @if ($metric && $metric['data'])
                        <div class="kpi-card">
                            <div class="label">{{ $metric['label'] }}</div>
                            <div class="value">{{ $formatValue($metric['data']['current'] ?? 0, $metric['format']) }}</div>
                            @php
                                $direction = $metric['data']['direction'] ?? 'neutral';
                                $deltaClass = match ($direction) {
                                    'up' => 'delta-up',
                                    'down' => 'delta-down',
                                    default => 'delta-neutral',
                                };
                            @endphp
                            <div class="delta {{ $deltaClass }}">{{ $formatDelta($metric['data']) }}</div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if (! empty($organic_meta_summary))
                <div class="meta-summary-block">
                    <div class="meta-summary-header">
                        {{ $organic_meta_summary['narrative']['title'] ?? 'Facebook + Instagram integrado' }}
                    </div>
                    <div class="meta-summary-body">
                        @if (! empty($organic_meta_summary['narrative']['leaders']))
                            <div class="meta-leaders">
                                @foreach ($organic_meta_summary['narrative']['leaders'] as $leader)
                                    <span class="meta-leader-chip">
                                        {{ $leader['label'] ?? 'Métrica' }}: {{ $leader['winner_label'] ?? '—' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <table style="margin-bottom: 16px;">
                            <thead>
                                <tr>
                                    <th>Métrica</th>
                                    <th>{{ $organic_meta_summary['facebook']['label'] ?? 'Facebook' }}</th>
                                    <th>{{ $organic_meta_summary['instagram']['label'] ?? 'Instagram' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($organic_meta_summary['comparison']['rows'] ?? [] as $row)
                                    @if (($row['left'] ?? 0) == 0 && ($row['right'] ?? 0) == 0 && ($row['metric'] ?? '') === 'spend')
                                        @continue
                                    @endif
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td>
                                            @if ($row['format'] === 'currency') ${{ number_format($row['left'], 2) }}
                                            @elseif ($row['format'] === 'percent') {{ number_format($row['left'], 2) }}%
                                            @else {{ number_format($row['left']) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row['format'] === 'currency') ${{ number_format($row['right'], 2) }}
                                            @elseif ($row['format'] === 'percent') {{ number_format($row['right'], 2) }}%
                                            @else {{ number_format($row['right']) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="narrative-block" style="margin-top: 0;">
                            @foreach ($organic_meta_summary['narrative']['paragraphs'] ?? [] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                            @if (! empty($organic_meta_summary['narrative']['signals']))
                                <div class="narrative-signals">
                                    @foreach ($organic_meta_summary['narrative']['signals'] as $signal)
                                        <span class="signal signal-{{ $signal['type'] ?? 'neutral' }}">{{ $signal['text'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if (! empty($narrative['executive']['paragraphs'] ?? []))
                <div class="narrative-block">
                    <div class="subheading">{{ $narrative['executive']['title'] ?? 'Qué nos dice el período' }}</div>
                    @foreach ($narrative['executive']['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    @if (! empty($narrative['executive']['signals']))
                        <div class="narrative-signals">
                            @foreach ($narrative['executive']['signals'] as $signal)
                                <span class="signal signal-{{ $signal['type'] ?? 'neutral' }}">{{ $signal['text'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
        </section>
    @endif

    @if (! empty($channel_sections))
        @include('reports::pdf.partials.slide-divider', [
            'title' => 'Análisis por canal',
            'kicker' => 'Organic & Paid',
        ])
        @foreach ($channel_sections as $section)
        <section class="slide content-slide">
            @include('reports::pdf.partials.slide-footer')
            <div class="slide-header">
                <h2>{{ $section['label'] ?? $section['key'] }}</h2>
                <p>{{ ($section['kind'] ?? '') === 'paid' ? 'Rendimiento pagado' : 'Rendimiento orgánico' }}</p>
            </div>
                <div class="channel-block" style="border:none;padding:0;margin:0;">

                    @php
                        $channelNarrative = collect($narrative['channels'] ?? [])->firstWhere('key', $section['key'] ?? null);
                    @endphp
                    @if ($channelNarrative && ! empty($channelNarrative['paragraphs']))
                        <div class="narrative-block" style="margin-top: 0; margin-bottom: 14px;">
                            <div class="subheading" style="margin-top: 0;">{{ $channelNarrative['title'] ?? 'Qué nos dice este canal' }}</div>
                            @foreach ($channelNarrative['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                            @if (! empty($channelNarrative['bullets']))
                                <ul class="narrative-bullets">
                                    @foreach ($channelNarrative['bullets'] as $bullet)
                                        <li>{{ $bullet }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    @if (($section['kind'] ?? '') === 'organic')
                        @php
                            $channelKpis = $section['kpis'] ?? [];
                            $channelAssetType = match ($section['key'] ?? '') {
                                'facebook' => AssetType::FacebookPage,
                                'instagram' => AssetType::InstagramAccount,
                                default => null,
                            };
                            $channelKpiLabels = $channelAssetType
                                ? MetaMetricLabels::kpiLabelsForAssetType($channelAssetType)
                                : [];
                            $supplementalLabels = $channelAssetType
                                ? MetaMetricLabels::supplementalLabelsForAssetType($channelAssetType)
                                : [];
                            $supplemental = $section['supplemental'] ?? [];
                        @endphp
                        <div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr);">
                            @foreach ($channelKpiLabels as $kpiKey => $kpiMeta)
                                @php $kpiData = $channelKpis[$kpiKey] ?? null; @endphp
                                @if ($kpiData)
                                    <div class="kpi-card">
                                        <div class="label">{{ $kpiMeta['label'] }}</div>
                                        <div class="value">{{ $formatValue($kpiData['current'] ?? 0, $kpiMeta['format']) }}</div>
                                        <div class="delta delta-neutral">{{ $formatDelta($kpiData) }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if (! empty($supplementalLabels))
                            <div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr); margin-top: 12px;">
                                @foreach ($supplementalLabels as $metricKey => $metricMeta)
                                    @if (($supplemental[$metricKey] ?? 0) > 0)
                                        <div class="kpi-card">
                                            <div class="label">{{ $metricMeta['label'] }}</div>
                                            <div class="value">{{ $formatValue($supplemental[$metricKey] ?? 0, $metricMeta['format']) }}</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if (! empty($section['content_breakdown']))
                            <div class="subheading">Desglose por tipo de contenido</div>
                            <table>
                                <thead><tr><th>Tipo</th><th>Alcance</th><th>Posts</th></tr></thead>
                                <tbody>
                                    @foreach ($section['content_breakdown'] as $row)
                                        <tr>
                                            <td>{{ ucfirst($row['type'] ?? '—') }}</td>
                                            <td>{{ number_format($row['reach'] ?? 0) }}</td>
                                            <td>{{ $row['posts'] ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if (! empty($section['top_posts']))
                            <div class="subheading">Top 3 publicaciones por alcance</div>
                            <table>
                                <thead><tr><th>Contenido</th><th>Alcance</th><th>Interacciones</th><th>Fecha</th></tr></thead>
                                <tbody>
                                    @foreach ($section['top_posts'] as $post)
                                        <tr>
                                            <td class="post-preview">{{ \Illuminate\Support\Str::limit($post['content_preview'] ?? 'Post', 70) }}</td>
                                            <td>{{ number_format($post['metrics']['reach'] ?? 0) }}</td>
                                            <td>{{ number_format($post['metrics']['interactions'] ?? ($post['metrics']['engagement'] ?? 0)) }}</td>
                                            <td>{{ isset($post['published_at']) ? \Illuminate\Support\Carbon::parse($post['published_at'])->format('d/m/Y') : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if (! empty($section['top_reels']))
                            <div class="subheading">Top 3 reels por alcance</div>
                            <table>
                                <thead><tr><th>Contenido</th><th>Alcance</th><th>Interacciones</th><th>Fecha</th></tr></thead>
                                <tbody>
                                    @foreach ($section['top_reels'] as $post)
                                        <tr>
                                            <td class="post-preview">{{ \Illuminate\Support\Str::limit($post['content_preview'] ?? 'Reel', 70) }}</td>
                                            <td>{{ number_format($post['metrics']['reach'] ?? 0) }}</td>
                                            <td>{{ number_format($post['metrics']['interactions'] ?? ($post['metrics']['engagement'] ?? 0)) }}</td>
                                            <td>{{ isset($post['published_at']) ? \Illuminate\Support\Carbon::parse($post['published_at'])->format('d/m/Y') : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    @endif

                    @if (($section['kind'] ?? '') === 'paid')
                        @php $paid = $section['paid_summary'] ?? []; @endphp
                        <div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 12px;">
                            <div class="kpi-card">
                                <div class="label">Inversión</div>
                                <div class="value">${{ number_format($paid['spend'] ?? 0, 2) }}</div>
                            </div>
                            <div class="kpi-card">
                                <div class="label">Impresiones</div>
                                <div class="value">{{ number_format($paid['impressions'] ?? 0) }}</div>
                            </div>
                            <div class="kpi-card">
                                <div class="label">Clics</div>
                                <div class="value">{{ number_format($paid['clicks'] ?? 0) }}</div>
                            </div>
                        </div>
                        <table>
                            <thead><tr><th>Campaña</th><th>Activo</th><th>Inversión</th><th>Impresiones</th></tr></thead>
                            <tbody>
                                @forelse ($paid['top_campaigns'] ?? [] as $campaign)
                                    <tr>
                                        <td>{{ $campaign['campaign_name'] ?? '—' }}</td>
                                        <td>{{ $campaign['asset_name'] ?? '—' }}</td>
                                        <td>${{ number_format($campaign['spend'] ?? 0, 2) }}</td>
                                        <td>{{ number_format($campaign['impressions'] ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">Sin campañas en el período.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
        </section>
        @endforeach
    @endif

    @if ($sections['organic'] ?? false)
        @include('reports::pdf.partials.slide-divider', ['title' => 'Rendimiento orgánico', 'kicker' => 'Organic'])
        <section class="slide content-slide">
            @include('reports::pdf.partials.slide-footer')
            <div class="slide-header">
                <h2>Rendimiento orgánico</h2>
            </div>
        <div class="section">
            <div class="two-col">
                <div>
                    <table>
                        <thead><tr><th>Canal</th><th>Alcance</th><th>Impresiones</th></tr></thead>
                        <tbody>
                            @forelse ($analytics['channel_breakdown'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['channel'] ?? '—' }}</td>
                                    <td>{{ number_format($row['reach'] ?? 0) }}</td>
                                    <td>{{ number_format($row['impressions'] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Sin datos orgánicos en el período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>
                    <table>
                        <thead><tr><th>Tipo contenido</th><th>Alcance</th><th>Posts</th></tr></thead>
                        <tbody>
                            @forelse ($analytics['content_breakdown'] ?? [] as $row)
                                <tr>
                                    <td>{{ ucfirst($row['type'] ?? '—') }}</td>
                                    <td>{{ number_format($row['reach'] ?? 0) }}</td>
                                    <td>{{ $row['posts'] ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Sin contenido publicado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </section>
    @endif

    @if ($sections['paid'] ?? false)
        @include('reports::pdf.partials.slide-divider', ['title' => 'Rendimiento pagado', 'kicker' => 'Paid Media'])
        <section class="slide content-slide">
            @include('reports::pdf.partials.slide-footer')
            <div class="slide-header">
                <h2>Rendimiento pagado</h2>
            </div>
        <div class="section">
            <div class="kpi-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 16px;">
                <div class="kpi-card">
                    <div class="label">Inversión total</div>
                    <div class="value">${{ number_format($paid_summary['spend'] ?? 0, 2) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="label">Impresiones</div>
                    <div class="value">{{ number_format($paid_summary['impressions'] ?? 0) }}</div>
                </div>
            </div>
            <table>
                <thead><tr><th>Campaña</th><th>Activo</th><th>Inversión</th><th>Impresiones</th></tr></thead>
                <tbody>
                    @forelse ($paid_summary['top_campaigns'] ?? [] as $campaign)
                        <tr>
                            <td>{{ $campaign['campaign_name'] ?? '—' }}</td>
                            <td>{{ $campaign['asset_name'] ?? '—' }}</td>
                            <td>${{ number_format($campaign['spend'] ?? 0, 2) }}</td>
                            <td>{{ number_format($campaign['impressions'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sin campañas pagadas en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </section>
    @endif

    @if ($sections['top_content'] ?? false)
        @include('reports::pdf.partials.slide-divider', ['title' => 'Top contenidos', 'kicker' => 'Content'])
        <section class="slide content-slide">
            @include('reports::pdf.partials.slide-footer')
            <div class="slide-header">
                <h2>Top contenidos por alcance</h2>
            </div>
        <div class="section">
            <table>
                <thead><tr><th>Contenido</th><th>Tipo</th><th>Alcance</th><th>Engagement</th><th>Fecha</th></tr></thead>
                <tbody>
                    @forelse ($analytics['top_posts']['by_reach'] ?? [] as $post)
                        <tr>
                            <td>{{ \Illuminate\Support\Str::limit($post['content_preview'] ?? 'Post', 60) }}</td>
                            <td>{{ ucfirst($post['post_type'] ?? '—') }}</td>
                            <td>{{ number_format($post['metrics']['reach'] ?? 0) }}</td>
                            <td>{{ number_format($post['metrics']['engagement'] ?? ($post['metrics']['likes'] ?? 0)) }}</td>
                            <td>{{ isset($post['published_at']) ? \Illuminate\Support\Carbon::parse($post['published_at'])->format('Y-m-d') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Sin posts en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </section>
    @endif

    @if (($sections['comparisons'] ?? false) && $comparison)
        @include('reports::pdf.partials.slide-divider', ['title' => 'Orgánico vs pagado', 'kicker' => 'Compare'])
        <section class="slide content-slide">
            @include('reports::pdf.partials.slide-footer')
            <div class="slide-header">
                <h2>Comparación orgánico vs pagado</h2>
            </div>
        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Métrica</th>
                        <th>{{ $comparison['left_label'] ?? 'Orgánico' }}</th>
                        <th>{{ $comparison['right_label'] ?? 'Pagado' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($comparison['rows'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td>
                                @if ($row['format'] === 'currency') ${{ number_format($row['left'], 2) }}
                                @elseif ($row['format'] === 'percent') {{ number_format($row['left'], 2) }}%
                                @else {{ number_format($row['left']) }}
                                @endif
                            </td>
                            <td>
                                @if ($row['format'] === 'currency') ${{ number_format($row['right'], 2) }}
                                @elseif ($row['format'] === 'percent') {{ number_format($row['right'], 2) }}%
                                @else {{ number_format($row['right']) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </section>
    @endif

    @if (($sections['competitors'] ?? false) && ! empty($competitors['comparison_rows']))
        @include('reports::pdf.partials.slide-divider', ['title' => 'Competidores', 'kicker' => 'Benchmark externo'])
        <section class="slide content-slide">
            @include('reports::pdf.partials.slide-footer')
            <div class="slide-header">
                <h2>Competidores (datos manuales)</h2>
            </div>
        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Marca</th>
                        <th>Seguidores</th>
                        <th>Alcance prom.</th>
                        <th>Engagement</th>
                        <th>Fuente</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $competitors['client']['label'] ?? 'Tu marca' }}</td>
                        <td>—</td>
                        <td>{{ number_format($competitors['client']['avg_reach'] ?? 0) }}</td>
                        <td>{{ number_format($competitors['client']['avg_engagement_rate'] ?? 0, 2) }}%</td>
                        <td>API / ingesta</td>
                    </tr>
                    @foreach ($competitors['comparison_rows'] as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ isset($row['followers_count']) ? number_format($row['followers_count']) : '—' }}</td>
                            <td>{{ isset($row['avg_reach']) ? number_format($row['avg_reach']) : '—' }}</td>
                            <td>{{ isset($row['avg_engagement_rate']) ? number_format($row['avg_engagement_rate'], 2).'%' : '—' }}</td>
                            <td>{{ $row['data_source_note'] ?? 'Manual' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if (! empty($competitor_insight['text']))
                <div class="narrative-block" style="margin-top: 16px;">
                    <div class="subheading" style="margin-top: 0;">Análisis competitivo</div>
                    @if (! ($competitor_insight['is_reviewed'] ?? false))
                        <p class="text-xs text-sp-muted" style="color:#92400e;margin-bottom:8px;">
                            Borrador IA sin revisión final — validar antes de compartir con el cliente.
                        </p>
                    @endif
                    @foreach (preg_split('/\R\R+/', trim($competitor_insight['text'])) as $paragraph)
                        @if ($paragraph !== '')
                            <p>{{ $paragraph }}</p>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        </section>
    @endif

    @if (! empty($appendix))
        @include('reports::pdf.partials.slide-divider', [
            'title' => $appendix['title'] ?? 'Anexo tabular',
            'kicker' => 'Data appendix',
        ])
        <section class="slide content-slide">
            @include('reports::pdf.partials.slide-footer')
            <div class="slide-header">
                <h2>{{ $appendix['title'] ?? 'Anexo tabular' }}</h2>
            </div>
        <div class="section">

            @if (! empty($appendix['summary']['rows']))
                <div class="subheading">Resumen general del período</div>
                <table class="appendix-table">
                    <thead>
                        <tr>
                            @foreach ($appendix['summary']['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appendix['summary']['rows'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['current'] }}</td>
                                <td>{{ $row['previous'] }}</td>
                                <td>{{ $row['change'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @foreach ($appendix['assets'] ?? [] as $assetTable)
                <div class="subheading">{{ $assetTable['channel'] }} — {{ $assetTable['asset_name'] }}</div>
                <table class="appendix-table">
                    <thead>
                        <tr>
                            @foreach ($assetTable['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assetTable['rows'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['current'] }}</td>
                                <td>{{ $row['previous'] }}</td>
                                <td>{{ $row['change'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            @if (! empty($appendix['campaigns']['rows']))
                <div class="subheading">Detalle de campañas pagadas</div>
                <table class="appendix-table">
                    <thead>
                        <tr>
                            @foreach ($appendix['campaigns']['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appendix['campaigns']['rows'] as $row)
                            <tr>
                                <td>{{ $row['campaign'] }}</td>
                                <td>{{ $row['asset'] }}</td>
                                <td>{{ $row['channel'] }}</td>
                                <td>${{ number_format($row['spend'], 2) }}</td>
                                <td>{{ number_format($row['impressions']) }}</td>
                                <td>{{ number_format($row['reach']) }}</td>
                                <td>{{ number_format($row['clicks']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (! empty($appendix['posts']['rows']))
                <div class="subheading">Detalle de publicaciones orgánicas</div>
                <table class="appendix-table">
                    <thead>
                        <tr>
                            @foreach ($appendix['posts']['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appendix['posts']['rows'] as $row)
                            <tr>
                                <td>{{ $row['channel'] }}</td>
                                <td>{{ $row['asset'] }}</td>
                                <td>{{ $row['type'] }}</td>
                                <td>{{ $row['published_at'] }}</td>
                                <td>{{ number_format($row['reach']) }}</td>
                                <td>{{ number_format($row['impressions']) }}</td>
                                <td>{{ number_format($row['interactions']) }}</td>
                                <td>{{ number_format($row['link_clicks']) }}</td>
                                <td>{{ number_format($row['video_views']) }}</td>
                                <td>{{ $row['preview'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (! empty($appendix['daily_reach']['rows']))
                <div class="subheading">Alcance diario</div>
                <table class="appendix-table">
                    <thead>
                        <tr>
                            @foreach ($appendix['daily_reach']['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appendix['daily_reach']['rows'] as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ number_format($row['organic']) }}</td>
                                <td>{{ number_format($row['paid']) }}</td>
                                <td>{{ number_format($row['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        </section>
    @endif

@include('reports::pdf.partials.slide-divider', [
    'title' => 'Gracias',
    'kicker' => 'SocialPulse',
    'subtitle' => $workspace['name'].' · '.$report['period']['from'].' — '.$report['period']['to'],
])
</body>
</html>
