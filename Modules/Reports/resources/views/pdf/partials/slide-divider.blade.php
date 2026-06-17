<section class="slide slide-divider">
    <div class="slide-divider-inner">
        <p class="divider-kicker">{{ $kicker ?? 'Sección' }}</p>
        <h2>{{ $title }}</h2>
        @if (! empty($subtitle))
            <p class="divider-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
</section>
