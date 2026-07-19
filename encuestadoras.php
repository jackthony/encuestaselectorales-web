<?php
/**
 * encuestadoras.php — pollster directory. Source:
 * canvas-gemini/directorio_de_encuestadoras.html, relocated verbatim.
 * No legal scrub applies (tasks.md 6.2): listing that Ipsos Perú, Datum
 * Internacional and CPI exist and are JNE-registered is factual — the
 * directory's whole purpose — and no poll *result* is attributed to any
 * of them here, only registry metadata (registration number, methodology,
 * study count, scope).
 */

require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Directorio de Encuestadoras Registradas JNE - Encuestas Electorales';
$pageDescription = 'Directorio oficial de encuestadoras de opinión pública registradas en el JNE para las Elecciones 2026.';
$activeNav = '';
?><!doctype html>
<html lang="es">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <!-- BARRA TIPO TICKER -->
    <div class="bg-brand-green text-white text-[11px] md:text-xs font-semibold py-2 px-4 w-full relative z-20 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div id="reloj" class="flex items-center gap-2 font-mono tracking-wide" aria-live="polite">
                <i class="far fa-clock"></i> Cargar hora...
            </div>
            <div class="hidden md:flex items-center gap-4">
                <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] uppercase tracking-widest">Transparencia</span>
                <span class="text-white/90">Directorio auditado según normativa JNE</span>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/partials/header.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="max-w-7xl mx-auto px-4 py-8 md:py-12 flex-grow w-full">

        <!-- Migas de Pan -->
        <nav class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-6 scroll-animate">
            <a href="index.php" class="hover:text-brand-green transition-colors">Inicio</a>
            <span class="mx-2">/</span>
            <span class="text-brand-blue">Directorio de Encuestadoras</span>
        </nav>

        <!-- Cabecera de Sección -->
        <div class="mb-10 scroll-animate border-b border-brand-border pb-8">
            <h1 class="text-3xl md:text-5xl font-serif font-bold text-brand-blue leading-tight mb-4">
                Encuestadoras Registradas
            </h1>
            <p class="text-lg text-gray-600 font-medium leading-relaxed max-w-3xl">
                Listado oficial de las empresas de investigación de opinión pública. Según la Ley Orgánica de Elecciones, solo pueden publicar pronósticos electorales aquellas entidades inscritas en el Registro Electoral de Encuestadoras del Jurado Nacional de Elecciones (REE-JNE).
            </p>
        </div>

        <!-- Filtros y Buscador (Prototipo UI) -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8 scroll-animate">
            <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0 no-scrollbar">
                <button class="bg-brand-blue text-white px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap shadow-sm hover:bg-[#0a2060] transition-colors">Todas</button>
                <button class="bg-white border border-brand-border text-brand-text px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap shadow-sm hover:border-brand-blue transition-colors">Vigentes JNE</button>
                <button class="bg-white border border-brand-border text-brand-text px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap shadow-sm hover:border-brand-blue transition-colors">Con Faltas</button>
            </div>

            <div class="relative w-full md:w-64 shrink-0">
                <input type="text" placeholder="Buscar encuestadora..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-brand-green focus:ring-1 focus:ring-brand-green transition-colors">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
            </div>
        </div>

        <!-- GRILLA DE ENCUESTADORAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="pollster-grid">

            <!-- TARJETA 1 -->
            <article class="bg-white rounded-xl border border-brand-border shadow-sm hover:shadow-lg hover:border-brand-blue/30 transition-all duration-300 flex flex-col group scroll-animate">
                <div class="p-6 border-b border-gray-100 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-brand-blue font-serif mb-1 group-hover:text-brand-green transition-colors">Ipsos Perú</h2>
                        <div class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-2">Ipsos Opinión y Mercado S.A.</div>
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                        </span>
                    </div>
                    <!-- Placeholder Logo -->
                    <div class="w-12 h-12 rounded bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-chart-pie text-gray-300 text-xl"></i>
                    </div>
                </div>

                <div class="p-6 flex-grow bg-gray-50/50">
                    <dl class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm">
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Registro JNE</dt>
                            <dd class="font-medium text-gray-900 font-mono text-xs">0001-REE/JNE</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Metodología</dt>
                            <dd class="font-medium text-gray-900">Presencial / Hogares</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Estudios 2026</dt>
                            <dd class="font-medium text-brand-blue text-lg">12 <span class="text-xs text-gray-400 font-normal">publicados</span></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Alcance</dt>
                            <dd class="font-medium text-gray-900">Nacional y Regional</dd>
                        </div>
                    </dl>
                </div>

                <div class="p-4 border-t border-gray-100 mt-auto">
                    <a href="#" class="flex justify-between items-center text-sm font-bold text-brand-blue group-hover:text-brand-green transition-colors w-full">
                        Ver perfil y estudios <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </article>

            <!-- TARJETA 2 -->
            <article class="bg-white rounded-xl border border-brand-border shadow-sm hover:shadow-lg hover:border-brand-blue/30 transition-all duration-300 flex flex-col group scroll-animate delay-100">
                <div class="p-6 border-b border-gray-100 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-brand-blue font-serif mb-1 group-hover:text-brand-green transition-colors">Datum Internacional</h2>
                        <div class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-2">Datum Internacional S.A.</div>
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-chart-line text-gray-300 text-xl"></i>
                    </div>
                </div>

                <div class="p-6 flex-grow bg-gray-50/50">
                    <dl class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm">
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Registro JNE</dt>
                            <dd class="font-medium text-gray-900 font-mono text-xs">0002-REE/JNE</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Metodología</dt>
                            <dd class="font-medium text-gray-900">Presencial / CATI</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Estudios 2026</dt>
                            <dd class="font-medium text-brand-blue text-lg">8 <span class="text-xs text-gray-400 font-normal">publicados</span></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Alcance</dt>
                            <dd class="font-medium text-gray-900">Nacional</dd>
                        </div>
                    </dl>
                </div>

                <div class="p-4 border-t border-gray-100 mt-auto">
                    <a href="#" class="flex justify-between items-center text-sm font-bold text-brand-blue group-hover:text-brand-green transition-colors w-full">
                        Ver perfil y estudios <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </article>

            <!-- TARJETA 3 -->
            <article class="bg-white rounded-xl border border-brand-border shadow-sm hover:shadow-lg hover:border-brand-blue/30 transition-all duration-300 flex flex-col group scroll-animate delay-200">
                <div class="p-6 border-b border-gray-100 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-brand-blue font-serif mb-1 group-hover:text-brand-green transition-colors">CPI</h2>
                        <div class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-2">Compañía Peruana de Estudios</div>
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-poll text-gray-300 text-xl"></i>
                    </div>
                </div>

                <div class="p-6 flex-grow bg-gray-50/50">
                    <dl class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm">
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Registro JNE</dt>
                            <dd class="font-medium text-gray-900 font-mono text-xs">0010-REE/JNE</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Metodología</dt>
                            <dd class="font-medium text-gray-900">Presencial (Hogares)</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Estudios 2026</dt>
                            <dd class="font-medium text-brand-blue text-lg">5 <span class="text-xs text-gray-400 font-normal">publicados</span></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Alcance</dt>
                            <dd class="font-medium text-gray-900">Nacional y Local</dd>
                        </div>
                    </dl>
                </div>

                <div class="p-4 border-t border-gray-100 mt-auto">
                    <a href="#" class="flex justify-between items-center text-sm font-bold text-brand-blue group-hover:text-brand-green transition-colors w-full">
                        Ver perfil y estudios <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </article>

            <!-- TARJETA 4 (Estado Suspendido Ejemplo) -->
            <article class="bg-gray-50 rounded-xl border border-gray-200 shadow-sm flex flex-col group scroll-animate delay-300 opacity-80">
                <div class="p-6 border-b border-gray-200 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-600 font-serif mb-1">Encuestadora X</h2>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Ejemplo de Suspensión S.A.C.</div>
                        <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Suspendida
                        </span>
                    </div>
                </div>
                <div class="p-6 flex-grow bg-white/50">
                    <p class="text-sm text-gray-500 font-medium">Esta empresa no puede publicar estudios electorales por no actualizar su registro ante la Dirección Nacional de Fiscalización del JNE.</p>
                </div>
            </article>

        </div>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
