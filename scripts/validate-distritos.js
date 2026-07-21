const fs = require('fs');
const path = require('path');

const DATA_PATH = path.join(__dirname, '..', 'data', 'distrito.json');
const EXPECTED_COUNT = 44;
const REQUIRED_FIELDS = ['id', 'nombre', 'provincia', 'region'];
const OPTIONAL_FIELDS = ['ubigeo'];
const SLUG_PATTERN = /^[a-z0-9]+(-[a-z0-9]+)*$/;

function fail(message) {
  console.error(`FAIL: ${message}`);
  process.exitCode = 1;
}

function main() {
  const raw = fs.readFileSync(DATA_PATH, 'utf8');
  const distritos = JSON.parse(raw);

  if (!Array.isArray(distritos)) {
    fail('data/distrito.json must be a JSON array');
    return;
  }

  if (distritos.length !== EXPECTED_COUNT) {
    fail(`expected ${EXPECTED_COUNT} districts, found ${distritos.length}`);
  }

  const seenIds = new Set();

  distritos.forEach((record, index) => {
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

    for (const field of OPTIONAL_FIELDS) {
      if (Object.prototype.hasOwnProperty.call(record, field) && record[field] !== null && typeof record[field] !== 'string') {
        fail(`record at index ${index} has invalid optional field "${field}"`);
      }
    }
  });

  if (process.exitCode !== 1) {
    console.log(`OK: ${distritos.length} valid district records`);
  }
}

main();
