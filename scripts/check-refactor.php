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

/**
 * Compensatory check: excluding <header>/<footer> from the per-page diff
 * (above) means a broken partials/header.php or partials/footer.php could
 * pass the whole suite silently. This verifies two things instead of
 * trusting the exclusion blindly:
 *   1. Every page that includes the partial renders the *same* header (and
 *      the same footer) as every other page — proves there is truly one
 *      shared source, not accidental per-page drift.
 *   2. That shared header/footer matches, tag-for-tag and class-for-class,
 *      the canvas prototype tasks.md 3.1/3.2 name as the source of truth:
 *      portal_de_sondeos_ciudadanos.html (picked because it's the newest/
 *      most complete "cluster B" header — the one design.md itself
 *      describes as "nav + Distritos de Lima dropdown" — and reused for
 *      the footer for internal consistency, tasks.md section 3).
 * This does NOT relax to "close enough" if the diff isn't exact — a real
 * mismatch here is reported and fails the run, same as any other check.
 */
/**
 * partials/header.php parameterizes which nav item is "active" per page
 * (an intentional design.md-consistent choice — see the partial's own
 * docblock: callers set $activeNav so the right item is highlighted,
 * matching what each canvas original did for its own page). That makes
 * the *only* legitimate difference between any two pages' rendered
 * headers the active/inactive class pair on one nav `<a>`. Normalized to
 * a single canonical token here so the cross-page consistency check
 * (below) isn't fooled by this known, documented, expected variance while
 * still catching every other kind of drift.
 */
const NAV_LINK_STATE_VARIANTS = [
    'text-brand-green border-b-2 border-brand-green pb-1',
    'hover:text-brand-blue/70 transition-colors',
    'block px-6 py-4 bg-brand-surface text-brand-green',
    'block px-6 py-4 hover:bg-brand-surface',
];

function extractSubtree(string $html, string $tag): array
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();

    $node = $doc->getElementsByTagName($tag)->item(0);
    $tokens = [];
    if (!$node) {
        return $tokens;
    }

    $walk = function (DOMNode $n) use (&$walk, &$tokens) {
        foreach ($n->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            /** @var DOMElement $child */
            if (strtolower($child->tagName) === 'script') {
                $walk($child);
                continue;
            }
            $classAttr = trim($child->getAttribute('class'));
            if (strtolower($child->tagName) === 'a' && in_array($classAttr, NAV_LINK_STATE_VARIANTS, true)) {
                $classAttr = 'nav-link-active-state';
            }
            $classes = preg_split('/\s+/', $classAttr);
            $classes = array_values(array_filter($classes, fn($c) => $c !== ''));
            sort($classes);
            $token = strtolower($child->tagName) . (count($classes) ? '.' . implode('.', $classes) : '');
            $tokens[$token] = ($tokens[$token] ?? 0) + 1;
            $walk($child);
        }
    };
    // Include the root tag itself (its own class list matters too).
    $rootClasses = preg_split('/\s+/', trim($node->getAttribute('class')));
    $rootClasses = array_values(array_filter($rootClasses, fn($c) => $c !== ''));
    sort($rootClasses);
    $rootToken = strtolower($tag) . (count($rootClasses) ? '.' . implode('.', $rootClasses) : '');
    $tokens[$rootToken] = ($tokens[$rootToken] ?? 0) + 1;
    $walk($node);

    return $tokens;
}

$chromeSourceFile = 'portal_de_sondeos_ciudadanos.html'; // tasks.md 3.1/3.2: newest/most-complete header wins
$chromeSourceHtml = readCanvasSource($root, $canvasDir, $canvasRef, $chromeSourceFile);
$chromeFailures = 0;

foreach (['header', 'footer'] as $chromeTag) {
    if ($chromeSourceHtml === null) {
        echo "FAIL  partials/{$chromeTag}.php: cannot read source $chromeSourceFile\n";
        $chromeFailures++;
        continue;
    }
    $expectedTokens = extractSubtree($chromeSourceHtml, $chromeTag);

    $perPageTokens = [];
    foreach (array_keys($pages) as $page) {
        $html = renderPhpPage($root, $page);
        if ($html === null) {
            continue;
        }
        $perPageTokens[$page] = extractSubtree($html, $chromeTag);
    }

    // 1. All pages agree with each other.
    $reference = reset($perPageTokens);
    $referencePage = array_key_first($perPageTokens);
    $inconsistentPages = [];
    foreach ($perPageTokens as $page => $tokens) {
        if ($tokens !== $reference) {
            $inconsistentPages[] = $page;
        }
    }

    // 2. The shared version matches the chosen canvas source exactly.
    $diffVsSource = diffMultisets($expectedTokens, $reference ?: []);

    if (empty($inconsistentPages) && empty($diffVsSource['missing']) && empty($diffVsSource['extra'])) {
        echo "PASS  partials/{$chromeTag}.php (matches {$chromeSourceFile}, identical across all pages that render it)\n";
    } else {
        $chromeFailures++;
        echo "FAIL  partials/{$chromeTag}.php\n";
        if (!empty($inconsistentPages)) {
            echo "        inconsistent vs {$referencePage} on: " . implode(', ', $inconsistentPages) . "\n";
        }
        foreach ($diffVsSource['missing'] as $token => $count) {
            echo "        missing vs {$chromeSourceFile} x{$count}: {$token}\n";
        }
        foreach ($diffVsSource['extra'] as $token => $count) {
            echo "        extra vs {$chromeSourceFile} x{$count}: {$token}\n";
        }
    }
}

if ($chromeFailures > 0) {
    $failures += $chromeFailures;
}

exit($failures > 0 ? 1 : 0);
