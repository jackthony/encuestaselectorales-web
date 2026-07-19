<?php
/**
 * candidato.php — candidate profile / trend view. Accepts ?dni= (BL-16
 * wires it to data/candidato.json; hardcoded content stays hardcoded,
 * relocated not rewritten, per proposal.md's explicit scope).
 * Source: canvas-gemini/perfil_de_candidato.html, relocated verbatim
 * except the owner-authorized legal scrub (tasks.md 6.1, 2026-07-18):
 * the "Últimos Registros" sidebar attributed 3 fabricated data points to
 * Ipsos Perú / Datum Internacional / CPI — retitled to the `ejemplo`
 * entry from data/encuestadora.json. The trend-analysis paragraph's "CPI y
 * Datum" mention is reworded the same way. The Chart.js line color, which
 * the prototype hardcoded as the literal `#B22222`, is now read from
 * data/partido.json via partyColor() instead (php-architecture spec,
 * "Party colors come from data, not literals") — same rendered color,
 * since Renovación Popular's JSON entry already is #B22222.
 */

require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Evolución de Carlos Canales - Encuestas Electorales';
$pageDescription = 'Tendencia y resultados históricos del candidato a la Alcaldía de Lima en las Elecciones 2026.';
$activeNav = '';

$colorLinea = partyColorOrGray('RP');
$colorLineaRgb = hexToRgbTriplet($colorLinea);
?><!doctype html>
<html lang="es">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>

    <!-- Librería para el gráfico de tendencias -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <!-- BARRA TIPO TICKER -->
    <div class="bg-brand-green text-white text-[11px] md:text-xs font-semibold py-2 px-4 w-full relative z-20 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div id="reloj" class="flex items-center gap-2 font-mono tracking-wide" aria-live="polite">
                <i class="far fa-clock"></i> Cargar hora...
            </div>
            <div class="hidden md:flex items-center gap-4">
                <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] uppercase tracking-widest">Alerta JNE</span>
                <span class="text-white/90">Padrón electoral cierra en 15 días.</span>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/partials/header.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="max-w-7xl mx-auto px-4 py-8 md:py-10 flex-grow w-full">

        <!-- Migas de Pan -->
        <nav class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-6 scroll-animate">
            <a href="index.php" class="hover:text-brand-green transition-colors">Inicio</a>
            <span class="mx-2">/</span>
            <a href="#" class="hover:text-brand-green transition-colors">Alcaldía de Lima</a>
            <span class="mx-2">/</span>
            <span class="text-brand-blue">Perfil de Candidato</span>
        </nav>

        <!-- FICHA PRINCIPAL DEL CANDIDATO -->
        <div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden mb-8 scroll-animate">
            <div class="p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-8">

                <!-- Foto -->
                <div class="w-32 h-32 md:w-40 md:h-40 shrink-0 rounded-full border-4 border-gray-100 shadow-inner overflow-hidden bg-gray-50 relative">
                    <img src="https://placehold.co/200x200/e2e8f0/475569?text=CC" alt="Carlos Canales" class="w-full h-full object-cover">
                    <!-- Logo Partido Flotante -->
                    <div class="absolute bottom-0 right-0 w-10 h-10 md:w-12 md:h-12 bg-white rounded-full border border-gray-200 p-1 shadow-md">
                        <div class="w-full h-full rounded-full bg-[url('https://placehold.co/40x40/ffffff/B22222?text=RP')] bg-cover"></div>
                    </div>
                </div>

                <!-- Info -->
                <div class="flex-grow text-center md:text-left flex flex-col justify-center">
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-textMuted mb-2 justify-center md:justify-start">
                        <span class="bg-blue-50 text-brand-blue px-2.5 py-1 rounded-full border border-blue-100">Candidato Municipal</span>
                        <span>Elecciones 2026</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-serif font-bold text-brand-blue mb-2">
                        Carlos Canales
                    </h1>
                    <p class="text-lg text-gray-600 font-medium mb-4">
                        Postulante a la <strong class="text-gray-900">Alcaldía de Lima Metropolitana</strong> por el partido <strong class="text-party-rp">Renovación Popular</strong>.
                    </p>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mt-2">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-center">
                            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1">Promedio Actual</div>
                            <div class="text-2xl font-bold text-brand-blue leading-none tabular-nums">28.5%</div>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-center">
                            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1">Tendencia Mes</div>
                            <div class="text-xl font-bold text-brand-green leading-none tabular-nums"><i class="fas fa-arrow-up text-sm mr-1"></i>2.1%</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- COLUMNA IZQUIERDA: GRÁFICO DE EVOLUCIÓN -->
            <div class="lg:col-span-2 space-y-8">
                <section class="bg-white rounded-xl border border-brand-border shadow-sm p-6 scroll-animate delay-100">
                    <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                        <h2 class="text-xl font-serif font-bold text-brand-blue">Evolución en Encuestas (2026)</h2>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider bg-gray-50 px-2 py-1 rounded">Últimos 6 meses</span>
                    </div>

                    <!-- Contenedor del Canvas de Chart.js -->
                    <div class="relative w-full h-[300px] md:h-[400px]">
                        <canvas id="trendChart"></canvas>
                    </div>

                    <p class="text-xs text-gray-500 mt-4 text-center">
                        * Los datos reflejan el promedio ponderado de simulacros de votación válidos registrados en el JNE.
                    </p>
                </section>

                <article class="prose prose-blue max-w-none text-gray-700 leading-relaxed scroll-animate">
                    <h3 class="text-lg font-bold text-brand-blue mb-3 font-serif">Análisis de Tendencia</h3>
                    <p>
                        El candidato muestra un crecimiento sostenido desde marzo de 2026, capitalizando el voto de los sectores A y B de Lima Moderna. El reciente incremento del <strong>2.1%</strong> coincide con el inicio de los debates metropolitanos oficiales. El reto principal de su campaña será mantener la tracción en Lima Norte, donde presenta mayor volatilidad según nuestro estudio de ejemplo.
                    </p>
                </article>
            </div>

            <!-- COLUMNA DERECHA: ÚLTIMAS ENCUESTAS DEL CANDIDATO -->
            <aside class="space-y-6">

                <div class="bg-brand-blue text-white rounded-xl shadow-md p-6 scroll-animate delay-200">
                    <h3 class="font-serif text-lg font-bold mb-4 flex items-center gap-2 border-b border-white/20 pb-2">
                        <i class="fas fa-list-ul text-brand-green"></i> Últimos Registros
                    </h3>

                    <div class="flex flex-col gap-4">
                        <!-- Registro 1 -->
                        <a href="encuesta.php" class="block bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg p-3 transition-colors group">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-green">15 Ago 2026</span>
                                <span class="text-lg font-bold text-white tabular-nums">28.5%</span>
                            </div>
                            <div class="font-medium text-sm text-white mb-0.5">Encuestadora de ejemplo (dato ficticio, no es una institución real)</div>
                            <div class="text-xs text-blue-200 flex justify-between items-center">
                                Simulacro Presencial
                                <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition-opacity transform group-hover:translate-x-1"></i>
                            </div>
                        </a>

                        <!-- Registro 2 -->
                        <a href="#" class="block bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg p-3 transition-colors group">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-300">02 Ago 2026</span>
                                <span class="text-lg font-bold text-white tabular-nums">26.4%</span>
                            </div>
                            <div class="font-medium text-sm text-white mb-0.5">Encuestadora de ejemplo (dato ficticio, no es una institución real)</div>
                            <div class="text-xs text-blue-200 flex justify-between items-center">
                                Encuesta Hogares
                                <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition-opacity transform group-hover:translate-x-1"></i>
                            </div>
                        </a>

                        <!-- Registro 3 -->
                        <a href="#" class="block bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg p-3 transition-colors group">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-300">18 Jul 2026</span>
                                <span class="text-lg font-bold text-white tabular-nums">25.0%</span>
                            </div>
                            <div class="font-medium text-sm text-white mb-0.5">Encuestadora de ejemplo (dato ficticio, no es una institución real)</div>
                            <div class="text-xs text-blue-200 flex justify-between items-center">
                                Encuesta Nacional
                                <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition-opacity transform group-hover:translate-x-1"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Caja Metodológica -->
                <div class="bg-gray-50 border border-gray-200 p-5 rounded-xl shadow-sm scroll-animate delay-300">
                    <h4 class="font-bold text-gray-700 text-xs uppercase tracking-wider mb-2"><i class="fas fa-info-circle text-brand-blue mr-1"></i> Nota sobre el gráfico</h4>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        La línea de tendencia se construye interpolando los resultados de encuestadoras registradas en el JNE. Las ligeras variaciones (caídas o picos) pueden deberse a las diferencias metodológicas (margen de error) de cada empresa encuestadora en un mes específico.
                    </p>
                </div>

            </aside>
        </div>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <!-- LÓGICA DEL GRÁFICO (page-specific — no relocated a assets/js/app.js) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvasEl = document.getElementById('trendChart');
            if (!canvasEl) return;
            const ctx = canvasEl.getContext('2d');

            // Color del partido leído de data/partido.json vía includes/helpers.php (partyColor()) — no hex literal en este archivo.
            const colorLinea = '<?= esc($colorLinea) ?>';
            const colorFondoGradiente = ctx.createLinearGradient(0, 0, 0, 400);
            colorFondoGradiente.addColorStop(0, 'rgba(<?= esc($colorLineaRgb) ?>, 0.2)');
            colorFondoGradiente.addColorStop(1, 'rgba(<?= esc($colorLineaRgb) ?>, 0)');

            // Datos Mock de Marzo a Agosto
            const datosMeses = ['Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto'];
            const porcentajes = [15.2, 18.0, 19.5, 23.1, 25.0, 28.5];

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: datosMeses,
                    datasets: [{
                        label: 'Intención de Voto (%)',
                        data: porcentajes,
                        borderColor: colorLinea,
                        backgroundColor: colorFondoGradiente,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: colorLinea,
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#102f86',
                            titleFont: { family: 'Inter', size: 13 },
                            bodyFont: { family: 'Inter', size: 14, weight: 'bold' },
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 40,
                            grid: {
                                color: '#e2e8f0',
                                borderDash: [5, 5],
                                drawBorder: false
                            },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#64748b',
                                callback: function(value) { return value + '%'; }
                            }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                font: { family: 'Inter', size: 12, weight: '500' },
                                color: '#475569'
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });
        });
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
