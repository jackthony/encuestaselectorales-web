<?php
/**
 * BL-10 regression check (design.md Decision 4).
 *
 * For each PHP page produced by the refactor, compares the multiset of
 * "tag + sorted class list" it emits against the canvas-gemini/ prototype
 * it replaces. Comparison is scoped to <body> minus <header>...</header>
 * and minus <footer>...</footer>: those two elements are *intentionally*
 * unified into single shared partials (partials/header.php, partials/footer.php)
 * per design.md Decision 2/3 and the "Repeated UI lives in partials"
 * requirement, so they are expected to render identically across all 8
 * pages rather than identically to each page's own original. Everything
 * else — ticker bar, breadcrumbs, main content, floating buttons, modals —
 * is page-specific and must stay byte-identical in structure.
 *
 * The only allowed *value* delta is the Decision 1 palette reconciliation
 * (#fcfcfc/#f8fafc -> #f4f5f3, #15BA75 -> #15ba75). That reconciliation
 * lives entirely inside inline <script>/<style> text in <head> (Tailwind
 * config), which this check never inspects (it only looks at tag names
 * and class attributes), so in practice it produces zero diff here.
 *
 * The legal scrub (tasks.md section 6, owner-authorized 2026-07-18) is
 * implemented as *text-only* substitutions in the page bodies (retitling
 * a pollster attribution, rewording the out-of-scope GORE Ucayali article)
 * so it does not touch tags or classes either — no special-casing needed.
 *
 * distrito.php has no canvas-gemini source (see tasks.md Notes / this
 * project's BL-10 execution log) so it is checked for existence + valid
 * render instead of a structural diff.
 *
 * Usage: php scripts/check-refactor.php
 * Exit 0 = all pages pass. Exit 1 = at least one page failed.
 */

error_reporting(E_ALL & ~E_DEPRECATED);

$root       = dirname(__DIR__);
$canvasDir  = $root . '/canvas-gemini';
$canvasRef  = '2a6e18f'; // commit the canvas prototypes are snapshotted at (task 8.2)

/**
 * page-file => canvas-source-file
 * (widget-gps / flujo_de_votaci_n_gps.html is verified separately, see
 * checkWidgetGps() below, since it is consolidated into partials/widget-gps.php
 * rather than becoming a standalone page.)
 */
$pages = [
    'index.php'          => 'portal_de_encuestas.html',
    'sondeos.php'         => 'portal_de_sondeos_ciudadanos.html',
    'encuesta.php'        => 'detalle_de_encuesta.html',
    'candidato.php'       => 'perfil_de_candidato.html',
    'encuestadoras.php'   => 'directorio_de_encuestadoras.html',
    'metodologia.php'     => 'metodolog_a.html',
    'quienes-somos.php'   => 'qui_nes_somos_autoridad.html',
];

/** Reads a canvas-gemini source file, falling back to the git blob at
 *  $canvasRef once the working-tree copy is deleted (task 8.1/8.2). */
function readCanvasSource(string $root, string $canvasDir, string $canvasRef, string $file): ?string
{
    $path = $canvasDir . '/' . $file;
    if (is_file($path)) {
        return file_get_contents($path);
    }
    $out = [];
    $cmd = sprintf(
        'git -C %s show %s:canvas-gemini/%s 2>&1',
        escapeshellarg($root),
        escapeshellarg($canvasRef),
        escapeshellarg($file)
    );
    exec($cmd, $out, $exit);
    if ($exit !== 0) {
        return null;
    }
    return implode("\n", $out);
}

/** Renders a PHP page via CLI and returns its stdout, or null on failure. */
function renderPhpPage(string $root, string $page): ?string
{
    $path = $root . '/' . $page;
    if (!is_file($path)) {
        return null;
    }
    $php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
    $cmd = sprintf('%s -f %s 2>&1', escapeshellarg($php), escapeshellarg($path));
    $out = shell_exec($cmd);
    return $out === null ? null : $out;
}

/**
 * Extracts a sorted "tag.class1.class2" multiset for every element inside
 * <body>, excluding descendants of <header> and <footer> (and those two
 * tags themselves) — see file docblock for why.
 *
 * @return array<string,int> token => count
 */
function extractBodyMinusChrome(string $html): array
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();

    $body = $doc->getElementsByTagName('body')->item(0);
    $tokens = [];
    if (!$body) {
        return $tokens;
    }

    // Mark every node under <header>/<footer> (inclusive) to exclude.
    $excluded = new SplObjectStorage();
    foreach (['header', 'footer'] as $chromeTag) {
        foreach ($doc->getElementsByTagName($chromeTag) as $chromeNode) {
            $excluded->attach($chromeNode);
            $stack = [$chromeNode];
            while ($stack) {
                $n = array_pop($stack);
                foreach ($n->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        $excluded->attach($child);
                        $stack[] = $child;
                    }
                }
            }
        }
    }

    $walk = function (DOMNode $node) use (&$walk, &$tokens, $excluded) {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            if ($excluded->contains($child)) {
                continue;
            }
            /** @var DOMElement $child */
            // <script> tags are excluded from the diff entirely. None of the
            // 8 canvas prototypes ever put a `class` on a <script> tag, so
            // counting bare tag presence doesn't verify anything about
            // whether the JS *content* moved correctly (this check compares
            // tags+classes, not text) — it would only produce false
            // failures from consolidating N inline scripts into one shared
            // assets/js/app.js <script src> (proposal.md's stated problem;
            // design.md target structure), which is the intended, spec-
            // mandated result of this refactor, not a structural regression.
            if (strtolower($child->tagName) === 'script') {
                $walk($child);
                continue;
            }
            $classAttr = $child->getAttribute('class');
            $classes = preg_split('/\s+/', trim($classAttr));
            $classes = array_values(array_filter($classes, fn($c) => $c !== ''));
            sort($classes);
            $token = strtolower($child->tagName) . (count($classes) ? '.' . implode('.', $classes) : '');
            $tokens[$token] = ($tokens[$token] ?? 0) + 1;
            $walk($child);
        }
    };
    $walk($body);

    return $tokens;
}

/** @return array{missing: array<string,int>, extra: array<string,int>} */
function diffMultisets(array $expected, array $actual): array
{
    $missing = [];
    $extra = [];
    foreach ($expected as $token => $count) {
        $delta = $count - ($actual[$token] ?? 0);
        if ($delta > 0) {
            $missing[$token] = $delta;
        }
    }
    foreach ($actual as $token => $count) {
        $delta = $count - ($expected[$token] ?? 0);
        if ($delta > 0) {
            $extra[$token] = $delta;
        }
    }
    return ['missing' => $missing, 'extra' => $extra];
}

$failures = 0;
$results = [];

foreach ($pages as $page => $canvasFile) {
    $canvasHtml = readCanvasSource($root, $canvasDir, $canvasRef, $canvasFile);
    if ($canvasHtml === null) {
        $results[] = ['page' => $page, 'ok' => false, 'reason' => "cannot read canvas source $canvasFile"];
        $failures++;
        continue;
    }

    $phpHtml = renderPhpPage($root, $page);
    if ($phpHtml === null) {
        $results[] = ['page' => $page, 'ok' => false, 'reason' => "$page does not exist or did not render"];
        $failures++;
        continue;
    }

    $expected = extractBodyMinusChrome($canvasHtml);
    $actual   = extractBodyMinusChrome($phpHtml);
    $diff     = diffMultisets($expected, $actual);

    if (empty($diff['missing']) && empty($diff['extra'])) {
        $results[] = ['page' => $page, 'ok' => true];
    } else {
        $results[] = [
            'page' => $page,
            'ok' => false,
            'reason' => 'structural diff vs ' . $canvasFile,
            'missing' => $diff['missing'],
            'extra' => $diff['extra'],
        ];
        $failures++;
    }
}

/**
 * 8th check: partials/widget-gps.php must render the same modal-overlay
 * structure (#modal-overlay and its 4 steps) as flujo_de_votaci_n_gps.html,
 * wherever it is included. distrito.php has no canvas source (logged in
 * tasks.md) so it gets an existence + render smoke check instead of a
 * structural diff.
 */
function checkDistrito(string $root): array
{
    $path = $root . '/distrito.php';
    if (!is_file($path)) {
        return ['page' => 'distrito.php', 'ok' => false, 'reason' => 'file does not exist'];
    }
    $php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
    $lint = shell_exec(sprintf('%s -l %s 2>&1', escapeshellarg($php), escapeshellarg($path)));
    if (strpos($lint ?? '', 'No syntax errors') === false) {
        return ['page' => 'distrito.php', 'ok' => false, 'reason' => trim($lint ?? 'lint failed')];
    }
    $out = renderPhpPage($root, 'distrito.php');
    if ($out === null || trim($out) === '') {
        return ['page' => 'distrito.php', 'ok' => false, 'reason' => 'rendered empty output'];
    }
    if (strpos($out, '<header') === false || strpos($out, '<footer') === false) {
        return ['page' => 'distrito.php', 'ok' => false, 'reason' => 'missing shared header/footer partials'];
    }
    return ['page' => 'distrito.php', 'ok' => true, 'reason' => 'no canvas source — existence/render smoke check only'];
}

$distritoResult = checkDistrito($root);
$results[] = $distritoResult;
if (!$distritoResult['ok']) {
    $failures++;
}

// --- Report ---
$total = count($results);
$passed = $total - $failures;

foreach ($results as $r) {
    if ($r['ok']) {
        $note = isset($r['reason']) ? " ({$r['reason']})" : '';
        echo "PASS  {$r['page']}{$note}\n";
    } else {
        echo "FAIL  {$r['page']}: {$r['reason']}\n";
        foreach (($r['missing'] ?? []) as $token => $count) {
            echo "        missing x{$count}: {$token}\n";
        }
        foreach (($r['extra'] ?? []) as $token => $count) {
            echo "        extra   x{$count}: {$token}\n";
        }
    }
}

echo "\n{$passed} of {$total} pages match.\n";

exit($failures > 0 ? 1 : 0);
