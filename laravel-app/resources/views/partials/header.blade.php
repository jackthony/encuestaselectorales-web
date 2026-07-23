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
<header class="bg-brand-card w-full border-b border-brand-border sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex flex-col cursor-pointer group" aria-label="Ir al inicio">
            <div class="text-2xl md:text-3xl font-extrabold tracking-tight leading-none mb-0.5">
                <span class="text-brand-blue">Encuestas</span><span class="text-brand-text">electorales</span><span class="text-brand-green">.pe</span>
            </div>
            <div class="text-[10px] md:text-xs font-bold text-brand-muted tracking-widest uppercase">
                Sondeo ciudadano · Perú 2026
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-8 text-[13px] font-bold text-brand-blue tracking-widest uppercase">
            <a href="{{ url('/') }}" class="{{ $navClass('inicio') }}">Inicio</a>
            <div class="relative group cursor-pointer">
                <span class="hover:text-brand-blue/70 transition-colors flex items-center gap-1">Distritos de Lima <i class="fas fa-chevron-down text-[10px]"></i></span>
            </div>
            <a href="{{ url('/metodologia.php') }}" class="{{ $navClass('metodologia') }}">Metodología</a>
            <a href="{{ url('/quienes-somos.php') }}" class="{{ $navClass('quienes-somos') }}">Quiénes somos</a>
        </nav>

        <div class="flex items-center gap-4">
            <button class="w-10 h-10 rounded-full bg-brand-surface hover:bg-gray-200 flex items-center justify-center text-brand-blue transition-colors" aria-label="Buscar distrito">
                <i class="fas fa-search"></i>
            </button>
            <button id="mobile-menu-btn" class="md:hidden text-brand-blue text-2xl p-2" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden absolute top-full left-0 w-full bg-brand-card border-b border-brand-border shadow-md">
        <nav class="flex flex-col text-sm font-bold text-brand-blue uppercase divide-y divide-gray-100">
            <a href="{{ url('/') }}" class="{{ $navClassMobile('inicio') }}">Inicio</a>
            <a href="{{ url('/#distritos') }}" class="block px-6 py-4 hover:bg-brand-surface">Distritos de Lima</a>
            <a href="{{ url('/metodologia.php') }}" class="{{ $navClassMobile('metodologia') }}">Metodología</a>
            <a href="{{ url('/quienes-somos.php') }}" class="{{ $navClassMobile('quienes-somos') }}">Quiénes somos</a>
        </nav>
    </div>
</header>
