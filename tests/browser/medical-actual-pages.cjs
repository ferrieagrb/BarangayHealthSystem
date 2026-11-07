// Real Blade pages rendered by AppearancePreferencesTest with synthetic records.
// Set BHEAMS_APPEARANCE_FIXTURES and PLAYWRIGHT_MODULE before running.
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { spawn } = require('node:child_process');
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright-core');
const fixtures = process.env.BHEAMS_APPEARANCE_FIXTURES;
if (!fixtures) throw Error('Generate actual Blade fixtures first.');
const root = path.resolve(__dirname, '../..');
let base;
const errors = [];
const server = http.createServer(async (request, response) => {
    const url = new URL(request.url, base);
    if (url.pathname === '/medical-terminology/search') {
        const chunks = [];
        for await (const chunk of request) chunks.push(chunk);
        const php = spawn(process.env.PHP_BINARY || 'php', [path.join(__dirname, 'medical-live-bridge.php')], { cwd: root });
        let output = '', diagnostic = '';
        php.stdout.on('data', data => output += data);
        php.stderr.on('data', data => diagnostic += data);
        php.on('close', code => {
            response.setHeader('Content-Type', 'application/json');
            if (code) { errors.push(diagnostic); response.statusCode = 500; }
            response.end(output || '{}');
        });
        php.stdin.end(Buffer.concat(chunks));
        return;
    }
    const file = url.pathname.endsWith('.html')
        ? path.join(fixtures, path.basename(url.pathname))
        : path.join(root, 'public', url.pathname);
    if (!fs.existsSync(file) || !fs.statSync(file).isFile()) { response.statusCode = 404; response.end(); return; }
    const html = file.endsWith('.html');
    response.setHeader('Content-Type', html ? 'text/html' : file.endsWith('.js') ? 'text/javascript' : file.endsWith('.css') ? 'text/css' : 'application/octet-stream');
    response.end(html ? fs.readFileSync(file, 'utf8').replaceAll('http://localhost', base) : fs.readFileSync(file));
});

(async () => {
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    base = `http://127.0.0.1:${server.address().port}`;
    const browser = await chromium.launch({ executablePath: process.env.BHEAMS_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: true });
    try {
        for (const width of [390, 1440]) {
            for (const file of ['bhw-citizendetails.html', 'bhw-citizen_show.html']) {
                const page = await browser.newPage({ viewport: { width, height: 1000 } });
                const pageErrors = [];
                page.on('pageerror', error => pageErrors.push(error.stack));
                await page.route('**/*', route => route.request().url().startsWith(base) ? route.continue() : route.abort());
                await page.goto(`${base}/${file}`);
                await page.locator('#editBtn').click();
                const medicine = page.locator('#maintenance_medicine_name');
                await medicine.fill('Amoxcillin');
                const medicineResults = medicine.locator('..').locator('[role=option]');
                await medicineResults.first().waitFor();
                assert.equal(await medicineResults.first().getAttribute('data-bheams-action'), null, 'Suggestion rows keep their own dropdown styling');
                assert.match(await medicineResults.first().innerText(), /Amoxicillin/i);
                assert.match(await medicineResults.first().innerText(), /WHO|DOH/);
                await medicine.press('ArrowDown'); await medicine.press('Enter');
                assert.equal((await medicine.inputValue()).toLowerCase(), 'amoxicillin');
                await medicine.fill('flui');
                await medicineResults.first().waitFor();
                assert.match(await medicineResults.first().innerText(), /Fluimucil/);
                assert.match(await medicineResults.first().innerText(), /Generic: Acetylcysteine/);
                await medicineResults.first().click();
                assert.equal(await medicine.inputValue(), 'Fluimucil');
                await medicine.fill('fluimucil');
                await medicineResults.first().waitFor();
                assert.doesNotMatch(await medicineResults.first().innerText(), /Did you mean/);
                assert.doesNotMatch(await medicine.locator('..').innerText(), /spelling could not be verified/);
                await medicine.press('ArrowDown'); await medicine.press('Enter');
                await medicine.fill('Flumucil');
                await medicineResults.first().waitFor();
                assert.match(await medicineResults.first().innerText(), /Did you mean: Fluimucil/);
                await medicineResults.first().click();
                assert.equal(await medicine.inputValue(), 'Fluimucil');
                const diagnosis = page.locator('#diagnosisInput');
                await diagnosis.fill('Hypertenion');
                const conditions = diagnosis.locator('..').locator('[role=option]');
                await conditions.first().waitFor({ timeout: 35000 });
                assert.match(await conditions.first().innerText(), /hypertension/i);
                await diagnosis.fill('Iga Vascullitis');
                await conditions.first().waitFor({ timeout: 60000 });
                assert.match(await conditions.first().innerText(), /Did you mean: IgA Vasculitis/i);
                assert.match(await conditions.first().innerText(), /MeSH D011695/);
                if (process.env.BHEAMS_MEDICAL_SCREENSHOTS) {
                    fs.mkdirSync(process.env.BHEAMS_MEDICAL_SCREENSHOTS, { recursive: true });
                    await diagnosis.scrollIntoViewIfNeeded();
                    await page.screenshot({ path: path.join(process.env.BHEAMS_MEDICAL_SCREENSHOTS, `iga-${width}-${file}.png`) });
                }
                await diagnosis.press('ArrowDown'); await diagnosis.press('Enter');
                assert.equal(await diagnosis.inputValue(), 'IgA Vasculitis');
                assert.deepEqual(pageErrors, [], `${file}: page JavaScript must be valid`);
                if (process.env.BHEAMS_MEDICAL_SCREENSHOTS) {
                    fs.mkdirSync(process.env.BHEAMS_MEDICAL_SCREENSHOTS, { recursive: true });
                    await medicine.focus();
                    await medicineResults.first().waitFor();
                    await medicine.scrollIntoViewIfNeeded();
                    await page.screenshot({ path: path.join(process.env.BHEAMS_MEDICAL_SCREENSHOTS, `${width}-${file}.png`) });
                }
                console.log(`${file} ${width}px: maintenance and diagnosis suggestions passed using real PHP service.`);
                await page.close();
            }
        }
        assert.deepEqual(errors, []);
    } finally {
        await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
})().catch(error => { console.error(error); server.close(); process.exitCode = 1; });
