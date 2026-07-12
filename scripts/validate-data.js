'use strict';

const fs = require('fs');
const path = require('path');

const SLUG_RE = /^[a-z0-9]+(-[a-z0-9]+)*$/;
const REQUIRED_FIELDS = ['id', 'nombre', 'provincia', 'region'];
const EXPECTED_COUNT = 43;

function fail(message) {
  console.error(`FAIL: ${message}`);
  process.exitCode = 1;
}

function loadDistricts(filePath) {
  if (!fs.existsSync(filePath)) {
    fail(`${filePath} does not exist`);
    return null;
  }
  let raw;
  try {
    raw = fs.readFileSync(filePath, 'utf8');
  } catch (err) {
    fail(`could not read ${filePath}: ${err.message}`);
    return null;
  }
  let data;
  try {
    data = JSON.parse(raw);
  } catch (err) {
    fail(`${filePath} is not valid JSON: ${err.message}`);
    return null;
  }
  if (!Array.isArray(data)) {
    fail(`${filePath} must be a JSON array`);
    return null;
  }
  return data;
}

function validateDistricts(districts) {
  if (districts.length !== EXPECTED_COUNT) {
    fail(`expected exactly ${EXPECTED_COUNT} districts, found ${districts.length}`);
  }

  const seenIds = new Set();
  districts.forEach((record, index) => {
    for (const field of REQUIRED_FIELDS) {
      if (typeof record[field] !== 'string' || record[field].trim() === '') {
        fail(`record at index ${index} is missing required field "${field}"`);
      }
    }

    if (typeof record.id === 'string') {
      if (seenIds.has(record.id)) {
        fail(`duplicate district id "${record.id}"`);
      }
      seenIds.add(record.id);

      if (!SLUG_RE.test(record.id)) {
        fail(`district id "${record.id}" is not a valid slug (expected ${SLUG_RE})`);
      }
    }

    if (record.provincia !== 'lima') {
      fail(`district "${record.id || index}" has provincia "${record.provincia}", expected "lima"`);
    }
    if (record.region !== 'lima') {
      fail(`district "${record.id || index}" has region "${record.region}", expected "lima"`);
    }
  });
}

function main() {
  const distritoPath = path.join(__dirname, '..', 'data', 'distrito.json');
  const districts = loadDistricts(distritoPath);
  if (districts !== null) {
    validateDistricts(districts);
  }

  if (process.exitCode) {
    console.error('validate-data: FAILED');
  } else {
    console.log('validate-data: OK');
  }
}

main();
