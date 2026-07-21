<?php
/**
 * encuesta.php — poll (campo/field-study) detail view. Accepts ?id=.
 *
 * Rewritten 2026-07-19 (bl-11c-purge-datos-ficticios). The prior version was
 * entirely hardcoded fabricated content — not just a placeholder pollster
 * label, but invented result percentages attributed to real, named public
 * figures (Carlos Canales, Daniel Urresti, Francis Allison) in a race
 * ("Alcaldía de Lima Metropolitana") none of them may even be contesting in
 * 2026. That is exactly the highest-liability content category CLAUDE.md's
 * Editorial & Legal Rules exist to guard — real name, fabricated number,
 * wrong context, live in production. Full data wiring (BL-16) is still not
 * this item's job, but rendering nothing rather than something false is:
 * this page now looks up `?id=` against data/encuesta.json and shows a real
 * empty state when there's no match. Today that remains the expected path
 * because no real campo study exists yet.
 */

require_once __DIR__ . '/includes/helpers.php';

$data          = require __DIR__ . '/includes/data.php';
$encuestas     = $data['encuestas'];
$resultados    = $data['resultados'];
$encuestadoras = $data['encuestadoras'];
$candidatos    = $data['candidatos'];

$id = isset($_GET['id']) ? (string) $_GET['id'] : '';
$encuesta = null;
foreach ($encuestas as $e) {
    if ($e['id'] === $id) {
        $encuesta = $e;
        break;
    }
}

$resultado = null;
$encuestadora = null;
if ($encuesta) {
    foreach ($resultados as $r) {
        if ($r['encuestaId'] === $encuesta['id']) {
            $resultado = $r;
            break;
        }
    }
    foreach ($encuestadoras as $e) {
        if ($e['id'] === $encuesta['encuestadoraId']) {
            $encuestadora = $e;
            break;
        }
    }
}

$pageTitle = $encuesta
    ? 'Detalle del estudio — ' . esc($encuestadora['nombre'] ?? '') . ' | EncuestasElectorales.pe'
    : 'Estudio no disponible | EncuestasElectorales.pe';
$pageDescription = 'Ficha técnica y resultados de estudios de opinión pública registrados.';
$activeNav = '';
?><!doctype html>
<html lang="es">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans">

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-16">
<?php if (!$encuesta || !$resultado): ?>
        <div class="max-w-xl mx-auto text-center py-12">
            <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-6">
                <i class="fas fa-file-circle-question"></i>
            </div>
            <h1 class="text-2xl font-serif font-bold text-brand-blue mb-3">Este estudio no está disponible</h1>
            <p class="text-brand-muted leading-relaxed mb-8">
                Aún no tenemos publicado un estudio de campo con este identificador, o todavía no existen estudios reales registrados en la plataforma.
            </p>
            <a href="sondeos.php" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors">
                Ver sondeos activos
            </a>
        </div>
<?php else: ?>
        <nav class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-6">
            <a href="index.php" class="hover:text-brand-green transition-colors">Inicio</a>
            <span class="mx-2">/</span>
            <span class="text-brand-blue"><?= esc($encuestadora['nombre'] ?? '') ?></span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            <div class="lg:col-span-8 flex flex-col gap-8">
                <header class="border-b border-brand-border pb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="bg-brand-blue text-white text-[10px] font-bold px-2 py-1 uppercase tracking-widest rounded-sm">Elecciones Municipales</span>
                        <span class="text-xs font-bold text-brand-textMuted uppercase"><i class="far fa-calendar-alt mr-1"></i> <?= esc($encuesta['fechaFin']) ?></span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-blue leading-tight">
                        Intención de voto — <?= esc($encuestadora['nombre'] ?? '') ?>
                    </h1>
                </header>

                <section class="bg-white border border-brand-border p-6 md:p-8 rounded-lg shadow-sm">
                    <div class="flex justify-between items-end mb-8 border-b border-gray-100 pb-4">
                        <h2 class="text-xl font-bold text-brand-blue font-serif">Resultados</h2>
                        <span class="text-xs font-semibold text-gray-400 uppercase">Base: <?= (int) $encuesta['tamanoMuestra'] ?> casos</span>
                    </div>
                    <div class="space-y-6">
<?php foreach ($resultado['resultados'] as $r): $cand = findCandidato((int) $r['candidatoId']); if (!$cand) continue; $color = partyColorOrGray((int) $cand['partidoId']); $partido = findPartido((int) $cand['partidoId']); ?>
                        <div class="relative">
                            <div class="flex justify-between items-baseline mb-2">
                                <div>
                                    <div class="font-bold text-gray-900 text-lg leading-none"><?= esc($cand['nombre']) ?></div>
                                    <div class="text-xs text-brand-textMuted font-medium mt-1"><?= esc($partido['nombre'] ?? '') ?></div>
                                </div>
                                <div class="font-bold text-2xl text-brand-blue tabular-nums"><?= esc(pct($r['porcentaje'])) ?></div>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="background-color: <?= esc($color) ?>; width: <?= (float) $r['porcentaje'] ?>%"></div>
                            </div>
                        </div>
<?php endforeach; ?>
                        <div class="pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                <div class="text-xs text-brand-textMuted mb-1">Blanco/Viciado</div>
                                <div class="font-bold text-brand-blue text-lg"><?= esc(pct($resultado['votoBlancoNulo'])) ?></div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                <div class="text-xs text-brand-textMuted mb-1">Indecisos</div>
                                <div class="font-bold text-brand-blue text-lg"><?= esc(pct($resultado['indecisos'])) ?></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="lg:col-span-4 flex flex-col gap-6">
                <div class="bg-brand-blue text-white p-6 rounded-lg shadow-md">
                    <h3 class="font-serif text-lg font-bold mb-4 flex items-center gap-2 border-b border-white/20 pb-2">
                        <i class="fas fa-clipboard-list text-brand-green"></i> Ficha Técnica
                    </h3>
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Encuestadora</dt>
                            <dd class="font-medium"><?= esc($encuestadora['nombre'] ?? '') ?></dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Muestra</dt>
                                <dd class="font-medium"><?= (int) $encuesta['tamanoMuestra'] ?> casos</dd>
                            </div>
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Margen de Error</dt>
                                <dd class="font-medium text-brand-green">± <?= esc(pct($encuesta['margenError'])) ?></dd>
                            </div>
                        </div>
                        <div>
                            <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Nivel de Confianza</dt>
                            <dd class="font-medium"><?= esc(pct($encuesta['nivelConfianza'], 0)) ?></dd>
                        </div>
                        <div>
                            <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Fecha de Campo</dt>
                            <dd class="font-medium"><?= esc($encuesta['fechaInicio']) ?> al <?= esc($encuesta['fechaFin']) ?></dd>
                        </div>
<?php if ($encuestadora && !empty($encuestadora['web'])): ?>
                        <div>
                            <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Fuente</dt>
                            <dd class="font-medium"><a href="<?= esc($encuestadora['web']) ?>" class="underline hover:text-brand-green" target="_blank" rel="noopener">Sitio de la encuestadora</a></dd>
                        </div>
<?php endif; ?>
                    </dl>
                </div>
            </aside>
        </div>
<?php endif; ?>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
