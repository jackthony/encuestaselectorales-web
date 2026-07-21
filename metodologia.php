<?php
/**
 * metodologia.php — methodology / editorial rigor page. Source:
 * canvas-gemini/metodolog_a.html, relocated verbatim. No legal scrub
 * applies — this prototype already follows PROMPT-portal.md's "no
 * third-party pollster" rule (own opt-in web sondeo only).
 */

require_once __DIR__ . '/includes/helpers.php';
$data = require __DIR__ . '/includes/data.php';
$totalDistritos = count($data['distritos']);

$pageTitle = 'Metodología y Rigor | EncuestasElectorales.pe';
$pageDescription = 'Conoce la metodología detrás de nuestra plataforma de medición ciudadana en tiempo real para las Elecciones de Lima 2026.';
$activeNav = 'metodologia';
?><!doctype html>
<html lang="es" class="scroll-smooth">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <!-- Ticker Superior -->
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
        <!-- Page Header -->
        <section class="bg-brand-surface border-b border-brand-border pt-16 pb-20 px-4">
            <div class="max-w-7xl mx-auto">
                <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-blue mb-6 bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-full shadow-sm scroll-animate">
                    <i class="fas fa-chart-network"></i> Inteligencia Electoral
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-brand-blue max-w-4xl leading-[1.1] mb-6 scroll-animate delay-100">
                    Midiendo el pulso político de Lima en tiempo real
                </h1>
                <p class="text-lg md:text-xl text-brand-muted max-w-3xl leading-relaxed scroll-animate delay-200">
                    EncuestasElectorales.pe es la plataforma tecnológica de medición ciudadana que pone el escenario electoral de la capital al alcance de todos. Participación masiva, datos vivos y transparencia absoluta.
                </p>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">

            <!-- Sidebar Navigation (Sticky) -->
            <aside class="hidden lg:block lg:col-span-3">
                <div class="sticky top-28 bg-brand-card border border-brand-border rounded-xl p-6 shadow-sm scroll-animate">
                    <h3 class="text-xs font-bold text-brand-muted uppercase tracking-widest mb-5">En esta página</h3>
                    <nav class="flex flex-col gap-4 text-sm font-semibold">
                        <a href="#como-medimos" class="text-brand-text hover:text-brand-green transition-colors flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block"></span> Cómo medimos
                        </a>
                        <a href="#nuestro-compromiso" class="text-brand-text hover:text-brand-green transition-colors flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block"></span> Nuestro compromiso
                        </a>
                        <a href="#rigor-transparencia" class="text-brand-text hover:text-brand-green transition-colors flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block"></span> Rigor y transparencia
                        </a>
                    </nav>

                    <div class="mt-8 pt-6 border-t border-brand-border">
                        <div class="bg-blue-50 rounded-lg p-5">
                            <i class="fas fa-bolt text-xl text-brand-blue mb-3"></i>
                            <h4 class="font-bold text-brand-blue text-sm mb-2">Datos Vivos</h4>
                            <p class="text-xs text-brand-muted leading-relaxed">Nuestra arquitectura permite que los resultados se actualicen continuamente conforme la ciudadanía participa.</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content / Editorial Prose -->
            <div class="lg:col-span-8 lg:col-start-4 prose-editorial scroll-animate delay-100">

                <p class="text-xl text-brand-blue font-serif font-bold leading-relaxed mb-10">
                    El ecosistema de la información ha cambiado. Las decisiones electorales ya no esperan semanas para ser analizadas. En EncuestasElectorales.pe hemos construido una infraestructura digital diseñada para capturar, procesar y visualizar la intención de voto de la ciudadanía limeña con la inmediatez que exige el mundo moderno.
                </p>

                <h2 id="como-medimos">1. Cómo medimos: El sondeo digital de alto alcance</h2>
                <p>
                    Nuestra metodología se basa en el despliegue de <strong>sondeos de opinión online abiertos (opt-in)</strong>. A diferencia de las mediciones tradicionales y episódicas, nuestra plataforma funciona como un organismo vivo que recopila la opinión de los votantes las 24 horas del día.
                </p>
                <p>
                    A través de nuestro portal interactivo, movilizamos una participación ciudadana masiva. Diseñamos nuestras encuestas para que sean friccionales y accesibles desde cualquier dispositivo móvil o de escritorio, lo que nos permite medir el termómetro electoral con una velocidad sin precedentes.
                </p>
                <ul>
                    <li><strong>Cobertura Total:</strong> Mapeamos y mantenemos sondeos activos para <?= esc((string) $totalDistritos) ?> distritos cubiertos por la plataforma de forma simultánea.</li>
                    <li><strong>Actualización Continua:</strong> Los datos no reposan en cajones. A medida que aumenta la participación ciudadana, las tendencias se reflejan en la plataforma alimentando el debate público al instante.</li>
                    <li><strong>Muestra Dinámica:</strong> Al ser una plataforma abierta, nuestra muestra crece orgánicamente, capturando la energía, el interés y la movilización digital de los diferentes sectores del electorado.</li>
                </ul>

                <blockquote>
                    "Reemplazamos la espera estática por datos fluidos. Entregamos a la ciudadanía una radiografía digital constante de su propio distrito."
                </blockquote>

                <h2 id="nuestro-compromiso">2. Nuestro compromiso: Independencia y neutralidad</h2>
                <p>
                    La confianza en los datos nace de la ausencia de agendas ocultas. <strong>EncuestasElectorales.pe</strong> opera bajo un mandato estricto de independencia editorial y metodológica.
                </p>
                <p>
                    No estamos vinculados a ninguna organización política, candidato, ni entidad gubernamental. Nuestro único objetivo es actuar como un espejo tecnológico de las preferencias ciudadanas. Creemos firmemente que presentar los datos de manera clara, accesible y libre de ruidos mediáticos empodera al votante para tomar decisiones mejor informadas.
                </p>
                <p>
                    Nuestra plataforma está financiada de manera independiente, garantizando que el diseño de nuestros sondeos y la publicación de nuestros paneles de resultados jamás respondan a presiones externas ni intereses partidarios.
                </p>

                <h2 id="rigor-transparencia">3. Rigor tecnológico y transparencia de datos</h2>
                <p>
                    Medir la opinión pública en el entorno digital requiere de escudos tecnológicos robustos para proteger la integridad de la información. No nos limitamos a contar clics; validamos interacciones.
                </p>
                <p>
                    Para asegurar la calidad de nuestro sondeo ciudadano, hemos implementado una capa de seguridad y control en la recolección de datos:
                </p>

                <div class="grid md:grid-cols-2 gap-6 my-8">
                    <div class="bg-brand-surface p-6 border border-brand-border rounded-xl">
                        <div class="text-brand-green text-2xl mb-3"><i class="fas fa-shield-check"></i></div>
                        <h4 class="font-bold text-brand-text text-lg mb-2">Unicidad del Voto</h4>
                        <p class="text-sm text-brand-muted m-0 leading-relaxed">Nuestros sistemas aplican algoritmos de huella digital (Browser Fingerprinting) combinados con hashing de red para asegurar que cada ciudadano emita un solo voto válido por distrito.</p>
                    </div>
                    <div class="bg-brand-surface p-6 border border-brand-border rounded-xl">
                        <div class="text-brand-green text-2xl mb-3"><i class="fas fa-robot"></i></div>
                        <h4 class="font-bold text-brand-text text-lg mb-2">Filtros Anti-Bot</h4>
                        <p class="text-sm text-brand-muted m-0 leading-relaxed">Analizamos patrones de tráfico y velocidad de respuesta para descartar participaciones automatizadas, protegiendo la plataforma de manipulaciones inorgánicas.</p>
                    </div>
                </div>

                <p>
                    <strong>Transparencia ante todo:</strong> La honestidad es nuestro principal valor. En cada uno de nuestros paneles de resultados informamos claramente al usuario la naturaleza de nuestro estudio. Los ciudadanos saben en todo momento que están consultando un sondeo web basado en participación voluntaria (opt-in).
                </p>
                <p>
                    No ocultamos nuestras cartas. Presentamos la cantidad total de votos emitidos, el momento exacto de la última actualización y el desglose completo de todas las opciones, incluyendo el voto blanco y nulo, garantizando un mapa electoral nítido y auditable por cualquiera.
                </p>

                <hr class="border-brand-border my-12">

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center">
                    <h3 class="font-serif text-2xl font-bold text-brand-blue mb-4 !mt-0">¿Tienes dudas sobre nuestra plataforma?</h3>
                    <p class="text-brand-muted mb-6 max-w-lg mx-auto">
                        Nuestro equipo de analistas e ingenieros está a disposición de la prensa, la academia y la ciudadanía para detallar nuestra infraestructura cívica.
                    </p>
                    <a href="mailto:contacto@encuestaselectorales.pe" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-lg text-white bg-brand-blue hover:bg-[#0c2466] shadow-sm transition-transform hover:-translate-y-0.5">
                        <i class="far fa-envelope mr-2"></i> Contactar al equipo
                    </a>
                </div>

            </div>
        </section>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
