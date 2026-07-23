@php
    $activeNav = $activeNav ?? '';
    $liveSurvey = null;

    $buildSurveyContext = static function (array $round, ?string $fallbackUrl = null): ?array {
        $options = isset($round['options']) && is_array($round['options']) ? $round['options'] : [];
        $leader = collect($options)
            ->sortByDesc(fn (array $option): int => (int) ($option['vote_count'] ?? 0))
            ->first();

        $territory = is_array($round['territory'] ?? null) ? $round['territory'] : [];
        $scopeType = (string) ($territory['scope_type'] ?? 'district');
        $scopeLabel = match ($scopeType) {
            'region' => 'Región',
            'province' => 'Provincia',
            default => 'Distrito',
        };

        return [
            'title' => (string) ($round['titulo'] ?? $round['title'] ?? 'Encuesta activa'),
            'label' => trim($scopeLabel . ' ' . (string) ($round['scope_label'] ?? ($territory['name'] ?? ''))),
            'votes' => (int) ($round['total_votes'] ?? 0),
            'leader' => is_array($leader) ? (string) ($leader['candidate']['name'] ?? $leader['candidate_name'] ?? '') : '',
            'url' => (string) ($round['target_url'] ?? $fallbackUrl ?? route('home')),
        ];
    };

    if (isset($activeRound) && is_array($activeRound)) {
        $liveSurvey = $buildSurveyContext($activeRound, request()->fullUrl());
    } elseif (!empty($encuestas) && is_array($encuestas[0] ?? null)) {
        $encuesta = $encuestas[0];
        $liveSurvey = [
            'title' => (string) ($encuesta['encuestadora_nombre'] ?? 'Encuesta activa'),
            'label' => 'Sondeo en curso',
            'votes' => (int) ($encuesta['total_votes'] ?? 0),
            'leader' => '',
            'url' => route('home'),
        ];
    }

    $surveyCtaUrl = $liveSurvey['url'] ?? route('home');
    $surveyCtaText = $liveSurvey ? 'Ver encuesta activa' : 'Ir al inicio';
    $navClass = static function (string $key) use ($activeNav): string {
        return $activeNav === $key
            ? 'text-brand-green border-b-2 border-brand-green pb-1'
            : 'hover:text-brand-blue/70 transition-colors';
    };
    $navClassMobile = static function (string $key) use ($activeNav): string {
        return $activeNav === $key
            ? 'block px-6 py-4 bg-brand-surface text-brand-green'
            : 'block px-6 py-4 hover:bg-brand-surface';
    };
@endphp
<header class="bg-brand-card w-full border-b border-brand-border sticky top-0 z-40 shadow-sm backdrop-blur">
    @if ($liveSurvey)
        <div class="bg-brand-blue text-white border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 py-2.5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest">
                    <span class="inline-block w-2 h-2 rounded-full bg-brand-green animate-pulse"></span>
                    Votación actual
                </div>
                <div class="min-w-0 flex-1 text-sm md:text-center">
                    <span class="font-semibold truncate inline-block max-w-full">{{ $liveSurvey['label'] }}</span>
                    <span class="mx-2 text-white/50">·</span>
                    <span class="text-white/75 truncate inline-block max-w-full">{{ $liveSurvey['title'] }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-sm font-bold text-brand-green tabular-nums">{{ number_format((int) ($liveSurvey['votes'] ?? 0)) }} votos</div>
                        @if (!empty($liveSurvey['leader']))
                            <div class="text-[11px] text-white/70 truncate max-w-[180px]">Lidera {{ $liveSurvey['leader'] }}</div>
                        @endif
                    </div>
                    <a href="{{ $surveyCtaUrl }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white text-brand-blue font-bold px-4 py-2 text-sm hover:bg-brand-surface transition-colors">
                        {{ $surveyCtaText }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="flex flex-col cursor-pointer group min-w-0" aria-label="Ir al inicio">
            <div class="text-2xl md:text-3xl font-extrabold tracking-tight leading-none mb-0.5">
                <span class="text-brand-blue">Encuestas</span><span class="text-brand-text">electorales</span><span class="text-brand-green">.pe</span>
            </div>
            <div class="text-[10px] md:text-xs font-bold text-brand-muted tracking-widest uppercase">
                Votos actuales · Perú 2026
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-8 text-[13px] font-bold text-brand-blue tracking-widest uppercase">
            <a href="{{ url('/') }}" class="{{ $navClass('inicio') }}">Inicio</a>
            <a href="{{ $surveyCtaUrl }}" class="hover:text-brand-blue/70 transition-colors flex items-center gap-1">
                Encuestas activas <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        </nav>

        <div class="flex items-center gap-4">
            <button id="mobile-menu-btn" type="button" class="md:hidden text-brand-blue text-2xl p-2" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden absolute top-full left-0 w-full bg-brand-card border-b border-brand-border shadow-md">
        <nav class="flex flex-col text-sm font-bold text-brand-blue uppercase divide-y divide-gray-100">
            <a href="{{ url('/') }}" class="{{ $navClassMobile('inicio') }}">Inicio</a>
            <a href="{{ $surveyCtaUrl }}" class="{{ $navClassMobile('sondeos') }}">Encuestas activas</a>
        </nav>
    </div>
</header>
