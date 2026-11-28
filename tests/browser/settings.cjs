/* Generate fixtures with BHEAMS_APPEARANCE_FIXTURES and AppearancePreferencesTest first.
   Uses real Blade output and local assets; push services and writes are simulated. */
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright-core');
const fixtures = process.env.BHEAMS_APPEARANCE_FIXTURES;
const publicRoot = path.resolve(__dirname, '../../public');
if (!fixtures) throw Error('Set BHEAMS_APPEARANCE_FIXTURES.');
const endpoint = 'https://fcm.googleapis.com/fcm/send/settings-test';
const device = { id: 1, device_name: 'Chrome on Windows', endpoint_hash: crypto.createHash('sha256').update(endpoint).digest('hex'), created_at: '2026-11-28T08:00:00Z', last_seen_at: '2026-11-28T08:30:00Z' };

async function mockPush(page, options = {}) {
    const state = { devices: options.existing ? [device] : [], failList: false, writes: 0 };
    await page.addInitScript(({ endpoint, options }) => {
        window.__push = { subscribed: !!options.existing, registrationCalls: 0, unsubscribeCalls: 0 };
        const subscription = {
            endpoint,
            toJSON: () => ({ endpoint, keys: { auth: 'test', p256dh: 'test' } }),
            unsubscribe: async () => { window.__push.subscribed = false; window.__push.unsubscribeCalls++; return true; },
        };
        const registration = { pushManager: {
            getSubscription: async () => window.__push.subscribed ? subscription : null,
            subscribe: async () => { window.__push.subscribed = true; return subscription; },
        } };
        Object.defineProperty(navigator, 'serviceWorker', { configurable: true, value: {
            register: async () => {
                window.__push.registrationCalls++;
                if (options.failOnce && window.__push.registrationCalls === 1) throw Error('Connection failed. Try again.');
                return registration;
            },
            ready: Promise.resolve(registration),
        } });
        Object.defineProperty(window, 'PushManager', { configurable: true, value: function () {} });
        if (options.unsupported) delete window.PushManager;
        Object.defineProperty(window, 'Notification', { configurable: true, value: {
            permission: options.denied ? 'denied' : 'default',
            requestPermission: async () => options.denied ? 'denied' : 'granted',
        } });
    }, { endpoint, options });
    await page.route('**/push-subscriptions**', async route => {
        const request = route.request();
        if (request.method() === 'GET') {
            return route.fulfill({ status: state.failList ? 503 : 200, json: { devices: state.devices } });
        }
        state.writes++;
        state.devices = request.method() === 'POST' ? [device] : [];
        return route.fulfill({ json: { message: 'Updated' } });
    });
    return state;
}

(async () => {
    let base;
    const server = http.createServer((request, response) => {
        const url = new URL(request.url, base);
        const pathname = decodeURIComponent(url.pathname);
        const fixture = path.join(fixtures, path.basename(pathname));
        const asset = path.resolve(publicRoot, '.' + pathname);
        if (pathname.endsWith('.html') && fs.existsSync(fixture)) {
            response.setHeader('Content-Type', 'text/html; charset=utf-8');
            response.end(fs.readFileSync(fixture, 'utf8').replaceAll('http://localhost', base)
                .replace(/data-public-key="[^"]*"/, `data-public-key="${url.searchParams.has('unconfigured') ? '' : 'BHVlc3Qta2V5'}"`));
        } else if (asset.startsWith(publicRoot + path.sep) && fs.existsSync(asset) && fs.statSync(asset).isFile()) {
            const ext = path.extname(asset);
            response.setHeader('Content-Type', ({ '.css': 'text/css', '.js': 'text/javascript', '.png': 'image/png', '.svg': 'image/svg+xml' })[ext] || 'application/octet-stream');
            response.end(fs.readFileSync(asset));
        } else { response.statusCode = 404; response.end(); }
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    base = `http://127.0.0.1:${server.address().port}`;
    let browser;
    let checks = 0;
    const files = ['bhw-0.html', 'admin-0.html', 'admin-1.html', 'superadmin-1.html', 'citizen-0.html'];
    const widths = process.env.SETTINGS_WIDTHS ? process.env.SETTINGS_WIDTHS.split(',').map(Number) : [360, 390, 768, 1024, 1440];
    try {
        browser = await chromium.launch({ executablePath: process.env.BHEAMS_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: true });
        for (const width of widths) {
            const context = await browser.newContext({ viewport: { width, height: 1000 } });
            await context.route('**/*', route => route.request().url().startsWith(base) ? route.continue() : route.abort());
            const page = await context.newPage();
            await mockPush(page);
            for (const file of files) {
                await page.goto(`${base}/${file}`, { waitUntil: 'load' });
                await page.waitForFunction(() => !document.querySelector('[data-push-toggle]').disabled);
                await page.locator('#global-loader').waitFor({ state: 'hidden' });
                const root = page.locator('.account-settings, .citizen-settings');
                assert.equal(await root.count(), 1, `${file}: settings page must render`);
                for (const theme of ['light', 'dark']) {
                    await page.selectOption('[name="theme"]', theme);
                    for (const font of ['normal', 'xl']) {
                        await page.selectOption('[name="font_size"]', font);
                        await page.waitForTimeout(250);
                        const overflow = await root.evaluate(el => {
                            const box = el.getBoundingClientRect();
                            return [...el.querySelectorAll('input:not([type="hidden"]), select, button, section, a')]
                                .filter(child => child.getClientRects().length && !child.closest('dialog:not([open])'))
                                .map(child => ({ tag: child.tagName, text: (child.textContent || child.id).trim().slice(0, 35), r: child.getBoundingClientRect() }))
                                .filter(({ r }) => r.left < box.left - 2 || r.right > box.right + 2).map(({ tag, text }) => ({ tag, text }));
                        });
                        assert.deepEqual(overflow, [], `${file} ${width} ${theme} ${font}: overflow`);
                        const contrast = await root.evaluate(el => {
                            const rgb = value => (value.match(/[\d.]+/g) || []).map(Number);
                            const lum = c => c.slice(0, 3).map(v => (v /= 255) <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4).reduce((s, v, i) => s + v * [.2126, .7152, .0722][i], 0);
                            const issues = [];
                            for (const node of el.querySelectorAll('*')) {
                                if (node.closest('svg, script, style') || !node.getClientRects().length) continue;
                                if (![...node.childNodes].some(n => n.nodeType === 3 && n.textContent.trim()) && !node.matches('input:not([type="hidden"]), select')) continue;
                                const style = getComputedStyle(node);
                                if (style.visibility === 'hidden' || +style.opacity < .9) continue;
                                let bg;
                                for (let parent = node; parent; parent = parent.parentElement) {
                                    const color = rgb(getComputedStyle(parent).backgroundColor);
                                    if (color.length === 3 || color[3] >= .98) { bg = color; break; }
                                }
                                if (!bg) continue;
                                const fg = rgb(style.color);
                                const ratio = (Math.max(lum(fg), lum(bg)) + .05) / (Math.min(lum(fg), lum(bg)) + .05);
                                const large = parseFloat(style.fontSize) >= 24 || (parseFloat(style.fontSize) >= 18.66 && +style.fontWeight >= 700);
                                if (ratio < (large ? 3 : 4.5)) issues.push({ text: (node.value || node.textContent).trim().slice(0, 50), ratio: +ratio.toFixed(2) });
                            }
                            return issues;
                        });
                        assert.deepEqual(contrast, [], `${file} ${width} ${theme} ${font}: text contrast`);
                        checks++;
                    }
                }
                await page.locator('[data-appearance-form] [type="reset"]').click();
                assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
                assert.equal(await page.locator('html').getAttribute('data-font'), 'xl');
                // Each password field's existing visibility control must work independently.
                const input = page.locator('#current_password');
                await input.fill('TestPassword123!');
                const toggle = input.locator('..').locator('.pw-toggle-btn');
                await toggle.click(); assert.equal(await input.getAttribute('type'), 'text');
                await toggle.click(); assert.equal(await input.getAttribute('type'), 'password');
                await page.selectOption('[name="theme"]', 'light');
                await page.selectOption('[name="font_size"]', 'normal');
                await page.waitForTimeout(250);
                if (file === 'bhw-0.html' && [390, 1440].includes(width)) {
                    await page.screenshot({ path: path.join(fixtures, `settings-${width}.png`), fullPage: true });
                }
                for (const [url, selector] of [
                    ['settings/preferences', '[data-appearance-form] [type="submit"]'],
                    ['settings/password', 'form[action$="/settings/password"] button[type="submit"]'],
                    ['email/verification-notification', 'form[action$="/email/verification-notification"] button'],
                ]) {
                    await page.route(`**/${url}`, route => route.fulfill({ status: 200, contentType: 'text/html', body: 'Test submitted' }));
                    if (url === 'settings/password') {
                        await page.fill('#current_password', 'Password123!');
                        await page.fill('#password', 'NewPassword123!');
                        await page.fill('#password_confirmation', 'NewPassword123!');
                    }
                    const requested = page.waitForRequest(r => r.method() === 'POST' && r.url().endsWith(url));
                    await page.locator(selector).click();
                    const request = await requested;
                    assert(request.postData().includes('_token='), 'CSRF token required');
                    if (url.startsWith('settings/')) assert(request.postData().includes('_method=PUT'));
                    await page.goto(`${base}/${file}`, { waitUntil: 'load' });
                    await page.locator('#global-loader').waitFor({ state: 'hidden' });
                    checks++;
                }
                checks++;
            }
            await context.close();
        }

        for (const options of [{}, { failOnce: true }, { denied: true }, { existing: true }, { unsupported: true }, { unconfigured: true }]) {
            const context = await browser.newContext({ viewport: { width: 390, height: 900 } });
            await context.route('**/*', route => route.request().url().startsWith(base) ? route.continue() : route.abort());
            const page = await context.newPage();
            const state = await mockPush(page, options);
            await page.goto(`${base}/bhw-0.html${options.unconfigured ? '?unconfigured' : ''}`);
            await page.locator('#global-loader').waitFor({ state: 'hidden' });
            const toggle = page.locator('[data-push-toggle]');
            if (options.unsupported || options.unconfigured) {
                await page.waitForFunction(() => document.querySelector('[data-push-toggle]').textContent === 'Unavailable');
                assert(await toggle.isDisabled());
            } else if (options.denied) {
                await page.waitForFunction(() => document.querySelector('[data-push-toggle]').textContent === 'Blocked');
                assert(await toggle.isDisabled());
            } else {
                if (options.failOnce) {
                    await page.waitForFunction(() => document.querySelector('[data-push-toggle]').textContent === 'Try again');
                    await toggle.click();
                }
                await page.waitForFunction(() => !document.querySelector('[data-push-toggle]').disabled);
                if (!options.existing) {
                    state.failList = true;
                    await toggle.click();
                    await page.waitForFunction(() => document.querySelector('[data-push-toggle]').textContent.includes('Disable'));
                    assert(await page.evaluate(() => window.__push.subscribed), 'A list error must not disable successful subscription');
                    state.failList = false;
                    await toggle.click();
                    await page.waitForFunction(() => document.querySelector('[data-push-toggle]').textContent.includes('Enable'));
                    assert.equal(await page.evaluate(() => window.__push.subscribed), false);
                } else {
                    await page.locator('.push-device-remove').click();
                    await page.locator('#appConfirmModal .app-confirm-cancel').click();
                    assert.equal(state.devices.length, 1, 'Cancel keeps the registered device');
                    await page.locator('.push-device-remove').click();
                    await page.locator('#appConfirmModal .app-confirm-submit').click();
                    await page.waitForFunction(() => document.querySelector('[data-push-device-status]').textContent.includes('No notification'));
                    assert.equal(await page.evaluate(() => window.__push.subscribed), false);
                    assert.equal(state.devices.length, 0);
                }
            }
            checks++;
            await context.close();
        }
        console.log(JSON.stringify({ checks, widths, pages: files.length, screenshots: ['settings-390.png', 'settings-1440.png'], result: 'passed' }));
    } finally {
        if (browser) await browser.close();
        server.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
