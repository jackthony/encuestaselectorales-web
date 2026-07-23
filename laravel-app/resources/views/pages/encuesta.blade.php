@extends('layouts.public')

@section('content')
    @php
        $resolveMedia = static function (string $src): string {
            return preg_match('/^https?:\/\//i', $src) ? $src : asset($src);
        };
    @endphp

    <main class="max-w-7xl mx-auto px-4 py-8 md:py-10 flex-grow w-full">
        @if (!$survey || !$result)
            <div class="max-w-xl mx-auto text-center py-16">
                <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-6">
                    <i class="fas fa-file-circle-question"></i>
                </div>
                <h1 class="text-2xl font-serif font-bold text-brand-blue mb-3">Este estudio no está disponible</h1>
                <p class="text-brand-muted leading-relaxed mb-8">
                    Aún no tenemos publicado un estudio de campo con este identificador, o todavía no existen estudios reales registrados en la plataforma.
                </p>
                <a href="{{ url('/sondeos.php') }}" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors">
                    Ver sondeos activos
                </a>
            </div>
        @else
            <nav class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-6">
                <a href="{{ url('/') }}" class="hover:text-brand-green transition-colors">Inicio</a>
                <span class="mx-2">/</span>
                <a href="{{ url('/sondeos.php') }}" class="hover:text-brand-green transition-colors">Sondeos</a>
                <span class="mx-2">/</span>
                <span class="text-brand-blue">{{ $pollster['nombre'] ?? 'Estudio' }}</span>
            </nav>

            <div class="mb-8">
                @include('partials.share-actions')
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                <div class="lg:col-span-8 flex flex-col gap-8">
                    <header class="border-b border-brand-border pb-6">
                        <div class="flex items-center gap-3 mb-3 flex-wrap">
                            <span class="bg-brand-blue text-white text-[10px] font-bold px-2 py-1 uppercase tracking-widest rounded-sm">Estudio de campo</span>
                            <span class="text-xs font-bold text-brand-textMuted uppercase"><i class="far fa-calendar-alt mr-1"></i> {{ $survey['fechaFin'] ?? '' }}</span>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-blue leading-tight">
                            Intención de voto — {{ $pollster['nombre'] ?? 'Encuestadora' }}
                        </h1>
                        <p class="text-sm text-brand-muted leading-relaxed mt-3">
                            {{ $survey['titulo'] ?? 'Ficha técnica y resultados publicados.' }}
                        </p>
                    </header>

                    <section class="bg-brand-card border border-brand-border p-6 md:p-8 rounded-2xl shadow-sm">
                        <div class="flex justify-between items-end mb-8 border-b border-gray-100 pb-4">
                            <h2 class="text-xl font-bold text-brand-blue font-serif">Resultados</h2>
                            <span class="text-xs font-semibold text-gray-400 uppercase">Base: {{ (int) ($survey['tamanoMuestra'] ?? 0) }} casos</span>
                        </div>

                        @if (count($rows) === 0)
                            <div class="text-center py-12 text-brand-muted">
                                <i class="fas fa-chart-column text-3xl mb-3 opacity-40"></i>
                                <p class="text-sm">Todavía no hay resultados publicados para este estudio.</p>
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach ($rows as $row)
                                    @php
                                        $candidate = $row['candidate'];
                                        $party = $row['party'];
                                    @endphp
                                    <div class="relative">
                                        <div class="flex justify-between items-baseline mb-2">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border-2 shadow-sm" style="border-color: {{ $row['color'] }}; background-color: #f8fafc;">
                                                    <img src="{{ $resolveMedia(candidatePhotoSrc($candidate)) }}" alt="{{ $candidate['nombre'] }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('assets/img/default-face.svg') }}';">
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="font-bold text-gray-900 text-lg leading-none truncate">{{ $candidate['nombre'] }}</div>
                                                    <div class="text-xs text-brand-textMuted font-medium mt-1 truncate">
                                                        {{ $party['nombre'] ?? '' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="font-bold text-2xl text-brand-blue tabular-nums">{{ number_format($row['percentage'], 1) }}%</div>
                                        </div>
                                        <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full" style="background-color: {{ $row['color'] }}; width: {{ (float) $row['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                        <div class="text-xs text-brand-textMuted mb-1">Blanco/Viciado</div>
                                        <div class="font-bold text-brand-blue text-lg">{{ pct((float) ($result['votoBlancoNulo'] ?? 0)) }}</div>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                        <div class="text-xs text-brand-textMuted mb-1">Indecisos</div>
                                        <div class="font-bold text-brand-blue text-lg">{{ pct((float) ($result['indecisos'] ?? 0)) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="lg:col-span-4 flex flex-col gap-6">
                    <div class="bg-brand-blue text-white p-6 rounded-2xl shadow-md">
                        <h3 class="font-serif text-lg font-bold mb-4 flex items-center gap-2 border-b border-white/20 pb-2">
                            <i class="fas fa-clipboard-list text-brand-green"></i> Ficha técnica
                        </h3>
                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Encuestadora</dt>
                                <dd class="font-medium">{{ $pollster['nombre'] ?? '' }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Muestra</dt>
                                    <dd class="font-medium">{{ (int) ($survey['tamanoMuestra'] ?? 0) }} casos</dd>
                                </div>
                                <div>
                                    <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Margen de error</dt>
                                    <dd class="font-medium text-brand-green">± {{ pct((float) ($survey['margenError'] ?? 0)) }}</dd>
                                </div>
                            </div>
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Nivel de confianza</dt>
                                <dd class="font-medium">{{ pct((float) ($survey['nivelConfianza'] ?? 0), 0) }}</dd>
                            </div>
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Fecha de campo</dt>
                                <dd class="font-medium">{{ $survey['fechaInicio'] ?? '' }} al {{ $survey['fechaFin'] ?? '' }}</dd>
                            </div>
                            @if (!empty($pollster['web']))
                                <div>
                                    <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Fuente</dt>
                                    <dd class="font-medium"><a href="{{ $pollster['web'] }}" class="underline hover:text-brand-green" target="_blank" rel="noopener">Sitio de la encuestadora</a></dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </aside>
            </div>
        @endif
    </main>
@endsection
