<?php
/**
 * Single shared header (php-architecture spec, "Repeated UI lives in partials").
 *
 * Finding (tasks.md section 3.1): the 8 canvas prototypes actually carry TWO
 * visually distinct header generations —
 *  - "cluster A" (portal_de_encuestas, detalle_de_encuesta,
 *    directorio_de_encuestadoras, perfil_de_candidato): two-line logo,
 *    social icons, simple "Distritos" nav item, no search.
 *  - "cluster B" (portal_de_sondeos_ciudadanos, metodolog_a,
 *    qui_nes_somos_autoridad): single-line logo, search button, "Distritos
 *    de Lima ▾" nav item. This is the newer generation — canvas-gemini/PROMPT-portal.md
 *    (the brief actually used for that round) confirms it post-dates cluster A
 *    (drops third-party pollster mentions, Lima-only scope).
 * Per Decision 2/3.1 "newest prototype wins", this partial is cluster B's
 * header (portal_de_sondeos_ciudadanos.html, the most complete variant —
 * it is also the one design.md itself describes: "nav + Distritos de Lima
 * dropdown"). Cluster A pages (index.php, encuesta.php, candidato.php,
 * encuestadoras.php) visually pick up this newer header as a result — an
 * intentional, recorded consequence of consolidating to one partial, not an
 * accidental regression. The ticker bar above it stays page-specific
 * (its announcement text differs per page) and is NOT part of this partial.
 *
 * Callers set $activeNav (one of: 'inicio', 'distritos', 'metodologia',
 * 'quienes-somos') before requiring this partial to get the right nav item
 * highlighted, matching what each canvas original did for its own page.
 */

require_once __DIR__ . '/../includes/helpers.php';

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
?>
    <header class="bg-brand-card w-full border-b border-brand-border sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="index.php" class="flex flex-col cursor-pointer group" aria-label="Ir al inicio">
                <div class="text-2xl md:text-3xl font-extrabold tracking-tight leading-none mb-0.5">
                    <span class="text-brand-blue">Encuestas</span><span class="text-brand-text">electorales</span><span class="text-brand-green">.pe</span>
                </div>
                <div class="text-[10px] md:text-xs font-bold text-brand-muted tracking-widest uppercase">
                    Sondeo ciudadano · Lima 2026
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-[13px] font-bold text-brand-blue tracking-widest uppercase">
                <a href="index.php" class="<?= esc($navClass('inicio')) ?>">Inicio</a>
                <div class="relative group cursor-pointer">
                    <span class="hover:text-brand-blue/70 transition-colors flex items-center gap-1">Distritos de Lima <i class="fas fa-chevron-down text-[10px]"></i></span>
                </div>
                <a href="metodologia.php" class="<?= esc($navClass('metodologia')) ?>">Metodología</a>
                <a href="quienes-somos.php" class="<?= esc($navClass('quienes-somos')) ?>">Quiénes somos</a>
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

        <!-- Menú Mobile -->
        <div id="mobile-menu" class="hidden absolute top-full left-0 w-full bg-brand-card border-b border-brand-border shadow-md">
            <nav class="flex flex-col text-sm font-bold text-brand-blue uppercase divide-y divide-gray-100">
                <a href="index.php" class="<?= esc($navClassMobile('inicio')) ?>">Inicio</a>
                <a href="index.php#distritos" class="block px-6 py-4 hover:bg-brand-surface">Distritos de Lima</a>
                <a href="metodologia.php" class="<?= esc($navClassMobile('metodologia')) ?>">Metodología</a>
                <a href="quienes-somos.php" class="<?= esc($navClassMobile('quienes-somos')) ?>">Quiénes somos</a>
            </nav>
        </div>
    </header>
