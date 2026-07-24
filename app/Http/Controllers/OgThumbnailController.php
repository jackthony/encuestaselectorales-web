<?php

namespace App\Http\Controllers;

use App\Application\Portal\OgThumbnailData;
use App\Application\Portal\SurveyRoundDetailFactory;
use App\Domain\Catalog\Contracts\TerritoryCatalog;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Infrastructure\Media\OgThumbnailCache;
use App\Infrastructure\Media\OgThumbnailRenderer;
use Illuminate\Http\Response;

final class OgThumbnailController extends Controller
{
    public function __construct(
        private readonly TerritoryCatalog $territories,
        private readonly SurveyRoundQuery $rounds,
        private readonly SurveyRoundDetailFactory $details,
        private readonly OgThumbnailData $transformer,
        private readonly OgThumbnailRenderer $renderer,
        private readonly OgThumbnailCache $cache,
    ) {}

    public function show(string $scope, string $slug): Response
    {
        abort_unless(in_array($scope, ['region', 'province', 'district'], true), 404);

        $territory = $this->territories->findPublishedByScopeAndSlug($scope, $slug);
        abort_unless($territory !== null, 404);

        $detail = $this->details->make($this->rounds->forTerritory($territory->id));
        $data = $this->transformer->make($detail);
        abort_unless($data !== null, 404);

        $lastVoteAt = $detail['round']['last_vote_at'] ?? null;
        $png = $this->cache->remember($territory->id, $lastVoteAt, fn (): string => $this->renderer->render($data));

        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=300');
    }
}
