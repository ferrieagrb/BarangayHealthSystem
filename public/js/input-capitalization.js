(() => {
    const configuration = document.querySelector('[data-input-capitalization]');
    if (!configuration) return;

    const policy = JSON.parse(configuration.textContent);
    const titleFields = new Set(policy.title);
    const wordFields = new Set(policy.words);
    const preservedWords = new Set(policy.preserve_words);
    const selector = 'input:not([type]), input[type="text"], textarea';

    function modeFor(control) {
        if (!(control instanceof HTMLInputElement || control instanceof HTMLTextAreaElement)
            || !control.matches(selector) || control.disabled || control.readOnly
            || control.closest('[data-capitalization="off"]')) return null;

        const field = (control.name || '').replaceAll(']', '').split('[').pop();
        if (titleFields.has(field)) return 'title';
        if (wordFields.has(field)) return 'words';
        return null;
    }

    function capitalize(value, mode) {
        if (mode === 'title') value = value.toLowerCase();
        return value.replace(/\p{L}[\p{L}\p{M}]*/gu, word => {
            if ((mode !== 'title' && preservedWords.has(word)) || word !== word.toLowerCase()) return word;
            const [first, ...rest] = [...word];
            return first.toUpperCase() + rest.join('');
        });
    }

    function normalize(control) {
        const mode = modeFor(control);
        if (!mode) return;

        const value = control.value;
        const capitalized = capitalize(value, mode);
        if (value === capitalized) return;

        const start = control.selectionStart;
        const end = control.selectionEnd;
        const direction = control.selectionDirection;
        control.value = capitalized;
        if (document.activeElement === control && start !== null && end !== null) {
            control.setSelectionRange(
                capitalize(value.slice(0, start), mode).length,
                capitalize(value.slice(0, end), mode).length,
                direction
            );
        }
    }

    function initialize(control) {
        if (!modeFor(control)) return;
        control.setAttribute('autocapitalize', 'words');
        normalize(control);
    }

    function scan(root) {
        if (root instanceof Element && root.matches(selector)) initialize(root);
        root.querySelectorAll(selector).forEach(initialize);
    }

    document.addEventListener('input', event => {
        if (!event.isComposing) normalize(event.target);
    }, true);
    for (const eventName of ['change', 'focusout', 'compositionend']) {
        document.addEventListener(eventName, event => normalize(event.target), true);
    }
    document.addEventListener('submit', event => {
        if (event.target instanceof HTMLFormElement) {
            [...event.target.elements].forEach(normalize);
        }
    }, true);

    scan(document);
    new MutationObserver(records => {
        for (const record of records) {
            for (const node of record.addedNodes) {
                if (node instanceof Element) scan(node);
            }
        }
    }).observe(document.documentElement, { childList: true, subtree: true });
})();
