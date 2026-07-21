<?php
/**
 * sondeos.php — citizen-sondeo feed (opt-in web poll), by district.
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/encuestas.php';

$data = require __DIR__ . '/includes/data.php';
$totalDistritos = count($data['distritos']);
$rondasAbiertas = getRondasActivas();

$heroWords = [];
foreach ($rondasAbiertas as $ronda) {
    $distrito = findDistritoById((string) ($ronda['distrito_id'] ?? ''));
    if ($distrito) {
        $heroWords[] = $distrito['nombre'];
    }
}

if (count($heroWords) === 0) {
    foreach (array_slice($data['distritos'], 0, 4) as $distrito) {
        if (!empty($distrito['nombre'])) {
            $heroWords[] = $distrito['nombre'];
        }
    }
}

$heroWords[] = 'tu distrito';
$heroWords = array_slice(array_values(array_unique($heroWords)), 0, 4);

$pageTitle = 'EncuestasElectorales.pe - Sondeo en vivo';
$activeNav = 'inicio';
$whatsappNumero = '51971388435';

function formatVoteDate(?string $value): string
{
    if (!$value) {
        return 'fecha pendiente';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('d/m/Y H:i', $timestamp);
}
?><!doctype html>
<html lang="es" class="scroll-smooth">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <div class="bg-brand-green text-[#062010] text-[11px] md:text-xs font-bold py-2 px-4 w-full relative z-50 shadow-sm">
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

    <main class="flex-grow w-full bg-brand-bg">
        <section class="relative bg-brand-blue text-white overflow-hidden border-b border-brand-blue/80">
            <div class="absolute inset-0 bg-grid-pattern opacity-30"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-brand-blue to-transparent"></div>

            <div class="relative max-w-7xl mx-auto px-4 py-20 md:py-28 flex flex-col items-center text-center">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-bold leading-tight mb-6 tracking-tight max-w-4xl scroll-animate">
                    ¿Quién va ganando en <br class="hidden md:block"/>
                    <span class="word-slider text-brand-green">
<?php foreach ($heroWords as $word): ?>
                        <span><?= esc($word) ?>?</span>
<?php endforeach; ?>
                    </span>
                </h1>
                <p class="text-lg md:text-xl text-blue-100 max-w-2xl font-medium mb-10 leading-relaxed scroll-animate delay-100">
                    El portal cívico de inteligencia electoral. Mira las rondas abiertas, entra al distrito y vota con validación GPS.
                </p>

                <div class="flex gap-4 flex-wrap justify-center scroll-animate delay-100">
                    <a href="#sondeos-activos" class="bg-brand-green hover:bg-[#12a668] text-white font-bold py-3 px-8 rounded-full shadow-lg transition-transform hover:-translate-y-0.5">
                        Ver sondeos activos
                    </a>
                    <a href="index.php" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 text-white font-bold py-3 px-8 rounded-full transition-colors">
                        Ir a la home
                    </a>
                </div>
            </div>
        </section>

        <section id="sondeos-activos" class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            <div class="lg:col-span-8">
                <div class="flex justify-between items-baseline mb-8 border-b border-brand-border pb-4">
                    <h2 class="text-3xl font-serif font-bold text-brand-blue">Sondeos Activos</h2>
                    <span class="text-sm font-bold text-brand-muted uppercase tracking-wider"><?= esc((string) $totalDistritos) ?> Distritos</span>
                </div>

<?php if (count($rondasAbiertas) === 0): ?>
                <div class="bg-brand-card border border-brand-border rounded-2xl p-8 md:p-10 shadow-sm">
                    <div class="w-14 h-14 bg-[#e6f8f0] text-brand-greenText rounded-full flex items-center justify-center text-2xl mb-4">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h3 class="font-serif font-bold text-2xl text-brand-blue mb-2">Aún no hay sondeos web abiertos</h3>
                    <p class="text-sm text-brand-muted leading-relaxed max-w-2xl mb-6">
                        Cuando una ronda se active en MySQL, aparecerá aquí junto al distrito correspondiente. Mientras tanto puedes entrar a la home o proponer candidatos por WhatsApp.
                    </p>
                    <a href="https://wa.me/<?= esc($whatsappNumero) ?>?text=<?= rawurlencode('Hola, quiero proponer un candidato') ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#25D366] text-white font-bold py-3 px-5 rounded-xl hover:bg-[#20bd5a] transition-colors shadow-sm text-sm">
                        <i class="fab fa-whatsapp text-lg"></i> Proponer candidato por WhatsApp
                    </a>
                </div>
<?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<?php foreach ($rondasAbiertas as $ronda): ?>
<?php
    $rondaDistrito = findDistritoById((string) ($ronda['distrito_id'] ?? ''));
    if (!$rondaDistrito) {
        continue;
    }
?>
                    <article class="bg-brand-card border border-brand-border rounded-2xl p-6 md:p-7 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-serif font-bold text-2xl text-brand-blue leading-tight"><?= esc($rondaDistrito['nombre']) ?></h3>
                            <span class="inline-flex items-center gap-1.5 bg-[#f0fdf4] text-brand-green border border-[#dcfce7] px-2.5 py-1 rounded text-[9px] uppercase font-bold tracking-widest shadow-sm shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-green animate-pulse"></span> Ronda abierta
                            </span>
                        </div>

                        <div class="flex items-center gap-3 text-[11px] text-brand-muted mb-5 font-medium border-b border-gray-50 pb-4">
                            <span class="flex items-center gap-1.5"><i class="far fa-calendar-alt opacity-60 text-brand-blue"></i> <?= esc(formatVoteDate((string) ($ronda['fecha_apertura'] ?? ''))) ?></span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="flex items-center gap-1.5"><i class="fas fa-clock opacity-60 text-brand-blue"></i> Hasta <?= esc(formatVoteDate((string) ($ronda['fecha_cierre'] ?? ''))) ?></span>
                        </div>

                        <p class="text-sm text-brand-muted leading-relaxed mb-6">
                            <?= esc($ronda['titulo']) ?>. El voto entra con IP blindada, GPS y bloqueo anti duplicado.
                        </p>

                        <div class="mt-auto pt-5 border-t border-brand-border flex justify-between items-center">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><i class="fas fa-chart-bar mr-1 opacity-50"></i> Voto web activo</span>
                            <a href="distrito.php?slug=<?= esc($rondaDistrito['id']) ?>" class="text-[13px] font-bold text-brand-blue hover:text-brand-green flex items-center gap-1.5 group transition-colors">
                                Ir al distrito
                                <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </article>
<?php endforeach; ?>
                </div>
<?php endif; ?>
            </div>

            <aside class="lg:col-span-4">
<?php $rondaActiva = $rondasAbiertas[0] ?? null; ?>
<?php if ($rondaActiva): ?>
<?php $distritoActiva = findDistritoById((string) ($rondaActiva['distrito_id'] ?? '')); ?>
                <div class="sticky top-28 bg-brand-card border border-brand-border rounded-2xl p-6 shadow-soft scroll-animate">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-brand-blue mb-2">Encuesta web activa</div>
                    <h3 class="font-serif font-bold text-lg text-brand-blue mb-2"><?= esc($rondaActiva['titulo']) ?></h3>
                    <p class="text-xs text-brand-muted leading-relaxed mb-4">
                        La votación en vivo se habilita en el distrito correspondiente con validación GPS y control anti duplicado.
                    </p>
<?php if ($distritoActiva): ?>
                    <a href="distrito.php?slug=<?= esc($distritoActiva['id']) ?>" class="inline-flex items-center justify-center gap-2 bg-brand-blue text-white font-bold py-3 px-5 rounded-xl hover:bg-[#0c2466] transition-colors w-full shadow-sm text-sm">
                        Ver <?= esc($distritoActiva['nombre']) ?>
                    </a>
<?php endif; ?>
                </div>
<?php else: ?>
                <div class="sticky top-28 bg-brand-card border border-brand-border rounded-2xl p-6 shadow-soft scroll-animate text-center">
                    <div class="w-12 h-12 bg-[#e6f8f0] text-brand-greenText rounded-full flex items-center justify-center text-xl mx-auto mb-4">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h3 class="font-serif font-bold text-lg text-brand-blue mb-2">Aún no hay un sondeo en línea abierto</h3>
                    <p class="text-xs text-brand-textMuted leading-relaxed mb-5">Elegí tu distrito en el buscador para ver sus candidatos, o proponé uno si aún no está en la lista.</p>
                    <a href="https://wa.me/<?= esc($whatsappNumero) ?>?text=<?= rawurlencode('Hola, quiero proponer un candidato') ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#25D366] text-white font-bold py-3 px-5 rounded-xl hover:bg-[#20bd5a] transition-colors w-full shadow-sm text-sm">
                        <i class="fab fa-whatsapp text-lg"></i> Proponer candidato por WhatsApp
                    </a>
                </div>
<?php endif; ?>
            </aside>
        </section>
    </main>

    <a href="https://wa.me/<?= esc($whatsappNumero) ?>?text=<?= rawurlencode('Hola, quiero proponer un candidato') ?>" class="fixed bottom-6 right-6 w-14 h-14 bg-[#25D366] text-white rounded-full flex items-center justify-center text-3xl shadow-lg hover:scale-110 transition-transform z-50" aria-label="Contactar por WhatsApp" target="_blank" rel="noopener">
        <i class="fab fa-whatsapp"></i>
    </a>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
