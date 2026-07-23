<?php
/**
 * candidato.php — candidate profile / trend view. Accepts ?id= (matching
 * data/candidato.json's actual field — the prior `?dni=` was never wired to
 * anything real, per this file's own BL-10-era docblock).
 *
 * Rewritten 2026-07-19 (bl-11c-purge-datos-ficticios). The prior version was
 * entirely hardcoded fabricated content built around a real, named public
 * figure (Carlos Canales): an invented 6-month polling trend, a fake current
 * average, a fake photo, and three "últimos registros" cards all attributed
 * to a placeholder pollster. That is exactly the highest-liability content
 * category CLAUDE.md's Editorial & Legal Rules exist to guard. This page now
 * looks up a real `data/candidato.json` record and shows an honest empty
 * state for the trend chart / recent-studies list — no real per-candidate
 * poll history exists yet (that's BL-16, once BL-13b/BL-14 produce one).
 */

require_once __DIR__ . '/includes/helpers.php';

$data       = require __DIR__ . '/includes/data.php';
$candidatos = $data['candidatos'];
$encuestas  = $data['encuestas'];
$resultados = $data['resultados'];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$candidato = findCandidato($id);
$partido = $candidato ? findPartido((int) $candidato['partidoId']) : null;
$distrito = $candidato ? findDistritoById($candidato['distritoId']) : null;

// Real per-candidate result history — none exists yet (no closed
// online_propia round). When a real trend exists for this candidate, it
// renders here.
$historial = [];
foreach ($resultados as $r) {
    foreach ($r['resultados'] as $rr) {
        if ((int) $rr['candidatoId'] === $id) {
            $historial[] = ['resultado' => $r, 'valor' => $rr];
        }
    }
}

$pageTitle = $candidato
    ? 'Perfil de ' . esc($candidato['nombre']) . ' | EncuestasElectorales.pe'
    : 'Candidato no encontrado | EncuestasElectorales.pe';
$pageDescription = 'Perfil, partido y trayectoria de sondeos del candidato.';
$activeNav = '';
?><!doctype html>
<html lang="es">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-8 md:py-10 flex-grow w-full">
<?php if (!$candidato): ?>
        <div class="max-w-xl mx-auto text-center py-16">
            <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-6">
                <i class="fas fa-user-slash"></i>
            </div>
            <h1 class="text-2xl font-serif font-bold text-brand-blue mb-3">Candidato no encontrado</h1>
            <p class="text-brand-muted leading-relaxed mb-8">No tenemos un perfil registrado con este identificador.</p>
            <a href="sondeos.php" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors">Ver sondeos activos</a>
        </div>
<?php else: ?>
        <nav class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-6">
            <a href="index.php" class="hover:text-brand-green transition-colors">Inicio</a>
            <span class="mx-2">/</span>
<?php if ($distrito): ?>
            <a href="distrito.php?slug=<?= esc($distrito['id']) ?>" class="hover:text-brand-green transition-colors"><?= esc($distrito['nombre']) ?></a>
            <span class="mx-2">/</span>
<?php endif; ?>
            <span class="text-brand-blue">Perfil de Candidato</span>
        </nav>

        <div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden mb-8">
            <div class="p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-8">
                <div class="w-32 h-32 md:w-40 md:h-40 shrink-0 rounded-full border-4 border-gray-100 shadow-inner overflow-hidden bg-gray-50">
                    <img
                        src="<?= esc(candidatePhotoSrc($candidato)) ?>"
                        alt="<?= esc($candidato['nombre']) ?>"
                        class="w-full h-full object-cover"
                        onerror="this.onerror=null;this.src='assets/img/default-face.svg';"
                    >
                </div>
                <div class="flex-grow text-center md:text-left flex flex-col justify-center">
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-textMuted mb-2 justify-center md:justify-start">
                        <span class="bg-blue-50 text-brand-blue px-2.5 py-1 rounded-full border border-blue-100">Candidato Municipal</span>
<?php if (empty($candidato['activo'])): ?>
                        <span class="bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full border border-amber-100">Candidatura 2022, no vigente para 2026</span>
<?php else: ?>
                        <span>Elecciones 2026</span>
<?php endif; ?>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-serif font-bold text-brand-blue mb-2"><?= esc($candidato['nombre']) ?></h1>
                    <p class="text-lg text-gray-600 font-medium mb-4">
                        <?php if ($distrito): ?>Postulante a la <strong class="text-gray-900">Alcaldía de <?= esc($distrito['nombre']) ?></strong> por el partido<?php else: ?>Candidato por el partido<?php endif ?>
                        <strong style="color: <?= esc(partyColorOrGray((int) $candidato['partidoId'])) ?>;"><?= esc($partido['nombre'] ?? '') ?></strong>.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <section class="bg-white rounded-xl border border-brand-border shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                        <h2 class="text-xl font-serif font-bold text-brand-blue">Evolución en Encuestas</h2>
                    </div>
<?php if (count($historial) > 0): ?>
                    <div class="relative w-full h-[300px] md:h-[400px]">
                        <canvas id="trendChart"></canvas>
                    </div>
<?php else: ?>
                    <div class="text-center py-12 text-brand-muted">
                        <i class="fas fa-chart-line text-3xl mb-3 opacity-40"></i>
                        <p class="text-sm">Todavía no hay historial de sondeos para este candidato.</p>
                    </div>
<?php endif; ?>
                </section>
            </div>

            <aside class="space-y-6">
                <div class="bg-brand-blue text-white rounded-xl shadow-md p-6">
                    <h3 class="font-serif text-lg font-bold mb-4 flex items-center gap-2 border-b border-white/20 pb-2">
                        <i class="fas fa-list-ul text-brand-green"></i> Últimos Registros
                    </h3>
<?php if (count($historial) > 0): ?>
                    <div class="flex flex-col gap-4">
<?php foreach ($historial as $h): ?>
                        <div class="bg-white/10 border border-white/10 rounded-lg p-3">
                            <span class="text-lg font-bold text-white tabular-nums"><?= esc(pct($h['valor']['porcentaje'])) ?></span>
                        </div>
<?php endforeach; ?>
                    </div>
<?php else: ?>
                    <p class="text-sm text-blue-100 leading-relaxed">Aún no hay estudios registrados para este candidato.</p>
<?php endif; ?>
                </div>
            </aside>
        </div>
<?php endif; ?>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
