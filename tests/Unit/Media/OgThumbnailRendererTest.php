<?php

namespace Tests\Unit\Media;

use App\Infrastructure\Media\OgThumbnailRenderer;
use Tests\TestCase;

final class OgThumbnailRendererTest extends TestCase
{
    public function test_renders_a_1200x630_png_for_a_typical_result_set(): void
    {
        $png = $this->renderer()->render($this->data());

        $info = getimagesizefromstring($png);

        self::assertNotFalse($info);
        self::assertSame(1200, $info[0]);
        self::assertSame(630, $info[1]);
        self::assertSame('image/png', $info['mime']);
    }

    public function test_renders_without_error_when_there_are_no_votes_yet(): void
    {
        $data = $this->data();
        $data['footer_text'] = 'Base: 0 votos';
        $data['results'][0]['percentage_label'] = '0.0%';
        $data['results'][0]['bar_width'] = 0;
        $data['results'][1]['percentage_label'] = '0.0%';
        $data['results'][1]['bar_width'] = 0;

        $png = $this->renderer()->render($data);

        $info = getimagesizefromstring($png);
        self::assertSame(1200, $info[0]);
        self::assertSame(630, $info[1]);
    }

    public function test_renders_a_long_title_without_throwing(): void
    {
        $data = $this->data();
        $data['title'] = 'Distrito de Carmen de la Legua-Reynoso y Alrededores del Callao Metropolitano';

        $png = $this->renderer()->render($data);

        $info = getimagesizefromstring($png);
        self::assertSame(1200, $info[0]);
        self::assertSame(630, $info[1]);
    }

    private function renderer(): OgThumbnailRenderer
    {
        return new OgThumbnailRenderer(
            backgroundPath: public_path('assets/miniatura-compartir/og-results-background-1200x630.png'),
            logoPath: public_path('assets/miniatura-compartir/brand-logo-horizontal-cleaned.png'),
            domainLockupPath: public_path('assets/miniatura-compartir/brand-domain-lockup-cleaned.png'),
            boldFontPath: resource_path('fonts/Inter-Bold.ttf'),
            semiBoldFontPath: resource_path('fonts/Inter-SemiBold.ttf'),
        );
    }

    /** @return array<string, mixed> */
    private function data(): array
    {
        return [
            'eyebrow' => 'SONDEO CIUDADANO · PERÚ 2026',
            'title' => 'Distrito de San Isidro',
            'subtitle' => 'Encuesta distrital de San Isidro · Ronda 1',
            'footer_text' => 'Base: 100 votos · Actualizado: 23/07/2026 19:32',
            'results' => [
                [
                    'position' => 1,
                    'is_first' => true,
                    'candidate_name' => 'María Fernanda Quispe Rojas',
                    'party_name' => 'AVANZA PAÍS',
                    'percentage_label' => '70.0%',
                    'votes_label' => '70',
                    'bar_width' => 181,
                ],
                [
                    'position' => 2,
                    'is_first' => false,
                    'candidate_name' => 'Jorge Luis Delgado',
                    'party_name' => 'FUERZA POPULAR',
                    'percentage_label' => '30.0%',
                    'votes_label' => '30',
                    'bar_width' => 77,
                ],
            ],
        ];
    }
}
