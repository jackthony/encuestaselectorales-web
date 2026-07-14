const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const DISTRITO_PATH = path.join(ROOT, 'data', 'distrito.json');
const PAGE_FILES = [
  'index.html',
  'metodologia.html',
  'quienes-somos.html',
  'politica-editorial.html',
  'politica-privacidad.html',
  'fuentes-correcciones.html',
];

const NAV_BLOCK_PATTERN = /<ul[^>]*\bid="distritos-nav"[^>]*>([\s\S]*?)<\/ul>/;
const LINK_PATTERN = /distrito\.html\?id=([a-z0-9-]+)/g;

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
