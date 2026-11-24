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
        // Exercise the Google adapter's DOM-ready lifecycle with the Maps API
        // contract stubbed, so QA never needs a live API key or patient data.
        const home = await browser.newPage({ viewport: { width: 1100, height: 800 } });
        const homeErrors = [];
        home.on('pageerror', error => homeErrors.push(error.message));
        await home.addInitScript(() => {
            window.qaPolygons = [];
            class LatLng {
                constructor(lat, lng) { this.latitude = lat; this.longitude = lng; }
                lat() { return this.latitude; }
                lng() { return this.longitude; }
            }
            class Bounds {
                constructor() { this.points = []; }
                extend(point) { this.points.push(point); }
                isEmpty() { return !this.points.length; }
            }
            class Map {
                constructor(element) { this.element = element; this.zoom = 15; this.handlers = {}; window.qaHomeMap = this; }
                getCenter() { return new LatLng(14.062, 120.852); }
                getZoom() { return this.zoom; }
                setZoom(zoom) { this.zoom = zoom; }
                fitBounds() {}
                addListener(event, handler) { this.handlers[event] = handler; }
            }
            class Overlay {
                setMap(map) { this.map = map; if (map) { this.onAdd(); this.draw(); } else this.onRemove(); }
                getMap() { return this.map; }
                getPanes() { return { overlayLayer: this.map.element }; }
                getProjection() {
                    const width = this.map.element.clientWidth, height = this.map.element.clientHeight;
                    return {
                        fromContainerPixelToLatLng: () => new LatLng(14.062 + height / 48000, 120.852 - width / 48000),
                        fromLatLngToDivPixel: latLng => ({ x: (latLng.lng() - 120.852) * 24000 + width / 2, y: (14.062 - latLng.lat()) * 24000 + height / 2 })
                    };
                }
            }
            class Polygon {
                constructor(options) { Object.assign(this, options); qaPolygons.push(this); }
                setMap(map) { this.map = map; }
                addListener(event, handler) { this.handler = handler; }
            }
            class Circle { addListener() {} }
            class InfoWindow {
                setContent(content) { this.content = content; }
                setPosition() {}
                open() { document.getElementById('popup').replaceChildren(this.content); }
            }
            window.google = { maps: { Map, LatLng, LatLngBounds: Bounds, OverlayView: Overlay, Polygon, Circle, InfoWindow,
                Point: class { constructor(x, y) { this.x = x; this.y = y; } },
                event: { addListenerOnce: (map, event, handler) => queueMicrotask(handler) } } };
        });
        const core = fs.readFileSync(enginePath, 'utf8');
        const adapter = fs.readFileSync(path.join(root, 'public/js/bhw/home-heatmap.js'), 'utf8');
        const homeData = areas.map(area => ({ ...area, lat: area.location?.lat ?? null, lng: area.location?.lng ?? null }));
        homeData[0].label = '<img src=x onerror=alert(1)>';
        await home.route('https://heatmap.test/', route => route.fulfill({ contentType: 'text/html', body:
            `<script>${core}</script><script>${adapter}</script><script id="bhwHeatmapData" type="application/json">${JSON.stringify(homeData)}</script><script>initBhwHomeMap();</script><style>#bhwCitizenHeatmap{position:relative;height:420px}</style><div id="bhwCitizenHeatmap"></div>${controls('bhwHeatmap')}<div id="popup"></div>` }));
        await home.goto('https://heatmap.test/');
        await home.waitForFunction(() => parseFloat(document.querySelector('canvas')?.style.width) > 0);
        assert.ok((await pixels(home)).colored > 0);
        assert.equal(await home.evaluate(() => qaPolygons.length), 2);
        assert.equal(await home.evaluate(() => qaPolygons.every(polygon => polygon.strokeWeight > 0)), true);
        await home.locator('#bhwHeatmapBoundaries').uncheck();
        assert.equal(await home.evaluate(() => qaPolygons.every(polygon => polygon.map === null)), true);
        await home.locator('#bhwHeatmapBoundaries').check();
        await home.evaluate(() => qaPolygons[0].handler({ latLng: new google.maps.LatLng(14.062, 120.852) }));
        assert.equal(await home.locator('#popup img').count(), 0, 'Area names must be rendered as text');
        assert.ok((await home.locator('#popup').textContent()).includes('<img'));
        assert.equal(await home.locator('#bhwHeatmapStatus').textContent(), '');
        assert.deepEqual(homeErrors, []);
        await home.close();
        console.log('Heat density, color ramp, controls, pan/zoom, responsive rendering and Google adapter lifecycle passed.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
