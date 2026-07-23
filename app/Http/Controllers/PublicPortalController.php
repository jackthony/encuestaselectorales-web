<?php

namespace App\Http\Controllers;

use App\Application\Portal\PublicPortalPageService;
use App\Domain\Catalog\Contracts\TerritoryCatalog;
use Illuminate\View\View;

final class PublicPortalController extends Controller
{
    public function __construct(
        private readonly PublicPortalPageService $pages,
        private readonly TerritoryCatalog $territories,
    ) {}

    public function scope(string $scope, string $slug): View
    {
        abort_unless(in_array($scope, ['region', 'province', 'district'], true), 404);
        $territory = $this->territories->findPublishedByScopeAndSlug($scope, $slug);
        abort_unless($territory !== null, 404);

        return view('pages.scope', $this->pages->scopeViewData($territory, request()->fullUrl()));
    }
}
