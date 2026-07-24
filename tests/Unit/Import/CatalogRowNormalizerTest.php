<?php

namespace Tests\Unit\Import;

use App\Infrastructure\Import\CatalogRowNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class CatalogRowNormalizerTest extends TestCase
{
    #[DataProvider('candidacyStatusCases')]
    public function test_candidacy_status_maps_public_rows_as_active(?string $input, ?string $expected): void
    {
        self::assertSame($expected, $this->invokeCandidacyStatus($input));
    }

    public static function candidacyStatusCases(): array
    {
        return [
            'admitted' => ['ADMITIDO', 'active'],
            'received' => ['RECIBIDO', 'active'],
            'pending' => ['PENDIENTE', 'active'],
            'excluded' => ['EXCLUIDO', 'active'],
            'withdrawn' => ['RETIRADO', 'active'],
            'challenged' => ['TACHA_FUNDADA', 'active'],
            'inactive' => ['INACTIVE', 'active'],
            'improcedente' => ['IMPROCEDENTE', 'active'],
            'inadmisible' => ['INADMISIBLE', 'active'],
            'blank' => [null, null],
        ];
    }

    private function invokeCandidacyStatus(?string $input): ?string
    {
        $normalizer = new CatalogRowNormalizer();
        $method = new ReflectionMethod($normalizer, 'candidacyStatus');
        $method->setAccessible(true);

        return $method->invoke($normalizer, $input);
    }
}
