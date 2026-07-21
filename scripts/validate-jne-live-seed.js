const fs = require('fs');
const path = require('path');
const { buildJneLiveSeed } = require('./build-jne-live-seed');

const ROOT = path.join(__dirname, '..');
const PARTIDOS_LIVE_PATH = path.join(ROOT, 'data', 'partido-live.json');
const CANDIDATOS_LIVE_PATH = path.join(ROOT, 'data', 'candidato-live.json');

let failures = 0;

function fail(message) {
  console.error(`FAIL: ${message}`);
  failures += 1;
}

function loadJson(filePath) {
  if (!fs.existsSync(filePath)) {
    fail(`${path.basename(filePath)} does not exist`);
    return null;
  }
  try {
    return JSON.parse(fs.readFileSync(filePath, 'utf8'));
  } catch (error) {
    fail(`${path.basename(filePath)} is not valid JSON: ${error.message}`);
    return null;
  }
}

function main() {
  const generated = buildJneLiveSeed();
  const partidos = loadJson(PARTIDOS_LIVE_PATH);
  const candidatos = loadJson(CANDIDATOS_LIVE_PATH);

  if (partidos && JSON.stringify(partidos) !== JSON.stringify(generated.partidos)) {
    fail('data/partido-live.json does not match the generated live party seed');
  }

  if (candidatos && JSON.stringify(candidatos) !== JSON.stringify(generated.candidatos)) {
    fail('data/candidato-live.json does not match the generated live candidate seed');
  }

  if (candidatos) {
    const partidoIds = new Set((partidos || []).map((party) => party.id));
    candidatos.forEach((candidate, index) => {
      if (!partidoIds.has(candidate.partidoId)) {
        fail(`candidate at index ${index} has unresolved partidoId ${candidate.partidoId}`);
      }
      if (candidate.activo !== true) {
        fail(`candidate at index ${index} must be activo=true`);
      }
      if (candidate.numero !== null) {
        fail(`candidate at index ${index} must keep numero=null`);
      }
      if (candidate.foto !== null) {
        fail(`candidate at index ${index} must keep foto=null`);
      }
    });
  }

  if (generated.candidatos.length === 0 || generated.partidos.length === 0) {
    fail('generator returned empty live seed data');
  }

  if (failures > 0) {
    process.exitCode = 1;
  } else {
    console.log(
      `OK: ${generated.candidatos.length} live candidates and ${generated.partidos.length} live parties normalized from ${generated.sourceFiles.length} CSV file(s)`
    );
  }
}

main();
