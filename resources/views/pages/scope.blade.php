@extends('layouts.public')

@section('content')
    <div class="bg-brand-green text-[#062010] text-[11px] font-bold py-2 px-4 border-b border-white/20">
        <div class="max-w-7xl mx-auto flex justify-between items-center uppercase tracking-wide">
            <span><span class="inline-block w-2 h-2 rounded-full bg-[#062010] mr-2 animate-pulse"></span>Sondeo ciudadano en vivo · Elecciones 2026</span>
            <span id="reloj" class="hidden md:block font-mono">--/--/---- --:--:--</span>
        </div>
    </div>

    <main class="flex-grow bg-brand-bg">
        <section class="relative overflow-hidden border-b border-brand-border bg-[radial-gradient(circle_at_top_right,_rgba(21,186,117,0.14),_transparent_38%),linear-gradient(135deg,#f8fbff_0%,#ffffff_58%,#f4f5f3_100%)]">
            <div class="max-w-7xl mx-auto px-4 py-12 md:py-16">
                <nav class="text-[11px] font-bold text-brand-muted uppercase tracking-wider mb-5">
                    <a href="{{ route('home') }}" class="hover:text-brand-green">Perú</a>
                    @foreach (array_reverse($territory['ancestors'] ?? []) as $ancestor)
                        <span class="mx-2 text-gray-300">/</span>{{ $ancestor['name'] }}
                    @endforeach
                </nav>
                <div class="max-w-4xl">
                    <span class="inline-flex rounded-full bg-brand-blue/10 text-brand-blue px-3 py-1 text-[10px] font-bold uppercase tracking-widest mb-4">
                        {{ $scopeLabel }}
                    </span>
                    <h1 class="text-4xl md:text-6xl font-serif font-bold text-brand-blue tracking-tight leading-none mb-4">
                        {{ $territory['name'] }}
                    </h1>
                    <p class="text-lg text-brand-muted max-w-2xl">
                        Candidaturas y encuesta web de nivel {{ strtolower($scopeLabel) }}, diferenciada por ubigeo y ámbito electoral.
                    </p>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 -mt-5 relative z-10">
            @include('partials.share-actions')
        </section>

        <section class="max-w-7xl mx-auto px-4 py-12">
            @if ($roundState !== 'active' || !$activeRound)
                <div class="max-w-3xl bg-brand-card border-2 border-dashed border-brand-green/40 rounded-2xl p-8 md:p-10 shadow-sm">
                    <div class="w-14 h-14 bg-[#e6f8f0] text-brand-greenText rounded-full flex items-center justify-center text-2xl mb-5">
                        <i class="fas fa-users-viewfinder"></i>
                    </div>
                    <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold mb-2">{{ $scopeLabel }} {{ $territory['name'] }}</div>
                    <h2 class="text-3xl font-serif font-bold text-brand-blue mb-3">
                        @if ($roundState === 'blocked')
                            Aún no hay candidaturas verificadas para esta encuesta
                        @elseif ($roundState === 'scheduled')
                            La ronda todavía no ha comenzado
                        @elseif ($roundState === 'closed')
                            La ronda ya terminó
                        @else
                            Aún no hay una ronda publicada
                        @endif
                    </h2>
                    <p class="text-brand-muted leading-relaxed">
                        No mostraremos candidatos de ejemplo. La encuesta se habilitará cuando el catálogo real y la ventana de publicación estén completos.
                    </p>
                </div>
            @else
                @php
                    $activeRoundOptions = $activeRound['options'] ?? [];
                    $activeRoundTotalVotes = (int) ($activeRound['total_votes'] ?? 0);
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                    <div class="lg:col-span-8">
                        <section class="bg-brand-card border border-brand-border rounded-2xl p-6 md:p-8 shadow-sm">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 border-b border-brand-border pb-6 mb-6">
                                <div>
                                    <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold mb-2">{{ $scopeLabel }} · {{ $activeRound['office_type'] }}</div>
                                    <h2 class="text-3xl font-serif font-bold text-brand-blue">{{ $activeRound['title'] }}</h2>
                                </div>
                                <span class="inline-flex items-center gap-2 bg-[#e6f8f0] text-brand-greenText rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest shrink-0">
                                    <span class="w-2 h-2 rounded-full bg-brand-green animate-pulse"></span> Ronda abierta
                                </span>
                            </div>

                            <form id="voto-panel"
                                  data-survey-round-id="{{ $activeRound['id'] }}"
                                  data-territory-name="{{ $territory['name'] }}"
                                  class="space-y-7">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($activeRound['options'] as $index => $option)
                                        <label class="relative bg-brand-surface border border-brand-border rounded-xl p-4 flex items-center gap-4 cursor-pointer hover:border-brand-blue/40 hover:bg-white transition-all">
                                            <input type="radio" name="candidato" value="{{ $option['option_id'] }}" class="sr-only peer" {{ $index === 0 ? 'required' : '' }}>
                                            <span class="absolute inset-0 rounded-xl ring-2 ring-transparent peer-checked:ring-brand-blue pointer-events-none"></span>
                                            <div class="w-16 h-16 rounded-full overflow-hidden shrink-0 border-2 border-white shadow-sm bg-white">
                                                <img src="{{ $option['candidate']['photo_url'] ?: asset('assets/img/default-face.svg') }}"
                                                     alt="{{ $option['candidate']['name'] }}"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.onerror=null;this.src='{{ asset('assets/img/default-face.svg') }}';">
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold text-brand-text leading-tight">{{ $option['candidate']['name'] }}</div>
                                                <div class="flex items-center gap-2 mt-2 text-xs font-semibold text-brand-muted uppercase tracking-wider">
                                                    @if (!empty($option['party']['logo_url']))
                                                        <img src="{{ $option['party']['logo_url'] }}" alt="{{ $option['party']['name'] }}" class="w-6 h-6 object-contain bg-white rounded border border-gray-100">
                                                    @else
                                                        <span class="inline-flex w-6 h-6 items-center justify-center bg-white rounded border border-gray-100 text-[9px] font-black text-brand-blue">
                                                            {{ strtoupper(substr($option['party']['name'], 0, 1)) }}
                                                        </span>
                                                    @endif
                                                    <span class="truncate">{{ $option['party']['name'] }}</span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="border-t border-brand-border pt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <p class="text-xs text-brand-muted max-w-xl">
                                        El voto exige permiso de ubicación. La ubicación se contrasta de forma aproximada con la zona configurada; la conexión se protege con cifrado y se bloquean duplicados por ronda.
                                    </p>
                                    <button type="button" onclick="iniciarValidacion()" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm">
                                        <i class="fas fa-location-arrow"></i> Registrar mi voto
                                    </button>
                                </div>
                            </form>
                        </section>

                        <section class="mt-8 bg-brand-card border border-brand-border rounded-2xl p-6 md:p-8 shadow-sm">
                            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 border-b border-brand-border pb-5 mb-5">
                                <div>
                                    <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold mb-2">Votación actual</div>
                                    <h3 class="text-2xl font-serif font-bold text-brand-blue">Conteo parcial por candidatura</h3>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-brand-blue tabular-nums">{{ number_format($activeRoundTotalVotes) }}</div>
                                    <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted">votos emitidos</div>
                                </div>
                            </div>

                            @if ($activeRoundTotalVotes === 0)
                                <div class="rounded-xl border border-dashed border-brand-border bg-brand-surface px-5 py-6 text-sm text-brand-muted leading-relaxed">
                                    Aún no hay votos registrados en esta ronda. Cuando empiece a entrar participación, verás aquí el conteo por candidato y partido.
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach ($activeRoundOptions as $option)
                                        @php
                                            $voteCount = (int) ($option['vote_count'] ?? 0);
                                            $voteShare = $activeRoundTotalVotes > 0 ? ($voteCount / $activeRoundTotalVotes) * 100 : 0;
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

                    <aside class="lg:col-span-4 space-y-5">
                        <div class="bg-brand-card border border-brand-border rounded-2xl p-6 shadow-sm">
                            <div class="text-[10px] uppercase tracking-widest text-brand-muted font-bold mb-3">Vigencia</div>
                            <div class="text-sm text-brand-text font-semibold mb-1">Hasta {{ \Carbon\CarbonImmutable::parse($activeRound['closes_at'])->timezone('America/Lima')->format('d/m/Y H:i') }}</div>
                            <p class="text-xs text-brand-muted">Hora de Lima</p>
                        </div>
                        <div class="bg-brand-blue text-white rounded-2xl p-6 shadow-sm">
                            <div class="text-[10px] uppercase tracking-widest text-white/60 font-bold mb-3">Referencia territorial</div>
                            <div class="text-2xl font-serif font-bold">{{ $scopeLabel }} {{ $territory['name'] }}</div>
                            <p class="text-sm text-white/70 mt-2">Ubigeo {{ $territory['official_code'] }}</p>
                            <p class="text-xs text-white/60 mt-3">El contraste de ubicación usa una zona aproximada y no certifica límites distritales exactos.</p>
                        </div>
                    </aside>
                </div>
            @endif
        </section>
    </main>

    @if ($roundState === 'active' && $activeRound)
        @include('partials.widget-gps')
    @endif
@endsection

@if ($roundState === 'active' && $activeRound)
    @push('scripts')
        <script src="{{ asset('assets/js/voto-gps.js') }}"></script>
    @endpush
@endif
