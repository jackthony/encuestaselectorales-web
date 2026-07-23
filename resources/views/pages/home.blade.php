@extends('layouts.public')

@section('content')
    @php
        $selectedRoundData = is_array($selectedRound ?? null) ? $selectedRound : null;
        $selectedTerritory = is_array($selectedRoundData['territory'] ?? null) ? $selectedRoundData['territory'] : null;
        $selectedOptions = is_array($selectedRoundData['top_options'] ?? null) ? $selectedRoundData['top_options'] : [];
        $selectedTotalVotes = (int) ($selectedRoundData['total_votes'] ?? 0);
        $selectedLeaderName = (string) ($selectedRoundData['leader_name'] ?? 'Candidatura');
        $selectedLeaderVotes = (int) ($selectedRoundData['leader_votes'] ?? 0);
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
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Encuestas activas</div>
                    <div class="text-2xl font-serif font-bold">{{ count($rondasAbiertas) }}</div>
                    <div class="text-sm text-white/75 mt-1">Rondas visibles hoy</div>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Vista elegida</div>
                    <div class="text-2xl font-serif font-bold">{{ $selectedTerritory['name'] ?? 'Sin selección' }}</div>
                    <div class="text-sm text-white/75 mt-1">{{ $selectedRoundData['round']['title'] ?? 'Selecciona una votación' }}</div>
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
                    <i class="fas fa-chart-line text-brand-green mr-2.5"></i> Encuestas Web Activas
                </h2>
                <p class="text-sm text-brand-muted mt-1">Elige una votación para ver cómo va y entrar a votar.</p>
            </div>

            @if (count($rondasAbiertas) === 0)
                <div class="bg-blue-50/50 rounded-2xl p-8 border border-blue-100/50 text-center">
                    <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-2xl text-brand-blue mx-auto mb-4 shadow-sm border border-blue-100">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h3 class="font-bold text-lg text-brand-blue mb-2">No hay encuestas web activas</h3>
                    <p class="text-sm text-brand-muted mb-0 max-w-sm mx-auto leading-relaxed">
                        Cuando haya una ronda publicada, aparecerá aquí con sus votos actuales.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($rondasAbiertas as $ronda)
                        @php
                            $isSelected = ($selectedTerritory['slug'] ?? null) === ($ronda['territory_slug'] ?? null)
                                && ($selectedTerritory['scope_type'] ?? null) === ($ronda['territory_scope'] ?? null);
                        @endphp
                        <a
                            href="{{ route('home', ['scope' => $ronda['territory_scope'], 'slug' => $ronda['territory_slug']]) }}"
                            class="block rounded-2xl p-6 border transition-all {{ $isSelected ? 'bg-brand-blue text-white border-brand-blue shadow-lg' : 'bg-brand-card border-brand-border hover:shadow-lg hover:border-brand-blue/30' }}"
                            @if ($isSelected) aria-current="page" @endif
                        >
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div class="min-w-0">
                                    <div class="text-[10px] uppercase tracking-widest font-bold {{ $isSelected ? 'text-white/70' : 'text-brand-muted' }}">
                                        {{ $ronda['scope_label'] }}
                                    </div>
                                    <h3 class="font-bold text-xl leading-tight {{ $isSelected ? 'text-white' : 'text-brand-blue' }}">
                                        {{ $ronda['titulo'] }}
                                    </h3>
                                </div>
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-widest shrink-0 {{ $isSelected ? 'bg-white/15 text-white' : 'bg-[#e6f8f0] text-brand-greenText border border-[#15ba75]/30' }}">
                                    {{ $isSelected ? 'Elegida' : 'Seleccionar' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-3 text-sm">
                                <div class="rounded-xl {{ $isSelected ? 'bg-white/10 border-white/10' : 'bg-brand-surface border-brand-border' }} border px-4 py-3">
                                    <div class="text-[10px] uppercase tracking-widest font-bold {{ $isSelected ? 'text-white/65' : 'text-brand-muted' }}">Votos</div>
                                    <div class="text-lg font-bold {{ $isSelected ? 'text-white' : 'text-brand-blue' }} tabular-nums">{{ number_format((int) ($ronda['total_votes'] ?? 0)) }}</div>
                                </div>
                                <div class="rounded-xl {{ $isSelected ? 'bg-white/10 border-white/10' : 'bg-brand-surface border-brand-border' }} border px-4 py-3">
                                    <div class="text-[10px] uppercase tracking-widest font-bold {{ $isSelected ? 'text-white/65' : 'text-brand-muted' }}">Lidera</div>
                                    <div class="text-lg font-bold {{ $isSelected ? 'text-white' : 'text-brand-blue' }} truncate">{{ $ronda['leader_name'] ?? 'Sin datos' }}</div>
                                </div>
                                <div class="rounded-xl {{ $isSelected ? 'bg-white/10 border-white/10' : 'bg-brand-surface border-brand-border' }} border px-4 py-3">
                                    <div class="text-[10px] uppercase tracking-widest font-bold {{ $isSelected ? 'text-white/65' : 'text-brand-muted' }}">Ventaja</div>
                                    <div class="text-lg font-bold {{ $isSelected ? 'text-white' : 'text-brand-blue' }} tabular-nums">{{ number_format((int) ($ronda['leader_votes'] ?? 0)) }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 pb-16">
        <div class="border-b border-brand-border pb-3 mb-6">
            <h2 class="text-2xl font-serif font-bold text-brand-blue flex items-center">
                <i class="fas fa-square-poll-vertical text-brand-green mr-2.5"></i> Votación seleccionada
            </h2>
            <p class="text-sm text-brand-muted mt-1">Aquí ves el conteo actual sin haber votado aún.</p>
        </div>

        @if (! is_array($selectedRoundData) || ! is_array($selectedTerritory))
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
                                    {{ $selectedTerritory['scope_type'] === 'region' ? 'Región' : ($selectedTerritory['scope_type'] === 'province' ? 'Provincia' : 'Distrito') }}
                                </div>
                                <h3 class="text-3xl font-serif font-bold text-brand-blue leading-tight">
                                    {{ $selectedTerritory['name'] ?? 'Territorio' }}
                                </h3>
                                <p class="text-sm text-brand-muted mt-2">
                                    {{ $selectedRoundData['round']['title'] ?? 'Encuesta activa' }}
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-[#e6f8f0] text-brand-greenText px-3 py-1 text-[10px] font-bold uppercase tracking-widest shrink-0">
                                <span class="w-2 h-2 rounded-full bg-brand-green animate-pulse"></span>
                                {{ $selectedRoundData['state'] === 'active' ? 'Abierta' : 'En revisión' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="rounded-xl bg-brand-surface border border-brand-border px-4 py-3">
                                <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold">Votos totales</div>
                                <div class="text-2xl font-bold text-brand-blue tabular-nums">{{ number_format($selectedTotalVotes) }}</div>
                            </div>
                            <div class="rounded-xl bg-brand-surface border border-brand-border px-4 py-3">
                                <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold">Lidera</div>
                                <div class="text-lg font-bold text-brand-blue truncate">{{ $selectedLeaderName }}</div>
                            </div>
                        </div>

                        <div class="rounded-xl bg-[#eef8f2] border border-[#bfe8cf] px-4 py-3 text-sm text-brand-text">
                            Lidera <span class="font-bold text-brand-blue">{{ $selectedLeaderName }}</span>
                            con <span class="font-bold text-brand-blue">{{ number_format($selectedLeaderVotes) }}</span> votos.
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
                            <div class="text-3xl font-bold text-brand-blue tabular-nums">{{ number_format($selectedTotalVotes) }}</div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted">votos emitidos</div>
                        </div>
                    </div>

                    @if ($selectedTotalVotes === 0)
                        <div class="rounded-xl border border-dashed border-brand-border bg-brand-surface px-5 py-6 text-sm text-brand-muted leading-relaxed">
                            Aún no hay votos registrados en esta ronda. Cuando empiece a entrar participación, verás aquí el conteo por candidato y partido.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($selectedOptions as $option)
                                @php
                                    $voteCount = (int) ($option['vote_count'] ?? 0);
                                    $voteShare = $selectedTotalVotes > 0 ? ($voteCount / $selectedTotalVotes) * 100 : 0;
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
