const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const DISTRITO_PATH = path.join(ROOT, 'data', 'distrito.json');
// index.html, metodologia.html and quienes-somos.html were refactored to
// PHP (BL-10) and dropped the standalone `<ul id="distritos-nav">` block
// this check looks for — their district picker moved out of the shared
// header partial entirely (verified: no `distritos-nav` id anywhere under
// index.php/partials/*.php), so checking them here no longer applies.
// Whether/how the new PHP pages should have an equivalent nav-completeness
// check is unresolved — flagged in BL-10b's deploy-readiness report as a
// follow-up, not decided here. This script still owns the 3 legacy pages
// below, which keep the original inline dropdown and are live in
// production today.
const PAGE_FILES = [
  'politica-editorial.html',
  'politica-privacidad.html',
  'fuentes-correcciones.html',
];

const NAV_BLOCK_PATTERN = /<ul[^>]*\bid="distritos-nav"[^>]*>([\s\S]*?)<\/ul>/;
// BL-10b (tasks.md 2.1) repointed these pages' district links at the PHP
// route (distrito.php?id=) since distrito.html no longer exists in prod.
const LINK_PATTERN = /distrito\.php\?id=([a-z0-9-]+)/g;

function fail(message) {
  console.error(`FAIL: ${message}`);
  process.exitCode = 1;
}

function main() {
  const distritos = JSON.parse(fs.readFileSync(DISTRITO_PATH, 'utf8'));
  const validIds = new Set(distritos.map((d) => d.id));

  for (const file of PAGE_FILES) {
    const filePath = path.join(ROOT, file);
    if (!fs.existsSync(filePath)) {
      fail(`${file} does not exist`);
      continue;
    }

    const html = fs.readFileSync(filePath, 'utf8');
    const navMatch = html.match(NAV_BLOCK_PATTERN);

    if (!navMatch) {
      fail(`${file} is missing the nav marker (<ul id="distritos-nav">)`);
      continue;
    }

    const navBlock = navMatch[1];
    const foundIds = [];
    let m;
    while ((m = LINK_PATTERN.exec(navBlock)) !== null) {
      foundIds.push(m[1]);
    }

    const seen = new Set();
    for (const id of foundIds) {
      if (!validIds.has(id)) {
        fail(`${file} nav links to unknown district id "${id}"`);
      }
      if (seen.has(id)) {
        fail(`${file} nav has duplicate district link "${id}"`);
      }
      seen.add(id);
    }

    for (const id of validIds) {
      if (!seen.has(id)) {
        fail(`${file} nav is missing district link "${id}"`);
      }
    }
  }

  if (process.exitCode !== 1) {
    console.log(`OK: nav validated on ${PAGE_FILES.length} pages, ${validIds.size} districts each`);
  }
}

main();
