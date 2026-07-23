// Fase 1 — captura de desarrollo local. No usar en producción.
// Requiere: `php artisan serve` corriendo y Chrome instalado en el sistema.
const puppeteer = require('puppeteer-core');
const path = require('path');

const CHROME_PATH = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE_URL = process.env.OG_PREVIEW_BASE_URL || 'http://127.0.0.1:8123/__design/og-results-preview';

// node capture.js [fixture] [outputFileName]
const fixture = process.argv[2] || 'og-results-preview';
const outputName = process.argv[3] || 'og-results-preview-static-1200x630.png';
const URL = `${BASE_URL}?fixture=${encodeURIComponent(fixture)}`;
const OUTPUT = path.resolve(__dirname, '..', 'storage/app/testing', outputName);

(async () => {
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1200, height: 630, deviceScaleFactor: 1 });
    await page.goto(URL, { waitUntil: 'networkidle0' });

    const fontsReady = await page.evaluate(async () => {
        await document.fonts.ready;
        return document.fonts.check('700 20px Inter') && document.fonts.check('600 24px Inter');
    });
    if (!fontsReady) {
        console.error('FALLO: la fuente Inter no cargó antes de la captura.');
        await browser.close();
        process.exit(1);
    }

    const canvas = await page.$('#og-canvas');
    const box = await canvas.boundingBox();
    if (Math.round(box.width) !== 1200 || Math.round(box.height) !== 630) {
        console.error(`FALLO: canvas mide ${box.width}x${box.height}, se esperaba 1200x630.`);
        await browser.close();
        process.exit(1);
    }

    await canvas.screenshot({ path: OUTPUT });
    await browser.close();
    console.log('OK:', OUTPUT);
})();
