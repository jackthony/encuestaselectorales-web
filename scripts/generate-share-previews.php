<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/encuestas.php';

$root = dirname(__DIR__);
$publicRoot = $root . '/laravel-app/public';
$shareRoot = $publicRoot . '/assets/img/share';

function ensureDir(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function svgEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function wrapLines(string $text, int $maxChars, int $maxLines = 3): array
{
    $words = preg_split('/\s+/u', trim($text)) ?: [];
    $lines = [];
    $current = '';

    foreach ($words as $word) {
        $candidate = $current === '' ? $word : $current . ' ' . $word;
        if (mb_strlen($candidate, 'UTF-8') > $maxChars && $current !== '') {
            $lines[] = $current;
            $current = $word;
            if (count($lines) >= $maxLines - 1) {
                break;
            }
            continue;
        }
        $current = $candidate;
    }

    if ($current !== '' && count($lines) < $maxLines) {
        $lines[] = $current;
    }

    if (count($lines) > $maxLines) {
        $lines = array_slice($lines, 0, $maxLines);
    }

    return $lines;
}

function mimeFromPath(string $path): string
{
    $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?? $path, PATHINFO_EXTENSION));
    return match ($ext) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        default => 'image/jpeg',
    };
}

function readBinarySource(string $src, string $root): ?array
{
    $src = trim($src);
    if ($src === '') {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $src) === 1) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'header' => "User-Agent: Mozilla/5.0 (Codex share preview)\r\nAccept: image/*,*/*;q=0.8\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($src, false, $context);
        if ($body === false) {
            return null;
        }

        return [
            'mime' => mimeFromPath($src),
            'data' => $body,
        ];
    }

    $relative = ltrim($src, '/\\');
    $candidates = [
        $root . '/laravel-app/public/' . $relative,
        $root . '/' . $relative,
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return [
                'mime' => mimeFromPath($candidate),
                'data' => (string) file_get_contents($candidate),
            ];
        }
    }

    return null;
}

function dataUriFromSource(?string $src, string $root): ?string
{
    if ($src === null) {
        return null;
    }

    $binary = readBinarySource($src, $root);
    if ($binary === null) {
        return null;
    }

    return 'data:' . $binary['mime'] . ';base64,' . base64_encode($binary['data']);
}

function convertSvgToPng(string $svg, string $outputPath): void
{
    $tmpSvg = tempnam(sys_get_temp_dir(), 'share_svg_');
    if ($tmpSvg === false) {
        throw new RuntimeException('Unable to create temporary SVG file.');
    }

    $svgPath = $tmpSvg . '.svg';
    rename($tmpSvg, $svgPath);
    file_put_contents($svgPath, $svg);

    $command = 'magick ' . escapeshellarg($svgPath) . ' ' . escapeshellarg($outputPath);
    $exitCode = 0;
    system($command, $exitCode);

    @unlink($svgPath);

    if ($exitCode !== 0 || !is_file($outputPath)) {
        throw new RuntimeException('Failed to generate ' . $outputPath);
    }
}

function buildPreviewSvg(array $config): string
{
    $width = 1080;
    $height = 1350;
    $titleLines = wrapLines((string) ($config['title'] ?? ''), 24, 4);
    $subtitleLines = wrapLines((string) ($config['subtitle'] ?? ''), 36, 3);
    $badge = svgEscape((string) ($config['badge'] ?? 'EncuestasElectorales.pe'));
    $footer = svgEscape((string) ($config['footer'] ?? 'Datos reales • sin ficticios'));
    $accent = svgEscape((string) ($config['accent'] ?? '#15ba75'));
    $image = $config['image'] ?? null;
    $logo = $config['logo'] ?? null;
    $imageHref = $image ? svgEscape((string) $image) : '';
    $logoHref = $logo ? svgEscape((string) $logo) : '';
    $imageBlock = '';
    $logoBlock = '';

    if ($imageHref !== '') {
        $imageBlock = <<<SVG
        <g clip-path="url(#photoClip)">
            <image href="{$imageHref}" x="640" y="215" width="330" height="330" preserveAspectRatio="xMidYMid slice"></image>
        </g>
SVG;
    }

    if ($logoHref !== '') {
        $logoBlock = <<<SVG
        <g clip-path="url(#logoClip)">
            <image href="{$logoHref}" x="92" y="940" width="92" height="92" preserveAspectRatio="xMidYMid slice"></image>
        </g>
SVG;
    }

    $titleSvg = '';
    $startY = 210;
    foreach ($titleLines as $index => $line) {
        $titleSvg .= '<text x="90" y="' . ($startY + ($index * 76)) . '" fill="#ffffff" font-family="Inter, Arial, sans-serif" font-size="66" font-weight="800">' . svgEscape($line) . '</text>' . PHP_EOL;
    }

    $subtitleSvg = '';
    $startSubtitleY = 520;
    foreach ($subtitleLines as $index => $line) {
        $subtitleSvg .= '<text x="90" y="' . ($startSubtitleY + ($index * 44)) . '" fill="rgba(255,255,255,0.82)" font-family="Inter, Arial, sans-serif" font-size="34" font-weight="500">' . svgEscape($line) . '</text>' . PHP_EOL;
    }

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#102f86"/>
            <stop offset="55%" stop-color="#0f7a4a"/>
            <stop offset="100%" stop-color="#15ba75"/>
        </linearGradient>
        <clipPath id="photoClip">
            <circle cx="805" cy="380" r="165"></circle>
        </clipPath>
        <clipPath id="logoClip">
            <rect x="92" y="940" width="92" height="92" rx="22" ry="22"></rect>
        </clipPath>
        <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="12" stdDeviation="18" flood-color="#052a16" flood-opacity="0.38"></feDropShadow>
        </filter>
    </defs>
    <rect width="{$width}" height="{$height}" fill="url(#bg)"></rect>
    <circle cx="920" cy="140" r="150" fill="rgba(255,255,255,0.10)"></circle>
    <circle cx="980" cy="1200" r="220" fill="rgba(255,255,255,0.08)"></circle>
    <circle cx="160" cy="1200" r="120" fill="rgba(255,255,255,0.08)"></circle>
    <rect x="70" y="80" width="400" height="54" rx="27" fill="rgba(255,255,255,0.12)"></rect>
    <text x="96" y="116" fill="#ffffff" font-family="Inter, Arial, sans-serif" font-size="24" font-weight="800" letter-spacing="2">{$badge}</text>
    <text x="90" y="340" fill="#ffffff" font-family="Inter, Arial, sans-serif" font-size="18" font-weight="700" letter-spacing="1.5">RUTA DE DATOS REAL</text>
    {$titleSvg}
    {$subtitleSvg}
    <rect x="80" y="870" width="920" height="320" rx="42" fill="rgba(255,255,255,0.10)" filter="url(#shadow)"></rect>
    <rect x="92" y="882" width="896" height="296" rx="34" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.14)"></rect>
    <circle cx="266" cy="1028" r="118" fill="rgba(255,255,255,0.14)" stroke="rgba(255,255,255,0.22)" stroke-width="4"></circle>
    {$imageBlock}
    <text x="430" y="972" fill="#ffffff" font-family="Inter, Arial, sans-serif" font-size="30" font-weight="800">Compartir miniatura</text>
    <text x="430" y="1018" fill="rgba(255,255,255,0.86)" font-family="Inter, Arial, sans-serif" font-size="26" font-weight="600">Estados, historias y vista previa social</text>
    <text x="430" y="1062" fill="rgba(255,255,255,0.78)" font-family="Inter, Arial, sans-serif" font-size="21" font-weight="500">{$footer}</text>
    <rect x="430" y="1100" width="246" height="10" rx="5" fill="rgba(255,255,255,0.28)"></rect>
    {$logoBlock}
    <text x="210" y="1008" text-anchor="middle" fill="#ffffff" font-family="Inter, Arial, sans-serif" font-size="24" font-weight="800">Previa</text>
    <text x="210" y="1043" text-anchor="middle" fill="rgba(255,255,255,0.82)" font-family="Inter, Arial, sans-serif" font-size="20" font-weight="500">1080 × 1350</text>
</svg>
SVG;
}

function savePreview(string $outputPath, string $svg): void
{
    ensureDir(dirname($outputPath));
    convertSvgToPng($svg, $outputPath);
}

$catalog = require $root . '/includes/data.php';
$distritos = $catalog['distritos'] ?? [];
$candidatos = $catalog['candidatos'] ?? [];
$partidos = $catalog['partidos'] ?? [];
$activeRounds = getRondasActivas();

$defaultFace = dataUriFromSource('assets/img/default-face.svg', $root);
$homeSvg = buildPreviewSvg([
    'badge' => 'EncuestasElectorales.pe',
    'title' => 'Encuestas web activas y candidaturas reales',
    'subtitle' => 'Sondeos ciudadanos por distrito, provincia y región con datos verificados y sin ficción.',
    'footer' => 'Publicación real para la web y redes sociales.',
    'image' => $defaultFace,
    'logo' => null,
]);
savePreview($shareRoot . '/default-share.png', $homeSvg);
savePreview($shareRoot . '/home.png', $homeSvg);

foreach ($candidatos as $candidate) {
    $candidateId = (string) ($candidate['id'] ?? '');
    if ($candidateId === '') {
        continue;
    }

    $party = null;
    foreach ($partidos as $item) {
        if ((string) ($item['id'] ?? '') === (string) ($candidate['partidoId'] ?? '')) {
            $party = $item;
            break;
        }
    }

    $photo = candidatePhotoSrc($candidate);
    $photoData = dataUriFromSource($photo, $root) ?? $defaultFace;
    $logoData = null;
    if ($party) {
        $logoData = dataUriFromSource((string) ($party['logo'] ?? ''), $root);
    }

    $candidateSvg = buildPreviewSvg([
        'badge' => 'Perfil candidato',
        'title' => (string) ($candidate['nombre'] ?? ''),
        'subtitle' => trim((string) ($party['nombre'] ?? '')) . ' · Foto y partido reales para compartir.',
        'footer' => 'Candidato real con fallback neutral si falta la foto.',
        'image' => $photoData,
        'logo' => $logoData,
    ]);

    savePreview($shareRoot . '/candidates/candidate-' . $candidateId . '.png', $candidateSvg);
}

foreach ($activeRounds as $round) {
    $roundId = (string) ($round['id'] ?? '');
    if ($roundId === '') {
        continue;
    }

    $district = null;
    foreach ($distritos as $item) {
        if ((string) ($item['id'] ?? '') === (string) ($round['distrito_id'] ?? '')) {
            $district = $item;
            break;
        }
    }

    $districtCandidates = [];
    foreach ($candidatos as $candidate) {
        if ((string) ($candidate['distritoId'] ?? '') === (string) ($round['distrito_id'] ?? '')) {
            $districtCandidates[] = $candidate;
        }
    }

    $heroCandidate = $districtCandidates[0] ?? null;
    $heroPhoto = $defaultFace;
    $heroLogo = null;
    if ($heroCandidate) {
        $heroPhoto = dataUriFromSource(candidatePhotoSrc($heroCandidate), $root) ?? $defaultFace;
        foreach ($partidos as $item) {
            if ((string) ($item['id'] ?? '') === (string) ($heroCandidate['partidoId'] ?? '')) {
                $heroLogo = dataUriFromSource((string) ($item['logo'] ?? ''), $root);
                break;
            }
        }
    }

    $levelLabel = surveyLevelLabel((string) ($round['nivel'] ?? 'distrito'));
    $placeLabel = '';
    if ($district) {
        $placeLabel = surveyScopeLabel($round, $district);
    } else {
        $placeLabel = $levelLabel . ' ' . territoryDisplayName((string) ($round['distrito_id'] ?? ''));
    }

    $roundSvg = buildPreviewSvg([
        'badge' => 'Encuesta web activa',
        'title' => $placeLabel,
        'subtitle' => (string) ($round['titulo'] ?? 'Ronda activa') . ' · Activa hasta ' . (string) ($round['fecha_cierre'] ?? ''),
        'footer' => 'Ronda real semilla hasta el 5 de agosto de 2026.',
        'image' => $heroPhoto,
        'logo' => $heroLogo,
    ]);

    savePreview($shareRoot . '/surveys/survey-' . $roundId . '.png', $roundSvg);
}

echo 'Generated share previews in ' . $shareRoot . PHP_EOL;
