// Offline layout fixtures from the actual Blade markup and local styles.
// Run: node tests/browser/bhw-mobile-audit.cjs --output <json> [--compare <before.json>]
// Dynamic widgets, authenticated requests and backend actions need a live smoke test.
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');
const crypto = require('crypto');
const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const args = process.argv.slice(2);
const option = name => args[args.indexOf(name) + 1];
const output = args.includes('--output') ? option('--output') : path.join(os.tmpdir(), 'bhw-mobile-audit.json');
const browser = process.env.BHW_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const temp = fs.mkdtempSync(path.join(os.tmpdir(), 'bhw-mobile-audit-'));
const layout = read('resources/views/templates/layout.blade.php');

function stripBlade(source) {
    source = source.replace(/{{--[\s\S]*?--}}/g, '').replace(/@php[\s\S]*?@endphp/g, '');
    source = source.replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, '');
    source = source.replace(/{{[\s\S]*?}}|{!![\s\S]*?!!}/g, 'Sample resident information with a long description');
    return source;
}

function clean(source) {
    source = stripBlade(source);
    let result = '';
    // Consume directive arguments with balanced parentheses, including nested calls.
    for (let i = 0; i < source.length;) {
        if (source[i] !== '@' || !/[a-zA-Z]/.test(source[i + 1] || '')) { result += source[i++]; continue; }
        const directive = source.slice(i).match(/^@\w+[ \t]*/)[0];
        i += directive.length;
        if (source[i] !== '(') continue;
        let depth = 0, quote = '';
        do {
            const ch = source[i++];
            if (quote) { if (ch === '\\') i++; else if (ch === quote) quote = ''; }
            else if (ch === '"' || ch === "'") quote = ch;
            else if (ch === '(') depth++;
            else if (ch === ')') depth--;
        } while (i < source.length && depth);
    }
    return result;
}

function styles(source) {
    const matches = /<style[^>]*>([\s\S]*?)<\/style>|<link\b[^>]*href=["']{{\s*asset\(['"]([^'"]+\.css)['"]\)\s*}}["'][^>]*>/gi;
    return [...source.matchAll(matches)].map(m => m[1] || read('public/' + m[2]))
        .join('\n').replace(/^@import[^\n]*$/gm, '');
}

const sidebar = clean(layout.split('<!-- SIDEBAR -->')[1].split('<!-- MAIN -->')[0]);
const navigation = layout.split('<!-- JS -->')[1].split('<script>')[1].split('</script>')[0];
const mobileFile = 'public/js/bhw-mobile.js';
const mobileScript = fs.existsSync(path.join(root, mobileFile)) ? read(mobileFile) : '';
const files = fs.readdirSync(path.join(root, 'resources/views/bhw'), { recursive: true })
    .filter(f => f.endsWith('.blade.php')).map(f => 'resources/views/bhw/' + f.replaceAll('\\', '/'));
files.push('resources/views/archive/index.blade.php', 'resources/views/settings.blade.php');
if (args.includes('--page')) {
    const selected = option('--page');
    for (let i = files.length - 1; i >= 0; i--) if (!files[i].includes(selected)) files.splice(i, 1);
    if (!files.length) throw Error('No matching page: ' + selected);
}
const widths = [320, 390, 600, 768, 820, 1024, 1440];
const all = [];

for (let page = 0; page < files.length; page++) {
    const file = files[page];
    let source = read(file);
    source = source.replace("@include('partials.appearance-settings')", read('resources/views/partials/appearance-settings.blade.php'));
    const content = source.split("@section('content')")[1]?.split('@endsection')[0] || '';
    const pageScript = file.endsWith('/logs.blade.php')
        ? source.split("@section('scripts')")[1].split('<script>')[1].split('</script>')[0]
            .replace("@json(request('tab', 'citizens'))", "'citizens'")
        : '';
    const css = styles(layout.split('@yield(\'CSSown\')')[0]) + styles(source)
        + read('public/css/theme.css') + read('public/css/bhw/sidebar.css')
        + (layout.includes("asset('css/bhw/mobile.css')") ? read('public/css/bhw/mobile.css') : '');
    const fixtures = [];
    for (const width of widths) for (const dark of [false, true]) {
        fixtures.push({ width, dark, doc: `<!doctype html><html data-theme="${dark ? 'dark' : 'light'}"><head><meta charset="UTF-8"><style>${css}</style></head><body data-appearance-role="bhw" class="theme-${dark ? 'dark' : 'light'}"><div class="dashboard">${sidebar}<main class="main-content">${clean(content)}</main></div><script>${navigation}</script><script>${mobileScript}</script><script>${pageScript}</script></body></html>` });
    }
    const inspect = `
        const rows=[]; let done=0;
        for(const c of ${JSON.stringify(fixtures).replaceAll('</script', '<\\/script')}) {
            const f=document.createElement('iframe'); f.style.width=c.width+'px'; f.style.height=(c.width===768? '390':'900')+'px';
            f.onload=()=>setTimeout(async()=>{
                const d=f.contentDocument,w=f.contentWindow,main=d.querySelector('.main-content'),bound=main.getBoundingClientRect();
                const visible=[...main.querySelectorAll('*')].filter(e=>e.getClientRects().length&&w.getComputedStyle(e).visibility!=='hidden');
                const bad=visible.filter(e=>{
                    if(e.closest('svg,canvas,thead,.home-map,.disease-map,#qr-reader'))return false;
                    let p=e;while(p&&p!==main){const s=w.getComputedStyle(p);if(['fixed','absolute'].includes(s.position)||['auto','scroll','hidden','clip'].includes(s.overflowX))return false;p=p.parentElement;}
                    const r=e.getBoundingClientRect(); return r.right>bound.right+1||r.left<bound.left-1;
                }).map(e=>e.tagName+'.'+e.className).slice(0,20);
                const snapshot=visible.map(e=>{const s=w.getComputedStyle(e),r=e.getBoundingClientRect();return [e.tagName,e.className,...['display','position','fontSize','lineHeight','padding','margin','color','backgroundColor','gridTemplateColumns'].map(k=>s[k]),...[r.x,r.y,r.width,r.height].map(n=>Math.round(n*100)/100)];});
                const interaction=[];
                const dialogs=[];
                if(c.width<=768){
                    for(const tab of main.querySelectorAll('.logs-page .tab')){
                        tab.click();const panel=d.getElementById(tab.dataset.tab),table=panel.querySelector('.log-table');
                        if(!panel.classList.contains('active'))interaction.push('audit log tab did not activate: '+tab.dataset.tab);
                        if(table.getBoundingClientRect().width>panel.getBoundingClientRect().width+1)interaction.push('audit log table exceeded panel: '+tab.dataset.tab);
                        if([...panel.querySelectorAll('td[data-label]')].some(cell=>parseFloat(w.getComputedStyle(cell).fontSize)<16))interaction.push('audit log text too small: '+tab.dataset.tab);
                    }
                    main.querySelector('.logs-page .tab[data-tab="citizens"]')?.click();
                    for(const chart of main.querySelectorAll('.analytics-chart')){
                        const style=chart.getAttribute('style'),sample=d.createElement('div');
                        chart.style.minHeight='1000px';sample.style.height='1000px';chart.append(sample);
                        if(chart.getBoundingClientRect().height>361)interaction.push('chart exceeded mobile height limit');
                        sample.remove();if(style===null)chart.removeAttribute('style');else chart.setAttribute('style',style);
                    }
                    const sidebar=d.getElementById('bhwSidebar'),toggle=sidebar.querySelector('.topbutton');
                    toggle.click();
                    const settle=()=>new Promise(resolve=>w.queueMicrotask(resolve));
                    await settle();
                    if(!d.querySelector('.bhw-mobile-backdrop:not([hidden])')||!main.inert)interaction.push('drawer did not lock background');
                    d.dispatchEvent(new w.KeyboardEvent('keydown',{key:'Escape',bubbles:true}));
                    if(sidebar.classList.contains('collapsed')||main.inert)interaction.push('Escape did not close drawer');
                    toggle.click();
                    await settle();
                    d.querySelector('.bhw-mobile-backdrop')?.click();
                    if(sidebar.classList.contains('collapsed')||main.inert)interaction.push('backdrop did not close drawer');
                    for(const box of main.querySelectorAll('dialog,.modal-content,.citizen-status-modal,.citizen-password-dialog,.citizen-export-dialog,.supplies-modal-content,.batch-release-dialog,.batch-picker-dialog,.medicine-picker-dialog,.edit-assignment-box,.deceased-modal-box,.announcement-modal-dialog,.picture-confirm-dialog,.event-confirm-modal,.deposit-confirm-modal,.announcement-confirm-modal,.diagnosis-confirm-modal,.custom-modal-box')){
                        const saved=[];let p=box;
                        while(p&&p!==main){if(w.getComputedStyle(p).display==='none'){saved.push([p,p.getAttribute('style')]);p.style.display='flex';}p=p.parentElement;}
                        const r=box.getBoundingClientRect();
                        if(r.width>w.innerWidth+1||r.height>w.innerHeight+1)dialogs.push(box.className);
                        for(const [node,style] of saved){if(style===null)node.removeAttribute('style');else node.setAttribute('style',style);}
                    }
                }
                rows.push({width:c.width,dark:c.dark,bad,snapshot,interaction,dialogs});
                if(++done===${fixtures.length})document.getElementById('result').textContent=JSON.stringify(rows);
            },500);
            f.srcdoc=c.doc;document.body.appendChild(f);
        }`;
    const fixture = path.join(temp, 'fixture.html');
    fs.writeFileSync(fixture, '<pre id="result">pending</pre><script>' + inspect + '</script>');
    const run = spawnSync(browser, ['--headless', '--no-sandbox', '--disable-gpu', '--disable-software-rasterizer', '--no-first-run', '--user-data-dir=' + path.join(temp, 'profile'), '--dump-dom', '--virtual-time-budget=3000', 'file:///' + fixture.replaceAll('\\', '/')], { encoding: 'utf8', timeout: 25000, maxBuffer: 32e6 });
    const raw = run.stdout?.match(/<pre id="result">([\s\S]*?)<\/pre>/)?.[1];
    if (!raw || raw === 'pending') throw Error('Browser fixture failed: ' + file + ': ' + run.stderr?.slice(-300));
    const rows = JSON.parse(raw.replaceAll('&lt;', '<').replaceAll('&gt;', '>').replaceAll('&amp;', '&'));
    for (const row of rows) {
        row.file = file;
        row.hash = crypto.createHash('sha256').update(JSON.stringify(row.snapshot)).digest('hex');
        delete row.snapshot;
        all.push(row);
    }
    console.log(`${page + 1}/${files.length} ${file}: ${rows.filter(r => r.width <= 768 && r.bad.length).length} mobile fixtures with overflow`);
}
fs.writeFileSync(output, JSON.stringify(all, null, 2));
console.log('Results: ' + output);
const failures = all.filter(row => row.width <= 768 && (row.bad.length || row.interaction.length || row.dialogs.length));
console.log('Mobile failures: ' + JSON.stringify(failures.map(row => [row.file, row.width, row.bad, row.interaction, row.dialogs])));
if (failures.length) process.exitCode = 1;
if (args.includes('--compare')) {
    const before = JSON.parse(fs.readFileSync(option('--compare'), 'utf8'));
    const changed = all.filter(row => row.width > 768 && before.find(b => b.file === row.file && b.width === row.width && b.dark === row.dark)?.hash !== row.hash);
    console.log('Desktop fixture changes: ' + JSON.stringify(changed.map(r => [r.file, r.width, r.dark])));
    if (changed.length) process.exitCode = 1;
}
