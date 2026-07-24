@extends('layouts.public')

@section('content')
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
                    <div class="text-[11px] font-bold uppercase tracking-widest text-white/60 mb-1">Acceso directo</div>
                    <div class="text-2xl font-serif font-bold">Abre una encuesta</div>
                    <div class="text-sm text-white/75 mt-1">Haz clic en “Ver detalle” para entrar al voto.</div>
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

                <div id="home-voting-body">
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
                <div id="home-voting-list" class="space-y-3">
                    @foreach ($rondasAbiertas as $ronda)
                        @php
                            $territoryAncestors = is_array($ronda['territory_ancestors'] ?? null) ? $ronda['territory_ancestors'] : [];
                            $territoryCode = (string) ($ronda['territory_code'] ?? '');
                            $territoryScopeRank = (int) ($ronda['territory_scope_rank'] ?? 3);
                            $roundNumber = (int) ($ronda['round_number'] ?? 1);
                        @endphp
                        <button
                            type="button"
                            data-voting-row
                            data-target-url="{{ route('surveys.scope', ['scope' => $ronda['territory_scope'], 'slug' => $ronda['territory_slug']]) }}"
                            data-scope-rank="{{ $territoryScopeRank }}"
                            data-territory-code="{{ $territoryCode }}"
                            data-round-number="{{ $roundNumber }}"
                            class="block w-full rounded-2xl border bg-brand-card border-brand-border text-left transition-all hover:shadow-lg hover:border-brand-blue/30 motion-safe:active:scale-[0.99]"
                        >
                            <div class="grid grid-cols-1 gap-3 px-4 py-4 sm:px-5 sm:py-5 md:grid-cols-12 md:items-center">
                                <div class="md:col-span-5 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="text-[10px] uppercase tracking-widest font-bold px-2.5 py-1 rounded-full bg-brand-surface text-brand-muted border border-brand-border">
                                            {{ $ronda['scope_label'] }}
                                        </span>
                                        <span class="text-[10px] uppercase tracking-widest font-bold px-2.5 py-1 rounded-full bg-brand-surface text-brand-muted border border-brand-border">
                                            Ronda {{ $roundNumber }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-lg sm:text-xl leading-snug text-brand-blue">
                                        {{ $ronda['titulo'] }}
                                    </h3>
                                    <div class="mt-2 text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-brand-muted break-words">
                                        @if (! empty($territoryAncestors))
                                            {{ implode(' · ', array_map(static fn (array $ancestor): string => (string) ($ancestor['name'] ?? ''), $territoryAncestors)) }} ·
                                        @endif
                                        {{ $territoryCode }}
                                    </div>
                                </div>
                                <div class="md:col-span-2 rounded-2xl px-4 py-3 bg-brand-surface border border-brand-border">
                                    <div class="text-[10px] uppercase tracking-widest font-bold text-brand-muted">Votos</div>
                                    <div class="text-xl sm:text-2xl font-bold text-brand-blue tabular-nums">{{ number_format((int) ($ronda['total_votes'] ?? 0)) }}</div>
                                </div>
                                <div class="md:col-span-5 flex md:justify-end">
                                    <span class="inline-flex w-full md:w-auto items-center justify-center gap-2 rounded-full px-3 py-2 text-[10px] font-bold uppercase tracking-widest bg-brand-blue text-white">
                                        Ver detalle
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
@endsection
