const fs = require('fs');
const path = require('path');

const DATA_PATH = path.join(__dirname, '..', 'data', 'encuestadora.json');
const EXPECTED_COUNT = 6;
const REQUIRED_FIELDS = ['id', 'nombre', 'tipo'];
const SLUG_PATTERN = /^[a-z0-9]+(-[a-z0-9]+)*$/;
const VALID_TIPOS = ['institucional', 'propia', 'ejemplo'];

function fail(message) {
  console.error(`FAIL: ${message}`);
  process.exitCode = 1;
}

function main() {
  const raw = fs.readFileSync(DATA_PATH, 'utf8');
  const encuestadoras = JSON.parse(raw);

  if (!Array.isArray(encuestadoras)) {
    fail('data/encuestadora.json must be a JSON array');
    return;
  }

  if (encuestadoras.length !== EXPECTED_COUNT) {
    fail(`expected ${EXPECTED_COUNT} pollsters, found ${encuestadoras.length}`);
  }

  const seenIds = new Set();

  encuestadoras.forEach((record, index) => {
    for (const field of REQUIRED_FIELDS) {
      if (typeof record[field] !== 'string' || record[field].trim() === '') {
        fail(`record at index ${index} (${JSON.stringify(record)}) missing required field "${field}"`);
      }
    }

    if (typeof record.id === 'string') {
      if (!SLUG_PATTERN.test(record.id)) {
        fail(`record at index ${index} has invalid id slug "${record.id}"`);
      }
      if (seenIds.has(record.id)) {
        fail(`duplicate id slug "${record.id}"`);
      }
      seenIds.add(record.id);
    }

    if (typeof record.tipo === 'string' && !VALID_TIPOS.includes(record.tipo)) {
      fail(`record at index ${index} has invalid tipo "${record.tipo}" (expected one of ${VALID_TIPOS.join(', ')})`);
    }

    if (record.tipo !== 'ejemplo') {
      if (typeof record.web !== 'string' || record.web.trim() === '') {
        fail(`record at index ${index} (${JSON.stringify(record)}) missing required field "web"`);
      }
    } else if (record.web !== null && (typeof record.web !== 'string' || record.web.trim() === '')) {
      fail(`record at index ${index} has invalid "web" (must be null or a non-empty string for tipo "ejemplo")`);
    }
  });

  if (process.exitCode !== 1) {
    console.log(`OK: ${encuestadoras.length} valid pollster records`);
  }
}

main();
