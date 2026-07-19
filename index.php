<?php
/**
 * index.php — home / feed of published studies.
 * Source: canvas-gemini/portal_de_encuestas.html, relocated verbatim
 * except for the owner-authorized legal scrub (tasks.md section 6,
 * authorized 2026-07-18):
 *  - 6.1: "Ipsos Perú" attribution on the Comas article, and the ticker's
 *    "Datum Internacional" presidential-study headline, are demo figures —
 *    retitled to the `ejemplo` entry from data/encuestadora.json.
 *  - 6.3: Article 2 was "Elecciones Regionales: Gobierno Regional de
 *    Ucayali" (GORE Ucayali, attributed to CPI) — entirely out of the
 *    Lima Metropolitana district scope lock. Its text is replaced with an
 *    honest, non-fabricated methodology note (reusing the real copy
 *    already used in metodolog_a.html) rather than a new invented figure —
 *    the article's DOM shape is kept so the shared-partial refactor stays
 *    verifiable structurally (see scripts/check-refactor.php docblock).
 */

require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Encuestas Electorales Perú - Transparencia y Datos 2026';
$activeNav = 'inicio';
?><!doctype html>
<html lang="es">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans">

    <!-- BARRA SUPERIOR (Ticker en Tiempo Real) -->
    <div class="bg-brand-green text-white text-[11px] md:text-xs font-semibold py-2 px-4 w-full relative z-20">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div id="reloj" class="flex items-center gap-2 tracking-wide font-mono" aria-live="polite">
                <i class="far fa-clock"></i> --/--/---- --:--:--
            </div>
            <div class="hidden md:flex items-center justify-end gap-6 overflow-hidden w-2/3">
                <a href="#" class="cursor-pointer hover:text-brand-blue transition-colors truncate">
                    Último estudio: Estudio Presidencial — Encuestadora de ejemplo (dato ficticio, no es una institución real) - Agosto 2026
                </a>
                <div class="flex gap-3 border-l border-white/30 pl-4">
                    <button aria-label="Anterior" class="hover:text-brand-blue transition-colors"><i class="fas fa-chevron-left text-[10px]"></i></button>
                    <button aria-label="Siguiente" class="hover:text-brand-blue transition-colors"><i class="fas fa-chevron-right text-[10px]"></i></button>
                </div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-10 overflow-hidden">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            <!-- COLUMNA IZQUIERDA: FEED DE ENCUESTAS (Estilo Noticiero/Blog) -->
            <div class="lg:col-span-8 flex flex-col gap-8">

                <div class="flex items-center justify-between border-b-2 border-brand-blue pb-2 mb-2 scroll-animate">
                    <h2 class="text-xl md:text-2xl font-serif font-bold text-brand-blue">Últimos Estudios Publicados</h2>
                    <span class="text-xs font-semibold text-brand-textMuted uppercase tracking-wider bg-gray-100 px-3 py-1 rounded">Ver todos</span>
                </div>

                <!-- ARTÍCULO 1 (Destacado) -->
                <article class="flex flex-col md:flex-row gap-6 group cursor-pointer scroll-animate bg-white p-4 border border-brand-border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <!-- Imagen / Portada -->
                    <div class="relative w-full md:w-2/5 aspect-[4/3] shrink-0 overflow-hidden rounded bg-gray-100">
                        <img src="https://placehold.co/600x450/e2e8f0/475569?text=Municipalidad+Comas" alt="Comas" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out">
                        <!-- Tag Categórico -->
                        <span class="absolute top-3 left-3 bg-brand-blue text-white text-[10px] font-bold px-2.5 py-1 uppercase tracking-wider rounded-sm shadow">
                            Lima Norte
                        </span>
                    </div>

                    <!-- Contenido (Data) -->
                    <div class="flex flex-col flex-1 py-1">
                        <!-- Metadata (Fecha, Fuente) -->
                        <div class="flex flex-wrap items-center text-[11px] text-brand-textMuted font-semibold uppercase tracking-wider gap-3 mb-2">
                            <span class="text-brand-green"><i class="fas fa-chart-bar mr-1"></i> Encuestadora de ejemplo (dato ficticio, no es una institución real)</span>
                            <span>·</span>
                            <span><i class="far fa-calendar mr-1"></i> Trabajo de campo: Ago 2026</span>
                        </div>

                        <!-- Titular -->
                        <h3 class="text-2xl font-serif font-bold text-brand-blue leading-tight mb-3 group-hover:text-brand-green transition-colors">
                            Intención de voto para la Alcaldía de Comas
                        </h3>

                        <!-- Resumen (Extracto de Blog) -->
                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3 mb-4">
                            Según el último estudio de opinión a nivel distrital, el candidato Ulises Villegas consolida su liderazgo en Comas, superando en más de 12 puntos a su más cercano competidor en un escenario de alta indecisión a tres meses de los comicios...
                        </p>

                        <!-- Footer del Artículo -->
                        <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4">
                            <div class="flex gap-2">
                                <span class="bg-gray-100 text-brand-textMuted hover:bg-gray-200 px-2.5 py-1.5 text-xs rounded transition-colors" title="Compartir en X"><i class="fa-brands fa-x-twitter"></i></span>
                                <span class="bg-[#1877F2]/10 text-[#1877F2] hover:bg-[#1877F2]/20 px-2.5 py-1.5 text-xs rounded transition-colors" title="Compartir en Facebook"><i class="fab fa-facebook-f"></i></span>
                            </div>
                            <span class="text-brand-blue font-bold text-sm group-hover:text-brand-green flex items-center gap-1 transition-colors">
                                Leer informe técnico <i class="fas fa-arrow-right text-[10px] mt-0.5"></i>
                            </span>
                        </div>
                    </div>
                </article>

                <!-- ARTÍCULO 2 -->
                <article class="flex flex-col md:flex-row gap-6 group cursor-pointer scroll-animate delay-100 bg-white p-4 border border-brand-border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="relative w-full md:w-2/5 aspect-[4/3] shrink-0 overflow-hidden rounded bg-gray-100">
                        <img src="https://placehold.co/600x450/cbd5e1/475569?text=Lima+Metropolitana" alt="Lima Metropolitana" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out">
                        <span class="absolute top-3 left-3 bg-brand-blue text-white text-[10px] font-bold px-2.5 py-1 uppercase tracking-wider rounded-sm shadow">
                            Cobertura
                        </span>
                    </div>
                    <div class="flex flex-col flex-1 py-1">
                        <div class="flex flex-wrap items-center text-[11px] text-brand-textMuted font-semibold uppercase tracking-wider gap-3 mb-2">
                            <span class="text-brand-green"><i class="fas fa-chart-bar mr-1"></i> Encuestadora de ejemplo (dato ficticio, no es una institución real)</span>
                            <span>·</span>
                            <span><i class="far fa-calendar mr-1"></i> Actualizado: Jul 2026</span>
                        </div>
                        <h3 class="text-2xl font-serif font-bold text-brand-blue leading-tight mb-3 group-hover:text-brand-green transition-colors">
                            Cobertura: los 43 distritos de Lima Metropolitana
                        </h3>
                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3 mb-4">
                            Mapeamos y mantenemos sondeos activos para los 43 distritos de Lima Metropolitana de forma simultánea. A medida que aumenta la participación ciudadana, las tendencias se reflejan en la plataforma alimentando el debate público al instante.
                        </p>
                        <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4">
                            <div class="flex gap-2">
                                <span class="bg-gray-100 text-brand-textMuted hover:bg-gray-200 px-2.5 py-1.5 text-xs rounded transition-colors"><i class="fa-brands fa-x-twitter"></i></span>
                                <span class="bg-[#1877F2]/10 text-[#1877F2] hover:bg-[#1877F2]/20 px-2.5 py-1.5 text-xs rounded transition-colors"><i class="fab fa-facebook-f"></i></span>
                            </div>
                            <span class="text-brand-blue font-bold text-sm group-hover:text-brand-green flex items-center gap-1 transition-colors">
                                Leer nuestra metodología <i class="fas fa-arrow-right text-[10px] mt-0.5"></i>
                            </span>
                        </div>
                    </div>
                </article>

                <!-- Paginación Simple -->
                <div class="flex justify-center mt-4 scroll-animate delay-200">
                    <button class="border border-brand-border bg-white text-brand-blue font-bold px-6 py-2 rounded shadow-sm hover:border-brand-blue transition-colors">
                        Cargar más resultados
                    </button>
                </div>
            </div>

            <!-- COLUMNA DERECHA: SIDEBAR (Simulador de Voto y Rigor) -->
            <aside class="lg:col-span-4 flex flex-col gap-8">

                <!-- Caja Institucional (Reemplazo del banner publicitario) -->
                <div class="bg-brand-blue text-white p-6 rounded-lg shadow-md relative overflow-hidden scroll-animate">
                    <!-- Elemento decorativo gráfico de fondo -->
                    <div class="absolute -right-6 -top-6 opacity-10">
                        <i class="fas fa-chart-pie text-9xl"></i>
                    </div>

                    <h3 class="font-serif text-lg font-bold mb-2 relative z-10">Metodología Rigurosa</h3>
                    <p class="text-sm text-blue-100 leading-relaxed mb-4 relative z-10 font-medium">
                        Agregamos y normalizamos datos de las principales encuestadoras inscritas en el JNE para combatir la desinformación electoral.
                    </p>
                    <a href="metodologia.php" class="inline-flex items-center gap-2 text-xs font-bold text-white uppercase tracking-wider hover:text-brand-green transition-colors relative z-10">
                        Nuestra Metodología <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- WIDGET INTERACTIVO: Sondeo de Usuario -->
                <div class="bg-white rounded-lg shadow-md border border-brand-border overflow-hidden scroll-animate delay-100">
                    <div class="bg-gray-50 border-b border-brand-border p-4">
                        <div class="flex items-center gap-2 text-brand-green text-xs font-bold uppercase tracking-widest mb-1">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-green opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-green"></span>
                            </span>
                            Sondeo Interactivo Abierto
                        </div>
                        <h3 class="font-serif text-brand-blue font-bold text-[15px] leading-snug">
                            ¿Por cuál de los siguientes candidatos a la Alcaldía de Lima votaría si las elecciones fueran mañana?
                        </h3>
                    </div>

                    <form action="#" method="POST" class="p-0">
                        <fieldset class="border-none m-0 p-0">
                            <legend class="sr-only">Seleccione un candidato a la Alcaldía de Lima</legend>

                            <div class="flex flex-col divide-y divide-gray-100">

                                <!-- Opción 1 -->
                                <label class="group relative flex flex-col p-4 cursor-pointer hover:bg-blue-50/50 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div class="flex gap-3">
                                            <div class="mt-1">
                                                <input type="radio" name="sondeo_lima" value="c1" class="w-4 h-4 text-brand-green focus:ring-brand-green accent-brand-green border-gray-300">
                                            </div>
                                            <div>
                                                <span class="block text-sm font-bold text-gray-800">Carlos Canales</span>
                                                <span class="block text-xs text-gray-500 mt-0.5">Renovación Popular</span>
                                            </div>
                                        </div>
                                        <!-- Miniatura Logo Partido -->
                                        <div class="w-8 h-8 rounded-full border border-gray-200 bg-[url('https://placehold.co/40x40/ffffff/102f86?text=RP')] bg-cover shrink-0"></div>
                                    </div>
                                    <!-- Barra de resultado animada (simula mostrarse al votar) -->
                                    <div class="w-full bg-gray-100 h-1.5 mt-3 rounded-full overflow-hidden hidden group-focus-within:block">
                                        <div class="bg-brand-blue h-full bar-fill rounded-full" style="width: 28%"></div>
                                    </div>
                                </label>

                                <!-- Opción 2 -->
                                <label class="group relative flex flex-col p-4 cursor-pointer hover:bg-blue-50/50 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div class="flex gap-3">
                                            <div class="mt-1">
                                                <input type="radio" name="sondeo_lima" value="c2" class="w-4 h-4 text-brand-green focus:ring-brand-green accent-brand-green border-gray-300">
                                            </div>
                                            <div>
                                                <span class="block text-sm font-bold text-gray-800">Julio Gagó</span>
                                                <span class="block text-xs text-gray-500 mt-0.5">Fuerza Popular</span>
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 rounded-full border border-gray-200 bg-[url('https://placehold.co/40x40/ffffff/f97316?text=FP')] bg-cover shrink-0"></div>
                                    </div>
                                </label>

                                <!-- Opción 3 -->
                                <label class="group relative flex flex-col p-4 cursor-pointer hover:bg-blue-50/50 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div class="flex gap-3">
                                            <div class="mt-1">
                                                <input type="radio" name="sondeo_lima" value="c3" class="w-4 h-4 text-brand-green focus:ring-brand-green accent-brand-green border-gray-300">
                                            </div>
                                            <div>
                                                <span class="block text-sm font-bold text-gray-800">Alberto Tejada</span>
                                                <span class="block text-xs text-gray-500 mt-0.5">Acción Popular</span>
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 rounded-full border border-gray-200 bg-[url('https://placehold.co/40x40/ffffff/dc2626?text=AP')] bg-cover shrink-0"></div>
                                    </div>
                                </label>

                                <!-- Otros -->
                                <label class="group relative flex items-center gap-3 p-4 cursor-pointer hover:bg-blue-50/50 transition-colors">
                                    <input type="radio" name="sondeo_lima" value="blanco_viciado" class="w-4 h-4 text-brand-green focus:ring-brand-green accent-brand-green border-gray-300">
                                    <span class="text-sm font-medium text-gray-600">Blanco / Viciado / No precisa</span>
                                </label>

                            </div>

                            <div class="p-4 bg-gray-50 border-t border-gray-100">
                                <button type="button" class="w-full bg-brand-blue text-white font-bold py-2.5 rounded hover:bg-[#0c2466] spring-transition transform hover:-translate-y-0.5 shadow-md hover:shadow-lg text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2">
                                    Registrar mi voto
                                </button>
                                <p class="text-[10px] text-center text-gray-400 mt-3 font-medium">
                                    Protección Anti-Bot activa. Solo 1 voto permitido por IP/Dispositivo.
                                </p>
                            </div>
                        </fieldset>
                    </form>
                </div>

            </aside>
        </div>
    </main>

    <!-- Botón Flotante (WhatsApp para contacto de candidatos) -->
    <a href="#" aria-label="Contactar por WhatsApp" class="fixed bottom-6 right-6 bg-[#25D366] text-white w-14 h-14 rounded-full flex items-center justify-center text-3xl shadow-lg hover:scale-110 spring-transition z-50 group focus:outline-none focus:ring-4 focus:ring-green-300">
        <i class="fab fa-whatsapp"></i>
        <!-- Tooltip animado -->
        <div class="absolute bottom-16 right-0 bg-white text-gray-800 text-xs w-52 p-3 rounded-lg shadow-xl text-center border border-gray-200 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 pointer-events-none origin-bottom-right">
            <span class="block text-gray-500 mb-1 leading-snug">¿Falta tu candidato o información en la plataforma?</span>
            <span class="font-bold text-[#25D366]">Escríbenos directamente</span>
            <!-- Triángulo del tooltip -->
            <div class="absolute -bottom-2 right-5 w-4 h-4 bg-white border-b border-r border-gray-200 transform rotate-45"></div>
        </div>
    </a>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
