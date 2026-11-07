// Offline UI checks: real shared JS/CSS, mocked terminology responses, no patient data.
// PLAYWRIGHT_MODULE may point to a temporary playwright-core installation.
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright-core');
const root = path.resolve(__dirname, '../..');
const markup = `<!doctype html><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="medical-terminology-search-url" content="/search"><meta name="csrf-token" content="fixture">
<link rel="stylesheet" href="/public/css/bhw/medical-autocomplete.css">
<style>body{margin:16px;font-family:Arial}form{max-width:420px}input,select,textarea{box-sizing:border-box;width:100%}</style>
<form><label>Medicine<input id="medicine" data-medical-autocomplete="medicine"></label>
<input id="dose" value="User dosage"><textarea id="multiple" data-medical-autocomplete="medicine" data-medical-multiple></textarea>
<textarea id="diagnosis" data-medical-autocomplete="diagnosis"></textarea>
<select name="category"><option value="">Select category</option><option>Medicine</option><option>Vaccine</option><option>Dressings</option></select>
<input id="supply" data-medical-autocomplete="supply">
<select id="vaccine" data-medical-autocomplete="vaccine"><option value="">Select vaccine</option>
<option value="BCG" data-code="BCG" data-eligibility-status="unavailable" disabled>BCG</option></select>
<button type="reset">Reset</button></form><button id="outside">Outside</button>
<script src="/public/js/medical-autocomplete.js"></script>`;

(async () => {
    const server = http.createServer((request, response) => {
        if (request.url === '/') { response.setHeader('Content-Type', 'text/html'); response.end(markup); return; }
        const file = path.join(root, request.url);
        response.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : 'text/css');
        response.end(fs.readFileSync(file));
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const base = `http://127.0.0.1:${server.address().port}`;
    let browser;
    let checks = 0;
    try {
        browser = await chromium.launch({
            executablePath: process.env.BHEAMS_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
            headless: true,
        });
        for (const width of [390, 1440]) {
            const page = await browser.newPage({ viewport: { width, height: 900 } });
            const errors = [];
            const requests = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.route('**/search', async route => {
                const body = route.request().postDataJSON(); requests.push(body);
                if (body.q === 'offline') return route.fulfill({ status: 503, body: '{}' });
                if (body.q === 'nomatch') return route.fulfill({ json: { suggestions: [], online_available: true } });
                if (body.q === 'old') await new Promise(resolve => setTimeout(resolve, 600));
                const item = body.type === 'diagnosis'
                    ? { name: 'Hypertension', classification_code: 'I10', code_system: 'ICD-10-CM', source: 'NLM Medical Conditions' }
                    : body.type === 'vaccine'
                        ? { name: 'BCG', code: 'BCG', source: 'CDC CVX', eligibility_status: 'age_based' }
                        : { name: body.q === 'old' ? 'Old response' : 'amoxicillin', source: 'NLM RxNorm', match_type: 'approximate' };
                return route.fulfill({ json: { suggestions: [item], online_available: true,
                    terminology_type: body.type === 'supply' ? (body.category === 'Medicine' ? 'medicine' : 'supply') : body.type } });
            });
            await page.goto(base);
            const field = page.locator('#medicine');
            const wrapper = field.locator('..');
            await field.fill('Amoxcillin');
            await wrapper.locator('[role=option]').waitFor();
            assert.equal(await field.inputValue(), 'Amoxcillin', 'Never autocorrect without selection'); checks++;
            assert.match(await wrapper.innerText(), /Did you mean: amoxicillin/); checks++;
            await field.press('ArrowDown'); await field.press('Enter');
            assert.equal(await field.inputValue(), 'amoxicillin'); checks++;
            assert.equal(await page.locator('#dose').inputValue(), 'User dosage'); checks++;

            const multi = page.locator('#multiple');
            await multi.fill('paracetamol, Amoxcillin');
            await multi.locator('..').locator('[role=option]').waitFor();
            await multi.press('ArrowDown'); await multi.press('Enter');
            assert.equal(await multi.inputValue(), 'paracetamol, amoxicillin'); checks++;

            // Editing an earlier medicine must preserve the entries after it.
            await multi.fill('Amoxcillin, paracetamol');
            await multi.evaluate(input => {
                input.setSelectionRange(4, 4); input.dispatchEvent(new Event('click', { bubbles: true }));
            });
            await multi.locator('..').locator('[role=option]').waitFor();
            assert.equal(requests.at(-1).q, 'Amoxcillin'); checks++;
            await multi.locator('..').locator('[role=option]').click();
            assert.equal(await multi.inputValue(), 'amoxicillin, paracetamol'); checks++;

            await multi.fill('paracetamol;\nAmoxcillin, aspirin');
            await multi.evaluate(input => {
                const caret = input.value.indexOf('Amoxcillin') + 3;
                input.setSelectionRange(caret, caret); input.dispatchEvent(new Event('click', { bubbles: true }));
            });
            await multi.locator('..').locator('[role=option]').waitFor();
            await multi.press('ArrowDown'); await multi.press('Enter');
            assert.equal(await multi.inputValue(), 'paracetamol;\namoxicillin, aspirin'); checks++;

            const diagnosis = page.locator('#diagnosis');
            await diagnosis.fill('Hypertenion');
            await diagnosis.locator('..').locator('[role=option]').waitFor();
            assert.match(await diagnosis.locator('..').innerText(), /ICD-10-CM I10/); checks++;
            assert.doesNotMatch(await diagnosis.locator('..').innerText(), /CVX I10/); checks++;
            await diagnosis.press('Escape');
            assert.equal(await diagnosis.locator('..').locator('[role=listbox]').isVisible(), false); checks++;

            const supply = page.locator('#supply');
            const count = requests.length;
            await supply.fill('amox'); await page.waitForTimeout(400);
            assert.equal(requests.length, count, 'Category must be selected before supply search'); checks++;
            await page.selectOption('[name=category]', 'Medicine');
            await supply.locator('..').locator('[role=option]').waitFor();
            assert.equal(requests.at(-1).category, 'Medicine'); checks++;
            await page.selectOption('[name=category]', 'Dressings');
            await page.waitForTimeout(400);
            assert.equal(requests.at(-1).category, 'Dressings'); checks++;

            const vaccine = page.locator('.medical-autocomplete-vaccine input');
            await vaccine.fill('bcg');
            await vaccine.locator('..').locator('[role=option]').waitFor();
            assert.equal(await vaccine.locator('..').locator('[role=option]').isDisabled(), true); checks++;
            await vaccine.press('ArrowDown'); await vaccine.press('Enter');
            assert.equal(await page.locator('#vaccine').inputValue(), '', 'Cannot bypass age restrictions'); checks++;
            assert.equal(await vaccine.getAttribute('aria-activedescendant'), null); checks++;

            // Navigation skips an age-ineligible option and can choose an eligible one.
            await page.locator('#vaccine').evaluate(select => {
                const option = new Option('BCG eligible fixture', 'BCG eligible fixture');
                option.dataset.code = 'ELIGIBLE'; option.dataset.eligibilityStatus = 'available';
                select.append(option);
            });
            await vaccine.fill('bcg');
            await page.waitForFunction(() => document.querySelector('.medical-autocomplete-vaccine [role=listbox]').children.length === 2);
            await vaccine.press('ArrowDown'); await vaccine.press('Enter');
            assert.equal(await page.locator('#vaccine').inputValue(), 'BCG eligible fixture'); checks++;

            await field.fill('old'); await page.waitForTimeout(350);
            await field.fill('new');
            await page.waitForTimeout(1000);
            assert.doesNotMatch(await wrapper.innerText(), /Old response/); checks++;
            await field.fill('offline');
            await wrapper.locator('[role=status]').filter({ hasText: /unavailable/ }).waitFor();
            assert.equal(await field.inputValue(), 'offline', 'Outage preserves manual entry'); checks++;

            await field.fill('nomatch');
            await wrapper.locator('[role=status]').filter({ hasText: /could not be verified/ }).waitFor();
            assert.equal(await wrapper.locator('[role=listbox]').isVisible(), false); checks++;
            assert.equal(await field.inputValue(), 'nomatch'); checks++;

            const beforeReload = await page.locator('.medical-autocomplete').count();
            await page.addScriptTag({ url: base + '/public/js/medical-autocomplete.js' });
            assert.equal(await page.locator('.medical-autocomplete').count(), beforeReload, 'Loading the component twice must not nest wrappers'); checks++;

            await page.evaluate(() => {
                const input = document.createElement('input'); input.id = 'dynamic';
                input.dataset.medicalAutocomplete = 'medicine'; document.querySelector('form').append(input);
            });
            await page.locator('#dynamic').fill('amox');
            await page.locator('#dynamic').locator('..').locator('[role=option]').waitFor();
            await page.locator('#outside').click();
            await page.waitForTimeout(150);
            assert.equal(await page.locator('#dynamic').locator('..').locator('[role=listbox]').isVisible(), false); checks++;
            const ids = await page.locator('[role=listbox]').evaluateAll(nodes => nodes.map(n => n.id));
            assert.equal(new Set(ids).size, ids.length); checks++;
            await page.locator('button[type=reset]').click();
            await page.waitForTimeout(20);
            assert.equal(await vaccine.inputValue(), ''); checks++;
            assert.equal(await field.inputValue(), ''); checks++;
            assert.equal(await page.locator('.medical-autocomplete-results:visible').count(), 0); checks++;

            // Dynamically loaded edit forms must work inside a scrolling modal.
            await page.evaluate(() => {
                const dialog = document.createElement('dialog'); dialog.id = 'editModal';
                dialog.style.cssText = 'box-sizing:border-box;width:min(420px,calc(100% - 32px));max-height:80vh;overflow:auto';
                dialog.innerHTML = '<form><label>Medicine<input id="editMedicine" data-medical-autocomplete="medicine" value="Old medicine"></label>'
                    + '<input id="editQuantity" value="4"><select name="category"><option selected>Medicine</option></select>'
                    + '<input id="editSupply" data-medical-autocomplete="supply" value="Old supply"></form>';
                document.body.append(dialog); dialog.showModal();
            });
            const editMedicine = page.locator('#editMedicine');
            await editMedicine.fill('Amoxcillin');
            const editOption = editMedicine.locator('..').locator('[role=option]');
            await editOption.waitFor();
            const modalBounds = await page.locator('#editModal').boundingBox();
            const optionBounds = await editOption.boundingBox();
            assert.ok(optionBounds.x >= modalBounds.x && optionBounds.x + optionBounds.width <= modalBounds.x + modalBounds.width); checks++;
            await editOption.click();
            assert.equal(await editMedicine.inputValue(), 'amoxicillin'); checks++;
            assert.equal(await page.locator('#editQuantity').inputValue(), '4'); checks++;
            const editSupply = page.locator('#editSupply');
            await editSupply.fill('amox');
            await editSupply.locator('..').locator('[role=option]').waitFor();
            assert.equal(requests.at(-1).type, 'supply'); checks++;
            assert.equal(requests.at(-1).category, 'Medicine'); checks++;
            await page.locator('#editModal').evaluate(dialog => dialog.close());
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true); checks++;
            assert.deepEqual(errors, []); checks++;
            await page.close();
        }
        console.log(`${checks} medical autocomplete browser checks passed at 390px and 1440px.`);
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
