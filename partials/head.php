<?php
/**
 * Single source for the design system (php-architecture spec, "Single source
 * for the design system"). Every page sets $pageTitle (required) and
 * $pageDescription (optional) before requiring this partial.
 *
 * Palette reconciled per design.md Decision 1 (the 8 canvas prototypes
 * disagreed): brand.blue #102f86, brand.green #15ba75 (lowercase),
 * brand.bg #f4f5f3, brand.card #ffffff. #fcfcfc and #f8fafc are dropped.
 *
 * Party colors are read from data/partido.json at render time (never a
 * hardcoded hex literal here — php-architecture spec, "Party colors come
 * from data, not literals"), so the `party.*` Tailwind tokens used by the
 * `bg-party-*` / `text-party-*` utility classes in encuesta.php/candidato.php
 * stay data-driven instead of the canvas prototypes' inline `#B22222` etc.
 */

require_once __DIR__ . '/../includes/helpers.php';

$partidos = require __DIR__ . '/../includes/data.php';
$partidos = $partidos['partidos'];

$pageTitle = $pageTitle ?? 'Encuestas Electorales Perú';
$pageDescription = $pageDescription ?? null;
?><meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>
<?php if ($pageDescription): ?>
    <meta name="description" content="<?= esc($pageDescription) ?>">
<?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Serif:wght@600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Noto Serif', 'serif'],
                    },
                    colors: {
                        brand: {
                            blue: '#102f86',
                            green: '#15ba75',
                            bg: '#f4f5f3',
                            surface: '#f4f5f3',
                            card: '#ffffff',
                            border: '#e5e7eb',
                            text: '#111827',
                            textMuted: '#4b5563',
                            muted: '#4b5563'
                        },
                        party: {
<?php foreach ($partidos as $p): ?>
                            <?= strtolower($p['siglas']) ?>: '<?= esc($p['color']) ?>',
<?php endforeach; ?>
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(16, 47, 134, 0.05)',
                        'modal': '0 25px 50px -12px rgba(16, 47, 134, 0.25)',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: theme('colors.brand.bg'); color: theme('colors.brand.text'); }
        h1, h2, h3, h4, .font-serif { font-family: theme('fontFamily.serif'); letter-spacing: -0.02em; }

        /* Efectos Hover */
        .hover-color-green:hover { color: theme('colors.brand.green'); transition: color 0.2s ease; }
        .hover-bg-green:hover { background-color: theme('colors.brand.green'); transition: background-color 0.2s ease; }

        /* Utilidades para scrollbars invisibles en sliders horizontales */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* ANIMACIONES 2026: "Spring Physics" simulada con bezier */
        .spring-transition {
            transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.1);
        }

        /* Clases base para el Intersection Observer (Scroll Reveal) */
        .scroll-animate {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.7s ease-out, transform 0.7s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .scroll-animate.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }

        /* Microinteracción de barras en la votación / resultados */
        .bar-fill {
            width: 0%;
            transition: width 1.2s cubic-bezier(0.2, 0.8, 0.2, 1) 0.3s;
        }
        .data-bar {
            width: 0%;
            transition: width 1.2s cubic-bezier(0.2, 0.8, 0.2, 1) 0.3s;
        }

        /* Animación del fondo del Hero (Cuadrícula desplazable) — portal de sondeos */
        .bg-grid-pattern {
            background-size: 40px 40px;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            animation: panGrid 20s linear infinite;
        }
        @keyframes panGrid {
            0% { background-position: 0 0; }
            100% { background-position: 40px 40px; }
        }

        /* Efecto carrusel de texto — hero del portal de sondeos */
        .word-slider {
            display: inline-block;
            overflow: hidden;
            vertical-align: bottom;
            height: 1.2em;
        }
        .word-slider span {
            display: block;
            animation: slideWords 10s infinite cubic-bezier(0.16, 1, 0.3, 1);
            color: theme('colors.brand.green');
        }
        @keyframes slideWords {
            0%, 20% { transform: translateY(0); }
            25%, 45% { transform: translateY(-100%); }
            50%, 70% { transform: translateY(-200%); }
            75%, 95% { transform: translateY(-300%); }
            100% { transform: translateY(-400%); }
        }

        /* Animación de brillo para las barras de resultados */
        @keyframes shine {
            0% { transform: translateX(-100%) skewX(-12deg); }
            100% { transform: translateX(200%) skewX(-12deg); }
        }

        /* Radar GPS — widget de votación */
        .radar-ping { animation: ping-slow 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
        @keyframes ping-slow {
            75%, 100% { transform: scale(2); opacity: 0; }
        }

        /* Fade in para modales — widget de votación */
        .fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Fondo de puntos — hero de "Quiénes somos" */
        .bg-dots {
            background-image: radial-gradient(theme('colors.brand.border') 1px, transparent 1px);
            background-size: 20px 20px;
        }

        a { transition: color 0.2s ease; }

        /* Estilos editoriales para contenido tipo Pew Research — metodología */
        .prose-editorial p { margin-bottom: 1.5em; line-height: 1.8; color: theme('colors.brand.text'); font-size: 1.125rem; font-weight: 400; }
        .prose-editorial h2 { font-family: theme('fontFamily.serif'); font-size: 2rem; font-weight: 800; color: theme('colors.brand.blue'); margin-top: 2.5em; margin-bottom: 1em; padding-bottom: 0.5em; border-bottom: 2px solid theme('colors.brand.border'); line-height: 1.2; }
        .prose-editorial h3 { font-family: theme('fontFamily.sans'); font-weight: 700; font-size: 1.25rem; color: theme('colors.brand.text'); margin-top: 1.5em; margin-bottom: 0.75em; }
        .prose-editorial ul { margin-bottom: 1.5em; padding-left: 1.5em; color: theme('colors.brand.text'); font-size: 1.125rem; }
        .prose-editorial li { margin-bottom: 0.75em; line-height: 1.6; }
        .prose-editorial li::marker { color: theme('colors.brand.green'); font-weight: bold; }
        .prose-editorial strong { color: theme('colors.brand.blue'); font-weight: 700; }
        .prose-editorial blockquote { border-left: 4px solid theme('colors.brand.green'); padding-left: 1.5em; font-style: italic; color: theme('colors.brand.muted'); margin-top: 2em; margin-bottom: 2em; font-size: 1.25rem; }
    </style>
