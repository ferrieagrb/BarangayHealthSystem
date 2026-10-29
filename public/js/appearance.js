/* Per-document preview only. The authenticated preference form is the sole writer. */
(() => {
    'use strict';
    const root = document.documentElement;
    const body = document.body;
    const saved = Object.freeze({
        theme: root.dataset.theme === 'dark' ? 'dark' : 'light',
        font: ['large', 'xl'].includes(root.dataset.font) ? root.dataset.font : 'normal'
    });
    const citizen = body.dataset.appearanceRole === 'citizen';
    const scales = citizen ? { normal: .9, large: 1, xl: 1.1 } : { normal: 1, large: 1.15, xl: 1.3 };
    let fonts = new WeakMap();
    const measured = new Set();
    let current = { ...saved };
    let scheduled = false;

    function scaleText() {
        for (const element of measured) {
            if (!element.isConnected) measured.delete(element);
        }
        const scale = scales[current.font];
        const elements = [...body.querySelectorAll('*')].filter(element =>
            !element.closest('script, style, svg, canvas, .bx, .fa, [data-font-fixed]') &&
            (element.matches('input, select, textarea, button') || [...element.childNodes].some(node =>
                node.nodeType === Node.TEXT_NODE && node.textContent.trim()))
        );
        // Read before writing: nested text never compounds and Cancel restores inline styles.
        for (const element of elements) {
            if (fonts.has(element)) continue;
            let size = parseFloat(getComputedStyle(element).fontSize);
            const parent = element.parentElement;
            const parentOriginal = fonts.get(parent);
            if (parentOriginal && !element.style.fontSize &&
                size === parseFloat(getComputedStyle(parent).fontSize)) {
                // New inherited text starts from its parent's unscaled size.
                size = parentOriginal.size;
            }
            if (Number.isFinite(size)) {
                fonts.set(element, { size, inline: element.style.fontSize });
                measured.add(element);
            }
        }
        for (const element of elements) {
            const original = fonts.get(element);
            if (original) element.style.fontSize = scale === 1 ? original.inline : `${original.size * scale}px`;
        }
    }

    function apply(preferences) {
        current = {
            theme: preferences.theme === 'dark' ? 'dark' : 'light',
            font: Object.hasOwn(scales, preferences.font) ? preferences.font : 'normal'
        };
        for (const element of [root, body]) {
            element.classList.toggle('theme-dark', current.theme === 'dark');
            element.classList.toggle('theme-light', current.theme !== 'dark');
            element.dataset.theme = current.theme;
            element.dataset.font = current.font;
        }
        body.classList.remove('font-normal', 'font-large', 'font-xl');
        body.classList.add(`font-${current.font}`);
        scaleText();
        document.dispatchEvent(new CustomEvent('bheams:appearance', { detail: { ...current } }));
    }

    const forms = [...document.querySelectorAll('[data-appearance-form]')];
    function restore() {
        apply(saved);
        for (const form of forms) {
            form.querySelector('[name="theme"]').value = saved.theme;
            form.querySelector('[name="font_size"]').value = saved.font;
            form.querySelector('[data-appearance-status]').textContent = 'Your saved preferences are applied.';
        }
    }
    forms.forEach(form => {
        form.addEventListener('change', () => {
            apply({ theme: form.elements.theme.value, font: form.elements.font_size.value });
            const dirty = current.theme !== saved.theme || current.font !== saved.font;
            form.querySelector('[data-appearance-status]').textContent = dirty
                ? 'Preview only. Save Preferences to keep these changes.' : 'Your saved preferences are applied.';
        });
        form.addEventListener('reset', event => { event.preventDefault(); restore(); });
    });
    apply(saved);
    // A cached settings page must not resurrect an unsaved preview on Back/Forward.
    window.addEventListener('pagehide', restore);
    window.addEventListener('pageshow', event => { if (event.persisted) restore(); });
    let resizeFrame;
    window.addEventListener('resize', () => {
        cancelAnimationFrame(resizeFrame);
        resizeFrame = requestAnimationFrame(() => {
            for (const element of measured) {
                element.style.fontSize = fonts.get(element).inline;
            }
            fonts = new WeakMap();
            measured.clear();
            scaleText();
        });
    }, { passive: true });
    new MutationObserver(records => {
        if (!records.some(record => record.addedNodes.length) || scheduled) return;
        scheduled = true;
        requestAnimationFrame(() => { scheduled = false; scaleText(); });
    }).observe(body, { childList: true, subtree: true });
})();
