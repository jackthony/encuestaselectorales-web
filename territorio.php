<?php
/**
 * territorio.php — aggregated regional/provincial survey catalog.
 *
 * Uses the current district catalog to group districts by región or provincia
 * without inventing a new data source. This gives the public site a visible
 * landing page for the data the user is already sending, while the detailed
 * voting flow stays on distrito.php.
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/encuestas.php';

$data = require __DIR__ . '/includes/data.php';
$distritos = $data['distritos'];
$candidatos = $data['candidatos'];

$nivel = strtolower(trim((string) ($_GET['nivel'] ?? '')));
$slug = strtolower(trim((string) ($_GET['slug'] ?? '')));

$nivelesValidos = ['region', 'provincia'];
if (!in_array($nivel, $nivelesValidos, true) || $slug === '') {
    $nivel = '';
}

$nivelLabel = $nivel === 'provincia' ? 'Provincia' : ($nivel === 'region' ? 'Región' : 'Territorio');
$territoryName = territoryDisplayName($slug);

$distritosTerritorio = [];
foreach ($distritos as $distrito) {
    if ($nivel !== '' && (($distrito[$nivel] ?? '') === $slug)) {
        $distritosTerritorio[] = $distrito;
    }
}

$territoryRound = $nivel !== '' ? getRondaActiva($slug, $nivel) : null;

$candidatosPorDistrito = [];
foreach ($candidatos as $candidato) {
    $districtId = (string) ($candidato['distritoId'] ?? '');
    if ($districtId === '') {
        continue;
    }
    $candidatosPorDistrito[$districtId][] = $candidato;
}

$totalCandidatos = 0;
$rondasActivas = 0;
foreach ($distritosTerritorio as $distrito) {
    $totalCandidatos += count($candidatosPorDistrito[$distrito['id']] ?? []);
    if (getRondaActiva($distrito['id'])) {
        $rondasActivas++;
    }
}
if ($territoryRound) {
    $rondasActivas++;
}

$pageTitle = $nivel !== ''
    ? $nivelLabel . ' ' . $territoryName . ' | EncuestasElectorales.pe'
    : 'Encuestas por territorio | EncuestasElectorales.pe';
$pageDescription = 'Encuestas, candidaturas y rondas activas agrupadas por región y provincia.';
$activeNav = 'inicio';
?><!doctype html>
<html lang="es">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="flex-grow w-full bg-brand-bg">
<?php if ($nivel === ''): ?>
        <section class="max-w-4xl mx-auto px-4 py-16 text-center">
            <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-6">
                <i class="fas fa-map"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-blue mb-3">Encuestas por territorio</h1>
            <p class="text-brand-muted leading-relaxed mb-8">
                Usa la búsqueda de la home para entrar a una región, provincia o distrito. Las páginas territoriales ya están preparadas para agrupar la data cuando llegue el siguiente lote.
            </p>
            <a href="index.php" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-6 rounded-xl hover:bg-[#0a2060] transition-colors">
                Ir a la home
            </a>
        </section>
<?php else: ?>
        <section class="relative bg-brand-blue text-white overflow-hidden border-b border-brand-blue/80">
            <div class="absolute inset-0 bg-grid-pattern opacity-30"></div>
            <div class="relative max-w-7xl mx-auto px-4 py-14 md:py-16">
                <nav class="text-[11px] font-bold uppercase tracking-wider text-white/70 mb-5">
                    <a href="index.php" class="hover:text-brand-green transition-colors">Inicio</a>
                    <span class="mx-2 text-white/40">/</span>
                    <span><?= esc($nivelLabel) ?></span>
                    <span class="mx-2 text-white/40">/</span>
                    <span class="text-brand-green"><?= esc($territoryName) ?></span>
                </nav>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold tracking-tight leading-tight">
                    <?= esc($nivelLabel) ?> <?= esc($territoryName) ?>
                </h1>
                <p class="text-white/80 text-lg md:text-xl max-w-3xl mt-4 leading-relaxed">
                    <?= esc($totalCandidatos) ?> candidaturas visibles en <?= esc((string) count($distritosTerritorio)) ?> distritos, con <?= esc((string) $rondasActivas) ?> rondas activas detectadas.
                </p>
<?php if ($territoryRound): ?>
                <div class="mt-6 inline-flex flex-col gap-2 rounded-2xl bg-white/10 border border-white/15 px-5 py-4 backdrop-blur-sm">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-brand-green"><?= esc(surveyScopeLabel($territoryRound)) ?></div>
                    <div class="text-sm text-white/90 leading-relaxed"><?= esc($territoryRound['titulo']) ?></div>
                </div>
<?php endif; ?>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-brand-card border border-brand-border rounded-2xl p-5">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Distritos</div>
                    <div class="text-3xl font-serif font-bold text-brand-blue"><?= esc((string) count($distritosTerritorio)) ?></div>
                </div>
                <div class="bg-brand-card border border-brand-border rounded-2xl p-5">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Candidaturas</div>
                    <div class="text-3xl font-serif font-bold text-brand-blue"><?= esc((string) $totalCandidatos) ?></div>
                </div>
                <div class="bg-brand-card border border-brand-border rounded-2xl p-5">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-2">Rondas activas</div>
                    <div class="text-3xl font-serif font-bold text-brand-blue"><?= esc((string) $rondasActivas) ?></div>
                </div>
            </div>

<?php if (count($distritosTerritorio) === 0): ?>
            <div class="bg-brand-card border border-brand-border rounded-2xl p-8 text-center">
                <div class="w-14 h-14 bg-brand-surface border border-brand-border rounded-full flex items-center justify-center text-2xl text-brand-muted mx-auto mb-4">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h2 class="text-2xl font-serif font-bold text-brand-blue mb-2">Aún no hay data para este ámbito</h2>
                <p class="text-brand-muted leading-relaxed">Cuando cargues el siguiente lote de candidaturas, esta página empezará a agruparlas automáticamente.</p>
            </div>
<?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<?php foreach ($distritosTerritorio as $distrito): ?>
<?php
    $candidatosDistrito = $candidatosPorDistrito[$distrito['id']] ?? [];
    $rondaActiva = getRondaActiva($distrito['id'], 'distrito');
?>
                <article class="bg-brand-card border border-brand-border rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-1"><?= esc($nivelLabel) ?></div>
                            <h2 class="text-2xl font-serif font-bold text-brand-blue leading-tight"><?= esc($distrito['nombre']) ?></h2>
                            <p class="text-sm text-brand-muted mt-1">Provincia <?= esc(territoryDisplayName((string) ($distrito['provincia'] ?? ''))) ?> · Región <?= esc(territoryDisplayName((string) ($distrito['region'] ?? ''))) ?></p>
                        </div>
                        <?php if ($rondaActiva): ?>
                        <span class="inline-flex items-center gap-1.5 bg-[#f0fdf4] text-brand-green border border-[#dcfce7] px-2.5 py-1 rounded text-[9px] uppercase font-bold tracking-widest shadow-sm shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-green animate-pulse"></span> Voto web activo
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-3 text-[11px] text-brand-muted mb-5 font-medium border-b border-gray-50 pb-4">
                        <span class="flex items-center gap-1.5"><i class="fas fa-users opacity-60 text-brand-blue"></i> <?= esc((string) count($candidatosDistrito)) ?> candidaturas</span>
                        <?php if ($rondaActiva): ?>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="flex items-center gap-1.5"><i class="far fa-calendar-alt opacity-60 text-brand-blue"></i> Hasta <?= esc($rondaActiva['fecha_cierre']) ?></span>
                        <?php endif; ?>
                    </div>

<?php if (count($candidatosDistrito) === 0): ?>
                    <div class="bg-brand-surface border border-dashed border-brand-border rounded-xl p-5 text-sm text-brand-muted">
                        No hay candidaturas cargadas todavía para este distrito.
                    </div>
<?php else: ?>
                    <div class="space-y-3">
<?php foreach (array_slice($candidatosDistrito, 0, 3) as $candidato): $partido = findPartido((int) $candidato['partidoId']); $color = partyColorOrGray((int) $candidato['partidoId']); ?>
                        <div class="flex items-center gap-3 bg-brand-surface border border-brand-border rounded-xl p-3">
                            <div class="w-12 h-12 rounded-full overflow-hidden shrink-0 border-2 shadow-sm" style="border-color: <?= esc($color) ?>; background-color: #f8fafc;">
                                <img
                                    src="<?= esc(candidatePhotoSrc($candidato)) ?>"
                                    alt="<?= esc($candidato['nombre']) ?>"
                                    class="w-full h-full object-cover"
                                    onerror="this.onerror=null;this.src='assets/img/default-face.svg';"
                                >
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-brand-text leading-tight truncate"><?= esc($candidato['nombre']) ?></div>
                                <div class="text-[11px] text-brand-muted uppercase tracking-wider truncate">
                                    <?= esc($partido['nombre'] ?? '') ?><?php if ($partido): ?> <span class="text-[10px] font-normal opacity-70 ml-1">(<?= esc($partido['siglas']) ?>)</span><?php endif; ?>
                                </div>
                            </div>
                        </div>
<?php endforeach; ?>
                    </div>
<?php endif; ?>

                    <div class="mt-5 pt-4 border-t border-brand-border flex justify-between items-center">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Detalle territorial</span>
                        <a href="distrito.php?slug=<?= esc($distrito['id']) ?>" class="text-[13px] font-bold text-brand-blue hover:text-brand-green flex items-center gap-1.5 group transition-colors">
                            Ver distrito
                            <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </article>
<?php endforeach; ?>
            </div>
<?php endif; ?>
        </section>
<?php endif; ?>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
