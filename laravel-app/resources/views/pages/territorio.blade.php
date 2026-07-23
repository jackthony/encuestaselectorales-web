@extends('layouts.public')

@section('content')
    <main class="flex-grow w-full bg-brand-bg">
        @if ($level === '')
            <section class="max-w-4xl mx-auto px-4 py-16 text-center">
                <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-6">
                    <i class="fas fa-map"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-blue mb-3">Encuestas por territorio</h1>
                <p class="text-brand-muted leading-relaxed mb-8">
                    Usa la búsqueda de la home para entrar a una región, provincia o distrito. Las páginas territoriales ya están preparadas para agrupar la data cuando llegue el siguiente lote.
                </p>
                <a href="/" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors">
                    Ir a la home
                </a>
            </section>
        @else
            <section class="relative bg-brand-blue text-white overflow-hidden border-b border-brand-blue/80">
                <div class="absolute inset-0 bg-grid-pattern opacity-30"></div>
                <div class="relative max-w-7xl mx-auto px-4 py-14 md:py-16">
                    <nav class="text-[11px] font-bold uppercase tracking-wider text-white/70 mb-5">
                        <a href="/" class="hover:text-brand-green transition-colors">Inicio</a>
                        <span class="mx-2 text-white/40">/</span>
                        <span>{{ $level === 'provincia' ? 'Provincia' : 'Región' }}</span>
                        <span class="mx-2 text-white/40">/</span>
                        <span class="text-brand-green">{{ $territoryName }}</span>
                    </nav>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold tracking-tight leading-tight">
                        {{ $level === 'provincia' ? 'Provincia' : 'Región' }} {{ $territoryName }}
                    </h1>
                    <p class="text-white/80 text-lg md:text-xl max-w-3xl mt-4 leading-relaxed">
                        {{ count($districts) }} distritos cargados en este territorio, con rondas activas detectadas en la data pública.
                    </p>
                    @if ($activeRound)
                        <div class="mt-6 inline-flex flex-col gap-2 rounded-2xl bg-white/10 border border-white/15 px-5 py-4 backdrop-blur-sm">
                            <div class="text-[10px] font-bold uppercase tracking-widest text-brand-green">{{ surveyScopeLabel($activeRound) }}</div>
                            <div class="text-sm text-white/90 leading-relaxed">{{ $activeRound['titulo'] }}</div>
                            <div class="text-xs text-white/70">
                                Activa hasta {{ $activeRound['fecha_cierre'] }}
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section class="max-w-7xl mx-auto px-4 -mt-8 relative z-20">
                @include('partials.share-actions')
            </section>

            <section class="max-w-7xl mx-auto px-4 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-brand-card border border-brand-border rounded-2xl p-5">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Distritos</div>
                        <div class="text-3xl font-serif font-bold text-brand-blue">{{ count($districts) }}</div>
                    </div>
                    <div class="bg-brand-card border border-brand-border rounded-2xl p-5">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Candidaturas</div>
                        <div class="text-3xl font-serif font-bold text-brand-blue">{{ array_sum(array_map(static fn (array $group): int => array_sum(array_map(static fn (array $district): int => (int) ($district['candidate_count'] ?? 0), $group['districts'] ?? [])), $groups)) }}</div>
                    </div>
                    <div class="bg-brand-card border border-brand-border rounded-2xl p-5">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Rondas activas</div>
                        <div class="text-3xl font-serif font-bold text-brand-blue">{{ count(array_filter($districts, static fn (array $district): bool => !empty($district['active_round']))) + ($activeRound ? 1 : 0) }}</div>
                    </div>
                </div>

                @if (count($districts) === 0)
                    <div class="bg-brand-card border border-brand-border rounded-2xl p-8 text-center">
                        <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-4">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h2 class="text-2xl font-serif font-bold text-brand-blue mb-2">Aún no hay data para este ámbito</h2>
                        <p class="text-brand-muted leading-relaxed">Cuando cargues el siguiente lote de candidaturas, esta página empezará a agruparlas automáticamente.</p>
                    </div>
                @else
                    <div class="space-y-8">
                        @foreach ($groups as $group)
                            <section class="bg-brand-card border border-brand-border rounded-2xl p-6 md:p-8 shadow-sm">
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-1">{{ $level === 'region' ? 'Provincia' : 'Distrito base' }}</div>
                                        <h2 class="text-2xl md:text-3xl font-serif font-bold text-brand-blue">{{ $group['label'] }}</h2>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 bg-[#f0fdf4] text-brand-green border border-[#dcfce7] px-2.5 py-1 rounded text-[9px] uppercase font-bold tracking-widest shrink-0">
                                        {{ count($group['districts']) }} distritos
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                    @foreach ($group['districts'] as $district)
                                        <article class="bg-brand-surface border border-brand-border rounded-2xl p-5">
                                            <div class="flex items-start justify-between gap-3 mb-4">
                                                <div>
                                                    <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-1">Distrito</div>
                                                    <h3 class="text-xl font-serif font-bold text-brand-blue leading-tight">{{ $district['nombre'] }}</h3>
                                                    <p class="text-xs text-brand-muted mt-1">
                                                        Provincia {{ territoryDisplayName((string) ($district['provincia'] ?? '')) }} · Región {{ territoryDisplayName((string) ($district['region'] ?? '')) }}
                                                    </p>
                                                </div>
                                                @if (!empty($district['active_round']))
                                                    <span class="inline-flex items-center gap-1.5 bg-[#f0fdf4] text-brand-green border border-[#dcfce7] px-2.5 py-1 rounded text-[9px] uppercase font-bold tracking-widest shrink-0">
                                                        Voto web activo
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-3 text-[11px] text-brand-muted mb-4 font-medium border-b border-white/70 pb-4">
                                                <span class="flex items-center gap-1.5"><i class="fas fa-users opacity-60 text-brand-blue"></i> {{ $district['candidate_count'] }} candidaturas</span>
                                                @if (!empty($district['active_round']))
                                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                    <span class="flex items-center gap-1.5"><i class="far fa-calendar-alt opacity-60 text-brand-blue"></i> Hasta {{ $district['active_round']['fecha_cierre'] }}</span>
                                                @endif
                                            </div>

                                            @if ($district['candidate_count'] === 0)
                                                <div class="bg-white border border-dashed border-brand-border rounded-xl p-4 text-sm text-brand-muted">
                                                    No hay candidaturas cargadas todavía para este distrito.
                                                </div>
                                            @else
                                                <div class="space-y-3">
                                                    @foreach (array_slice($district['candidates'], 0, 3) as $candidate)
                                                        <div class="flex items-center gap-3 bg-white border border-brand-border rounded-xl p-3">
                                                            <div class="w-12 h-12 rounded-full overflow-hidden shrink-0 border-2 shadow-sm" style="border-color: {{ $candidate['partido_color'] }}; background-color: #f8fafc;">
                                                                <img
                                                                    src="{{ $candidate['foto'] }}"
                                                                    alt="{{ $candidate['nombre'] }}"
                                                                    class="w-full h-full object-cover"
                                                                    onerror="this.onerror=null;this.src='{{ asset('assets/img/default-face.svg') }}';"
                                                                >
                                                            </div>
                                                            <div class="min-w-0 flex-1">
                                                                <div class="text-sm font-bold text-brand-text leading-tight truncate">{{ $candidate['nombre'] }}</div>
                                                                <div class="flex items-center gap-2 text-[11px] text-brand-muted uppercase tracking-wider truncate">
                                                                    @if (!empty($candidate['partido_logo']))
                                                                        <img src="{{ $candidate['partido_logo'] }}" alt="{{ $candidate['partido_nombre'] }}" class="w-4 h-4 rounded-sm object-contain bg-white border border-gray-100">
                                                                    @else
                                                                        <span class="inline-flex w-4 h-4 items-center justify-center rounded-sm bg-white border border-gray-100 text-[8px] font-black" style="color: {{ $candidate['partido_color'] }}">
                                                                            {{ $candidate['partido_initials'] !== '' ? $candidate['partido_initials'] : 'P' }}
                                                                        </span>
                                                                    @endif
                                                                    <span>{{ $candidate['partido_nombre'] }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="mt-5 pt-4 border-t border-brand-border flex justify-between items-center">
                                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Detalle territorial</span>
                                                <a href="{{ url($district['detail_url']) }}" class="text-[13px] font-bold text-brand-blue hover:text-brand-green flex items-center gap-1.5 group transition-colors">
                                                    Ver distrito
                                                    <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                                                </a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
    </main>
@endsection
