@extends('layouts.public')

@section('content')
    @php
        $hasExplicitSelection = request()->filled('scope') && request()->filled('slug');
        $initialSelectedRoundData = $hasExplicitSelection && is_array($selectedRound ?? null) ? $selectedRound : null;
        $selectedRoundData = $hasExplicitSelection && is_array($selectedRound ?? null) ? $selectedRound : null;
        $selectedTerritory = is_array($selectedRoundData['territory'] ?? null) ? $selectedRoundData['territory'] : null;
        $selectedOptions = is_array($selectedRoundData['top_options'] ?? null) ? $selectedRoundData['top_options'] : [];
        $selectedTotalVotes = (int) ($selectedRoundData['total_votes'] ?? 0);
        $displaySelectedRound = $hasExplicitSelection ? $selectedRoundData : null;
        $displaySelectedTerritory = $hasExplicitSelection ? $selectedTerritory : null;
        $displaySelectedOptions = $hasExplicitSelection ? array_slice(array_pad($selectedOptions, 5, null), 0, 5) : [];
        $displaySelectedTotalVotes = $hasExplicitSelection ? $selectedTotalVotes : 0;
        $selectedRoute = is_array($selectedTerritory)
            ? route('surveys.scope', ['scope' => (string) ($selectedTerritory['scope_type'] ?? 'district'), 'slug' => (string) ($selectedTerritory['slug'] ?? '')])
            : route('home');
    @endphp

    <script type="application/json" id="home-initial-selection">
        @json($initialSelectedRoundData)
    </script>

    <section class="bg-brand-blue text-white pb-16 pt-12 px-4 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-grid-pattern"></div>
        <div class="max-w-6xl mx-auto relative z-10 flex flex-col items-center text-center">
            <div class="max-w-4xl mx-auto">
                <span class="inline-block py-1.5 px-4 rounded-full bg-brand-green/20 text-brand-green font-semibold text-[11px] uppercase tracking-widest mb-6 border border-brand-green/30">
                    <span class="inline-block w-2 h-2 rounded-full bg-brand-green mr-1 animate-pulse"></span>
                    Votos actuales en vivo
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold mb-6 leading-tight tracking-tight mx-auto">
                    Todas las votaciones activas en una sola portada.
                </h1>
                <p class="text-lg md:text-xl text-white/80 mb-10 max-w-2xl font-medium mx-auto">
                    Elige una encuesta, revisa cómo va sin votar y entra al detalle cuando quieras participar.
                </p>
            </div>

            <div class="grid w-full max-w-4xl grid-cols-1 gap-3 text-left md:grid-cols-3">
                <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur h-full">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Rondas cargadas</div>
                    <div class="text-2xl font-serif font-bold">{{ count($rondasAbiertas) }}</div>
                    <div class="text-sm text-white/75 mt-1">Rondas visibles hoy</div>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur h-full">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Vista elegida</div>
                    <div id="home-selected-territory" class="text-2xl font-serif font-bold">{{ $displaySelectedTerritory['name'] ?? 'Sin selección' }}</div>
                    <div id="home-selected-round" class="text-sm text-white/75 mt-1">{{ $displaySelectedRound['round']['title'] ?? 'Selecciona una votación' }}</div>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur h-full">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Acción</div>
                    <a href="#encuestas-activas" class="inline-flex items-center gap-2 text-2xl font-serif font-bold text-white">
                        Ver todo <i class="fas fa-arrow-down text-sm"></i>
                    </a>
                    <div class="text-sm text-white/75 mt-1">Resultados y acceso al voto</div>
                </div>
            </div>
        </div>
    </section>

    <section id="encuestas-activas" class="max-w-6xl mx-auto px-4 py-12 md:py-16">
        <div class="space-y-6">
            <div class="border-b border-brand-border pb-3">
                <h2 class="text-2xl font-serif font-bold text-brand-blue flex items-center">
                    <i class="fas fa-chart-line text-brand-green mr-2.5"></i> Encuestas Web
                </h2>
                <p class="text-sm text-brand-muted mt-1 max-w-3xl">Busca una votación, ordénala y abre el detalle para ver el conteo actual.</p>
            </div>

            <div class="bg-brand-card border border-brand-border rounded-2xl p-4 md:p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-[0.22em] text-brand-muted mb-1">Controles</div>
                        <p class="text-sm text-brand-muted max-w-2xl leading-relaxed">Usa el buscador para ubicar una fila y contrae la lista cuando ya elegiste una votación.</p>
                    </div>
                    <button
                        type="button"
                        id="home-voting-toggle"
                        class="inline-flex w-full sm:w-auto min-w-[13rem] items-center justify-center gap-2 rounded-2xl border border-brand-border bg-white px-6 py-4 text-sm sm:text-base font-extrabold tracking-wide text-brand-blue shadow-sm hover:border-brand-blue/30 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue focus-visible:ring-offset-2 transition-all"
                        aria-expanded="true"
                        aria-controls="home-voting-body home-voting-list"
                    >
                        <i class="fas fa-chevron-up text-[11px]" aria-hidden="true"></i>
                        <span>Ocultar listado</span>
                    </button>
                </div>

                <div id="home-voting-body" class="{{ $hasExplicitSelection ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-12">
                        <label class="block lg:col-span-6">
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Buscar</span>
                            <div class="relative">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-brand-muted text-sm"></i>
                                <input
                                    id="home-voting-search"
                                    type="search"
                                    placeholder="Buscar región, provincia, distrito, partido o nombre de la candidatura"
                                    class="w-full rounded-xl border border-brand-border bg-white pl-11 pr-4 py-3 text-sm font-semibold text-brand-blue placeholder:text-brand-muted focus:border-brand-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue focus-visible:ring-offset-2"
                                >
                            </div>
                        </label>
                        <label class="block lg:col-span-3">
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Orden</span>
                            <select id="home-voting-sort" class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-semibold text-brand-blue focus:border-brand-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue focus-visible:ring-offset-2">
                                <option value="geo-asc">ASC</option>
                                <option value="geo-desc">DESC</option>
                            </select>
                        </label>
                        <label class="block lg:col-span-3">
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Ronda</span>
                            <select id="home-voting-round" class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-semibold text-brand-blue focus:border-brand-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue focus-visible:ring-offset-2">
                                <option value="all">Todas</option>
                                <option value="1">Ronda 1</option>
                                <option value="2">Ronda 2</option>
                            </select>
                        </label>
                        <div class="flex items-end lg:col-span-12">
                            <button type="button" id="home-voting-reset" class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-bold text-brand-blue hover:border-brand-blue/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue focus-visible:ring-offset-2 transition-colors">
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($rondasAbiertas) === 0)
                <div class="bg-blue-50/50 rounded-2xl p-8 border border-blue-100/50 text-center">
                    <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-2xl text-brand-blue mx-auto mb-4 shadow-sm border border-blue-100">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h3 class="font-bold text-lg text-brand-blue mb-2">No hay votaciones publicadas</h3>
                    <p class="text-sm text-brand-muted mb-0 max-w-sm mx-auto leading-relaxed">
                        Cuando haya una ronda publicada, aparecerá aquí en formato de fila con sus votos actuales.
                    </p>
                </div>
            @else
                <div id="home-voting-list" class="space-y-3 {{ $hasExplicitSelection ? 'hidden' : '' }}">
                    @foreach ($rondasAbiertas as $ronda)
                        @php
                            $isSelected = ($selectedTerritory['slug'] ?? null) === ($ronda['territory_slug'] ?? null)
                                && ($selectedTerritory['scope_type'] ?? null) === ($ronda['territory_scope'] ?? null);
                            $territoryAncestors = is_array($ronda['territory_ancestors'] ?? null) ? $ronda['territory_ancestors'] : [];
                            $territoryCode = (string) ($ronda['territory_code'] ?? '');
                            $territoryScopeRank = (int) ($ronda['territory_scope_rank'] ?? 3);
                            $roundNumber = (int) ($ronda['round_number'] ?? 1);
                        @endphp
                        <button
                            type="button"
                            data-voting-row
                            data-voting-payload="{{ base64_encode(json_encode($ronda, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}"
                            data-target-url="{{ route('home', ['scope' => $ronda['territory_scope'], 'slug' => $ronda['territory_slug']]) }}"
                            data-scope-rank="{{ $territoryScopeRank }}"
                            data-territory-code="{{ $territoryCode }}"
                            data-round-number="{{ $roundNumber }}"
                            class="block w-full rounded-2xl border text-left transition-all {{ $isSelected ? 'bg-brand-blue text-white border-brand-blue shadow-lg' : 'bg-brand-card border-brand-border hover:shadow-lg hover:border-brand-blue/30' }} motion-safe:active:scale-[0.99]"
                            @if ($isSelected) aria-current="page" @endif
                        >
                            <div class="grid grid-cols-1 gap-3 px-4 py-4 sm:px-5 sm:py-5 md:grid-cols-12 md:items-center">
                                <div class="md:col-span-5 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="text-[10px] uppercase tracking-widest font-bold px-2.5 py-1 rounded-full {{ $isSelected ? 'bg-white/15 text-white' : 'bg-brand-surface text-brand-muted border border-brand-border' }}">
                                            {{ $ronda['scope_label'] }}
                                        </span>
                                        <span class="text-[10px] uppercase tracking-widest font-bold px-2.5 py-1 rounded-full {{ $isSelected ? 'bg-white/15 text-white' : 'bg-brand-surface text-brand-muted border border-brand-border' }}">
                                            Ronda {{ $roundNumber }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-lg sm:text-xl leading-snug {{ $isSelected ? 'text-white' : 'text-brand-blue' }}">
                                        {{ $ronda['titulo'] }}
                                    </h3>
                                    <div class="mt-2 text-[11px] sm:text-xs font-semibold uppercase tracking-wider {{ $isSelected ? 'text-white/70' : 'text-brand-muted' }} break-words">
                                        @if (! empty($territoryAncestors))
                                            {{ implode(' · ', array_map(static fn (array $ancestor): string => (string) ($ancestor['name'] ?? ''), $territoryAncestors)) }} ·
                                        @endif
                                        {{ $territoryCode }}
                                    </div>
                                </div>
                                <div class="md:col-span-2 rounded-2xl px-4 py-3 {{ $isSelected ? 'bg-white/10 border border-white/10' : 'bg-brand-surface border border-brand-border' }}">
                                    <div class="text-[10px] uppercase tracking-widest font-bold {{ $isSelected ? 'text-white/60' : 'text-brand-muted' }}">Votos</div>
                                    <div class="text-xl sm:text-2xl font-bold {{ $isSelected ? 'text-white' : 'text-brand-blue' }} tabular-nums">{{ number_format((int) ($ronda['total_votes'] ?? 0)) }}</div>
                                </div>
                                <div class="md:col-span-5 flex md:justify-end">
                                    <span class="inline-flex w-full md:w-auto items-center justify-center gap-2 rounded-full px-3 py-2 text-[10px] font-bold uppercase tracking-widest {{ $isSelected ? 'bg-white text-brand-blue' : 'bg-brand-blue text-white' }}">
                                        {{ $isSelected ? 'Elegida' : 'Ver detalle' }}
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
                <div id="home-voting-empty" class="hidden bg-brand-card border border-brand-border rounded-2xl p-6 sm:p-8 text-center">
                    <div class="w-14 h-14 bg-brand-surface text-brand-blue rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-filter"></i>
                    </div>
                    <h3 class="font-bold text-lg text-brand-blue mb-2">No hay filas que coincidan con los filtros</h3>
                    <p class="text-sm text-brand-muted mb-0 max-w-sm mx-auto leading-relaxed">
                        Cambia el orden o limpia los filtros para ver las votaciones disponibles.
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section id="votacion-seleccionada" class="max-w-6xl mx-auto px-4 pb-16 scroll-mt-28">
        <div class="border-b border-brand-border pb-3 mb-6">
            <div>
                <h2 class="text-2xl font-serif font-bold text-brand-blue flex items-center">
                    <i class="fas fa-square-poll-vertical text-brand-green mr-2.5"></i> Votación seleccionada
                </h2>
                <p class="text-sm text-brand-muted mt-1">Aquí ves el conteo actual sin haber votado aún.</p>
            </div>
        </div>

        <div id="selected-vote-empty" class="{{ $hasExplicitSelection && is_array($displaySelectedRound) && is_array($displaySelectedTerritory) ? 'hidden' : '' }} bg-brand-card border border-brand-border rounded-2xl p-8 text-center">
                <div class="w-14 h-14 bg-brand-surface text-brand-blue rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-hand-pointer"></i>
                </div>
                <h3 class="font-bold text-lg text-brand-blue mb-2">Selecciona una votación arriba</h3>
                <p class="text-sm text-brand-muted mb-0 max-w-sm mx-auto leading-relaxed">
                    Cuando elijas una fila, aquí aparecerá el conteo actual sin necesidad de votar.
                </p>
        </div>

        <div id="selected-vote-content" class="{{ $hasExplicitSelection && is_array($displaySelectedRound) && is_array($displaySelectedTerritory) ? '' : 'hidden' }}">
            @if (! is_array($displaySelectedRound) || ! is_array($displaySelectedTerritory))
            <div class="bg-brand-card border border-brand-border rounded-2xl p-8 text-sm text-brand-muted">
                No hay una votación destacada para mostrar.
            </div>
            @else
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                <section class="lg:col-span-5 bg-brand-card border border-brand-border rounded-2xl p-6 md:p-8 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div id="selected-vote-scope" class="text-[10px] uppercase tracking-widest text-brand-muted font-bold mb-2">
                                    {{ $displaySelectedTerritory['scope_type'] === 'region' ? 'Región' : ($displaySelectedTerritory['scope_type'] === 'province' ? 'Provincia' : 'Distrito') }}
                                </div>
                                <h3 id="selected-vote-territory" class="text-3xl font-serif font-bold text-brand-blue leading-tight">
                                    {{ $displaySelectedTerritory['name'] ?? 'Territorio' }}
                                </h3>
                                <p id="selected-vote-title" class="text-sm text-brand-muted mt-2">
                                    {{ $displaySelectedRound['round']['title'] ?? 'Encuesta activa' }}
                                </p>
                            </div>
                            <span id="selected-vote-state" class="inline-flex items-center gap-2 rounded-full bg-[#e6f8f0] text-brand-greenText px-3 py-1 text-[10px] font-bold uppercase tracking-widest shrink-0">
                                <span class="w-2 h-2 rounded-full bg-brand-green animate-pulse"></span>
                                {{ $displaySelectedRound['state'] === 'active' ? 'Abierta' : 'En revisión' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 pt-2">
                            <div class="rounded-xl bg-brand-surface border border-brand-border px-4 py-3">
                                <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold">Votos totales</div>
                                <div id="selected-vote-total" class="text-2xl font-bold text-brand-blue tabular-nums">{{ number_format($displaySelectedTotalVotes) }}</div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <a id="selected-vote-action" href="{{ $selectedRoute }}" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm">
                                Ir a votar
                            </a>
                        </div>
                    </div>
                </section>

                <section class="lg:col-span-7 bg-brand-card border border-brand-border rounded-2xl p-6 md:p-8 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 border-b border-brand-border pb-5 mb-5">
                        <div>
                            <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold mb-2">Resultados actuales</div>
                            <h3 class="text-2xl font-serif font-bold text-brand-blue">Conteo por candidatura</h3>
                        </div>
                        <div class="text-right">
                            <div id="selected-vote-total-2" class="text-3xl font-bold text-brand-blue tabular-nums">{{ number_format($displaySelectedTotalVotes) }}</div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted">votos emitidos</div>
                        </div>
                    </div>

                    <div id="selected-vote-zero" class="{{ $displaySelectedTotalVotes === 0 ? '' : 'hidden' }} rounded-xl border border-dashed border-brand-border bg-brand-surface px-5 py-6 text-sm text-brand-muted leading-relaxed">
                            Aún no hay votos registrados en esta ronda. Cuando empiece a entrar participación, verás aquí el conteo por candidato y partido.
                    </div>

                    <div id="selected-vote-options" class="{{ $displaySelectedTotalVotes === 0 ? 'hidden' : '' }} space-y-4">
                            @foreach ($displaySelectedOptions as $slot => $option)
                                @php
                                    $isPlaceholder = ! is_array($option);
                                    $voteCount = $isPlaceholder ? 0 : (int) ($option['vote_count'] ?? 0);
                                    $voteShare = $displaySelectedTotalVotes > 0 ? ($voteCount / $displaySelectedTotalVotes) * 100 : 0;
                                @endphp
                                <div class="rounded-xl border border-brand-border bg-white p-4" data-selected-option-slot="{{ $slot }}">
                                    <div class="flex items-start justify-between gap-4 mb-2">
                                        <div class="min-w-0">
                                            <div class="font-bold text-brand-text truncate" data-selected-option-name>{{ $isPlaceholder ? 'Sin candidatura' : ($option['candidate']['name'] ?? '') }}</div>
                                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted mt-1 truncate" data-selected-option-party>
                                                {{ $isPlaceholder ? 'Espacio reservado' : ($option['party']['name'] ?? '') }}
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <div class="text-xl font-bold text-brand-blue tabular-nums" data-selected-option-votes>{{ number_format($voteCount) }}</div>
                                            <div class="text-[10px] font-semibold uppercase tracking-wider text-brand-muted">votos</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs font-semibold text-brand-muted mb-2">
                                        <span data-selected-option-label>{{ $isPlaceholder ? 'Pendiente' : ($option['candidate']['name'] ?? '') }}</span>
                                        <span data-selected-option-share>{{ number_format($voteShare, 1) }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-brand-surface overflow-hidden">
                                        <div class="h-full rounded-full bg-brand-blue" data-selected-option-bar style="width: {{ $voteShare }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                    </div>
                </section>
            </div>
            @endif
        </div>
    </section>
@endsection
