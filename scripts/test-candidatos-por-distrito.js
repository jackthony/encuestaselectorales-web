const path = require('path');
const candidatosPorDistrito = require('./candidatos-por-distrito');
const candidatos = require(path.join(__dirname, '..', 'data', 'candidato.json'));
const partidos = require(path.join(__dirname, '..', 'data', 'partido.json'));

let failures = 0;

function assert(condition, message) {
  if (!condition) {
    console.error(`FAIL: ${message}`);
    failures += 1;
  }
}

function run() {
  // Scenario: district with candidates returns enriched list
  const miraflores = candidatosPorDistrito('miraflores', candidatos, partidos);
  assert(miraflores.length === 8, `expected 8 Miraflores candidates, got ${miraflores.length}`);
  assert(
    miraflores.every((c) => c.partido && typeof c.partido.nombre === 'string'),
    'every Miraflores candidate should have a resolved partido with a nombre'
  );

  // Scenario: district with no candidates returns an empty list
  const barranco = candidatosPorDistrito('barranco', candidatos, partidos);
  assert(Array.isArray(barranco) && barranco.length === 0, `expected empty array for barranco, got ${JSON.stringify(barranco)}`);

  // Scenario: unresolvable partidoId does not throw
  const fakeCandidatos = [{ id: 999, nombre: 'Test', distritoId: 'miraflores', partidoId: 9999, numero: null, foto: null }];
  let unresolved;
  try {
    unresolved = candidatosPorDistrito('miraflores', fakeCandidatos, partidos);
  } catch (e) {
    assert(false, `unresolvable partidoId should not throw, but threw: ${e.message}`);
    unresolved = [];
  }
  assert(unresolved.length === 1 && unresolved[0].partido === null, 'unresolvable partidoId should yield partido: null, not throw');

  if (failures > 0) {
    console.error(`FAIL: ${failures} assertion(s) failed`);
    process.exitCode = 1;
  } else {
    console.log('OK: candidatosPorDistrito passes all scenarios');
  }
}

run();
