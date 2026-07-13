const fs = require('fs');
const path = require('path');

const DATA_PATH = path.join(__dirname, '..', 'data', 'partido.json');
const EXPECTED_COUNT = 8;
const REQUIRED_STRING_FIELDS = ['nombre', 'siglas', 'color'];
const COLOR_PATTERN = /^#[0-9a-fA-F]{6}$/;

function fail(message) {
  console.error(`FAIL: ${message}`);
  process.exitCode = 1;
}

function main() {
  const raw = fs.readFileSync(DATA_PATH, 'utf8');
  const partidos = JSON.parse(raw);

  if (!Array.isArray(partidos)) {
    fail('data/partido.json must be a JSON array');
    return;
  }

  if (partidos.length !== EXPECTED_COUNT) {
    fail(`expected ${EXPECTED_COUNT} parties, found ${partidos.length}`);
  }

  const seenIds = new Set();
  const seenSiglas = new Set();

  partidos.forEach((record, index) => {
    if (typeof record.id !== 'number') {
      fail(`record at index ${index} missing numeric "id"`);
    } else {
      if (seenIds.has(record.id)) {
        fail(`duplicate party id ${record.id}`);
      }
      seenIds.add(record.id);
    }

    for (const field of REQUIRED_STRING_FIELDS) {
      if (typeof record[field] !== 'string' || record[field].trim() === '') {
        fail(`record at index ${index} (${JSON.stringify(record)}) missing required field "${field}"`);
      }
    }

    if (typeof record.siglas === 'string') {
      if (seenSiglas.has(record.siglas)) {
        fail(`duplicate siglas "${record.siglas}"`);
      }
      seenSiglas.add(record.siglas);
    }

    if (typeof record.color === 'string' && !COLOR_PATTERN.test(record.color)) {
      fail(`record at index ${index} has invalid color "${record.color}" (expected #RRGGBB)`);
    }

    if (!('logo' in record) || (record.logo !== null && (typeof record.logo !== 'string' || record.logo.trim() === ''))) {
      fail(`record at index ${index} has invalid "logo" (must be null or a non-empty string)`);
    }
  });

  if (process.exitCode !== 1) {
    console.log(`OK: ${partidos.length} valid party records`);
  }
}

main();
