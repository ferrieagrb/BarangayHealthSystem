(() => {
    const controlSelector = [
        'input:not([type="hidden"]):not([type="button"]):not([type="submit"]):not([type="reset"]):not([type="image"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="color"]):not([type="range"])',
        'textarea',
        'select'
    ].join(', ');

    function disableBrowserSuggestions(control) {
        if (control.closest('[data-browser-autocomplete="allow"]')) return;

        const autocomplete = control.getAttribute('autocomplete')?.toLowerCase();
        if (
            control instanceof HTMLInputElement &&
            control.type === 'password' &&
            (autocomplete === 'current-password' || autocomplete === 'new-password')
        ) {
            return;
        }

        if (autocomplete !== 'off') control.setAttribute('autocomplete', 'off');
    }

    function applyPolicy(root) {
        if (root instanceof Element && root.matches(controlSelector)) {
            disableBrowserSuggestions(root);
        }

        root.querySelectorAll(controlSelector).forEach(disableBrowserSuggestions);
    }

    applyPolicy(document);

    const observer = new MutationObserver(records => {
        records.forEach(record => {
            record.addedNodes.forEach(node => {
                if (node instanceof Element) applyPolicy(node);
            });
        });
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
})();
