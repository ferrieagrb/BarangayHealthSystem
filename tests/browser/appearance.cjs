/* Run after AppearancePreferencesTest with BHEAMS_APPEARANCE_FIXTURES set.
   Uses rendered test data only; never connects to the application's database.
   PLAYWRIGHT_MODULE can point to a temporary playwright-core installation. */
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright-core');
const fixtures = process.env.BHEAMS_APPEARANCE_FIXTURES;
if (!fixtures) throw Error('Set BHEAMS_APPEARANCE_FIXTURES to the generated fixture directory.');
const publicRoot = path.resolve(__dirname, '../../public');

function contrastAudit() {
    const rgb = value => (value.match(/[\d.]+/g) || []).map(Number);
    const lum = color => color.slice(0, 3).map(c => {
        c /= 255;
        return c <= .04045 ? c / 12.92 : ((c + .055) / 1.055) ** 2.4;
    }).reduce((sum, c, i) => sum + c * [.2126, .7152, .0722][i], 0);
    const issues = [];
    for (const element of document.body.querySelectorAll('*')) {
        if (element.closest('script, style, svg, canvas, .bx, .leaflet-container')) continue;
        if (element.matches('input[type="checkbox"], input[type="radio"], input[type="hidden"], input[type="file"]')) continue;
        if (![...element.childNodes].some(n => n.nodeType === 3 && n.textContent.trim()) && !element.matches('input, select, textarea')) continue;
        if (!element.getClientRects().length || !element.getBoundingClientRect().width) continue;
        const style = getComputedStyle(element);
        if (style.visibility === 'hidden' || +style.opacity < .9) continue;
        let bg;
        for (let parent = element; parent; parent = parent.parentElement) {
            const parentStyle = getComputedStyle(parent);
            if (parentStyle.backgroundImage !== 'none') { bg = null; break; }
            const color = rgb(parentStyle.backgroundColor);
            if (color.length === 3 || color[3] >= .98) { bg = color; break; }
        }
        if (!bg) continue;
        const fg = rgb(style.color);
        if (fg.length === 4 && fg[3] < .98) continue;
        const ratio = (Math.max(lum(fg), lum(bg)) + .05) / (Math.min(lum(fg), lum(bg)) + .05);
        const large = parseFloat(style.fontSize) >= 24 || (parseFloat(style.fontSize) >= 18.66 && +style.fontWeight >= 700);
        if (ratio < (large ? 3 : 4.5)) issues.push({
            tag: element.tagName, class: element.className,
            text: (element.value || element.textContent).trim().replace(/\s+/g, ' ').slice(0, 70),
            ratio: +ratio.toFixed(2), fg: style.color, bg: bg.slice(0, 3)
        });
    }
    return issues;
}

(async () => {
    let base;
    const server = http.createServer((request, response) => {
        const pathname = decodeURIComponent(new URL(request.url, base).pathname);
        const fixture = path.join(fixtures, path.basename(pathname));
        const asset = path.resolve(publicRoot, '.' + pathname);
        if (pathname.endsWith('.html') && fs.existsSync(fixture)) {
            response.setHeader('Content-Type', 'text/html');
            response.end(fs.readFileSync(fixture, 'utf8').replaceAll('http://localhost', base));
        } else if (asset.startsWith(publicRoot + path.sep) && fs.existsSync(asset) && fs.statSync(asset).isFile()) {
            response.setHeader('Content-Type', asset.endsWith('.css') ? 'text/css' : asset.endsWith('.js') ? 'text/javascript' : 'application/octet-stream');
            response.end(fs.readFileSync(asset));
        } else { response.statusCode = 404; response.end(); }
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    base = `http://127.0.0.1:${server.address().port}`;
    let browser;
    try {
        browser = await chromium.launch({
            executablePath: process.env.BHEAMS_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
            headless: true
        });
        const results = [];
        let checks = 0;
        for (const width of [1440, 768, 390]) {
            const context = await browser.newContext({ viewport: { width, height: 900 } });
            await context.route('**/*', route => {
                const url = route.request().url();
                const modules = process.env.PLAYWRIGHT_MODULE
                    ? path.dirname(process.env.PLAYWRIGHT_MODULE) : path.dirname(path.dirname(require.resolve('playwright-core/package.json')));
                const vendor = url.includes('fullcalendar') && url.endsWith('.js') ? 'fullcalendar/index.global.min.js'
                    : url.includes('apexcharts') ? 'apexcharts/dist/apexcharts.min.js'
                    : url.includes('leaflet') && url.endsWith('.js') ? 'leaflet/dist/leaflet.js'
                    : url.includes('leaflet') && url.endsWith('.css') ? 'leaflet/dist/leaflet.css' : null;
                if (vendor) return route.fulfill({ path: path.join(modules, vendor), contentType: vendor.endsWith('.js') ? 'text/javascript' : 'text/css' });
                return url.startsWith(base) ? route.continue() : route.abort();
            });
            const page = await context.newPage();
            for (const file of fs.readdirSync(fixtures).filter(file => file.endsWith('.html')).sort()) {
                await page.goto(`${base}/${file}`, { waitUntil: 'load' });
                await page.waitForTimeout(80);
                const appearanceForm = page.locator('[data-appearance-form]');
                if (await appearanceForm.count()) {
                    let writes = 0;
                    const observe = request => { if (request.method() !== 'GET') writes++; };
                    page.on('request', observe);
                    const heading = page.locator('h2').first();
                    const size = await heading.evaluate(el => parseFloat(getComputedStyle(el).fontSize));
                    await appearanceForm.locator('[name="theme"]').selectOption('light');
                    assert.equal(await page.locator('html').getAttribute('data-theme'), 'light');
                    await appearanceForm.locator('[name="theme"]').selectOption('dark');
                    await appearanceForm.locator('[name="font_size"]').selectOption('normal');
                    const smaller = await heading.evaluate(el => parseFloat(getComputedStyle(el).fontSize));
                    assert(smaller < size, file + ': text size preview must change measured text');
                    await appearanceForm.locator('[type="reset"]').click();
                    assert.equal(await page.locator('html').getAttribute('data-font'), 'xl');
                    assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
                    assert.equal(await heading.evaluate(el => parseFloat(getComputedStyle(el).fontSize)), size);
                    assert.equal(writes, 0, 'Preview and Cancel must never POST');
                    assert.equal(await page.evaluate(() => localStorage.length), 0);
                    await appearanceForm.locator('[name="theme"]').selectOption('light');
                    await page.reload();
                    assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
                    await appearanceForm.locator('[name="theme"]').selectOption('light');
                    await page.goto(`${base}/bhw-qr_scanner.html`);
                    await page.goBack();
                    assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
                    // Verify Save still submits to the existing endpoint, without touching a server DB.
                    await page.route('**/settings/preferences', route => route.fulfill({ status: 200, contentType: 'text/html', body: 'Saved test submission' }));
                    await page.locator('[data-appearance-form] [name="theme"]').selectOption('light');
                    await page.locator('[data-appearance-form] [name="font_size"]').selectOption('large');
                    const submitted = page.waitForRequest(r => r.method() === 'POST');
                    await page.locator('[data-appearance-form] [type="submit"]').click();
                    const request = await submitted;
                    assert(request.postData().includes('theme=light') && request.postData().includes('font_size=large'));
                    page.off('request', observe);
                    await page.goto(`${base}/${file}`);
                    checks++;
                }
                const tabs = page.locator('.demographics-tab');
                const panels = await tabs.count();
                for (let tab = 0; tab < Math.max(1, panels); tab++) {
                    if (panels) await tabs.nth(tab).click();
                    const issues = await page.evaluate(contrastAudit);
                    results.push({ file, width, tab, issues });
                    checks++;
                }
                if ([1440, 390].includes(width) && ['admin-demographics.html', 'bhw-citizen_show.html', 'citizen-dashboard.html', 'superadmin-0.html'].includes(file)) {
                    if (panels) await page.locator('[data-tab="subgroups"]').click();
                    await page.screenshot({ path: path.join(fixtures, `${file}-${width}.png`) });
                }
                await page.evaluate(() => {
                    document.querySelectorAll('dialog, [role="dialog"], .modal, .modal-overlay, [class$="-modal"]').forEach(modal => {
                        if (modal.matches('dialog')) modal.setAttribute('open', '');
                        modal.hidden = false;
                        modal.style.setProperty('display', 'block', 'important');
                        modal.style.setProperty('visibility', 'visible', 'important');
                        modal.style.setProperty('opacity', '1', 'important');
                    });
                });
                results.push({ file, width, tab: 'dialogs', issues: await page.evaluate(contrastAudit) });
                checks++;
            }
            await context.close();
        }
        fs.writeFileSync(path.join(fixtures, 'browser-results.json'), JSON.stringify(results, null, 2));
        const failures = results.filter(result => result.issues.length);
        console.log(JSON.stringify({ checks, pages: fs.readdirSync(fixtures).filter(f => f.endsWith('.html')).length,
            contrastFailures: failures.length, examples: failures.slice(0, 8) }, null, 2));
        assert.equal(failures.length, 0, 'Dark mode text contrast failures; inspect browser-results.json');
    } finally {
        if (browser) await browser.close();
        server.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
