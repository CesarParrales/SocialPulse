<section class="slide slide-cover">
    <div class="slide-cover-inner">
        @if ($branding['logo_data_uri'])
            <img src="{{ $branding['logo_data_uri'] }}" alt="Logo" class="cover-logo">
        @endif
        <p class="cover-kicker">Informe de rendimiento</p>
        <h1 class="cover-title">{{ $report['title'] }}</h1>
        <p class="cover-brand">{{ $workspace['name'] }}</p>
        <div class="cover-meta">
            <div><span>Período</span> {{ $report['period']['from'] }} — {{ $report['period']['to'] }}</div>
            <div><span>Días analizados</span> {{ $report['period']['days'] }}</div>
            <div><span>Generado</span> {{ $report['generated_at'] }}</div>
        </div>
    </div>
    <div class="slide-footer-bar slide-footer-bar--light">
        SocialPulse · {{ $workspace['name'] }}
    </div>
</section>
