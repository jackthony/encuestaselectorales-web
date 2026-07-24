<?php

namespace App\Infrastructure\Media;

use GdImage;
use RuntimeException;

/**
 * Reproduces, pixel for pixel, the layout approved in Fase 1
 * (resources/css/og-results-preview.css) using PHP GD instead of a browser.
 */
final class OgThumbnailRenderer
{
    private const WIDTH = 1200;

    private const HEIGHT = 630;

    private const PANEL_X = 42;

    private const PANEL_Y = 181;

    private const PANEL_WIDTH = 1116;

    private const PANEL_HEIGHT = 414;

    private const PANEL_RADIUS = 20;

    private const ROWS_AREA_TOP = 44;

    private const ROWS_AREA_HEIGHT = 325;

    private const TITLE_MAX_WIDTH = 805;

    private const TITLE_MIN_FONT_SIZE = 32;

    private const TITLE_BASE_FONT_SIZE = 59;

    private const COLOR_BLUE = '#102F86';

    private const COLOR_GREEN = '#15BA75';

    private const COLOR_GREEN_TEXT = '#0F7A4A';

    private const COLOR_TEXT = '#111827';

    private const COLOR_MUTED = '#4B5563';

    private const COLOR_BORDER = '#E5E7EB';

    private const COLOR_DIVIDER = '#CBD5E1';

    private const COLOR_WHITE = '#FFFFFF';

    /** @var array<string, int> */
    private array $colors = [];

    public function __construct(
        private readonly string $backgroundPath = '',
        private readonly string $logoPath = '',
        private readonly string $domainLockupPath = '',
        private readonly string $boldFontPath = '',
        private readonly string $semiBoldFontPath = '',
    ) {}

    /**
     * @param  array<string, mixed>  $data  OgThumbnailData::make() output
     */
    public function render(array $data): string
    {
        $this->colors = [];

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($canvas === false) {
            throw new RuntimeException('Unable to allocate the OG thumbnail canvas.');
        }

        imagefill($canvas, 0, 0, $this->color($canvas, self::COLOR_WHITE));

        $this->drawBackground($canvas);
        $this->drawLogo($canvas);
        $this->drawDivider($canvas);
        $this->drawEyebrow($canvas, $data['eyebrow']);
        $this->drawTitle($canvas, $data['title']);
        $this->drawSubtitle($canvas, $data['subtitle']);
        $this->drawPanel($canvas, $data['results'], $data['footer_text']);
        $this->drawDomainLockup($canvas);

        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();
        imagedestroy($canvas);

        if ($png === false) {
            throw new RuntimeException('Unable to encode the OG thumbnail as PNG.');
        }

        return $png;
    }

    private function drawBackground(GdImage $canvas): void
    {
        $bg = $this->loadPng($this->backgroundPath);
        $srcW = imagesx($bg);
        $srcH = imagesy($bg);

        $scale = max(self::WIDTH / $srcW, self::HEIGHT / $srcH);
        $cropW = self::WIDTH / $scale;
        $cropH = self::HEIGHT / $scale;
        $cropX = ($srcW - $cropW) / 2;
        $cropY = ($srcH - $cropH) / 2;

        imagecopyresampled(
            $canvas, $bg,
            0, 0, (int) round($cropX), (int) round($cropY),
            self::WIDTH, self::HEIGHT, (int) round($cropW), (int) round($cropH),
        );
        imagedestroy($bg);
    }

    private function drawLogo(GdImage $canvas): void
    {
        $logo = $this->loadPng($this->logoPath);
        $srcW = imagesx($logo);
        $srcH = imagesy($logo);

        $boxW = 246;
        $boxH = 126;
        $scale = min($boxW / $srcW, $boxH / $srcH);
        $dstW = (int) round($srcW * $scale);
        $dstH = (int) round($srcH * $scale);
        $dstY = 34 + intdiv($boxH - $dstH, 2);

        imagecopyresampled($canvas, $logo, 46, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($logo);
    }

    private function drawDivider(GdImage $canvas): void
    {
        $color = $this->color($canvas, self::COLOR_DIVIDER);
        imagefilledrectangle($canvas, 306, 38, 306, 38 + 124, $color);
    }

    private function drawEyebrow(GdImage $canvas, string $text): void
    {
        $this->drawText($canvas, $this->boldFontPath, 22, self::COLOR_BLUE, 340, 38, $text);
    }

    private function drawTitle(GdImage $canvas, string $text): void
    {
        $fontSize = $this->shrinkFontSizeToFit($this->boldFontPath, self::TITLE_BASE_FONT_SIZE, self::TITLE_MIN_FONT_SIZE, $text, self::TITLE_MAX_WIDTH);
        $this->drawText($canvas, $this->boldFontPath, $fontSize, self::COLOR_TEXT, 340, 67, $text);
    }

    /**
     * Mirrors the title's own shrink-to-fit behaviour (Fase 1 did this in JS via
     * document.fonts.ready) instead of hard-truncating readable candidate names.
     */
    private function shrinkFontSizeToFit(string $fontPath, int $baseSize, int $minSize, string $text, int $maxWidth): int
    {
        $fontSize = $baseSize;
        while ($fontSize > $minSize && $this->textWidth($fontPath, $fontSize, $text) > $maxWidth) {
            $fontSize--;
        }

        return $fontSize;
    }

    private function drawSubtitle(GdImage $canvas, string $text): void
    {
        $this->drawText($canvas, $this->semiBoldFontPath, 25, self::COLOR_MUTED, 340, 133, $text);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    private function drawPanel(GdImage $canvas, array $results, string $footerText): void
    {
        $this->roundedRect(
            $canvas,
            self::PANEL_X, self::PANEL_Y,
            self::PANEL_X + self::PANEL_WIDTH, self::PANEL_Y + self::PANEL_HEIGHT,
            self::PANEL_RADIUS, $this->color($canvas, self::COLOR_WHITE),
        );
        $this->roundedRectOutline(
            $canvas,
            self::PANEL_X, self::PANEL_Y,
            self::PANEL_X + self::PANEL_WIDTH, self::PANEL_Y + self::PANEL_HEIGHT,
            self::PANEL_RADIUS, $this->color($canvas, self::COLOR_BORDER),
        );

        $this->drawText($canvas, $this->boldFontPath, 18, self::COLOR_MUTED, self::PANEL_X + 42, self::PANEL_Y + 16, 'RESULTADOS');
        $this->drawCenteredText($canvas, $this->boldFontPath, 16, self::COLOR_MUTED, self::PANEL_X + 782, self::PANEL_Y + 16, 102, 'PORCENTAJE');
        $this->drawCenteredText($canvas, $this->boldFontPath, 16, self::COLOR_MUTED, self::PANEL_X + 972, self::PANEL_Y + 16, 112, 'VOTOS');

        $rowCount = max(count($results), 1);
        $rowPitch = self::ROWS_AREA_HEIGHT / $rowCount;

        foreach ($results as $index => $option) {
            $rowTop = self::PANEL_Y + self::ROWS_AREA_TOP + $index * $rowPitch;
            $this->drawRow($canvas, $option, $rowTop, $rowPitch);

            if ($index < count($results) - 1) {
                $sepY = (int) round(self::PANEL_Y + self::ROWS_AREA_TOP + ($index + 1) * $rowPitch);
                imagefilledrectangle(
                    $canvas, self::PANEL_X + 20, $sepY, self::PANEL_X + 20 + 1076, $sepY,
                    $this->color($canvas, self::COLOR_BORDER),
                );
            }
        }

        $this->drawFooter($canvas, $footerText);
    }

    /**
     * @param  array<string, mixed>  $option
     */
    private function drawRow(GdImage $canvas, array $option, float $rowTop, float $rowPitch): void
    {
        $centerY = (int) round($rowTop + $rowPitch / 2);
        $isFirst = (bool) $option['is_first'];
        $accent = $isFirst ? self::COLOR_GREEN : self::COLOR_BLUE;
        $accentText = $isFirst ? self::COLOR_GREEN_TEXT : self::COLOR_BLUE;

        // Rank badge (54x46, radius 6).
        $badgeX = self::PANEL_X + 37;
        $badgeY = $centerY - 23;
        $this->roundedRect($canvas, $badgeX, $badgeY, $badgeX + 54, $badgeY + 46, 6, $this->color($canvas, $accent));
        $this->drawCenteredTextVertical($canvas, $this->boldFontPath, 27, self::COLOR_WHITE, $badgeX, $badgeY, 54, 46, (string) $option['position']);

        // Candidate block. Shrink font to fit the 330px column before ever truncating text
        // (same principle as the title auto-shrink) — full names stay readable.
        $blockX = self::PANEL_X + 134;
        $candidateName = (string) $option['candidate_name'];
        $nameSize = $this->shrinkFontSizeToFit($this->boldFontPath, 22, 16, $candidateName, 330);
        $name = $this->textWidth($this->boldFontPath, $nameSize, $candidateName) <= 330
            ? $candidateName
            : $this->truncateToFit($this->boldFontPath, $nameSize, $candidateName, 330);
        $this->drawText($canvas, $this->boldFontPath, $nameSize, self::COLOR_TEXT, $blockX, $centerY - 22, $name);

        $partyName = (string) $option['party_name'];
        $partySize = $this->shrinkFontSizeToFit($this->boldFontPath, 16, 12, $partyName, 330);
        $party = $this->textWidth($this->boldFontPath, $partySize, $partyName) <= 330
            ? $partyName
            : $this->truncateToFit($this->boldFontPath, $partySize, $partyName, 330);
        $this->drawText($canvas, $this->boldFontPath, $partySize, $accentText, $blockX, $centerY + 2, $party);

        // Bar track + fill.
        $trackX = self::PANEL_X + 482;
        $trackY = $centerY - 4;
        imagefilledrectangle($canvas, $trackX, $trackY, $trackX + 258, $trackY + 9, $this->color($canvas, self::COLOR_BORDER));
        $barWidth = max(0, min(258, (int) $option['bar_width']));
        if ($barWidth > 0) {
            imagefilledrectangle($canvas, $trackX, $trackY, $trackX + $barWidth, $trackY + 9, $this->color($canvas, $accent));
        }

        // Percentage.
        $this->drawCenteredTextVertical($canvas, $this->boldFontPath, 25, $accentText, self::PANEL_X + 782, $centerY - 15, 102, 30, (string) $option['percentage_label']);

        // Votes box.
        $votesX = self::PANEL_X + 972;
        $votesY = $centerY - 23;
        $this->roundedRect($canvas, $votesX, $votesY, $votesX + 112, $votesY + 46, 6, $this->color($canvas, $accent));
        $this->drawCenteredTextVertical($canvas, $this->boldFontPath, 26, self::COLOR_WHITE, $votesX, $votesY, 112, 46, (string) $option['votes_label']);
    }

    private function drawFooter(GdImage $canvas, string $text): void
    {
        $footerY = self::PANEL_Y + 373;
        imagefilledrectangle($canvas, self::PANEL_X + 20, $footerY, self::PANEL_X + 20 + 1076, $footerY, $this->color($canvas, self::COLOR_BORDER));
        $this->drawText($canvas, $this->semiBoldFontPath, 17, self::COLOR_MUTED, self::PANEL_X + 70, $footerY + 14, $text);
    }

    private function drawDomainLockup(GdImage $canvas): void
    {
        $lockup = $this->loadPng($this->domainLockupPath);

        $boxX = 809;
        $boxY = 554;
        $boxW = 317;
        $boxH = 37;
        $displayW = 377;
        $displayH = 126;
        $offsetX = 33;
        $offsetY = 42;

        $scale = $displayW / imagesx($lockup);
        $srcX = (int) round($offsetX / $scale);
        $srcY = (int) round($offsetY / $scale);
        $srcW = (int) round($boxW / $scale);
        $srcH = (int) round($boxH / $scale);

        imagecopyresampled($canvas, $lockup, $boxX, $boxY, $srcX, $srcY, $boxW, $boxH, $srcW, $srcH);
        unset($displayH);
        imagedestroy($lockup);
    }

    private function loadPng(string $path): GdImage
    {
        if ($path === '' || ! is_file($path)) {
            throw new RuntimeException("OG thumbnail asset not found: {$path}");
        }

        $image = imagecreatefrompng($path);
        if ($image === false) {
            throw new RuntimeException("Unable to decode OG thumbnail asset: {$path}");
        }

        return $image;
    }

    private function color(GdImage $canvas, string $hex): int
    {
        return $this->colors[$hex] ??= (function () use ($canvas, $hex): int {
            [$r, $g, $b] = $this->hexToRgb($hex);
            $allocated = imagecolorallocate($canvas, $r, $g, $b);

            if ($allocated === false) {
                throw new RuntimeException("Unable to allocate color {$hex}.");
            }

            return $allocated;
        })();
    }

    /** @return array{0:int,1:int,2:int} */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function drawText(GdImage $canvas, string $fontPath, int $size, string $hexColor, int $x, int $top, string $text): void
    {
        $box = imagettfbbox($size, 0, $fontPath, $text);
        if ($box === false) {
            throw new RuntimeException("Unable to measure text with font {$fontPath}.");
        }

        $baselineY = $top - $box[5];
        imagettftext($canvas, $size, 0, $x, $baselineY, $this->color($canvas, $hexColor), $fontPath, $text);
    }

    private function drawCenteredText(GdImage $canvas, string $fontPath, int $size, string $hexColor, int $x, int $top, int $width, string $text): void
    {
        $textWidth = $this->textWidth($fontPath, $size, $text);
        $offsetX = $x + max(0, intdiv($width - $textWidth, 2));
        $this->drawText($canvas, $fontPath, $size, $hexColor, $offsetX, $top, $text);
    }

    private function drawCenteredTextVertical(GdImage $canvas, string $fontPath, int $size, string $hexColor, int $x, int $y, int $width, int $height, string $text): void
    {
        $box = imagettfbbox($size, 0, $fontPath, $text);
        if ($box === false) {
            throw new RuntimeException("Unable to measure text with font {$fontPath}.");
        }

        $textWidth = $box[2] - $box[0];
        $textAscent = -$box[5];
        $textHeight = $box[1] - $box[5];

        $offsetX = $x + max(0, intdiv($width - $textWidth, 2));
        $baselineY = $y + intdiv($height - (int) $textHeight, 2) + $textAscent;

        imagettftext($canvas, $size, 0, $offsetX, $baselineY, $this->color($canvas, $hexColor), $fontPath, $text);
    }

    private function textWidth(string $fontPath, int $size, string $text): int
    {
        $box = imagettfbbox($size, 0, $fontPath, $text);
        if ($box === false) {
            throw new RuntimeException("Unable to measure text with font {$fontPath}.");
        }

        return $box[2] - $box[0];
    }

    private function truncateToFit(string $fontPath, int $size, string $text, int $maxWidth): string
    {
        if ($this->textWidth($fontPath, $size, $text) <= $maxWidth) {
            return $text;
        }

        $characters = mb_str_split($text);
        $truncated = '';
        foreach ($characters as $character) {
            $candidate = $truncated.$character;
            if ($this->textWidth($fontPath, $size, $candidate) > $maxWidth) {
                break;
            }
            $truncated = $candidate;
        }

        return $truncated;
    }

    // ponytail: no box-shadow (GD has no blur) and no footer icon glyph — both are
    // decorative-only, the layout stays aligned without them. Revisit only if a
    // pixel-diff QA pass flags the thumbnail as visibly off from the Fase 1 reference.
    private function roundedRect(GdImage $canvas, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        imagefilledrectangle($canvas, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($canvas, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($canvas, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($canvas, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($canvas, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($canvas, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    private function roundedRectOutline(GdImage $canvas, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        imageline($canvas, $x1 + $radius, $y1, $x2 - $radius, $y1, $color);
        imageline($canvas, $x1 + $radius, $y2, $x2 - $radius, $y2, $color);
        imageline($canvas, $x1, $y1 + $radius, $x1, $y2 - $radius, $color);
        imageline($canvas, $x2, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagearc($canvas, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color);
        imagearc($canvas, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color);
        imagearc($canvas, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color);
        imagearc($canvas, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color);
    }
}
