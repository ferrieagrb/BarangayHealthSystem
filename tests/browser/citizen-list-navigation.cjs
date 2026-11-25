// Run CitizenRecordReviewTest with BHEAMS_CITIZEN_NAV_FIXTURES first.
// Uses rendered synthetic records and the real filter scripts; no application database.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const playwrightModule = process.env.PLAYWRIGHT_MODULE || 'playwright-core';
const { chromium } = require(playwrightModule);
const modules = path.dirname(path.dirname(require.resolve(playwrightModule + '/package.json')));
const fixtures = process.env.BHEAMS_CITIZEN_NAV_FIXTURES;
if (!fixtures) throw Error('Set BHEAMS_CITIZEN_NAV_FIXTURES to the generated fixture directory.');
const publicRoot = path.resolve(__dirname, '../../public');
const assertSameUrl = (actual, expected, message) => {
    const left = new URL(actual), right = new URL(expected);
    assert.equal(left.origin + left.pathname, right.origin + right.pathname, message);
    assert.deepEqual([...left.searchParams.entries()].sort(), [...right.searchParams.entries()].sort(), message);
};

(async () => {
    let base;
    const server = http.createServer((request, response) => {
        const url = new URL(request.url, base);
        let fixture;
        if (url.pathname === '/citizenlist') {
            fixture = url.searchParams.has('filter_needs_review')
                ? 'list-' + (url.searchParams.get('page') === '2' ? '2' : '1') + '.html'
                : 'reset.html';
        } else if (/^\/citizendetails\/\d+$/.test(url.pathname)) {
            fixture = 'details-' + (url.searchParams.get('list[page]') === '2' ? '2' : '1') + '.html';
        }
        if (fixture) {
            response.setHeader('Content-Type', 'text/html');
            response.end(fs.readFileSync(path.join(fixtures, fixture), 'utf8').replaceAll('http://localhost', base));
            return;
        }
        const file = path.resolve(publicRoot, '.' + decodeURIComponent(url.pathname));
        if (file.startsWith(publicRoot + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
            response.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : file.endsWith('.js') ? 'text/javascript' : 'application/octet-stream');
            response.end(fs.readFileSync(file));
        } else {
            response.statusCode = 404;
            response.end();
        }
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    base = 'http://127.0.0.1:' + server.address().port;
    let browser;
    try {
        browser = await chromium.launch({
            executablePath: process.env.BHEAMS_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
            headless: true,
        });
        for (const width of [1440, 390]) {
            const page = await browser.newPage({ viewport: { width, height: 1000 } });
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.route('**/*', route => {
                const url = route.request().url();
                if (url.includes('apexcharts')) {
                    return route.fulfill({ path: path.join(modules, 'apexcharts/dist/apexcharts.min.js'), contentType: 'text/javascript' });
                }
                return url.startsWith(base) ? route.continue() : route.abort();
            });
            const filters = JSON.parse(fs.readFileSync(path.join(fixtures, 'filters.json'), 'utf8'));
            const query = new URLSearchParams();
            for (const [key, value] of Object.entries(filters)) {
                if (Array.isArray(value)) value.forEach((item, index) => query.append(key + '[' + index + ']', item));
                else query.set(key, value);
            }
            const listUrl = base + '/citizenlist?' + query;
            await page.goto(listUrl);
            assert(await page.locator('#filterNeedsReview').isChecked());
            const status = await page.locator('#openCitizenStatusFilter').boundingBox();
            const reset = await page.locator('#resetCitizenFilters').boundingBox();
            assert(Math.abs(status.y - reset.y) < 1, 'Reset must sit beside Status & Filters');
            assert(reset.x >= status.x + status.width, 'Filter buttons must not overlap');
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);

            await page.locator('[data-citizen-details-link]').first().click();
            assert.equal(new URL(page.url()).searchParams.get('list[page]'), '2');
            await page.locator('[data-citizen-list-back]').click();
            await page.waitForLoadState('load');
            assertSameUrl(page.url(), listUrl, 'In-page Back must restore all filters and page 2');
            assert(await page.locator('#filterNeedsReview').isChecked());
            assert(await page.locator('input[name="filter_housing[]"][value="Renter"]').isChecked());

            await page.locator('[data-citizen-details-link]').first().click();
            await page.goBack();
            await page.waitForLoadState('load');
            assertSameUrl(page.url(), listUrl, 'Browser Back must also preserve list state');
            assert(await page.locator('#filterNeedsReview').isChecked());

            await page.locator('#citizenSearchInput').fill(' Review ');
            await page.waitForURL(url => url.pathname === '/citizenlist' && !url.searchParams.has('page'));
            const searchedUrl = page.url();
            assert.equal(new URL(searchedUrl).searchParams.get('filter_needs_review'), '1');
            await page.locator('[data-citizen-details-link]').first().click();
            assert.equal(new URL(page.url()).searchParams.get('list[search]'), 'Review');
            await page.locator('[data-citizen-list-back]').click();
            await page.waitForLoadState('load');
            assertSameUrl(page.url(), searchedUrl, 'Back must keep filters after an AJAX search');
            assert(await page.locator('input[name="filter_housing[]"][value="Renter"]').isChecked());

            await page.locator('#openCitizenStatusFilter').click();
            await page.locator('input[name="filter_housing[]"][value="Renter"]').uncheck();
            await page.locator('#applyCitizenStatusFilters').click();
            await page.waitForURL(url => ![...url.searchParams.keys()].some(key => key.startsWith('filter_housing')));

            await page.locator('#resetCitizenFilters').click();
            assert.equal(page.url(), base + '/citizenlist');
            assert.equal(await page.locator('#filterNeedsReview').isChecked(), false);
            assert.equal(await page.locator('#citizenSearchInput').inputValue(), '');
            assert.equal(await page.locator('#citizenAgeFilter').inputValue(), '');
            assert.equal(await page.locator('#purokFilter').inputValue(), '');
            assert.deepEqual(errors, []);
            await page.locator('.toolbar').first().screenshot({ path: path.join(fixtures, 'toolbar-' + width + '.png') });
            console.log(width + 'px: filter persistence, both Back buttons, AJAX search, reset, and toolbar alignment passed');
            await page.close();
        }
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
