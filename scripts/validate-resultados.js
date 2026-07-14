const fs = require('fs');
const path = require('path');

const DATA_PATH = path.join(__dirname, '..', 'data', 'resultado.json');
const ENCUESTA_PATH = path.join(__dirname, '..', 'data', 'encuesta.json');
const CANDIDATO_PATH = path.join(__dirname, '..', 'data', 'candidato.json');
const SUM_TOLERANCE = 0.5;

function fail(message) {
  console.error(`FAIL: ${message}`);
  process.exitCode = 1;
}

function main() {
  const resultados = JSON.parse(fs.readFileSync(DATA_PATH, 'utf8'));
  const encuestas = JSON.parse(fs.readFileSync(ENCUESTA_PATH, 'utf8'));
  const candidatos = JSON.parse(fs.readFileSync(CANDIDATO_PATH, 'utf8'));

  if (!Array.isArray(resultados)) {
    fail('data/resultado.json must be a JSON array');
    return;
  }

  const encuestasPorId = {};
  encuestas.forEach((e) => {
    encuestasPorId[e.id] = e;
  });

  const candidatosPorId = {};
  candidatos.forEach((c) => {
    candidatosPorId[c.id] = c;
  });

  resultados.forEach((record) => {
    const encuesta = encuestasPorId[record.encuestaId];
    if (!encuesta) {
      fail(`resultado references unresolvable encuestaId "${record.encuestaId}"`);
      return;
    }

    if (!Array.isArray(record.resultados)) {
      fail(`resultado for "${record.encuestaId}" is missing a "resultados" array`);
      return;
    }

    let sum = 0;
    record.resultados.forEach((r) => {
      const candidato = candidatosPorId[r.candidatoId];
      if (!candidato) {
        fail(`resultado for "${record.encuestaId}" references unresolvable candidatoId ${r.candidatoId}`);
      } else if (candidato.distritoId !== encuesta.distritoId || candidato.cargo !== encuesta.cargo) {
        fail(`resultado for "${record.encuestaId}" references candidatoId ${r.candidatoId}, which belongs to a different distrito/cargo`);
      }

      if (typeof r.porcentaje !== 'number') {
        fail(`resultado for "${record.encuestaId}", candidatoId ${r.candidatoId} has non-numeric porcentaje`);
      } else {
        sum += r.porcentaje;
      }
    });

    if (typeof record.indecisos === 'number') {
      sum += record.indecisos;
    } else {
      fail(`resultado for "${record.encuestaId}" is missing numeric "indecisos"`);
    }

    if (typeof record.votoBlancoNulo === 'number') {
      sum += record.votoBlancoNulo;
    } else {
      fail(`resultado for "${record.encuestaId}" is missing numeric "votoBlancoNulo"`);
    }

    if (Math.abs(sum - 100) > SUM_TOLERANCE) {
      fail(`resultado for "${record.encuestaId}" sums to ${sum.toFixed(2)}, expected ~100 (±${SUM_TOLERANCE})`);
    }
  });

  if (process.exitCode !== 1) {
    console.log(`OK: ${resultados.length} valid resultado record(s)`);
  }
}

main();
