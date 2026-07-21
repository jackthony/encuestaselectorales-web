const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const ROOT = path.join(__dirname, '..');
const SOURCE_DIR = path.join(ROOT, 'data', 'raw', 'jne');
const PARTIDOS_PATH = path.join(ROOT, 'data', 'partido.json');
const PARTIDOS_LIVE_PATH = path.join(ROOT, 'data', 'partido-live.json');
const CANDIDATOS_LIVE_PATH = path.join(ROOT, 'data', 'candidato-live.json');

const PARTY_NAME_OVERRIDES = {
  'accion popular': 'Acción Popular',
  'ahora nacion an': 'Ahora Nación - AN',
  'avanza pais partido de integracion social': 'Avanza País - Partido de Integración Social',
  'batalla peru': 'Batalla Perú',
  'coalicion transformadora tierra verde': 'Coalición Transformadora Tierra Verde',
  'frente popular agricola fia del peru': 'Frente Popular Agrícola FIA del Perú',
  'fuerza ciudadana': 'Fuerza Ciudadana',
  'fuerza popular': 'Fuerza Popular',
  'juntos por el peru': 'Juntos por el Perú',
  'partido civico obras': 'Partido Cívico Obras',
  'partido democrata verde': 'Partido Demócrata Verde',
  'partido democratico somos peru': 'Partido Democrático Somos Perú',
  'partido frente de la esperanza 2021': 'Partido Frente de la Esperanza 2021',
  'partido morado': 'Partido Morado',
  'partido patriotico del peru': 'Partido Patriótico del Perú',
  'partido politico integridad democratica': 'Partido Político Integridad Democrática',
  'partido politico nacional peru libre': 'Partido Político Nacional Perú Libre',
  'partido politico pueblo consciente': 'Partido Político Pueblo Consciente',
  'partido popular cristiano ppc': 'Partido Popular Cristiano - PPC',
  'podemos peru': 'Podemos Perú',
  'primero la gente comunidad ecologia libertad y progreso': 'Primero la Gente - Comunidad, Ecología, Libertad y Progreso',
  'renovacion popular peru': 'Renovación Popular Perú',
  'vision peru': 'Visión Perú',
};

const PARTY_SIGLAS_OVERRIDES = {
  'accion popular': 'AP',
  'ahora nacion an': 'AN',
  'avanza pais partido de integracion social': 'AVP',
  'batalla peru': 'BP',
  'coalicion transformadora tierra verde': 'CTTV',
  'frente popular agricola fia del peru': 'FREPAP',
  'fuerza ciudadana': 'FC',
  'fuerza popular': 'FP',
  'juntos por el peru': 'JPP',
  'partido civico obras': 'PCO',
  'partido democrata verde': 'PDV',
  'partido democratico somos peru': 'SP',
  'partido frente de la esperanza 2021': 'FEP',
  'partido morado': 'PM',
  'partido patriotico del peru': 'PPP',
  'partido politico integridad democratica': 'IPD',
  'partido politico nacional peru libre': 'PPL',
  'partido politico pueblo consciente': 'PPCN',
  'partido popular cristiano ppc': 'PPC',
  'podemos peru': 'PP',
  'primero la gente comunidad ecologia libertad y progreso': 'PLG',
  'renovacion popular peru': 'RPP',
  'vision peru': 'VP',
};

const PARTY_COLOR_OVERRIDES = {
  'accion popular': '#6EC6E8',
  'avanza pais partido de integracion social': '#F58220',
  'fuerza popular': '#FF6B00',
  'partido democratico somos peru': '#009444',
  'partido popular cristiano ppc': '#1E88E5',
  'podemos peru': '#00A99D',
  'partido morado': '#6A1B9A',
  'frente popular agricola fia del peru': '#7CB342',
  'partido frente de la esperanza 2021': '#14B8A6',
  'partido civico obras': '#0F766E',
  'partido patriotico del peru': '#7C3AED',
  'renovacion popular peru': '#B22222',
};

const PARTY_CATALOG = [
  ...readJsonArray(PARTIDOS_PATH),
  ...readJsonArray(PARTIDOS_LIVE_PATH),
];

const PARTY_BY_NAME = new Map();
const PARTY_BY_SIGLAS = new Map();
for (const party of PARTY_CATALOG) {
  PARTY_BY_NAME.set(normalizeKey(party.nombre), party);
  PARTY_BY_SIGLAS.set(normalizeKey(party.siglas), party);
}

function readJsonArray(filePath) {
  if (!fs.existsSync(filePath)) {
    return [];
  }
  const raw = fs.readFileSync(filePath, 'utf8');
  const decoded = JSON.parse(raw);
  return Array.isArray(decoded) ? decoded : [];
}

function normalizeKey(value) {
  return String(value ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, ' ')
    .trim();
}

function slugify(value) {
  return normalizeKey(value).replace(/\s+/g, '-');
}

function parseCsv(text) {
  const source = text.replace(/^\uFEFF/, '');
  const rows = [];
  let row = [];
  let cell = '';
  let inQuotes = false;

  for (let i = 0; i < source.length; i++) {
    const ch = source[i];
    const next = source[i + 1];

    if (inQuotes) {
      if (ch === '"' && next === '"') {
        cell += '"';
        i += 1;
        continue;
      }
      if (ch === '"') {
        inQuotes = false;
        continue;
      }
      cell += ch;
      continue;
    }

    if (ch === '"') {
      inQuotes = true;
      continue;
    }

    if (ch === ',') {
      row.push(cell);
      cell = '';
      continue;
    }

    if (ch === '\r') {
      continue;
    }

    if (ch === '\n') {
      row.push(cell);
      rows.push(row);
      row = [];
      cell = '';
      continue;
    }

    cell += ch;
  }

  if (cell !== '' || row.length > 0) {
    row.push(cell);
    rows.push(row);
  }

  return rows.filter((values) => values.some((value) => String(value).trim() !== ''));
}

function titleCaseSpanish(value) {
  const words = String(value ?? '')
    .trim()
    .split(/\s+/)
    .filter(Boolean);

  const smallWords = new Set(['de', 'del', 'la', 'las', 'los', 'y', 'e', 'o', 'u']);
  return words
    .map((word, index) => {
      if (/^-/.test(word) || /-$/.test(word)) {
        return word;
      }
      const lower = word.toLowerCase();
      if (index > 0 && index < words.length - 1 && smallWords.has(lower)) {
        return lower;
      }
      return lower.charAt(0).toUpperCase() + lower.slice(1);
    })
    .join(' ');
}

function canonicalPartyName(rawName) {
  const key = normalizeKey(rawName);
  return PARTY_NAME_OVERRIDES[key] || titleCaseSpanish(rawName);
}

function canonicalSiglas(rawName) {
  const key = normalizeKey(rawName);
  if (PARTY_SIGLAS_OVERRIDES[key]) {
    return PARTY_SIGLAS_OVERRIDES[key];
  }
  const words = key.split(' ').filter(Boolean);
  return words
    .filter((word) => !['de', 'del', 'la', 'las', 'los', 'y', 'e', 'o', 'u'].includes(word))
    .map((word) => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 6);
}

function canonicalColor(rawName) {
  const key = normalizeKey(rawName);
  if (PARTY_COLOR_OVERRIDES[key]) {
    return PARTY_COLOR_OVERRIDES[key];
  }

  const party = PARTY_BY_NAME.get(key) || PARTY_BY_SIGLAS.get(key);
  if (party && typeof party.color === 'string' && /^#[0-9a-fA-F]{6}$/.test(party.color)) {
    return party.color;
  }

  return hashToColor(key);
}

function hashToColor(key) {
  const digest = crypto.createHash('sha1').update(key).digest('hex');
  const hue = parseInt(digest.slice(0, 2), 16) % 360;
  const sat = 62;
  const light = 47;
  return hslToHex(hue, sat / 100, light / 100);
}

function hslToHex(h, s, l) {
  const c = (1 - Math.abs(2 * l - 1)) * s;
  const hp = h / 60;
  const x = c * (1 - Math.abs((hp % 2) - 1));
  let r = 0;
  let g = 0;
  let b = 0;

  if (hp >= 0 && hp < 1) {
    r = c;
    g = x;
  } else if (hp < 2) {
    r = x;
    g = c;
  } else if (hp < 3) {
    g = c;
    b = x;
  } else if (hp < 4) {
    g = x;
    b = c;
  } else if (hp < 5) {
    r = x;
    b = c;
  } else {
    r = c;
    b = x;
  }

  const m = l - c / 2;
  const toHex = (n) => {
    const v = Math.round((n + m) * 255);
    return v.toString(16).padStart(2, '0');
  };

  return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toUpperCase();
}

function stableInt(namespace, key, usedIds) {
  let salt = 0;
  while (salt < 1000) {
    const digest = crypto.createHash('sha1').update(`${namespace}|${key}|${salt}`).digest('hex');
    const candidate = 200000000 + (parseInt(digest.slice(0, 8), 16) % 700000000);
    if (!usedIds.has(candidate)) {
      usedIds.add(candidate);
      return candidate;
    }
    salt += 1;
  }
  throw new Error(`Unable to allocate stable id for ${namespace}:${key}`);
}

function resolveDistrictId(record, sourceFile) {
  const nivel = normalizeKey(record['Nivel de Eleccion']);
  const provincia = normalizeKey(record.Provincia);
  const distrito = normalizeKey(record.Distrito);
  const fileKey = normalizeKey(path.basename(sourceFile, path.extname(sourceFile)));

  if (distrito && distrito !== 'sin distrito') {
    return slugify(record.Distrito);
  }

  if (nivel.includes('regional')) {
    if (fileKey.includes('callao') || provincia === 'callao') {
      return 'callao';
    }
    if (fileKey.includes('lima') || provincia === 'lima') {
      return 'lima-cercado';
    }
  }

  if (nivel.includes('provincial')) {
    if (provincia === 'lima') {
      return 'lima-cercado';
    }
    if (provincia && provincia !== 'sin provincia') {
      return slugify(record.Provincia);
    }
  }

  if (provincia && provincia !== 'sin provincia') {
    return slugify(record.Provincia);
  }

  return slugify(fileKey);
}

function resolveCargo(rawCargo) {
  const key = normalizeKey(rawCargo);
  const overrides = {
    'alcalde distrital': 'alcalde_distrital',
    'alcalde provincial': 'alcalde_provincial',
    'gobernador regional': 'gobernador_regional',
  };
  return overrides[key] || key.replace(/\s+/g, '_');
}

function loadSourceFiles() {
  if (!fs.existsSync(SOURCE_DIR)) {
    throw new Error(`Missing source directory: ${SOURCE_DIR}`);
  }
  return fs
    .readdirSync(SOURCE_DIR)
    .filter((file) => file.toLowerCase().endsWith('.csv'))
    .sort((a, b) => a.localeCompare(b, 'es'));
}

function buildJneLiveSeed() {
  const sourceFiles = loadSourceFiles();
  const parties = [];
  const partyByKey = new Map();
  const partyUsedIds = new Set();
  const partySeenOrder = [];
  const candidates = [];
  const candidateUsedIds = new Set();

  for (const file of sourceFiles) {
    const filePath = path.join(SOURCE_DIR, file);
    const csv = fs.readFileSync(filePath, 'utf8');
    const rows = parseCsv(csv);
    if (rows.length === 0) {
      continue;
    }

    const headers = rows.shift().map((h) => h.trim());
    for (const values of rows) {
      const record = {};
      headers.forEach((header, index) => {
        record[header] = (values[index] ?? '').trim();
      });

      const rawPartyName = record['Organizacion politica'];
      const partyKey = normalizeKey(rawPartyName);
      if (!partyByKey.has(partyKey)) {
        const canonicalName = canonicalPartyName(rawPartyName);
        const id = stableInt('party', partyKey, partyUsedIds);
        const partyRecord = {
          id,
          nombre: canonicalName,
          siglas: canonicalSiglas(rawPartyName),
          color: canonicalColor(rawPartyName),
          logo: null,
        };
        partyByKey.set(partyKey, partyRecord);
        partySeenOrder.push(partyRecord);
      }

      const partyRecord = partyByKey.get(partyKey);
      const districtId = resolveDistrictId(record, filePath);
      const cargo = resolveCargo(record.Cargo);
      const candidateKey = [
        normalizeKey(record.Candidato),
        districtId,
        cargo,
        partyKey,
        normalizeKey(file),
      ].join('|');
      const candidateRecord = {
        id: stableInt('candidate', candidateKey, candidateUsedIds),
        nombre: titleCaseSpanish(record.Candidato),
        partidoId: partyRecord.id,
        cargo,
        distritoId: districtId,
        foto: null,
        numero: null,
        activo: true,
      };
      candidates.push(candidateRecord);
    }
  }

  return {
    sourceFiles,
    partidos: partySeenOrder.sort((a, b) => a.nombre.localeCompare(b.nombre, 'es')),
    candidatos: candidates,
  };
}

function writeJson(filePath, value) {
  const json = JSON.stringify(value, null, 2) + '\n';
  fs.writeFileSync(filePath, json, 'utf8');
}

if (require.main === module) {
  const seed = buildJneLiveSeed();
  writeJson(PARTIDOS_LIVE_PATH, seed.partidos);
  writeJson(CANDIDATOS_LIVE_PATH, seed.candidatos);
  process.stdout.write(
    `OK: normalized ${seed.candidatos.length} candidates and ${seed.partidos.length} live parties from ${seed.sourceFiles.length} CSV file(s)\n`
  );
}

module.exports = {
  buildJneLiveSeed,
  canonicalColor,
  canonicalPartyName,
  canonicalSiglas,
  parseCsv,
  resolveCargo,
  resolveDistrictId,
  titleCaseSpanish,
};
