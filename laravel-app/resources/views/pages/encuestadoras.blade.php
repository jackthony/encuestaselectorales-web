@extends('layouts.public')

@section('content')
    @php
        $resolveMedia = static function (string $src): string {
            return preg_match('/^https?:\/\//i', $src) ? $src : asset($src);
        };
    @endphp

    <main class="max-w-7xl mx-auto px-4 py-8 md:py-10 flex-grow w-full">
        <div class="mb-10 border-b border-brand-border pb-8">
            <h1 class="text-3xl md:text-5xl font-serif font-bold text-brand-blue leading-tight mb-4">
                Encuestadoras registradas
            </h1>
            <p class="text-lg text-gray-600 font-medium leading-relaxed max-w-3xl">
                Directorio público de encuestadoras visibles en la plataforma, con sus estudios publicados y enlaces oficiales.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($pollsters as $pollster)
                <article class="bg-brand-card rounded-2xl border border-brand-border shadow-sm hover:shadow-lg hover:border-brand-blue/30 transition-all duration-300 flex flex-col group">
                    <div class="p-6 border-b border-gray-100 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="text-xl font-bold text-brand-blue font-serif mb-1 group-hover:text-brand-green transition-colors truncate">{{ $pollster['nombre'] }}</h2>
                            <div class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-2">{{ $pollster['status_label'] }}</div>
                            <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-widest">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ $pollster['active_studies'] }} estudios
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0 text-brand-blue font-black text-sm">
                            {{ $pollster['initials'] !== '' ? $pollster['initials'] : 'EP' }}
                        </div>
                    </div>

                    <div class="p-6 flex-grow bg-gray-50/50">
                        <dl class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm">
                            <div>
                                <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Tipo</dt>
                                <dd class="font-medium text-gray-900">{{ $pollster['tipo'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Estudios</dt>
                                <dd class="font-medium text-brand-blue text-lg">{{ $pollster['active_studies'] }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Sitio oficial</dt>
                                <dd class="font-medium text-gray-900 truncate">
                                    @if (!empty($pollster['web']))
                                        <a href="{{ $pollster['web'] }}" target="_blank" rel="noopener" class="text-brand-blue hover:text-brand-green transition-colors">{{ $pollster['web'] }}</a>
                                    @else
                                        No publicado
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="p-4 border-t border-gray-100 mt-auto">
                        <span class="flex justify-between items-center text-sm font-bold text-brand-blue w-full">
                            Perfil público
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-brand-card border border-brand-border rounded-2xl p-8 text-center">
                    <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-4">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h2 class="text-2xl font-serif font-bold text-brand-blue mb-2">Aún no hay encuestadoras visibles</h2>
                    <p class="text-brand-muted leading-relaxed">La lista aparecerá cuando existan encuestadoras registradas en la data pública.</p>
                </div>
            @endforelse
        </div>
    </main>
@endsection
