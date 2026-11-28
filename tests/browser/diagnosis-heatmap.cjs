// Synthetic area totals only. Set LEAFLET_SOURCE to a local Leaflet 1.9.4 dist
// directory for offline QA; otherwise the test uses the app's existing CDN.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright-core');
const root = path.resolve(__dirname, '../..');
const enginePath = path.join(root, 'public/js/bhw/diagnosis-heatmap.js');
const sandbox = {};
vm.runInNewContext(fs.readFileSync(enginePath, 'utf8'), sandbox);
const heat = sandbox.DiagnosisHeatmap;
const field = points => heat.density(101, 101, points, 30);
const sample = { x: 50, y: 50, weight: 4 };
const single = field([sample]);
const combined = field([sample, sample]);
assert.equal(single[50 * 101 + 50], 4);
assert.equal(combined[50 * 101 + 50], 8, 'Overlapping counts must add before colorization');
assert.ok(single[50 * 101 + 65] < single[50 * 101 + 50], 'Intensity must fade with distance');
assert.equal(single[50 * 101 + 80], 0, 'No heat outside the radius');
assert.ok(field([{ ...sample, weight: 0 }]).every(value => value === 0));
assert.ok(field([{ x: -5, y: 50, weight: 4 }])[50 * 101] > 0, 'Offscreen points contribute inside their radius');
assert.ok(heat.metersPerPixel(14, 16) < heat.metersPerPixel(14, 15));

const boundary = { type: 'Polygon', coordinates: [[[120.85, 14.06], [120.854, 14.06], [120.854, 14.064], [120.85, 14.064], [120.85, 14.06]]] };
const center = heat.location({ boundary });
assert.ok(Math.abs(center.lat - 14.062) < 1e-9);
assert.ok(Math.abs(center.lng - 120.852) < 1e-9);
assert.equal(heat.location({}), null);

const controls = prefix => fs.readFileSync(path.join(root, 'resources/views/bhw/partials/heatmap-controls.blade.php'), 'utf8')
    .replaceAll('{{ $prefix }}', prefix);
const areas = [
    { type: 'purok', label: 'Purok A', name: 'Purok A', location: { lat: 14.062, lng: 120.852 }, boundary, records_count: 20, weight: 20, count: 20 },
    { type: 'subgroup', label: 'Purok B', name: 'Purok B', location: { lat: 14.063, lng: 120.854 }, boundary: null, records_count: 7, weight: 7, count: 7 },
    { type: 'purok', label: 'Boundary only', name: 'Boundary only', boundary: { type: 'Feature', geometry: boundary }, records_count: 3, weight: 3, count: 3 }
];
areas[0].diagnoses = [{ diagnosis: 'Dengue', count: 12 }, { diagnosis: 'Cough', count: 8 }];
areas[1].diagnoses = [{ diagnosis: 'Cough', count: 7 }];
areas[2].diagnoses = [{ diagnosis: 'Dengue', count: 3 }];
assert.equal(heat.diagnosisColor('New diagnosis'), heat.diagnosisColor(' NEW DIAGNOSIS '));
assert.match(heat.diagnosisColor('New diagnosis'), /^hsl\(/);
assert.notEqual(heat.diagnosisColor('New diagnosis'), heat.diagnosisColor('Another diagnosis'));

function pixels(page) {
    return page.evaluate(() => {
        const canvas = document.querySelector('.diagnosis-heatmap-canvas');
        const data = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data;
        let colored = 0, blue = 0, red = 0;
        for (let i = 0; i < data.length; i += 4) {
            if (data[i + 3] > 10) colored++;
            if (data[i + 3] > 100 && data[i + 2] > data[i] * 1.5) blue++;
            if (data[i + 3] > 100 && data[i] > data[i + 2] * 1.5) red++;
        }
        return { colored, blue, red, width: canvas.width, height: canvas.height };
    });
}

(async () => {
    const browser = await chromium.launch({ executablePath: process.env.BHEAMS_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: true });
    try {
        for (const width of [390, 1440]) {
            const page = await browser.newPage({ viewport: { width, height: 900 } });
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.setContent(`<meta name="viewport" content="width=device-width, initial-scale=1"><style>body{margin:16px;font:14px Arial;background:#fff}#map{height:420px;background:#eef4f7}</style><div id="map"></div>${controls('test')}`);
            await page.addStyleTag({ path: path.join(root, 'public/css/bhw/diagnosis-heatmap.css') });
            if (process.env.LEAFLET_SOURCE) {
                await page.addStyleTag({ path: path.join(process.env.LEAFLET_SOURCE, 'leaflet.css') });
                await page.addScriptTag({ path: path.join(process.env.LEAFLET_SOURCE, 'leaflet.js') });
            } else {
                await page.addStyleTag({ url: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' });
                await page.addScriptTag({ url: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js' });
            }
            await page.addScriptTag({ path: enginePath });
            await page.addScriptTag({ path: path.join(root, 'public/js/bhw/analytics-heatmap.js') });
            await page.evaluate(areas => {
                window.qaMap = L.map('map', { zoomAnimation: false }).setView([14.062, 120.853], 15);
                window.qaBounds = L.layerGroup().addTo(qaMap);
                areas.filter(area => area.boundary).forEach(area => L.geoJSON(area.boundary, {
                    style: { color: '#334155', weight: 1.5, fillOpacity: 0.001 }
                }).addTo(qaBounds));
                DiagnosisHeatmap.addToLeaflet(qaMap, areas, qaBounds, 'test');
            }, areas);
            await page.waitForFunction(() => parseFloat(document.querySelector('canvas')?.style.width) > 0);
            const initial = await pixels(page);
            assert.ok(initial.red > 0 && initial.blue > 0, `Continuous color ramp: ${JSON.stringify(initial)}`);
            assert.equal(await page.locator('#testStatus').textContent(), '');
            assert.equal(await page.locator('.diagnosis-dot').count(), 4);
            assert.equal(await page.locator('#testDiagnosisLegend span').count(), 2);
            await page.locator('.diagnosis-dot').first().click();
            assert.ok((await page.locator('.leaflet-popup-content').textContent()).includes('Cough'));
            await page.locator('#testDots').uncheck();
            assert.equal(await page.locator('.diagnosis-dot').count(), 0);
            await page.locator('#testDots').check();
            assert.equal(await page.locator('.diagnosis-dot').count(), 4);
            await page.locator('#testBoundaries').uncheck();
            assert.equal(await page.evaluate(() => qaMap.hasLayer(qaBounds)), false);
            await page.locator('#testBoundaries').check();
            assert.equal(await page.evaluate(() => qaMap.hasLayer(qaBounds)), true);
            await page.locator('#testRadius').fill('600');
            await page.locator('#testRadius').dispatchEvent('input');
            await page.waitForFunction(() => document.getElementById('testRadiusValue').textContent === '600 m');
            await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            assert.ok((await pixels(page)).colored > initial.colored, 'Radius should change the actual heat footprint');
            await page.evaluate(() => qaMap.panBy([30, 10], { animate: false }));
            await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            await page.evaluate(() => qaMap.setZoom(16));
            await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            assert.ok((await pixels(page)).colored > 0, 'Heat survives pan and zoom');
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, 'Controls fit the viewport');
            await page.setViewportSize({ width: width - 30, height: 800 });
            await page.waitForFunction(() => {
                const canvas = document.querySelector('canvas');
                return Math.abs(parseFloat(canvas.style.width) - document.getElementById('map').clientWidth) < 2;
            });
            assert.deepEqual(errors, []);
            if (process.env.HEATMAP_SCREENSHOTS) {
                fs.mkdirSync(process.env.HEATMAP_SCREENSHOTS, { recursive: true });
                await page.screenshot({ path: path.join(process.env.HEATMAP_SCREENSHOTS, `heatmap-${width}.png`), fullPage: true });
            }
            await page.close();
        }
        // Test the dashboard with real Leaflet, synthetic totals and no Google API.
        const home = await browser.newPage({ viewport: { width: 1100, height: 800 } });
        const homeErrors = [];
        home.on('pageerror', error => homeErrors.push(error.message));
        const core = fs.readFileSync(enginePath, 'utf8');
        const leafletAdapter = fs.readFileSync(path.join(root, 'public/js/bhw/analytics-heatmap.js'), 'utf8');
        const adapter = fs.readFileSync(path.join(root, 'public/js/bhw/home-heatmap.js'), 'utf8');
        const homeData = areas.map(area => ({ ...area, lat: area.location?.lat ?? null, lng: area.location?.lng ?? null }));
        homeData[0].label = '<img src=x onerror=alert(1)>';
        const leafletJs = process.env.LEAFLET_SOURCE
            ? fs.readFileSync(path.join(process.env.LEAFLET_SOURCE, 'leaflet.js'), 'utf8')
            : await (await fetch('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js')).text();
        const leafletCss = process.env.LEAFLET_SOURCE
            ? fs.readFileSync(path.join(process.env.LEAFLET_SOURCE, 'leaflet.css'), 'utf8')
            : await (await fetch('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css')).text();
        await home.route('https://*.tile.openstreetmap.org/**', route => route.fulfill({
            contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256"></svg>'
        }));
        await home.route('https://services.arcgisonline.com/**', route => route.fulfill({
            contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256"></svg>'
        }));
        await home.route('https://heatmap.test/', route => route.fulfill({ contentType: 'text/html', body:
            '<style>' + leafletCss + '</style><script>' + leafletJs + '</script><script>' + core + '</script><script>' + leafletAdapter + '</script><script>' + adapter + '</script><script id="bhwHeatmapData" type="application/json">' + JSON.stringify(homeData) + '</script><script>initBhwHomeMap();</script><style>#bhwCitizenHeatmap{position:relative;height:420px}</style><div id="bhwCitizenHeatmap"></div>' + controls('bhwHeatmap') }));
        await home.goto('https://heatmap.test/');
        await home.addStyleTag({ path: path.join(root, 'public/css/bhw/diagnosis-heatmap.css') });
        await home.waitForFunction(() => parseFloat(document.querySelector('canvas')?.style.width) > 0);
        assert.ok((await pixels(home)).colored > 0);
        assert.equal(await home.getByLabel('Satellite', { exact: true }).isChecked(), true);
        assert.ok(await home.locator('.leaflet-control-scale-line').count());
        await home.getByLabel('Street map', { exact: true }).check();
        assert.equal(await home.getByLabel('Satellite', { exact: true }).isChecked(), false);
        assert.ok((await pixels(home)).colored > 0, 'Heat remains visible when the basemap changes');
        await home.getByLabel('Satellite', { exact: true }).check();
        assert.equal(await home.locator('.leaflet-overlay-pane path').count(), 3);
        await home.locator('#bhwHeatmapBoundaries').uncheck();
        assert.equal(await home.locator('.leaflet-overlay-pane path').count(), 1);
        await home.locator('#bhwHeatmapBoundaries').check();
        await home.evaluate(() => {
            document.querySelector('.leaflet-overlay-pane path:nth-child(2)')
                .dispatchEvent(new MouseEvent('click', { bubbles: true }));
        });
        assert.equal(await home.locator('.leaflet-popup-content img').count(), 0, 'Area names must be rendered as text');
        assert.ok((await home.locator('.leaflet-popup-content').textContent()).includes('<img'));
        await home.locator('#bhwHeatmapRadius').fill('600');
        await home.locator('#bhwHeatmapRadius').dispatchEvent('input');
        assert.equal(await home.locator('#bhwHeatmapRadiusValue').textContent(), '600 m');
        assert.equal(await home.locator('#bhwHeatmapStatus').textContent(), '');
        await home.evaluate(() => initBhwHomeMap());
        assert.equal(await home.locator('.diagnosis-heatmap-canvas').count(), 1);
        assert.deepEqual(homeErrors, []);
        await home.close();
        console.log('Heat density, color ramp, controls, pan/zoom, responsive rendering and dashboard lifecycle passed.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
