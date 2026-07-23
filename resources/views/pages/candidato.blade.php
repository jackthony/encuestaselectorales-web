@extends('layouts.public')

@section('content')
    @php
        $resolveMedia = static function (string $src): string {
            return preg_match('/^https?:\/\//i', $src) ? $src : asset($src);
        };
    @endphp

    <main class="max-w-7xl mx-auto px-4 py-8 md:py-10 flex-grow w-full">
        @if (!$candidate)
            <div class="max-w-xl mx-auto text-center py-16">
                <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-6">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h1 class="text-2xl font-serif font-bold text-brand-blue mb-3">Candidato no encontrado</h1>
                <p class="text-brand-muted leading-relaxed mb-8">No tenemos un perfil registrado con este identificador.</p>
                <a href="{{ url('/sondeos.php') }}" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors">
                    Ver sondeos activos
                </a>
            </div>
        @else
            <nav class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-6">
                <a href="{{ url('/') }}" class="hover:text-brand-green transition-colors">Inicio</a>
                <span class="mx-2">/</span>
                @if ($district)
                    <a href="{{ route('surveys.scope', ['scope' => $district['scope_type'], 'slug' => $district['slug']]) }}" class="hover:text-brand-green transition-colors">{{ $district['nombre'] }}</a>
                    <span class="mx-2">/</span>
                @endif
                <span class="text-brand-blue">Perfil de candidato</span>
            </nav>

            <div class="mb-8">
                @include('partials.share-actions')
            </div>

            <div class="bg-brand-card rounded-2xl border border-brand-border shadow-sm overflow-hidden mb-8">
                <div class="p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-8">
                    <div class="w-32 h-32 md:w-40 md:h-40 shrink-0 rounded-full border-4 border-gray-100 shadow-inner overflow-hidden bg-gray-50">
                        <img
                            src="{{ $resolveMedia($candidate['foto']) }}"
                            alt="{{ $candidate['nombre'] }}"
                            class="w-full h-full object-cover"
                            onerror="this.onerror=null;this.src='{{ asset('assets/img/default-face.svg') }}';"
                        >
                    </div>
                    <div class="flex-grow text-center md:text-left flex flex-col justify-center">
                        <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-textMuted mb-2 justify-center md:justify-start flex-wrap">
                            <span class="bg-blue-50 text-brand-blue px-2.5 py-1 rounded-full border border-blue-100">Perfil de candidato</span>
                            @if (!empty($district))
                                <span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full border border-emerald-100">{{ $district['nombre'] }}</span>
                            @endif
                        </div>
                        <h1 class="text-3xl md:text-5xl font-serif font-bold text-brand-blue mb-2">{{ $candidate['nombre'] }}</h1>
                        <p class="text-lg text-gray-600 font-medium mb-4">
                            <?php if ($district): ?>Postulante a la <strong class="text-gray-900">{{ $district['nombre'] }}</strong><?php endif; ?>
                            por el partido <strong style="color: {{ $partyColor }};">{{ $party['nombre'] ?? '' }}</strong>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <section class="bg-brand-card rounded-2xl border border-brand-border shadow-sm p-6">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h2 class="text-xl font-serif font-bold text-brand-blue">Evolución en encuestas</h2>
                            @if (!empty($activeRound))
                                <span class="text-xs font-bold uppercase tracking-widest text-brand-muted">{{ $activeRound['scope_label'] ?? '' }}</span>
                            @endif
                        </div>
                        @if (count($history) > 0)
                            <div class="space-y-4">
                                @foreach ($history as $item)
                                    <div class="bg-brand-surface border border-brand-border rounded-xl p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm font-bold text-brand-text truncate">{{ $item['survey']['titulo'] ?? 'Estudio' }}</div>
                                                <div class="text-xs text-brand-muted mt-1">{{ $item['survey']['fechaInicio'] ?? '' }} al {{ $item['survey']['fechaFin'] ?? '' }}</div>
                                            </div>
                                            <div class="text-2xl font-bold text-brand-blue tabular-nums">{{ number_format((float) ($item['value']['porcentaje'] ?? 0), 1) }}%</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-brand-muted">
                                <i class="fas fa-chart-line text-3xl mb-3 opacity-40"></i>
                                <p class="text-sm">Todavía no hay historial de sondeos para este candidato.</p>
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="space-y-6">
                    <div class="bg-brand-blue text-white rounded-2xl shadow-md p-6">
                        <h3 class="font-serif text-lg font-bold mb-4 flex items-center gap-2 border-b border-white/20 pb-2">
                            <i class="fas fa-list-ul text-brand-green"></i> Datos del perfil
                        </h3>
                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Partido</dt>
                                <dd class="font-medium flex items-center gap-2">
                                    @if (!empty($partyLogo = ($party['logo'] ?? '')))
                                        <img src="{{ $resolveMedia($partyLogo) }}" alt="{{ $party['nombre'] }}" class="w-5 h-5 rounded-sm object-contain bg-white border border-white/20">
                                    @endif
                                    <span>{{ $party['nombre'] ?? '' }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Distrito</dt>
                                <dd class="font-medium">{{ $district['nombre'] ?? 'Sin distrito' }}</dd>
                            </div>
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Estado</dt>
                                <dd class="font-medium">{{ !empty($candidate['activo']) ? 'Activo' : 'No vigente' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-brand-card border border-brand-border rounded-2xl p-6">
                        <h3 class="text-xl font-serif font-bold text-brand-blue mb-2">Otros candidatos del distrito</h3>
                        <p class="text-sm text-brand-muted leading-relaxed mb-4">La tarjeta muestra nombres reales, partido y fallback visual si no hay foto.</p>
                        <div class="space-y-3">
                            @foreach (array_slice($relatedCandidates, 0, 4) as $other)
                                <div class="flex items-center gap-3 bg-brand-surface border border-brand-border rounded-xl p-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border-2 shadow-sm" style="border-color: {{ $other['partido_color'] }}; background-color: #f8fafc;">
                                        <img src="{{ $resolveMedia($other['foto']) }}" alt="{{ $other['nombre'] }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('assets/img/default-face.svg') }}';">
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-brand-text truncate">{{ $other['nombre'] }}</div>
                                        <div class="text-[11px] text-brand-muted uppercase tracking-wider truncate">{{ $other['partido_nombre'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>
        @endif
    </main>
@endsection
