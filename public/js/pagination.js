(() => {
    const initialized = new WeakSet();
    function centerActive(scroll) {
        const active = scroll.querySelector('[aria-current="page"]');
        if (!active || !scroll.clientWidth) return;
        const bounds = scroll.getBoundingClientRect();
        const page = active.getBoundingClientRect();
        scroll.scrollLeft += page.left - bounds.left - (scroll.clientWidth - page.width) / 2;
    }
    const resized = new ResizeObserver(entries => entries.forEach(entry => centerActive(entry.target)));
    function initialize(root) {
        const nodes = root.matches?.('[data-pagination-scroll]') ? [root] : [];
        nodes.push(...root.querySelectorAll('[data-pagination-scroll]'));
        nodes.forEach(scroll => {
            if (initialized.has(scroll)) return;
            initialized.add(scroll);
            resized.observe(scroll);
            centerActive(scroll);
        });
    }
    initialize(document);
    new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(node => {
        if (node instanceof Element) initialize(node);
    }))).observe(document.documentElement, { childList: true, subtree: true });
})();
