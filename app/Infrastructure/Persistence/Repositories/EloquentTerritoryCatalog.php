<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Data\TerritoryData;
use App\Domain\Catalog\Contracts\TerritoryCatalog;
use App\Domain\Survey\PublicationState;
use App\Infrastructure\Persistence\Models\Territory;
use Illuminate\Database\Eloquent\Builder;

final class EloquentTerritoryCatalog implements TerritoryCatalog
{
    public function findPublished(string $id): ?TerritoryData
    {
        $territory = $this->published()
            ->with('parent.parent')
            ->find($id);

        return $territory ? $this->toData($territory) : null;
    }

    public function findPublishedByScopeAndSlug(string $scopeType, string $slug): ?TerritoryData
    {
        $territory = $this->published()
            ->with('parent.parent')
            ->where('scope_type', $scopeType)
            ->where('slug', $slug)
            ->first();

        return $territory ? $this->toData($territory) : null;
    }

    public function toData(Territory $territory): TerritoryData
    {
        $ancestors = [];
        $parent = $territory->parent;

        while ($parent) {
            $ancestors[] = [
                'id' => (string) $parent->getKey(),
                'name' => $parent->name,
                'scope_type' => $parent->scope_type->value,
            ];
            $parent = $parent->parent;
        }

        return new TerritoryData(
            id: (string) $territory->getKey(),
            officialCode: $territory->official_code,
            name: $territory->name,
            slug: $territory->slug,
            scopeType: $territory->scope_type->value,
            ancestors: $ancestors,
        );
    }

    /** @return Builder<Territory> */
    private function published(): Builder
    {
        return Territory::query()
            ->where('publication_state', PublicationState::Published->value);
    }
}
