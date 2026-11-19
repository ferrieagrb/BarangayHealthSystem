const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

async function checkExport(timedOut) {
    const listeners = {};
    const timers = new Map();
    let timerId = 0;
    let downloaded = false;
    let loaderVisible = false;
    const pdfButton = {
        value: 'pdf', textContent: 'Export to PDF', disabled: false,
        setAttribute() {}, removeAttribute() {},
    };
    const feedback = { hidden: true, textContent: '' };
    const form = {
        action: 'https://example.test/reports',
        querySelectorAll: () => [pdfButton],
        addEventListener: (name, handler) => { listeners[name] = handler; },
    };
    const document = {
        addEventListener: (name, handler) => { listeners[name] = handler; },
        getElementById: id => id === 'reportBuilder' ? form : feedback,
        createElement: () => ({ click() { downloaded = true; }, remove() {} }),
        body: { appendChild() {} },
    };
    const window = {
        location: { href: form.action },
        showLoader() { loaderVisible = true; }, hideLoader() { loaderVisible = false; },
        setTimeout(fn, delay) { timers.set(++timerId, { fn, delay }); return timerId; },
        clearTimeout(id) { timers.delete(id); },
    };
    class DownloadUrl extends URL {
        static createObjectURL() { return 'blob:report'; }
        static revokeObjectURL() {}
    }
    const fetch = async (url, options) => {
        assert.equal(url.searchParams.get('export_format'), 'pdf');
        assert.equal(url.searchParams.get('report_type'), 'citizens');
        if (timedOut) {
            return new Promise((resolve, reject) => {
                options.signal.addEventListener('abort', () => reject(new Error('Aborted')));
            });
        }
        return {
            ok: true,
            headers: new Map([['Content-Type', 'application/pdf']]),
            blob: async () => new Blob(['%PDF-1.7']),
        };
    };
    vm.runInNewContext(fs.readFileSync('public/js/bhw/reports-inline-1.js', 'utf8'), {
        document, window, fetch, URL: DownloadUrl, URLSearchParams, AbortController,
        FormData: class { *[Symbol.iterator]() { yield ['report_type', 'citizens']; } },
    });
    listeners.DOMContentLoaded();
    const completion = listeners.submit({ submitter: pdfButton, preventDefault() {} });
    assert.equal(pdfButton.disabled, true);
    if (timedOut) {
        [...timers.values()].find(timer => timer.delay === 90000).fn();
    }
    await completion;
    assert.equal(pdfButton.disabled, false);
    assert.equal(pdfButton.textContent, 'Export to PDF');
    assert.equal(loaderVisible, false);
    assert.equal(downloaded, !timedOut);
    assert.equal(feedback.hidden, !timedOut);
    assert.equal([...timers.values()].some(timer => timer.delay === 90000), false);
    if (timedOut) assert.match(feedback.textContent, /export took too long/);
}

(async () => {
    await checkExport(false);
    await checkExport(true);
    console.log('Report download and timeout recovery passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
