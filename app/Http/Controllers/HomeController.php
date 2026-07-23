<?php

namespace App\Http\Controllers;

use App\Application\Portal\PublicPortalPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __construct(private readonly PublicPortalPageService $pages) {}

    public function index(Request $request): View
    {
        return view('pages.home', $this->pages->homeViewData(
            $request->query('scope') !== null ? (string) $request->query('scope') : null,
            $request->query('slug') !== null ? (string) $request->query('slug') : null,
        ));
    }
}
