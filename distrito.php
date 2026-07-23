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
 *   - active round panel           -> a real `encuestas` row is currently open
 *   - own-poll evolution chart     -> an active round exists for this district
 *   - campo-studies sidebar        -> a real campo result exists,
 *                                     independent of every block above
 *
 * The field-study placeholder record is excluded explicitly below so this
 * page stays correct even if legacy seed data is still present locally.
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/encuestas.php';

$data          = require __DIR__ . '/includes/data.php';
$candidatos    = $data['candidatos'];
$encuestas     = $data['encuestas'];
$resultados    = $data['resultados'];
$encuestadoras = $data['encuestadoras'];

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$distrito = findDistritoById($slug);

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

$rondaActiva = $distrito ? getRondaActiva($distrito['id'], 'distrito') : null;

// Campo (third-party) study — real ones only.
$campoEncuesta = null;
$campoResultado = null;
$campoEncuestadora = null;
if ($distrito) {
    foreach ($encuestas as $e) {
        if (($e['distritoId'] ?? null) === $distrito['id'] && findEncuestadoraById((string) ($e['encuestadoraId'] ?? '')) !== null) {
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
                         The active round panel below is driven by MySQL. -->
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
                        <h2 class="text-2xl font-serif font-bold text-brand-blue mb-6">Lista de candidatos</h2>
<?php if ($esHistorico): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3">
                            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                            <div class="text-sm text-amber-900 leading-relaxed">
                                <strong>Candidatura 2022, no vigente para 2026:</strong> el JNE aún no admite candidaturas para <?= esc($distrito['nombre']) ?> en el proceso 2026. Esta lista corresponde al proceso municipal anterior.
                            </div>
                        </div>
<?php endif; ?>
<?php if ($rondaActiva): ?>
                        <form id="voto-panel" class="space-y-6" data-encuesta-id="<?= esc($rondaActiva['id']) ?>" data-ubigeo-votacion="<?= esc($distrito['id']) ?>" data-distrito-nombre="<?= esc($distrito['nombre']) ?>">
<?php endif; ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<?php foreach ($candidatosDistrito as $index => $c): $color = partyColorOrGray((int) $c['partidoId']); $partido = findPartido((int) $c['partidoId']); ?>
                            <label class="bg-brand-surface border border-brand-border rounded-xl p-5 flex items-center gap-4 cursor-pointer transition-all hover:border-brand-blue/30 hover:bg-white">
                                <?php if ($rondaActiva): ?>
                                <input type="radio" name="candidato" value="<?= esc((string) $c['id']) ?>" class="sr-only peer" <?= $index === 0 ? 'required' : '' ?>>
                                <?php endif; ?>
                                <div class="w-14 h-14 rounded-full overflow-hidden shrink-0 border-2 shadow-sm" style="border-color: <?= esc($color) ?>; background-color: #f8fafc;">
                                    <img
                                        src="<?= esc(candidatePhotoSrc($c)) ?>"
                                        alt="<?= esc($c['nombre']) ?>"
                                        class="w-full h-full object-cover"
                                        onerror="this.onerror=null;this.src='assets/img/default-face.svg';"
                                    >
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-brand-text leading-tight mb-1"><?= esc($c['nombre']) ?></div>
                                    <div class="text-xs font-semibold text-brand-muted uppercase tracking-wider">
                                        <?= esc($partido['nombre'] ?? '') ?><?php if ($partido): ?> <span class="text-[10px] font-normal opacity-70 ml-1">(<?= esc($partido['siglas']) ?>)</span><?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($rondaActiva): ?>
                                <span class="ml-auto flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-gray-300 text-transparent transition-colors peer-checked:border-brand-blue peer-checked:bg-brand-blue peer-checked:text-white">
                                    <i class="fas fa-check text-[10px]"></i>
                                </span>
                                <?php endif; ?>
                            </label>
<?php endforeach; ?>
                        </div>
<?php if ($rondaActiva): ?>
                        <div class="border-t border-brand-border pt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <p class="text-xs text-brand-muted leading-relaxed">
                                Selecciona un candidato y valida tu ubicación para registrar el voto de <?= esc($distrito['nombre']) ?>.
                            </p>
                            <button type="button" onclick="iniciarValidacion()" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors shadow-sm">
                                <i class="fas fa-location-arrow"></i> Registrar mi voto
                            </button>
                        </div>
                        </form>
<?php endif; ?>
<?php if ($rondaActiva): ?>
<?php $scopeLabel = surveyScopeLabel($rondaActiva, $distrito); ?>
                        <div class="mt-8 border-t border-brand-border pt-6" data-distrito="<?= esc($distrito['id']) ?>">
                            <div class="bg-[#f7fbff] border border-[#d7e7ff] rounded-2xl p-5 mb-5">
                                <div class="text-[10px] font-bold uppercase tracking-widest text-brand-blue mb-2"><?= esc($scopeLabel) ?></div>
                                <h3 class="font-serif font-bold text-xl text-brand-blue leading-snug mb-2"><?= esc($rondaActiva['titulo']) ?></h3>
                                <p class="text-sm text-brand-muted">
                                    Ronda <?= esc((string) $rondaActiva['numero_ronda']) ?> disponible hasta <?= esc($rondaActiva['fecha_cierre']) ?>.
                                </p>
                            </div>
                        </div>
<?php endif; ?>
                    </section>
<?php endif; ?>

<?php if ($rondaActiva): ?>
                    <!-- Own-poll evolution chart: a real online_propia round exists -->
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

<?php if ($distrito && $tieneCandidatos && $rondaActiva): ?>
    <?php require __DIR__ . '/partials/widget-gps.php'; ?>
<?php endif; ?>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
<?php if ($distrito && $tieneCandidatos && $rondaActiva): ?>
    <script src="assets/js/voto-gps.js?v=<?= filemtime(__DIR__ . '/assets/js/voto-gps.js') ?>"></script>
<?php endif; ?>
</body>
</html>
