@php
    $pageTitle = $pageTitle ?? 'Encuestas Electorales Perú';
    $pageDescription = $pageDescription ?? null;
    $shareTitle = $shareTitle ?? $pageTitle;
    $shareDescription = $shareDescription ?? $pageDescription ?? $pageTitle;
    $shareImage = $shareImage ?? 'assets/img/share/default-share.png';
    $shareUrl = $shareUrl ?? url()->current();
    $shareType = $shareType ?? 'website';
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $pageTitle }}</title>
@if ($pageDescription)
    <meta name="description" content="{{ $pageDescription }}">
@endif
<link rel="canonical" href="{{ $shareUrl }}">
<meta property="og:site_name" content="EncuestasElectorales.pe">
<meta property="og:title" content="{{ $shareTitle }}">
<meta property="og:description" content="{{ $shareDescription }}">
<meta property="og:url" content="{{ $shareUrl }}">
<meta property="og:type" content="{{ $shareType }}">
<meta property="og:image" content="{{ preg_match('/^https?:\\/\\//i', (string) $shareImage) ? $shareImage : asset($shareImage) }}">
<meta property="og:image:width" content="1080">
<meta property="og:image:height" content="1350">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $shareTitle }}">
<meta name="twitter:description" content="{{ $shareDescription }}">
<meta name="twitter:image" content="{{ preg_match('/^https?:\\/\\//i', (string) $shareImage) ? $shareImage : asset($shareImage) }}">
<meta name="theme-color" content="#102f86">

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
                        greenText: '#0f7a4a',
                        bg: '#f4f5f3',
                        surface: '#f4f5f3',
                        card: '#ffffff',
                        border: '#e5e7eb',
                        text: '#111827',
                        textMuted: '#4b5563',
                        muted: '#4b5563'
                    },
                    party: {}
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
    .hover-color-green:hover { color: theme('colors.brand.green'); transition: color 0.2s ease; }
    .hover-bg-green:hover { background-color: theme('colors.brand.green'); transition: background-color 0.2s ease; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .spring-transition { transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.1); }
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
    .bar-fill { width: 0%; transition: width 1.2s cubic-bezier(0.2, 0.8, 0.2, 1) 0.3s; }
    .data-bar { width: 0%; transition: width 1.2s cubic-bezier(0.2, 0.8, 0.2, 1) 0.3s; }
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
    @keyframes shine {
        0% { transform: translateX(-100%) skewX(-12deg); }
        100% { transform: translateX(200%) skewX(-12deg); }
    }
    .radar-ping { animation: ping-slow 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
    @keyframes ping-slow {
        75%, 100% { transform: scale(2); opacity: 0; }
    }
    .fade-in { animation: fadeIn 0.3s ease-out forwards; }
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .bg-dots {
        background-image: radial-gradient(theme('colors.brand.border') 1px, transparent 1px);
        background-size: 20px 20px;
    }
    a { transition: color 0.2s ease; }
    .prose-editorial p { margin-bottom: 1.5em; line-height: 1.8; color: theme('colors.brand.text'); font-size: 1.125rem; font-weight: 400; }
    .prose-editorial h2 { font-family: theme('fontFamily.serif'); font-size: 2rem; font-weight: 800; color: theme('colors.brand.blue'); margin-top: 2.5em; margin-bottom: 1em; padding-bottom: 0.5em; border-bottom: 2px solid theme('colors.brand.border'); line-height: 1.2; }
    .prose-editorial h3 { font-family: theme('fontFamily.sans'); font-weight: 700; font-size: 1.25rem; color: theme('colors.brand.text'); margin-top: 1.5em; margin-bottom: 0.75em; }
    .prose-editorial ul { margin-bottom: 1.5em; padding-left: 1.5em; color: theme('colors.brand.text'); font-size: 1.125rem; }
    .prose-editorial li { margin-bottom: 0.75em; line-height: 1.6; }
    .prose-editorial li::marker { color: theme('colors.brand.green'); font-weight: bold; }
    .prose-editorial strong { color: theme('colors.brand.blue'); font-weight: 700; }
    .prose-editorial blockquote { border-left: 4px solid theme('colors.brand.green'); padding-left: 1.5em; font-style: italic; color: theme('colors.brand.muted'); margin-top: 2em; margin-bottom: 2em; font-size: 1.25rem; }
</style>
