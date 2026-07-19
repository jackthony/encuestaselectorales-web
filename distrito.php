<?php
/**
 * distrito.php — district detail view. Accepts ?slug= (e.g. ?slug=miraflores).
 *
 * FINDING (tasks.md section 5, logged per rule 4 — "no inventes datos"):
 * unlike the other 7 pages, distrito.php has **no canvas-gemini source**.
 * design.md's target structure lists it (`distrito.php <- ?slug=miraflores`)
 * but none of the 8 canvas-gemini/*.html prototypes is a district-detail
 * page — proposal.md's "Why" is explicit that this refactor converts
 * "the 8 prototypes" (7 standalone pages + the GPS modal consolidated into
 * partials/widget-gps.php), which is exactly what canvas-gemini/ contains.
 * Inventing a full district-detail layout here (candidate roster markup,
 * result bars, etc.) with no validated design to relocate from would
 * violate constraint 2 ("no mejores el markup", the design is either
 * relocated from Canvas or it doesn't exist yet) and constraint 4 ("no
 * inventes datos") at once. Real district content already exists in
 * data/distrito.json, data/candidato.json, data/encuesta.json (BL-07/09/12)
 * — wiring it into a real layout is BL-16's job once a Canvas prototype
 * for this page exists, not this structural refactor's.
 *
 * Until then this file exists (it's referenced from header.php's "Distritos
 * de Lima" nav intent and card-sondeo.php's "Ver informe completo" links)
 * as a minimal, honest page built only from the already-validated shared
 * partials — no fabricated candidate/result content. It is exempted from
 * scripts/check-refactor.php's structural diff for the reason above and
 * instead gets an existence/render smoke check (see that script).
 */

require_once __DIR__ . '/includes/helpers.php';

$distritos = require __DIR__ . '/includes/data.php';
$distritos = $distritos['distritos'];

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$distrito = null;
foreach ($distritos as $d) {
    if ($d['id'] === $slug) {
        $distrito = $d;
        break;
    }
}

$pageTitle = $distrito
    ? esc($distrito['nombre']) . ' — Alcaldía Distrital | EncuestasElectorales.pe'
    : 'Distritos de Lima | EncuestasElectorales.pe';
$pageDescription = 'Sondeo ciudadano por distrito para las Elecciones Municipales de Lima 2026.';
$activeNav = '';
?><!doctype html>
<html lang="es" class="scroll-smooth">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

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

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="flex-grow w-full">
        <section class="bg-brand-surface border-b border-brand-border py-16 md:py-20 px-4 text-center">
            <div class="max-w-3xl mx-auto scroll-animate">
                <h1 class="text-3xl md:text-5xl font-serif font-bold text-brand-blue leading-tight mb-4">
                    <?= $distrito ? esc($distrito['nombre']) : 'Distritos de Lima' ?>
                </h1>
                <p class="text-lg text-brand-muted leading-relaxed">
                    <?php if ($distrito): ?>
                        Sondeo ciudadano abierto para la Alcaldía Distrital. Los resultados de este distrito se publican en cuanto haya participación suficiente.
                    <?php else: ?>
                        Elegí un distrito desde <a href="sondeos.php" class="text-brand-blue hover:text-brand-green transition-colors font-semibold">Sondeos Activos</a> para ver su detalle.
                    <?php endif; ?>
                </p>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/partials/widget-gps.php'; ?>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/voto-gps.js"></script>
</body>
</html>
