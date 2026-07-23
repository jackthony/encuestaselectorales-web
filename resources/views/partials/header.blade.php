@php
    $activeNav = $activeNav ?? '';
    $navClass = static function (string $key) use ($activeNav): string {
        return $activeNav === $key
            ? 'text-brand-green border-b-2 border-brand-green pb-1'
            : 'hover:text-brand-blue/70 transition-colors';
    };
    $navClassMobile = static function (string $key) use ($activeNav): string {
        return $activeNav === $key
            ? 'block px-6 py-4 bg-brand-surface text-brand-green'
            : 'block px-6 py-4 hover:bg-brand-surface';
    };
@endphp
<header class="bg-brand-card w-full border-b border-brand-border sticky top-0 z-40 shadow-sm backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="flex flex-col cursor-pointer group min-w-0" aria-label="Ir al inicio">
            <div class="text-2xl md:text-3xl font-extrabold tracking-tight leading-none mb-0.5">
                <span class="text-brand-blue">Encuestas</span><span class="text-brand-text">electorales</span><span class="text-brand-green">.pe</span>
            </div>
            <div class="text-[10px] md:text-xs font-bold text-brand-muted tracking-widest uppercase">
                Votos actuales · Perú 2026
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-8 text-[13px] font-bold text-brand-blue tracking-widest uppercase">
            <a href="{{ url('/') }}" class="{{ $navClass('inicio') }}">Inicio</a>
            <a href="{{ route('home') }}#encuestas-activas" class="hover:text-brand-blue/70 transition-colors flex items-center gap-1">
                Votaciones <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        </nav>

        <div class="flex items-center gap-4">
            <button id="mobile-menu-btn" type="button" class="md:hidden text-brand-blue text-2xl p-2" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden absolute top-full left-0 w-full bg-brand-card border-b border-brand-border shadow-md">
        <nav class="flex flex-col text-sm font-bold text-brand-blue uppercase divide-y divide-gray-100">
            <a href="{{ url('/') }}" class="{{ $navClassMobile('inicio') }}">Inicio</a>
            <a href="{{ route('home') }}#encuestas-activas" class="{{ $navClassMobile('sondeos') }}">Votaciones</a>
        </nav>
    </div>
</header>
