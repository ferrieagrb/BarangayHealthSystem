// Layout regression: real admin styles, synthetic content, no application data.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright-core');
const root = path.resolve(__dirname, '../..');
const styles = ['layout.css', 'admin/responsive.css', 'admin/admin-ui.css', 'theme.css', 'typography.css', 'sidebar-scroll.css', 'admin/demographics.css']
    .map(file => fs.readFileSync(path.join(root, 'public/css', file), 'utf8').replace(/@import\s+url\([^)]*\);/g, '')).join('\n');
(async () => {
    const browser = await chromium.launch({ executablePath: process.env.BHEAMS_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: true });
    try {
        for (const width of [390, 1440]) {
            const page = await browser.newPage({ viewport: { width, height: 900 } });
            await page.setContent(`<meta name="viewport" content="width=device-width, initial-scale=1"><style>${styles}</style><body data-appearance-role="admin"><div class="dashboard"><aside class="sidebar"></aside><div class="main-content"><main id="demographicsApp" class="demographics-container"><section class="demographics-panel">${Array.from({length:12}, (_, i) => `<article class="demographics-card" style="min-height:150px">Subgroup ${i}</article>`).join('')}</section><section class="demographics-panel" hidden><div style="height:1000px">Hidden map</div></section></main></div></div></body>`);
            const layout = await page.evaluate(() => {
                window.scrollTo(0, document.documentElement.scrollHeight);
                const main = document.querySelector('.main-content');
                const last = document.querySelector('.demographics-card:last-child');
                return { contentHeight: main.clientHeight, contentScroll: main.scrollHeight,
                    bottomGap: innerHeight - last.getBoundingClientRect().bottom,
                    horizontalOverflow: document.documentElement.scrollWidth > innerWidth,
                    viewport: innerWidth, docWidth: document.documentElement.scrollWidth, mainWidth: main.getBoundingClientRect().width, pageScrolled: scrollY > 0 };
            });
            assert.equal(layout.contentHeight, layout.contentScroll, 'Main content must not have its own scrolling area');
            assert.equal(layout.pageScrolled, true, 'The document should scroll');
            assert.ok(layout.bottomGap >= 0 && layout.bottomGap <= 32, `No blank tail after last card: ${JSON.stringify(layout)}`);
            assert.equal(layout.horizontalOverflow, false, JSON.stringify(layout));
            await page.close();
        }
        console.log('Demographics layout passed at mobile and desktop widths.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });



