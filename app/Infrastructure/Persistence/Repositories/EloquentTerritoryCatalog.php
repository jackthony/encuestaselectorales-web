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

    public function searchPublished(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        return $this->published()
            ->with('parent.parent')
            ->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('canonical_name', 'like', "%{$query}%")
                    ->orWhere('official_code', 'like', "{$query}%");
            })
            ->orderByRaw("CASE scope_type WHEN 'region' THEN 1 WHEN 'province' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->limit(max(1, min($limit, 50)))
            ->get()
            ->map(fn (Territory $territory): TerritoryData => $this->toData($territory))
            ->all();
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
