<?php

namespace App\Domain\Catalog\Contracts;

use App\Application\Data\TerritoryData;

interface TerritoryCatalog
{
    public function findPublished(string $id): ?TerritoryData;

    /** @return array<int, TerritoryData> */
    public function searchPublished(string $query, int $limit = 20): array;
}
