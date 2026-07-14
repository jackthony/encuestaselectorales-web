const fs = require('fs');
const path = require('path');

const DATA_PATH = path.join(__dirname, '..', 'data', 'encuesta.json');
const DISTRITO_PATH = path.join(__dirname, '..', 'data', 'distrito.json');
const ENCUESTADORA_PATH = path.join(__dirname, '..', 'data', 'encuestadora.json');
const REQUIRED_FIELDS = [
  'id', 'cargo', 'ambito', 'fechaInicio', 'fechaFin',
  'tamanoMuestra', 'margenError', 'nivelConfianza', 'modalidad',
  'metodologia', 'encuestadoraId',
];

function fail(message) {
  console.error(`FAIL: ${message}`);
  process.exitCode = 1;
}

function loadIds(filePath) {
  const records = JSON.parse(fs.readFileSync(filePath, 'utf8'));
  return new Set(records.map((r) => r.id));
}

function main() {
  const encuestas = JSON.parse(fs.readFileSync(DATA_PATH, 'utf8'));

  if (!Array.isArray(encuestas)) {
    fail('data/encuesta.json must be a JSON array');
    return;
  }

  const distritoIds = loadIds(DISTRITO_PATH);
  const encuestadoraIds = loadIds(ENCUESTADORA_PATH);
  const seenIds = new Set();

  encuestas.forEach((record, index) => {
    for (const field of REQUIRED_FIELDS) {
      if (record[field] === undefined || record[field] === null || record[field] === '') {
        fail(`record at index ${index} (id: ${record.id || 'unknown'}) missing required field "${field}"`);
      }
    }

    if (typeof record.id === 'string') {
      if (seenIds.has(record.id)) {
        fail(`duplicate encuesta id "${record.id}"`);
      }
      seenIds.add(record.id);
    }

    if (record.distritoId !== null && record.distritoId !== undefined && !distritoIds.has(record.distritoId)) {
      fail(`record "${record.id}" has unresolvable distritoId "${record.distritoId}"`);
    }

    if (typeof record.encuestadoraId === 'string' && !encuestadoraIds.has(record.encuestadoraId)) {
      fail(`record "${record.id}" has unresolvable encuestadoraId "${record.encuestadoraId}"`);
    }

    if (typeof record.fechaInicio === 'string' && typeof record.fechaFin === 'string') {
      if (new Date(record.fechaInicio) > new Date(record.fechaFin)) {
        fail(`record "${record.id}" has fechaInicio after fechaFin`);
      }
    }
  });

  if (process.exitCode !== 1) {
    console.log(`OK: ${encuestas.length} valid encuesta records`);
  }
}

main();
