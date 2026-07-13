const fs = require('fs');
const path = require('path');

const DATA_PATH = path.join(__dirname, '..', 'data', 'candidato.json');
const DISTRITO_PATH = path.join(__dirname, '..', 'data', 'distrito.json');
const PARTIDO_PATH = path.join(__dirname, '..', 'data', 'partido.json');
const EXPECTED_COUNT = 8;
const VALID_CARGOS = ['alcalde_distrital'];

function fail(message) {
  console.error(`FAIL: ${message}`);
  process.exitCode = 1;
}

function loadIds(filePath, label) {
  if (!fs.existsSync(filePath)) {
    fail(`${label} does not exist`);
    return new Set();
  }
  const raw = fs.readFileSync(filePath, 'utf8');
  const records = JSON.parse(raw);
  return new Set(records.map((r) => r.id));
}

function main() {
  const raw = fs.readFileSync(DATA_PATH, 'utf8');
  const candidatos = JSON.parse(raw);

  if (!Array.isArray(candidatos)) {
    fail('data/candidato.json must be a JSON array');
    return;
  }

  if (candidatos.length !== EXPECTED_COUNT) {
    fail(`expected ${EXPECTED_COUNT} candidates, found ${candidatos.length}`);
  }

  const distritoIds = loadIds(DISTRITO_PATH, 'data/distrito.json');
  const partidoIds = loadIds(PARTIDO_PATH, 'data/partido.json');
  const seenIds = new Set();

  candidatos.forEach((record, index) => {
    if (typeof record.id !== 'number') {
      fail(`record at index ${index} missing numeric "id"`);
    } else {
      if (seenIds.has(record.id)) {
        fail(`duplicate candidate id ${record.id}`);
      }
      seenIds.add(record.id);
    }

    if (typeof record.nombre !== 'string' || record.nombre.trim() === '') {
      fail(`record at index ${index} missing required field "nombre"`);
    }

    if (!VALID_CARGOS.includes(record.cargo)) {
      fail(`record at index ${index} has invalid cargo "${record.cargo}" (expected one of ${VALID_CARGOS.join(', ')})`);
    }

    if (typeof record.distritoId !== 'string' || !distritoIds.has(record.distritoId)) {
      fail(`record at index ${index} has unresolvable distritoId "${record.distritoId}"`);
    }

    if (typeof record.partidoId !== 'number' || !partidoIds.has(record.partidoId)) {
      fail(`record at index ${index} has unresolvable partidoId ${record.partidoId}`);
    }

    if (record.foto !== null && (typeof record.foto !== 'string' || record.foto.trim() === '')) {
      fail(`record at index ${index} has invalid "foto" (must be null or a non-empty string)`);
    }

    if (record.numero !== null && typeof record.numero !== 'number') {
      fail(`record at index ${index} has invalid "numero" (must be null or a number)`);
    }

    if (record.activo !== false) {
      fail(`record at index ${index} has activo=${record.activo} — this historical (2022) dataset must mark every candidate activo:false`);
    }
  });

  if (process.exitCode !== 1) {
    console.log(`OK: ${candidatos.length} valid candidate records`);
  }
}

main();
