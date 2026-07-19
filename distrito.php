<?php
/**
 * distrito.php — hybrid district dashboard. Accepts ?slug= (e.g. ?slug=miraflores).
 *
 * Rebuilt 2026-07-19 from canvas-gemini/tablero_electoral_growth_hack_hibrido.html,
 * superseding canvas-gemini/distrito.html (bl-11-responsive-wcag design.md,
 * "Priority 0" — that file explains why and what each block below maps to).
 * One template, independently-toggling blocks, not exclusive page states:
 *
 *   - growth-hack CTA (WhatsApp)   -> no candidato.json entries for this district
 *   - candidate roster             -> candidato.json entries exist
 *   - vote widget                  -> candidates exist AND VOTACION_EN_VIVO is true
 *   - own-poll evolution chart     -> a closed online_propia round exists (none do
 *                                     yet — data/encuesta.json has no `tipo` field
 *                                     until bl-13b ships, so this stays dark honestly,
 *                                     not by a hardcoded false)
 *   - campo-studies sidebar        -> a real (non-"ejemplo") campo result exists,
 *                                     independent of every block above
 *
 * The "ejemplo" placeholder record is excluded explicitly below rather than
 * relied on being deleted by bl-11c-purge-datos-ficticios — this page is
 * correct regardless of which of the two changes lands first.
 */

require_once __DIR__ . '/includes/helpers.php';

$data          = require __DIR__ . '/includes/data.php';
$distritos     = $data['distritos'];
$candidatos    = $data['candidatos'];
$encuestas     = $data['encuestas'];
$resultados    = $data['resultados'];
$encuestadoras = $data['encuestadoras'];

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$distrito = null;
foreach ($distritos as $d) {
    if ($d['id'] === $slug) {
        $distrito = $d;
        break;
    }
}

$candidatosDistrito = [];
if ($distrito) {
    foreach ($candidatos as $c) {
        if ($c['distritoId'] === $distrito['id']) {
            $candidatosDistrito[] = $c;
        }
    }
}
$tieneCandidatos = count($candidatosDistrito) > 0;

$esHistorico = false;
if ($tieneCandidatos) {
    $esHistorico = true;
    foreach ($candidatosDistrito as $c) {
        if (!empty($c['activo'])) {
            $esHistorico = false;
            break;
        }
    }
}

// Own-poll closed round (tipo='online_propia') — bl-13b hasn't shipped the
// `tipo` field yet, so this is always null today. Not hardcoded false: the
// day that field exists with a closed round, this lights up on its own.
$rondaPropiaCerrada = null;
if ($distrito) {
    foreach ($encuestas as $e) {
        if (($e['distritoId'] ?? null) === $distrito['id'] && ($e['tipo'] ?? null) === 'online_propia') {
            $rondaPropiaCerrada = $e;
            break;
        }
    }
}

// Campo (third-party) study — real ones only, "ejemplo" excluded explicitly.
$campoEncuesta = null;
$campoResultado = null;
$campoEncuestadora = null;
if ($distrito) {
    foreach ($encuestas as $e) {
        if (($e['distritoId'] ?? null) === $distrito['id'] && ($e['encuestadoraId'] ?? null) !== 'ejemplo') {
            $campoEncuesta = $e;
            break;
        }
    }
}
if ($campoEncuesta) {
    foreach ($resultados as $r) {
        if ($r['encuestaId'] === $campoEncuesta['id']) {
            $campoResultado = $r;
            break;
        }
    }
    foreach ($encuestadoras as $e) {
        if ($e['id'] === $campoEncuesta['encuestadoraId']) {
            $campoEncuestadora = $e;
            break;
        }
    }
}

$pageTitle = $distrito
    ? esc($distrito['nombre']) . ' — Alcaldía Distrital | EncuestasElectorales.pe'
    : 'Distritos del Perú | EncuestasElectorales.pe';
$pageDescription = 'Sondeo ciudadano por distrito para las Elecciones Regionales y Municipales del Perú 2026.';
$activeNav = '';

$whatsappNumero = '51971388435';
$whatsappTexto = rawurlencode($distrito
    ? 'Hola, quiero proponer un candidato para ' . $distrito['nombre']
    : 'Hola, quiero proponer un candidato');
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

    <main class="flex-grow w-full pb-20">
<?php if (!$distrito): ?>
        <section class="bg-brand-surface border-b border-brand-border py-16 md:py-20 px-4 text-center">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-3xl md:text-5xl font-serif font-bold text-brand-blue leading-tight mb-4">Distritos del Perú</h1>
                <p class="text-lg text-brand-muted leading-relaxed">
                    Elegí un distrito desde <a href="sondeos.php" class="text-brand-blue hover:text-brand-green transition-colors font-semibold">Sondeos Activos</a> para ver su detalle.
                </p>
            </div>
        </section>
<?php else: ?>
        <section class="max-w-7xl mx-auto px-4 pt-12">
            <nav class="text-[11px] font-bold text-brand-muted uppercase tracking-wider mb-3">
                <a href="index.php" class="hover:text-brand-green transition-colors">Perú</a>
                <span class="mx-2 text-gray-300">/</span>
                <?= esc(ucfirst($distrito['region'])) ?>
                <span class="mx-2 text-gray-300">/</span>
                <?= esc(ucfirst($distrito['provincia'])) ?>
            </nav>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-brand-blue tracking-tight leading-none mb-3">
                <?= esc($distrito['nombre']) ?>
            </h1>
            <p class="text-brand-muted text-lg font-medium mb-8">Tablero Electoral Municipal 2026</p>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 pb-16">

                <div class="lg:col-span-8 space-y-10">
<?php if (!$tieneCandidatos): ?>
                    <!-- Growth-hack CTA: no candidato.json entries for this district.
                         No "Encuesta Online - Semana N" ribbon here (unlike the Canvas
                         source) — no real round has ever opened (VOTACION_EN_VIVO is
                         false, no data/encuesta.json tipo='online_propia' record
                         exists yet), and claiming one would be exactly the fictional-
                         data problem bl-11c-purge-datos-ficticios exists to close.
                         scripts/check-refactor.php's structural check for this block
                         excludes that one ribbon element from the Canvas diff for the
                         same reason — see its own comment. -->
                    <section class="bg-brand-card border-2 border-dashed border-brand-green/40 rounded-2xl p-6 md:p-8 relative overflow-hidden">
                        <div class="flex items-start gap-5">
                            <div class="w-14 h-14 bg-[#e6f8f0] text-brand-greenText rounded-full flex items-center justify-center text-2xl shrink-0">
                                <i class="fas fa-users-viewfinder"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-serif font-bold text-brand-blue mb-2">Aún no hay candidatos para <?= esc($distrito['nombre']) ?></h2>
                                <p class="text-brand-textMuted leading-relaxed mb-6">
                                    El Jurado Nacional de Elecciones (JNE) publica las listas oficiales de candidatos admitidos el <strong>5 de agosto de 2026</strong>. Mientras tanto, ayúdanos a identificar a los candidatos de tu distrito.
                                </p>
                                <div class="bg-brand-surface border border-brand-border rounded-xl p-5 mb-6">
                                    <h3 class="font-bold text-brand-text mb-2 text-sm">¿Tu candidato no está en la lista?</h3>
                                    <p class="text-xs text-brand-textMuted mb-4">Escríbenos por WhatsApp para incluirlo y ser de los primeros en habilitar el sondeo ciudadano en <?= esc($distrito['nombre']) ?>.</p>
                                    <a href="https://wa.me/<?= esc($whatsappNumero) ?>?text=<?= $whatsappTexto ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#25D366] text-white font-bold py-3 px-6 rounded-xl hover:bg-[#20bd5a] transition-colors w-full sm:w-auto shadow-sm">
                                        <i class="fab fa-whatsapp text-lg"></i> Proponer candidato por WhatsApp
                                    </a>
                                </div>
                                <div class="text-[10px] text-gray-400 font-medium"><i class="fas fa-shield-alt mr-1"></i> Cuando el sondeo se active, cada voto exigirá validación de ubicación por GPS.</div>
                            </div>
                        </div>
                    </section>
<?php else: ?>
                    <!-- Candidate roster: candidato.json entries exist -->
                    <section class="bg-brand-card border border-brand-border rounded-2xl p-6 md:p-8">
                        <h2 class="text-2xl font-serif font-bold text-brand-blue mb-6">Candidatos a la Alcaldía Distrital</h2>
<?php if ($esHistorico): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3">
                            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                            <div class="text-sm text-amber-900 leading-relaxed">
                                <strong>Candidatura 2022, no vigente para 2026:</strong> el JNE aún no admite candidaturas para <?= esc($distrito['nombre']) ?> en el proceso 2026. Esta lista corresponde al proceso municipal anterior.
                            </div>
                        </div>
<?php endif; ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<?php foreach ($candidatosDistrito as $c): $color = partyColorOrGray((int) $c['partidoId']); $partido = findPartido((int) $c['partidoId']); ?>
                            <div class="bg-brand-surface border border-brand-border rounded-xl p-5 flex items-center gap-4">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-xl shrink-0" style="background-color: <?= esc($color) ?>;"><?= esc(iniciales($c['nombre'])) ?></div>
                                <div>
                                    <div class="font-bold text-brand-text leading-tight mb-1"><?= esc($c['nombre']) ?></div>
                                    <div class="text-xs font-semibold text-brand-muted uppercase tracking-wider">
                                        <?= esc($partido['nombre'] ?? '') ?><?php if ($partido): ?> <span class="text-[10px] font-normal opacity-70 ml-1">(<?= esc($partido['siglas']) ?>)</span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
<?php endforeach; ?>
                        </div>
<?php if (VOTACION_EN_VIVO): ?>
                        <!-- Vote widget: candidates exist AND voting is live. Dark today. -->
                        <form id="form-voto-distrito" class="mt-8 border-t border-brand-border pt-6" data-distrito="<?= esc($distrito['id']) ?>">
                            <h3 class="font-serif font-bold text-xl text-brand-blue leading-snug mb-5">¿Por quién votarías para la Alcaldía de <?= esc($distrito['nombre']) ?>?</h3>
                            <div class="space-y-3 mb-5">
<?php foreach ($candidatosDistrito as $c): ?>
                                <label class="flex items-center p-3 border border-brand-border rounded-xl hover:bg-brand-surface cursor-pointer transition-colors">
                                    <input type="radio" name="candidato" value="<?= esc((string) $c['id']) ?>" class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue accent-brand-blue">
                                    <div class="ml-3 flex-grow">
                                        <div class="text-sm font-bold text-brand-text"><?= esc($c['nombre']) ?></div>
                                    </div>
                                </label>
<?php endforeach; ?>
                            </div>
                            <button type="button" onclick="document.getElementById('modal-overlay').classList.remove('hidden'); document.getElementById('paso-softask').classList.remove('hidden');" class="w-full bg-brand-blue text-white font-bold py-3.5 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm">
                                Registrar mi voto
                            </button>
                            <p class="text-[10px] text-gray-400 text-center mt-3">Protegido por verificación GPS y rate-limiting en servidor.</p>
                        </form>
<?php endif; ?>
                    </section>
<?php endif; ?>

<?php if ($rondaPropiaCerrada): ?>
                    <!-- Own-poll evolution chart: a closed online_propia round exists -->
                    <section>
                        <h2 class="text-2xl font-serif font-bold text-brand-blue mb-4">Evolución del Voto Online</h2>
                        <div class="bg-brand-card border border-brand-border rounded-2xl p-6">
                            <div class="relative w-full h-[300px]">
                                <canvas id="evolucionChart"></canvas>
                            </div>
                            <p class="text-xs text-brand-muted mt-4 text-center italic">* Basado exclusivamente en nuestro sondeo digital con validación GPS. No representa muestras probabilísticas de campo.</p>
                        </div>
                    </section>
<?php endif; ?>
                </div>

                <aside class="lg:col-span-4 space-y-4">
                    <h2 class="text-xl font-serif font-bold text-brand-blue flex items-center gap-2 border-b border-brand-border pb-3">
                        <i class="fas fa-clipboard-check text-brand-muted"></i> Encuestas de Campo
                    </h2>
<?php if ($campoEncuesta && $campoResultado): ?>
                    <article class="bg-brand-card border border-brand-border rounded-xl overflow-hidden">
                        <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="text-[10px] font-bold text-brand-muted uppercase tracking-widest mb-1"><?= esc($campoEncuestadora['nombre'] ?? '') ?></div>
                            <div class="text-xs text-brand-muted flex items-center gap-1.5">
                                <i class="far fa-calendar-alt"></i> <?= esc($campoEncuesta['fechaInicio']) ?> al <?= esc($campoEncuesta['fechaFin']) ?>
                            </div>
                        </div>
                        <div class="p-4 space-y-4">
<?php foreach ($campoResultado['resultados'] as $r): $cand = findCandidato((int) $r['candidatoId']); if (!$cand) continue; $color = partyColorOrGray((int) $cand['partidoId']); ?>
                            <div>
                                <div class="flex justify-between items-baseline mb-1">
                                    <div class="text-xs font-bold text-brand-text"><?= esc($cand['nombre']) ?></div>
                                    <div class="text-sm font-extrabold text-brand-blue tabular-nums"><?= esc(pct($r['porcentaje'])) ?></div>
                                </div>
                                <div class="w-full bg-brand-surface h-1.5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="background-color: <?= esc($color) ?>; width: <?= (float) $r['porcentaje'] ?>%"></div>
                                </div>
                            </div>
<?php endforeach; ?>
                            <a href="encuesta.php?id=<?= esc($campoEncuesta['id']) ?>" class="text-sm text-brand-blue font-bold hover:text-brand-green transition-colors flex items-center gap-1 pt-2">
                                Ver resultados <i class="fas fa-chevron-right text-[10px] mt-0.5"></i>
                            </a>
                        </div>
                    </article>
<?php else: ?>
                    <div class="bg-brand-card border border-brand-border rounded-xl p-6 text-center">
                        <p class="text-xs text-brand-muted leading-relaxed">Aún no hay estudios de campo publicados para <?= esc($distrito['nombre']) ?>.</p>
                    </div>
<?php endif; ?>
                </aside>
            </div>
        </section>
<?php endif; ?>
    </main>

<?php if ($distrito && $tieneCandidatos && VOTACION_EN_VIVO): ?>
    <?php require __DIR__ . '/partials/widget-gps.php'; ?>
<?php endif; ?>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
<?php if ($distrito && $tieneCandidatos && VOTACION_EN_VIVO): ?>
    <script src="assets/js/voto-gps.js"></script>
<?php endif; ?>
</body>
</html>
