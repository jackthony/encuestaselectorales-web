<?php

namespace App\Domain\Catalog\Contracts;

use App\Application\Data\TerritoryData;

// ponytail: Domain contract returns an Application-layer DTO — invert only if Domain needs to
// consume this without the Application layer loaded.
interface TerritoryCatalog
{
    public function findPublished(string $id): ?TerritoryData;

    public function findPublishedByScopeAndSlug(string $scopeType, string $slug): ?TerritoryData;
}
