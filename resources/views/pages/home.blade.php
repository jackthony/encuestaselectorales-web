@extends('layouts.public')

@section('content')
    @php
        $hasExplicitSelection = request()->filled('scope') && request()->filled('slug');
        $selectedRoundData = is_array($selectedRound ?? null) ? $selectedRound : null;
        $selectedTerritory = is_array($selectedRoundData['territory'] ?? null) ? $selectedRoundData['territory'] : null;
        $selectedOptions = is_array($selectedRoundData['top_options'] ?? null) ? $selectedRoundData['top_options'] : [];
        $selectedTotalVotes = (int) ($selectedRoundData['total_votes'] ?? 0);
        $selectedLeaderName = (string) ($selectedRoundData['leader_name'] ?? 'Candidatura');
        $selectedLeaderVotes = (int) ($selectedRoundData['leader_votes'] ?? 0);
        $displaySelectedRound = $hasExplicitSelection ? $selectedRoundData : null;
        $displaySelectedTerritory = $hasExplicitSelection ? $selectedTerritory : null;
        $displaySelectedOptions = $hasExplicitSelection ? $selectedOptions : [];
        $displaySelectedTotalVotes = $hasExplicitSelection ? $selectedTotalVotes : 0;
        $displaySelectedLeaderName = $hasExplicitSelection ? $selectedLeaderName : 'Sin selección';
        $displaySelectedLeaderVotes = $hasExplicitSelection ? $selectedLeaderVotes : 0;
        $selectedRoute = is_array($selectedTerritory)
            ? route('surveys.scope', ['scope' => (string) ($selectedTerritory['scope_type'] ?? 'district'), 'slug' => (string) ($selectedTerritory['slug'] ?? '')])
            : route('home');
    @endphp

    <section class="bg-brand-blue text-white pb-16 pt-12 px-4 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-grid-pattern"></div>
        <div class="max-w-6xl mx-auto relative z-10">
            <div class="max-w-4xl">
                <span class="inline-block py-1.5 px-4 rounded-full bg-brand-green/20 text-brand-green font-semibold text-[11px] uppercase tracking-widest mb-6 border border-brand-green/30">
                    <span class="inline-block w-2 h-2 rounded-full bg-brand-green mr-1 animate-pulse"></span>
                    Votos actuales en vivo
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold mb-6 leading-tight tracking-tight">
                    Todas las votaciones activas en una sola portada.
                </h1>
                <p class="text-lg md:text-xl text-white/80 mb-10 max-w-2xl font-medium">
                    Elige una encuesta, revisa cómo va sin votar y entra al detalle cuando quieras participar.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-left max-w-4xl">
                <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Rondas cargadas</div>
                    <div class="text-2xl font-serif font-bold">{{ count($rondasAbiertas) }}</div>
                    <div class="text-sm text-white/75 mt-1">Rondas visibles hoy</div>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Vista elegida</div>
                    <div class="text-2xl font-serif font-bold">{{ $displaySelectedTerritory['name'] ?? 'Sin selección' }}</div>
                    <div class="text-sm text-white/75 mt-1">{{ $displaySelectedRound['round']['title'] ?? 'Selecciona una votación' }}</div>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Acción</div>
                    <a href="#encuestas-activas" class="inline-flex items-center gap-2 text-2xl font-serif font-bold text-white">
                        Ver todo <i class="fas fa-arrow-down text-sm"></i>
                    </a>
                    <div class="text-sm text-white/75 mt-1">Resultados y acceso al voto</div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 -mt-8 relative z-20">
        @include('partials.share-actions')
    </section>

    <section id="encuestas-activas" class="max-w-6xl mx-auto px-4 py-12 md:py-16">
        <div class="space-y-6">
            <div class="border-b border-brand-border pb-3">
                <h2 class="text-2xl font-serif font-bold text-brand-blue flex items-center">
                    <i class="fas fa-chart-line text-brand-green mr-2.5"></i> Encuestas Web
                </h2>
                <p class="text-sm text-brand-muted mt-1">Ordena por territorio, filtra por estado y ronda, y abre una votación para verla en detalle.</p>
            </div>

            <div class="bg-brand-card border border-brand-border rounded-2xl p-4 md:p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-1">Controles</div>
                        <p class="text-sm text-brand-muted">
                            <span class="font-semibold text-brand-blue">Ordena y filtra por ronda</span> o minimiza la lista cuando ya elegiste una votación.
                        </p>
                    </div>
                    <button type="button" id="home-voting-toggle" class="rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-bold text-brand-blue hover:border-brand-blue/30 transition-colors">
                        Minimizar listado
                    </button>
                </div>

                <div id="home-voting-body" class="{{ $hasExplicitSelection ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <label class="block">
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Orden</span>
                            <select id="home-voting-sort" class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-semibold text-brand-blue focus:border-brand-blue focus:outline-none">
                                <option value="geo-asc">Jerarquía territorial: ascendente</option>
                                <option value="geo-desc">Jerarquía territorial: descendente</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Ronda</span>
                            <select id="home-voting-round" class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-semibold text-brand-blue focus:border-brand-blue focus:outline-none">
                                <option value="all">Todas</option>
                                <option value="1">Ronda 1</option>
                                <option value="2">Ronda 2</option>
                            </select>
                        </label>
                        <div class="flex items-end">
                            <button type="button" id="home-voting-reset" class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-bold text-brand-blue hover:border-brand-blue/30 transition-colors">
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-brand-muted">
                        La jerarquía usa región, provincia y distrito; la ronda filtra entre Ronda 1 y Ronda 2.
                    </p>
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
                        <a
                            href="{{ route('home', ['scope' => $ronda['territory_scope'], 'slug' => $ronda['territory_slug']]) }}#votacion-seleccionada"
                            data-voting-row
                            data-scope-rank="{{ $territoryScopeRank }}"
                            data-territory-code="{{ $territoryCode }}"
                            data-round-number="{{ $roundNumber }}"
                            class="block rounded-2xl border transition-all {{ $isSelected ? 'bg-brand-blue text-white border-brand-blue shadow-lg' : 'bg-brand-card border-brand-border hover:shadow-lg hover:border-brand-blue/30' }}"
                            @if ($isSelected) aria-current="page" @endif
                        >
                            <div class="grid grid-cols-1 gap-4 px-5 py-5 md:grid-cols-12 md:items-center">
                                <div class="md:col-span-5 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="text-[10px] uppercase tracking-widest font-bold px-2.5 py-1 rounded-full {{ $isSelected ? 'bg-white/15 text-white' : 'bg-brand-surface text-brand-muted border border-brand-border' }}">
                                            {{ $ronda['scope_label'] }}
                                        </span>
                                        <span class="text-[10px] uppercase tracking-widest font-bold px-2.5 py-1 rounded-full {{ $isSelected ? 'bg-white/15 text-white' : 'bg-brand-surface text-brand-muted border border-brand-border' }}">
                                            Ronda {{ $roundNumber }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-xl leading-tight {{ $isSelected ? 'text-white' : 'text-brand-blue' }}">
                                        {{ $ronda['titulo'] }}
                                    </h3>
                                    <div class="mt-2 text-xs font-semibold uppercase tracking-wider {{ $isSelected ? 'text-white/70' : 'text-brand-muted' }}">
                                        @if (! empty($territoryAncestors))
                                            {{ implode(' · ', array_map(static fn (array $ancestor): string => (string) ($ancestor['name'] ?? ''), $territoryAncestors)) }} ·
                                        @endif
                                        {{ $territoryCode }}
                                    </div>
                                </div>
                                <div class="md:col-span-2 rounded-2xl px-4 py-3 {{ $isSelected ? 'bg-white/10 border border-white/10' : 'bg-brand-surface border border-brand-border' }}">
                                    <div class="text-[10px] uppercase tracking-widest font-bold {{ $isSelected ? 'text-white/60' : 'text-brand-muted' }}">Votos</div>
                                    <div class="text-2xl font-bold {{ $isSelected ? 'text-white' : 'text-brand-blue' }} tabular-nums">{{ number_format((int) ($ronda['total_votes'] ?? 0)) }}</div>
                                </div>
                                <div class="md:col-span-3 rounded-2xl px-4 py-3 {{ $isSelected ? 'bg-white/10 border border-white/10' : 'bg-brand-surface border border-brand-border' }}">
                                    <div class="text-[10px] uppercase tracking-widest font-bold {{ $isSelected ? 'text-white/60' : 'text-brand-muted' }}">Lidera</div>
                                    <div class="text-base font-bold {{ $isSelected ? 'text-white' : 'text-brand-blue' }} truncate">{{ $ronda['leader_name'] ?? 'Sin datos' }}</div>
                                    <div class="text-xs font-semibold uppercase tracking-wider {{ $isSelected ? 'text-white/60' : 'text-brand-muted' }} mt-1">
                                        {{ number_format((int) ($ronda['leader_votes'] ?? 0)) }} votos
                                    </div>
                                </div>
                                <div class="md:col-span-2 flex md:justify-end">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-[10px] font-bold uppercase tracking-widest {{ $isSelected ? 'bg-white text-brand-blue' : 'bg-brand-blue text-white' }}">
                                        {{ $isSelected ? 'Elegida' : 'Abrir' }}
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div id="home-voting-empty" class="hidden bg-brand-card border border-brand-border rounded-2xl p-8 text-center">
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
            <h2 class="text-2xl font-serif font-bold text-brand-blue flex items-center">
                <i class="fas fa-square-poll-vertical text-brand-green mr-2.5"></i> Votación seleccionada
            </h2>
            <p class="text-sm text-brand-muted mt-1">Aquí ves el conteo actual sin haber votado aún.</p>
        </div>

        @if (! $hasExplicitSelection || ! is_array($displaySelectedRound) || ! is_array($displaySelectedTerritory))
            <div class="bg-brand-card border border-brand-border rounded-2xl p-8 text-center">
                <div class="w-14 h-14 bg-brand-surface text-brand-blue rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-hand-pointer"></i>
                </div>
                <h3 class="font-bold text-lg text-brand-blue mb-2">Selecciona una votación arriba</h3>
                <p class="text-sm text-brand-muted mb-0 max-w-sm mx-auto leading-relaxed">
                    Cuando elijas una fila, aquí aparecerá el conteo actual sin necesidad de votar.
                </p>
            </div>
        @elseif (! is_array($displaySelectedRound) || ! is_array($displaySelectedTerritory))
            <div class="bg-brand-card border border-brand-border rounded-2xl p-8 text-sm text-brand-muted">
                No hay una votación destacada para mostrar.
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                <section class="lg:col-span-5 bg-brand-card border border-brand-border rounded-2xl p-6 md:p-8 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold mb-2">
                                    {{ $displaySelectedTerritory['scope_type'] === 'region' ? 'Región' : ($displaySelectedTerritory['scope_type'] === 'province' ? 'Provincia' : 'Distrito') }}
                                </div>
                                <h3 class="text-3xl font-serif font-bold text-brand-blue leading-tight">
                                    {{ $displaySelectedTerritory['name'] ?? 'Territorio' }}
                                </h3>
                                <p class="text-sm text-brand-muted mt-2">
                                    {{ $displaySelectedRound['round']['title'] ?? 'Encuesta activa' }}
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-[#e6f8f0] text-brand-greenText px-3 py-1 text-[10px] font-bold uppercase tracking-widest shrink-0">
                                <span class="w-2 h-2 rounded-full bg-brand-green animate-pulse"></span>
                                {{ $displaySelectedRound['state'] === 'active' ? 'Abierta' : 'En revisión' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="rounded-xl bg-brand-surface border border-brand-border px-4 py-3">
                                <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold">Votos totales</div>
                                <div class="text-2xl font-bold text-brand-blue tabular-nums">{{ number_format($displaySelectedTotalVotes) }}</div>
                            </div>
                            <div class="rounded-xl bg-brand-surface border border-brand-border px-4 py-3">
                                <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold">Lidera</div>
                                <div class="text-lg font-bold text-brand-blue truncate">{{ $displaySelectedLeaderName }}</div>
                            </div>
                        </div>

                        <div class="rounded-xl bg-[#eef8f2] border border-[#bfe8cf] px-4 py-3 text-sm text-brand-text">
                            Lidera <span class="font-bold text-brand-blue">{{ $displaySelectedLeaderName }}</span>
                            con <span class="font-bold text-brand-blue">{{ number_format($displaySelectedLeaderVotes) }}</span> votos.
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <a href="{{ $selectedRoute }}" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm">
                                Ir a votar
                            </a>
                            <a href="#encuestas-activas" class="inline-flex items-center justify-center gap-2 border border-brand-border text-brand-blue font-bold py-3 px-6 rounded-xl hover:border-brand-blue/30 transition-colors">
                                Cambiar selección
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
                            <div class="text-3xl font-bold text-brand-blue tabular-nums">{{ number_format($displaySelectedTotalVotes) }}</div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted">votos emitidos</div>
                        </div>
                    </div>

                    @if ($displaySelectedTotalVotes === 0)
                        <div class="rounded-xl border border-dashed border-brand-border bg-brand-surface px-5 py-6 text-sm text-brand-muted leading-relaxed">
                            Aún no hay votos registrados en esta ronda. Cuando empiece a entrar participación, verás aquí el conteo por candidato y partido.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($displaySelectedOptions as $option)
                                @php
                                    $voteCount = (int) ($option['vote_count'] ?? 0);
                                    $voteShare = $displaySelectedTotalVotes > 0 ? ($voteCount / $displaySelectedTotalVotes) * 100 : 0;
                                @endphp
                                <div class="rounded-xl border border-brand-border bg-white p-4">
                                    <div class="flex items-start justify-between gap-4 mb-2">
                                        <div class="min-w-0">
                                            <div class="font-bold text-brand-text truncate">{{ $option['candidate']['name'] ?? '' }}</div>
                                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted mt-1 truncate">
                                                {{ $option['party']['name'] ?? '' }}
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <div class="text-xl font-bold text-brand-blue tabular-nums">{{ number_format($voteCount) }}</div>
                                            <div class="text-[10px] font-semibold uppercase tracking-wider text-brand-muted">votos</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs font-semibold text-brand-muted mb-2">
                                        <span>{{ $option['candidate']['name'] ?? '' }}</span>
                                        <span>{{ number_format($voteShare, 1) }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-brand-surface overflow-hidden">
                                        <div class="h-full rounded-full bg-brand-blue" style="width: {{ $voteShare }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        @endif
    </section>
@endsection
