<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LegacyBridgeController extends Controller
{
    private function legacyRoot(): string
    {
        return (string) config('legacy.root_path', dirname(base_path()));
    }

    private function resolveLegacyPath(string $relativePath): ?string
    {
        $relativePath = ltrim(str_replace(['\\', '..'], ['/', ''], $relativePath), '/');
        if ($relativePath === '') {
            return null;
        }

        $root = realpath($this->legacyRoot());
        if ($root === false) {
            return null;
        }

        $candidate = $this->legacyRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $resolved = realpath($candidate);
        if ($resolved === false || !str_starts_with($resolved, $root)) {
            return null;
        }

        return $resolved;
    }

    private function renderLegacyPhp(string $path, Request $request): string
    {
        $previousGet = $_GET;
        $previousRequest = $_REQUEST;
        $_GET = $request->query();
        $_REQUEST = array_merge($_REQUEST, $_GET);

        ob_start();
        include $path;
        $output = ob_get_clean();

        $_GET = $previousGet;
        $_REQUEST = $previousRequest;

        return $output === false ? '' : $output;
    }

    public function page(Request $request): Response
    {
        $page = (string) ($request->route('page') ?? 'index.php');
        $path = $this->resolveLegacyPath($page);
        if ($path === null || !is_file($path)) {
            abort(404);
        }

        $content = pathinfo($path, PATHINFO_EXTENSION) === 'php'
            ? $this->renderLegacyPhp($path, $request)
            : (string) file_get_contents($path);

        return response($content, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function asset(string $path): Response
    {
        $resolved = $this->resolveLegacyPath('assets/' . $path);
        if ($resolved === null || !is_file($resolved)) {
            abort(404);
        }

        return response()->file($resolved);
    }
}
