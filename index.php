<?php
/**
 * index.php — national home portal.
 *
 * Rebuilt 2026-07-19 (bl-11b-portal-nacional-home) from
 * canvas-gemini/portal_nacional_home.html, superseding the BL-10 rebuild of
 * portal_de_encuestas.html. The prior version was Lima-scoped copy ("Lima
 * 2026" throughout) and — like index.php's sibling pages fixed by
 * bl-11c-purge-datos-ficticios — hardcoded fabricated content: an invented
 * "Ulises Villegas" leading Comas, and a fake "Alcaldía de Lima" vote
 * simulator naming real public figures (Carlos Canales, Julio Gagó, Alberto
 * Tejada) with invented percentages. None of that is ported here.
 *
 * The prototype's two hub columns ("Encuestas Web Activas", "Últimos
 * Estudios de Campo") each show one example card in the Canvas file for
 * design reference — today, with zero online rounds ever opened and zero
 * real campo studies, both columns render their own real empty state
 * instead (see design.md).
 */

require_once __DIR__ . '/includes/helpers.php';

$data       = require __DIR__ . '/includes/data.php';
$distritos  = $data['distritos'];
$encuestas  = $data['encuestas'];

// Real campo (third-party) studies only — "ejemplo" already purged from the
// data itself (bl-11c), but filtered defensively here too regardless of
// which change landed first.
$estudiosCampo = [];
foreach ($encuestas as $e) {
    if (($e['encuestadoraId'] ?? null) !== 'ejemplo') {
        $estudiosCampo[] = $e;
    }
}

// Real open online rounds — none exist yet (no `tipo` field until bl-13b/BL-14).
$rondasAbiertas = [];
foreach ($encuestas as $e) {
    if (($e['tipo'] ?? null) === 'online_propia') {
        $rondasAbiertas[] = $e;
    }
}

$pageTitle = 'Encuestas Electorales Perú 2026 - Transparencia y Datos';
$pageDescription = 'El pulso electoral del Perú: sondeos ciudadanos por región, provincia y distrito para las Elecciones Regionales y Municipales 2026.';
$activeNav = 'inicio';

$whatsappNumero = '51971388435';
?><!doctype html>
<html lang="es">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
    <script type="application/json" id="distritos-data"><?= json_encode($distritos, JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="flex-grow">

        <!-- Hero Nacional -->
        <section class="bg-brand-blue text-white pb-16 pt-12 px-4 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-grid-pattern"></div>
            <div class="max-w-4xl mx-auto text-center relative z-10">
                <span class="inline-block py-1.5 px-4 rounded-full bg-brand-green/20 text-brand-green font-semibold text-[11px] uppercase tracking-widest mb-6 border border-brand-green/30">
                    <span class="inline-block w-2 h-2 rounded-full bg-brand-green mr-1 animate-pulse"></span>
                    Elecciones Regionales y Municipales 2026
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold mb-6 leading-tight tracking-tight">
                    El pulso electoral de todo el Perú, <span class="text-brand-green">en tiempo real.</span>
                </h1>
                <p class="text-lg md:text-xl text-white/80 mb-10 max-w-2xl mx-auto font-medium">
                    Vota de forma segura, consulta las tendencias de tu distrito y revisa los últimos estudios de campo de las encuestadoras formales.
                </p>

                <div class="relative max-w-3xl mx-auto">
                    <div class="bg-white rounded-xl p-2 md:p-3 shadow-2xl flex flex-col md:flex-row gap-2">
                        <div class="relative flex-grow flex items-center px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 focus-within:border-brand-blue focus-within:ring-1 focus-within:ring-brand-blue transition-all">
                            <i class="fas fa-map-marker-alt text-brand-blue mr-3 text-xl opacity-70"></i>
                            <input type="text" id="buscador-hero" placeholder="Escribe tu región, provincia o distrito..." class="w-full bg-transparent text-gray-800 focus:outline-none text-lg placeholder-gray-400" autocomplete="off">
                        </div>
                        <button type="button" id="buscador-hero-btn" class="bg-brand-blue text-white font-bold px-8 py-3.5 rounded-lg hover:bg-[#0c2466] transition-colors w-full md:w-auto shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    <div id="buscador-hero-resultados" class="hidden absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl z-20 overflow-hidden text-left"></div>
                </div>
            </div>
        </section>

        <!-- Hub de Encuestas -->
        <section class="max-w-6xl mx-auto px-4 py-12 md:py-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">

                <!-- Columna Izquierda: Actividad Online (Growth Hack) -->
                <div class="space-y-6">
                    <div class="border-b border-brand-border pb-3">
                        <h2 class="text-2xl font-serif font-bold text-brand-blue flex items-center">
                            <i class="fas fa-chart-line text-brand-green mr-2.5"></i> Encuestas Web Activas
                        </h2>
                        <p class="text-sm text-brand-muted mt-1">Sondeos ciudadanos en curso con validación GPS.</p>
                    </div>

                    <div class="space-y-4">
<?php if (count($rondasAbiertas) === 0): ?>
                        <div class="bg-blue-50/50 rounded-2xl p-8 border border-blue-100/50 text-center">
                            <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-2xl text-brand-blue mx-auto mb-4 shadow-sm border border-blue-100">
                                <i class="fas fa-vote-yea"></i>
                            </div>
                            <h3 class="font-bold text-lg text-brand-blue mb-2">¿Quieres medir a tu distrito?</h3>
                            <p class="text-sm text-brand-muted mb-6 max-w-sm mx-auto leading-relaxed">
                                Busca tu ubicación en el buscador de arriba para ver a los candidatos, o proponé el tuyo si tu distrito aún no tiene ninguno.
                            </p>
                            <a href="https://wa.me/<?= esc($whatsappNumero) ?>?text=<?= rawurlencode('Hola, quiero proponer un candidato') ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-white text-brand-blue font-bold px-6 py-2.5 rounded-xl border border-gray-200 hover:border-brand-blue hover:text-brand-green transition-all shadow-sm text-sm">
                                <i class="fab fa-whatsapp mr-1"></i> Proponer un candidato
                            </a>
                        </div>
<?php else: ?>
<?php foreach ($rondasAbiertas as $ronda): $d = null; foreach ($distritos as $dd) { if ($dd['id'] === ($ronda['distritoId'] ?? null)) { $d = $dd; break; } } if (!$d) continue; ?>
                        <a href="distrito.php?slug=<?= esc($d['id']) ?>" class="block bg-brand-card rounded-2xl p-6 border border-brand-border hover:shadow-lg hover:border-brand-blue/30 transition-all">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="font-bold text-xl text-brand-blue"><?= esc($d['nombre']) ?></h3>
                                <span class="bg-[#e6f8f0] text-brand-greenText text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-widest border border-[#15ba75]/30">Ronda Abierta</span>
                            </div>
                        </a>
<?php endforeach; ?>
<?php endif; ?>
                    </div>
                </div>

                <!-- Columna Derecha: Estudios de Campo (Solo lectura) -->
                <div class="space-y-6">
                    <div class="border-b border-brand-border pb-3">
                        <h2 class="text-2xl font-serif font-bold text-brand-blue flex items-center">
                            <i class="fas fa-clipboard-check text-gray-400 mr-2.5"></i> Últimos Estudios de Campo
                        </h2>
                        <p class="text-sm text-brand-muted mt-1">Reportes oficiales de encuestadoras registradas (JNE).</p>
                    </div>

                    <div class="space-y-4">
<?php if (count($estudiosCampo) === 0): ?>
                        <div class="bg-brand-card rounded-2xl p-6 border border-brand-border text-center">
                            <p class="text-sm text-brand-muted leading-relaxed">Aún no hay estudios de campo publicados.</p>
                        </div>
<?php else: ?>
<?php foreach ($estudiosCampo as $estudio): $encuestadora = null; foreach ($data['encuestadoras'] as $e) { if ($e['id'] === $estudio['encuestadoraId']) { $encuestadora = $e; break; } } ?>
                        <a href="encuesta.php?id=<?= esc($estudio['id']) ?>" class="block bg-brand-card rounded-2xl p-6 border border-brand-border hover:shadow-md transition-shadow">
                            <div class="text-[10px] font-bold text-brand-muted uppercase tracking-widest mb-1"><?= esc($encuestadora['nombre'] ?? '') ?></div>
                            <div class="text-xs font-semibold text-brand-muted"><i class="far fa-calendar-alt mr-1"></i> <?= esc($estudio['fechaInicio']) ?> al <?= esc($estudio['fechaFin']) ?></div>
                        </a>
<?php endforeach; ?>
<?php endif; ?>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
