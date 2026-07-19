<?php
/**
 * encuesta.php — poll detail view. Accepts ?id= (BL-16 wires it to
 * data/encuesta.json; hardcoded content stays hardcoded here, relocated
 * not rewritten, per proposal.md's explicit scope).
 * Source: canvas-gemini/detalle_de_encuesta.html, relocated verbatim
 * except the owner-authorized legal scrub (tasks.md 6.1, 2026-07-18):
 * this was titled "Detalle Estudio Ipsos Agosto 2026" and attributed a
 * fabricated 1,205-case study to Ipsos Perú (a real, JNE-registered
 * pollster) — retitled to the `ejemplo` entry from data/encuestadora.json,
 * matching data/encuesta.json's actual example study for Miraflores
 * ("DATO DE EJEMPLO — no es una encuesta real de ninguna encuestadora
 * institucional"). The disclaimer box previously claimed the figures were
 * "extraídas de publicaciones oficiales... registrados en el JNE", which
 * would now be false — replaced with that same real data/encuesta.json
 * wording instead of inventing new copy.
 */

require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Detalle Estudio de Ejemplo Agosto 2026 - Encuestas Electorales';
$pageDescription = 'Resultados detallados y ficha técnica del estudio de opinión pública.';
$activeNav = '';
?><!doctype html>
<html lang="es">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans">

    <!-- BARRA TIPO TICKER -->
    <div class="bg-brand-green text-white text-[11px] md:text-xs font-semibold py-2 px-4 w-full relative z-20">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div id="reloj" class="flex items-center gap-2 font-mono tracking-wide" aria-live="polite">
                <i class="far fa-clock"></i> Cargar hora...
            </div>
            <div class="hidden md:flex items-center gap-4">
                <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] uppercase tracking-widest">Último Minuto</span>
                <a href="#" class="hover:text-brand-blue transition-colors">Encuestadora de ejemplo (dato ficticio, no es una institución real)</a>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/partials/header.php'; ?>

    <!-- CONTENIDO PRINCIPAL: DETALLE DE ENCUESTA -->
    <main class="max-w-7xl mx-auto px-4 py-8 overflow-hidden">

        <!-- Migas de Pan -->
        <nav class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider mb-6 scroll-animate">
            <a href="index.php" class="hover:text-brand-green transition-colors">Inicio</a>
            <span class="mx-2">/</span>
            <a href="#" class="hover:text-brand-green transition-colors">Alcaldía de Lima</a>
            <span class="mx-2">/</span>
            <span class="text-brand-blue">Estudio de ejemplo Agosto 2026</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

            <!-- COLUMNA IZQUIERDA: RESULTADOS GRÁFICOS -->
            <div class="lg:col-span-8 flex flex-col gap-8">

                <!-- Cabecera del Estudio -->
                <header class="scroll-animate border-b border-brand-border pb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="bg-brand-blue text-white text-[10px] font-bold px-2 py-1 uppercase tracking-widest rounded-sm">Elecciones Municipales</span>
                        <span class="text-xs font-bold text-brand-textMuted uppercase"><i class="far fa-calendar-alt mr-1"></i> Publicado: 15 Ago 2026</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-blue leading-tight mb-4">
                        Intención de voto para la Alcaldía de Lima Metropolitana
                    </h1>
                    <p class="text-lg text-gray-600 font-medium leading-relaxed">
                        A tres meses de las elecciones, el candidato de Renovación Popular lidera las preferencias, seguido por un empate técnico en el segundo lugar entre Podemos Perú y Avanza País.
                    </p>
                </header>

                <!-- Panel de Datos / Gráfico -->
                <section class="bg-white border border-brand-border p-6 md:p-8 rounded-lg shadow-sm scroll-animate delay-100">
                    <div class="flex justify-between items-end mb-8 border-b border-gray-100 pb-4">
                        <h2 class="text-xl font-bold text-brand-blue font-serif">Resultados (Simulacro con Cédula)</h2>
                        <span class="text-xs font-semibold text-gray-400 uppercase">Base: Votos Emitidos</span>
                    </div>

                    <!-- Lista de Resultados (Gráfico de barras HTML) -->
                    <div class="space-y-6" id="grafico-resultados">

                        <!-- Candidato 1 -->
                        <div class="relative">
                            <div class="flex justify-between items-baseline mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded border border-gray-200 bg-[url('https://placehold.co/40x40/ffffff/B22222?text=RP')] bg-cover"></div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-lg leading-none">Carlos Canales</div>
                                        <div class="text-xs text-brand-textMuted font-medium mt-1">Renovación Popular</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-2xl text-brand-blue tabular-nums">28.5%</div>
                                    <div class="text-[11px] font-bold text-brand-green"><i class="fas fa-arrow-up"></i> 2.1%</div>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-party-rp h-full rounded-full data-bar" style="width: 28.5%"></div>
                            </div>
                        </div>

                        <!-- Candidato 2 -->
                        <div class="relative">
                            <div class="flex justify-between items-baseline mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded border border-gray-200 bg-[url('https://placehold.co/40x40/ffffff/00A99D?text=PP')] bg-cover"></div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-lg leading-none">Daniel Urresti</div>
                                        <div class="text-xs text-brand-textMuted font-medium mt-1">Podemos Perú</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-2xl text-brand-blue tabular-nums">19.2%</div>
                                    <div class="text-[11px] font-bold text-red-500"><i class="fas fa-arrow-down"></i> 1.5%</div>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-party-pp h-full rounded-full data-bar" style="width: 19.2%"></div>
                            </div>
                        </div>

                        <!-- Candidato 3 -->
                        <div class="relative">
                            <div class="flex justify-between items-baseline mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded border border-gray-200 bg-[url('https://placehold.co/40x40/ffffff/F58220?text=AVP')] bg-cover"></div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-lg leading-none">Francis Allison</div>
                                        <div class="text-xs text-brand-textMuted font-medium mt-1">Avanza País</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-2xl text-brand-blue tabular-nums">17.8%</div>
                                    <div class="text-[11px] font-bold text-gray-400"><i class="fas fa-minus"></i> 0.0%</div>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-party-avp h-full rounded-full data-bar" style="width: 17.8%"></div>
                            </div>
                        </div>

                        <!-- Otros -->
                        <div class="pt-4 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                <div class="text-xs text-brand-textMuted mb-1">Blanco/Viciado</div>
                                <div class="font-bold text-brand-blue text-lg">15.0%</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                <div class="text-xs text-brand-textMuted mb-1">No precisa</div>
                                <div class="font-bold text-brand-blue text-lg">11.5%</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                <div class="text-xs text-brand-textMuted mb-1">Otros</div>
                                <div class="font-bold text-brand-blue text-lg">8.0%</div>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- Análisis de la Redacción -->
                <article class="prose prose-blue max-w-none text-gray-700 leading-relaxed scroll-animate delay-200">
                    <h3 class="text-lg font-bold text-brand-blue mb-3 font-serif">Análisis de la muestra</h3>
                    <p class="mb-4">
                        El presente estudio revela una consolidación del voto conservador en los sectores A/B de Lima Moderna, donde Canales obtiene picos de hasta 35%. Por su parte, la zona de Lima Norte y Lima Este presenta un escenario altamente fragmentado.
                    </p>
                    <p>
                        Cabe destacar que el nivel de indecisión (Blanco, viciado o no precisa) suma un <strong class="text-brand-blue">26.5%</strong>, lo que indica que más de la cuarta parte del electorado limeño aún no tiene definido su voto.
                    </p>
                </article>

            </div>

            <!-- COLUMNA DERECHA: FICHA TÉCNICA (Requisito JNE) -->
            <aside class="lg:col-span-4 flex flex-col gap-6">

                <div class="bg-brand-blue text-white p-6 rounded-lg shadow-md scroll-animate">
                    <h3 class="font-serif text-lg font-bold mb-4 flex items-center gap-2 border-b border-white/20 pb-2">
                        <i class="fas fa-clipboard-list text-brand-green"></i> Ficha Técnica
                    </h3>

                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Encuestadora</dt>
                            <dd class="font-medium">Encuestadora de ejemplo (dato ficticio, no es una institución real)</dd>
                            <dd class="text-[11px] text-white/50 mt-0.5">Registro JNE: N/D — dato de ejemplo</dd>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Muestra</dt>
                                <dd class="font-medium">1,205 casos</dd>
                            </div>
                            <div>
                                <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Margen de Error</dt>
                                <dd class="font-medium text-brand-green">± 2.8%</dd>
                            </div>
                        </div>

                        <div>
                            <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Nivel de Confianza</dt>
                            <dd class="font-medium">95%</dd>
                        </div>

                        <div>
                            <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Fecha de Campo</dt>
                            <dd class="font-medium">10 al 12 de Agosto, 2026</dd>
                        </div>

                        <div>
                            <dt class="text-white/70 font-semibold uppercase tracking-wider text-[10px] mb-1">Ámbito</dt>
                            <dd class="font-medium">Lima Metropolitana (43 distritos)</dd>
                        </div>
                    </dl>

                    <div class="mt-6 pt-4 border-t border-white/20">
                        <a href="#" class="block w-full text-center bg-white text-brand-blue font-bold py-2 rounded text-xs uppercase tracking-wider hover:bg-brand-green hover:text-white transition-colors shadow">
                            Descargar Informe JNE (PDF)
                        </a>
                    </div>
                </div>

                <!-- Caja de Autoridad -->
                <div class="bg-white border border-brand-border p-5 rounded-lg shadow-sm scroll-animate delay-100">
                    <h4 class="font-bold text-brand-blue text-sm uppercase tracking-wider mb-2">Metodología de Plataforma</h4>
                    <p class="text-xs text-brand-textMuted leading-relaxed">
                        DATO DE EJEMPLO — no es una encuesta real de ninguna encuestadora institucional. Creado únicamente para validar el formato antes de que existan datos reales (el JNE admite candidaturas el 5 de agosto de 2026).
                    </p>
                </div>

            </aside>
        </div>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
