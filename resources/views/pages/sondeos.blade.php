@extends('layouts.public')

@section('content')
    <main class="flex-grow w-full bg-brand-bg">
        <section class="relative bg-brand-blue text-white overflow-hidden border-b border-brand-blue/80">
            <div class="absolute inset-0 bg-grid-pattern opacity-20"></div>
            <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-20">
                <div class="max-w-4xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-brand-green mb-5">
                        Sondeos activos
                    </span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold leading-tight tracking-tight mb-5">
                        Encuestas web abiertas y estudios de campo visibles
                    </h1>
                    <p class="text-white/80 text-lg md:text-xl max-w-3xl leading-relaxed">
                        Reunimos las rondas públicas en curso y los estudios de encuestadoras registradas, con etiquetas claras por distrito, provincia y región.
                    </p>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 -mt-8 relative z-20">
            @include('partials.share-actions')
        </section>

        <section class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                <div class="lg:col-span-8">
                    <div class="flex justify-between items-baseline mb-8 border-b border-brand-border pb-4">
                        <h2 class="text-3xl font-serif font-bold text-brand-blue">Encuestas web publicadas</h2>
                        <span class="text-sm font-bold text-brand-muted uppercase tracking-wider">{{ count($activeRounds) }} rondas</span>
                    </div>

                    @if (count($activeRounds) === 0)
                        <div class="bg-brand-card border border-brand-border rounded-2xl p-8 md:p-10 shadow-sm">
                            <div class="w-14 h-14 bg-[#e6f8f0] text-brand-greenText rounded-full flex items-center justify-center text-2xl mb-4">
                                <i class="fas fa-vote-yea"></i>
                            </div>
                            <h3 class="font-serif font-bold text-2xl text-brand-blue mb-2">Aún no hay sondeos web abiertos</h3>
                            <p class="text-sm text-brand-muted leading-relaxed max-w-2xl mb-6">
                                Cuando una ronda se active en MySQL, aparecerá aquí junto al distrito correspondiente. Mientras tanto puedes seguir revisando la data publicada.
                            </p>
                            <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-5 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm text-sm">
                                Ir a la home
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($activeRounds as $round)
                                <article class="bg-brand-card border border-brand-border rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col">
                                    <div class="flex justify-between items-start mb-3 gap-4">
                                        <div>
                                            <h3 class="font-serif font-bold text-2xl text-brand-blue leading-tight">{{ $round['scope_label'] }}</h3>
                                            <p class="text-xs text-brand-muted mt-1">{{ $round['titulo'] }}</p>
                                        </div>
                                            @if ($round['readiness_state'] === 'active')
                                                <span class="inline-flex items-center gap-1.5 bg-[#f0fdf4] text-brand-green border border-[#dcfce7] px-2.5 py-1 rounded text-[9px] uppercase font-bold tracking-widest shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-green animate-pulse"></span> Abierta
                                                </span>
                                            @else
                                                <span class="inline-flex items-center bg-amber-50 text-amber-800 border border-amber-200 px-2.5 py-1 rounded text-[9px] uppercase font-bold tracking-widest shrink-0">
                                                    Sin candidatos
                                                </span>
                                            @endif
                                    </div>

                                    <div class="flex items-center gap-3 text-[11px] text-brand-muted mb-5 font-medium border-b border-gray-50 pb-4">
                                        <span class="flex items-center gap-1.5"><i class="far fa-calendar-alt opacity-60 text-brand-blue"></i> {{ $round['fecha_apertura'] ?? '' }}</span>
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                        <span class="flex items-center gap-1.5"><i class="fas fa-clock opacity-60 text-brand-blue"></i> Hasta {{ $round['fecha_cierre'] ?? '' }}</span>
                                    </div>

                                    <p class="text-sm text-brand-muted leading-relaxed mb-6">
                                        El voto entra con control anti duplicado, validación geográfica y publicación real.
                                    </p>

                                    <div class="mt-auto pt-5 border-t border-brand-border flex justify-between items-center">
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                                            {{ $round['readiness_state'] === 'active' ? 'Voto web activo' : 'Pendiente de candidatos' }}
                                        </span>
                                        <a href="{{ $round['target_url'] }}" class="text-[13px] font-bold text-brand-blue hover:text-brand-green flex items-center gap-1.5 group transition-colors">
                                            Ir al sondeo
                                            <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="lg:col-span-4">
                    <div class="sticky top-28 space-y-6">
                        <div class="bg-brand-card border border-brand-border rounded-2xl p-6 shadow-sm">
                            <h3 class="font-serif text-lg font-bold text-brand-blue mb-3">Últimos estudios de campo</h3>
                            <p class="text-sm text-brand-muted leading-relaxed mb-5">
                                Estudios de encuestadoras registradas con metadatos públicos y sin datos ficticios.
                            </p>

                            @if (count($fieldStudies) === 0)
                                <div class="bg-brand-surface border border-dashed border-brand-border rounded-xl p-4 text-sm text-brand-muted">
                                    Aún no hay estudios de campo publicados.
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach (array_slice($fieldStudies, 0, 5) as $study)
                                        <a href="{{ url('/encuesta.php?id=' . rawurlencode((string) ($study['id'] ?? ''))) }}" class="block rounded-xl border border-brand-border bg-white p-4 hover:border-brand-blue/30 transition-colors">
                                            <div class="text-[10px] font-bold text-brand-muted uppercase tracking-widest mb-1">{{ $study['encuestadora_nombre'] ?? 'Encuestadora' }}</div>
                                            <div class="text-xs text-brand-muted">
                                                {{ $study['fechaInicio'] ?? '' }} al {{ $study['fechaFin'] ?? '' }}
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </main>
@endsection
