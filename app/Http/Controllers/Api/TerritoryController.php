<?php

namespace App\Http\Controllers\Api;

use App\Domain\Catalog\Contracts\TerritoryCatalog;
use App\Http\Controllers\Controller;
use App\Http\Resources\TerritoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TerritoryController extends Controller
{
    public function __construct(private readonly TerritoryCatalog $territories) {}

    public function search(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        return TerritoryResource::collection($this->territories->searchPublished(
            $validated['q'],
            (int) ($validated['limit'] ?? 20),
        ));
    }
}
