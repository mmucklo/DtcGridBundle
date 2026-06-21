/**
 * Takes screenshots of grid pages using Puppeteer, waiting for DataTables
 * to finish loading before capturing.
 *
 * Usage: node Tests/Screenshots/screenshot.mjs <base-url> <output-dir>
 * Example: node Tests/Screenshots/screenshot.mjs http://127.0.0.1:8199 Tests/Screenshots/output
 */

import puppeteer from 'puppeteer';
import { mkdirSync } from 'fs';

const baseUrl = process.argv[2] || 'http://127.0.0.1:8199';
const outputDir = process.argv[3] || 'Tests/Screenshots/output';
const entityClass = 'Dtc\\GridBundle\\Tests\\App\\Entity\\Product';
const encodedClass = encodeURIComponent(entityClass);

mkdirSync(outputDir, { recursive: true });

const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
});

const pages = [
    { name: 'table', type: 'table', waitFor: 'tbody tr' },
    { name: 'datatables', type: 'datatables', waitFor: 'tbody tr td' },
];

for (const { name, type, waitFor } of pages) {
    const url = `${baseUrl}/dtc_grid/grid?class=${encodedClass}&type=${type}`;
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });
    await page.goto(url, { waitUntil: 'networkidle0', timeout: 15000 });

    // Wait for data rows to appear
    try {
        await page.waitForSelector(waitFor, { timeout: 10000 });
        // Small extra delay for DataTables rendering
        await new Promise(r => setTimeout(r, 500));
    } catch {
        console.warn(`Warning: ${name} - timed out waiting for "${waitFor}"`);
    }

    // WebP keeps these flat grid captures ~5x smaller than lossless PNG
    // (~20KB vs ~130KB) while staying inline-renderable on GitHub.
    await page.screenshot({ path: `${outputDir}/${name}.webp`, type: 'webp', quality: 75, fullPage: false });
    console.log(`Screenshot: ${name}.webp`);
    await page.close();
}

await browser.close();
console.log('Done.');
