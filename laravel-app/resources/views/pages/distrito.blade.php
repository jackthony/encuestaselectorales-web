@extends('layouts.public')

@section('content')
    <div class="bg-brand-green text-[#062010] text-[11px] md:text-xs font-bold py-2 px-4 w-full relative z-50 shadow-sm border-b border-white/20">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2 tracking-wide uppercase">
                <span class="relative flex h-2 w-2 mr-1">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#062010] opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-[#062010]"></span>
                </span>
                Sondeo ciudadano en vivo · Elecciones 2026
            </div>
            <div id="reloj" class="font-mono tracking-wide hidden md:block" aria-live="polite">
                --/--/---- --:--:--
            </div>
        </div>
    </div>

    <main class="flex-grow w-full pb-20">
        @if (!$district)
            <section class="bg-brand-surface border-b border-brand-border py-16 md:py-20 px-4 text-center">
                <div class="max-w-3xl mx-auto">
                    <h1 class="text-3xl md:text-5xl font-serif font-bold text-brand-blue leading-tight mb-4">Distritos del Perú</h1>
                    <p class="text-lg text-brand-muted leading-relaxed">
                        Elegí un distrito desde <a href="sondeos.php" class="text-brand-blue hover:text-brand-green transition-colors font-semibold">Sondeos Activos</a> para ver su detalle.
                    </p>
                </div>
            </section>
        @else
            <section class="max-w-7xl mx-auto px-4 pt-12">
                <nav class="text-[11px] font-bold text-brand-muted uppercase tracking-wider mb-3">
                    <a href="/" class="hover:text-brand-green transition-colors">Perú</a>
                    <span class="mx-2 text-gray-300">/</span>
                    {{ ucfirst((string) ($district['region'] ?? '')) }}
                    <span class="mx-2 text-gray-300">/</span>
                    {{ ucfirst((string) ($district['provincia'] ?? '')) }}
                </nav>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-brand-blue tracking-tight leading-none mb-3">
                    {{ $district['nombre'] }}
                </h1>
                <p class="text-brand-muted text-lg font-medium mb-8">Tablero Electoral Municipal 2026</p>

                <div class="mb-8">
                    @include('partials.share-actions')
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 pb-16">
                    <div class="lg:col-span-8 space-y-10">
                        @if (!$hasCandidates)
                            <section class="bg-brand-card border-2 border-dashed border-brand-green/40 rounded-2xl p-6 md:p-8 relative overflow-hidden">
                                <div class="flex items-start gap-5">
                                    <div class="w-14 h-14 bg-[#e6f8f0] text-brand-greenText rounded-full flex items-center justify-center text-2xl shrink-0">
                                        <i class="fas fa-users-viewfinder"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-serif font-bold text-brand-blue mb-2">Aún no hay candidatos para {{ $district['nombre'] }}</h2>
                                        <p class="text-brand-textMuted leading-relaxed mb-6">
                                            El Jurado Nacional de Elecciones (JNE) publica las listas oficiales de candidatos admitidos el <strong>5 de agosto de 2026</strong>. Mientras tanto, ayúdanos a identificar a los candidatos de tu distrito.
                                        </p>
                                        <div class="bg-brand-surface border border-brand-border rounded-xl p-5 mb-6">
                                            <h3 class="font-bold text-brand-text mb-2 text-sm">¿Tu candidato no está en la lista?</h3>
                                            <p class="text-xs text-brand-textMuted mb-4">Escríbenos por WhatsApp para incluirlo y ser de los primeros en habilitar el sondeo ciudadano en {{ $district['nombre'] }}.</p>
                                            <a href="https://wa.me/{{ $whatsappNumero }}?text={{ rawurlencode('Hola, quiero proponer un candidato para ' . $district['nombre']) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#25D366] text-white font-bold py-3 px-6 rounded-xl hover:bg-[#20bd5a] transition-colors w-full sm:w-auto shadow-sm">
                                                <i class="fab fa-whatsapp text-lg"></i> Proponer candidato por WhatsApp
                                            </a>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-medium"><i class="fas fa-shield-alt mr-1"></i> Cuando el sondeo se active, cada voto exigirá validación de ubicación por GPS.</div>
                                    </div>
                                </div>
                            </section>
                        @else
                            <section class="bg-brand-card border border-brand-border rounded-2xl p-6 md:p-8">
                                <h2 class="text-2xl font-serif font-bold text-brand-blue mb-3">Lista de candidatos</h2>

                                @if ($blockedMessage)
                                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3">
                                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                                        <div class="text-sm text-amber-900 leading-relaxed">{{ $blockedMessage }}</div>
                                    </div>
                                @endif

                                @if ($voteEnabled)
                                    <form id="voto-panel" class="space-y-6" data-encuesta-id="{{ $activeRound['id'] }}" data-ubigeo-votacion="{{ $district['id'] }}" data-distrito-nombre="{{ $district['nombre'] }}">
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($candidates as $index => $candidate)
                                        <label class="bg-brand-surface border border-brand-border rounded-xl p-5 flex items-center gap-4 cursor-pointer transition-all hover:border-brand-blue/30 hover:bg-white">
                                            @if ($voteEnabled)
                                                <input type="radio" name="candidato" value="{{ $candidate['id'] }}" class="sr-only peer" {{ $index === 0 ? 'required' : '' }}>
                                            @endif
                                            <div class="w-14 h-14 rounded-full overflow-hidden shrink-0 border-2 shadow-sm" style="border-color: {{ $candidate['partido_color'] }}; background-color: #f8fafc;">
                                                <img
                                                    src="{{ $candidate['foto'] }}"
                                                    alt="{{ $candidate['nombre'] }}"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.onerror=null;this.src='{{ asset('assets/img/default-face.svg') }}';"
                                                >
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-bold text-brand-text leading-tight mb-1">{{ $candidate['nombre'] }}</div>
                                                <div class="text-xs font-semibold text-brand-muted uppercase tracking-wider flex items-center gap-2">
                                                    @if (!empty($candidate['partido_logo']))
                                                        <img src="{{ $candidate['partido_logo'] }}" alt="{{ $candidate['partido_nombre'] }}" class="h-4 w-4 object-contain">
                                                    @else
                                                        <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-blue/10 text-[9px] font-black text-brand-blue px-1">{{ $candidate['partido_initials'] !== '' ? $candidate['partido_initials'] : 'P' }}</span>
                                                    @endif
                                                    <span>{{ $candidate['partido_nombre'] }}</span>
                                                </div>
                                            </div>
                                            @if ($voteEnabled)
                                                <span class="ml-auto flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-gray-300 text-transparent transition-colors peer-checked:border-brand-blue peer-checked:bg-brand-blue peer-checked:text-white">
                                                    <i class="fas fa-check text-[10px]"></i>
                                                </span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>

                                @if ($voteEnabled)
                                    <div class="border-t border-brand-border pt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <p class="text-xs text-brand-muted leading-relaxed">
                                            Selecciona un candidato y valida tu ubicación para registrar el voto de {{ $district['nombre'] }}.
                                        </p>
                                        <button type="button" onclick="iniciarValidacion()" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm">
                                            <i class="fas fa-location-arrow"></i> Registrar mi voto
                                        </button>
                                    </div>
                                    </form>
                                @endif

                                @if ($activeRound)
                                    <div class="mt-8 border-t border-brand-border pt-6" data-distrito="{{ $district['id'] }}">
                                        <div class="bg-[#f7fbff] border border-[#d7e7ff] rounded-2xl p-5 mb-5">
                                            <div class="text-[10px] font-bold uppercase tracking-widest text-brand-blue mb-2">{{ $scopeLabel }}</div>
                                            <h3 class="font-serif font-bold text-xl text-brand-blue leading-snug mb-2">{{ $activeRound['titulo'] }}</h3>
                                            <p class="text-sm text-brand-muted">
                                                Ronda {{ $activeRound['numero_ronda'] }} disponible hasta {{ $activeRound['fecha_cierre'] }}.
                                            </p>
                                        </div>
                                        <p class="text-sm text-brand-muted leading-relaxed">
                                            El flujo de voto se habilita con BL-14; esta vista ya muestra la ronda real que está activa para {{ $district['nombre'] }}.
                                        </p>
                                    </div>
                                @endif
                            </section>
                        @endif
                    </div>

                    <aside class="lg:col-span-4 space-y-4">
                        <h2 class="text-xl font-serif font-bold text-brand-blue flex items-center gap-2 border-b border-brand-border pb-3">
                            <i class="fas fa-clipboard-check text-brand-muted"></i> Encuestas de Campo
                        </h2>
                        <div class="bg-brand-card border border-brand-border rounded-xl p-6 text-center">
                            <p class="text-xs text-brand-muted leading-relaxed">Aún no hay estudios de campo publicados para {{ $district['nombre'] }}.</p>
                        </div>
                    </aside>
                </div>
            </section>
        @endif
    </main>

    @if ($district && $voteEnabled)
        @php require dirname(base_path()) . '/partials/widget-gps.php'; @endphp
        <script src="{{ asset('assets/js/voto-gps.js') }}"></script>
    @endif
@endsection
