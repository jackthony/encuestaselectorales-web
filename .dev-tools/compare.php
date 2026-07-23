<?php
// Fase 1 — comparación dev-only. No producción.

$base = dirname(__DIR__);
$refSrc = $base . '/public/assets/miniatura-compartir/og-results-preview-approved.png';
$implSrc = $base . '/storage/app/testing/og-results-preview-static-1200x630.png';

$refNormalizedOut = $base . '/storage/app/testing/og-results-preview-reference-normalized.png';
$overlayOut = $base . '/storage/app/testing/og-results-preview-overlay.png';
$sideBySideOut = $base . '/storage/app/testing/og-results-preview-side-by-side.png';

// 1. Normalizar referencia a 1200x630 (solo para comparación, nunca como fondo del resultado).
$refOriginal = imagecreatefrompng($refSrc);
$refNormalized = imagecreatetruecolor(1200, 630);
imagecopyresampled($refNormalized, $refOriginal, 0, 0, 0, 0, 1200, 630, imagesx($refOriginal), imagesy($refOriginal));
imagepng($refNormalized, $refNormalizedOut);

// 2. Overlay: referencia 50% + implementación 50%.
$impl = imagecreatefrompng($implSrc);
$overlay = imagecreatetruecolor(1200, 630);
imagecopy($overlay, $refNormalized, 0, 0, 0, 0, 1200, 630);
imagecopymerge($overlay, $impl, 0, 0, 0, 0, 1200, 630, 50);
imagepng($overlay, $overlayOut);

// 3. Side-by-side.
$sideBySide = imagecreatetruecolor(2410, 630);
$white = imagecolorallocate($sideBySide, 255, 255, 255);
imagefill($sideBySide, 0, 0, $white);
imagecopy($sideBySide, $refNormalized, 0, 0, 0, 0, 1200, 630);
imagecopy($sideBySide, $impl, 1210, 0, 0, 0, 1200, 630);
imagepng($sideBySide, $sideBySideOut);

// 4. Diferencia numérica simple (promedio de delta RGB por pixel, muestreado).
$diffSum = 0;
$samples = 0;
for ($x = 0; $x < 1200; $x += 4) {
    for ($y = 0; $y < 630; $y += 4) {
        $c1 = imagecolorat($refNormalized, $x, $y);
        $c2 = imagecolorat($impl, $x, $y);
        $r1 = ($c1 >> 16) & 0xFF; $g1 = ($c1 >> 8) & 0xFF; $b1 = $c1 & 0xFF;
        $r2 = ($c2 >> 16) & 0xFF; $g2 = ($c2 >> 8) & 0xFF; $b2 = $c2 & 0xFF;
        $diffSum += (abs($r1 - $r2) + abs($g1 - $g2) + abs($b1 - $b2)) / 3;
        $samples++;
    }
}
$avgDiff = $diffSum / $samples;
$similarity = max(0, 100 - ($avgDiff / 255 * 100));
printf("Diferencia promedio por canal: %.2f / 255\n", $avgDiff);
printf("Similitud aproximada (heurística ingenua pixel-a-pixel, no perceptual): %.1f%%\n", $similarity);
echo "Generado:\n - $refNormalizedOut\n - $overlayOut\n - $sideBySideOut\n";
