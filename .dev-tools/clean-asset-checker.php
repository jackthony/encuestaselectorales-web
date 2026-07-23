<?php
// Fase 1 — corrige el defecto de exportación (checkerboard de transparencia
// horneado como píxeles reales) en los assets aprobados. No redibuja ni
// recolorea el contenido real (ícono, texto, marca): solo reemplaza los
// píxeles neutros/casi-blancos (fondo) por blanco sólido #FFFFFF.
// Deja los archivos originales intactos; genera copias "-cleaned".

function cleanChecker(string $src, string $dst): void
{
    $img = imagecreatefrompng($src);
    $w = imagesx($img);
    $h = imagesy($img);
    imagealphablending($img, false);
    imagesavealpha($img, true);

    $replaced = 0;
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $rgb = imagecolorat($img, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $max = max($r, $g, $b);
            $min = min($r, $g, $b);
            // Neutro (gris/blanco, no un color de marca) y claro => fondo del checker.
            if ($max >= 235 && ($max - $min) <= 8) {
                $white = imagecolorallocatealpha($img, 255, 255, 255, 0);
                imagesetpixel($img, $x, $y, $white);
                $replaced++;
            }
        }
    }
    imagepng($img, $dst);
    printf("%s -> %s (%d px reemplazados de %d)\n", $src, $dst, $replaced, $w * $h);
}

$base = dirname(__DIR__) . '/public/assets/miniatura-compartir';
cleanChecker("$base/brand-logo-horizontal.png", "$base/brand-logo-horizontal-cleaned.png");
cleanChecker("$base/brand-domain-lockup.png", "$base/brand-domain-lockup-cleaned.png");
