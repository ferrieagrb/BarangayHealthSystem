(() => {
    const actionControls = 'button, input[type="button"], input[type="submit"], input[type="reset"], a.btn, a[class*="btn-"], a.inventory-button';

    function getAction(control) {
        const label = [
            control.innerText,
            control.value,
            control.getAttribute('aria-label'),
            control.getAttribute('title')
        ].filter(Boolean).join(' ').toLowerCase();
        const classes = `${control.className} ${control.id}`.toLowerCase();

        if (/\b(delete|archive|remove|revoke|deactivate|disable|inactive|deny|reject)\b/.test(label)) {
            return 'danger';
        }

        if (/\b(unlock|suspend|warning|caution)\b/.test(label)) {
            return 'warning';
        }

        if (/\b(cancel|back|close|clear|reset|dismiss|print|log ?out|logout)\b/.test(label) ||
            control.type === 'reset' ||
            /\b(secondary|neutral|text-button)\b/.test(label)) {
            return 'secondary';
        }

        if (/\b(save|confirm|approve|complete|submit|success|claim|claimed|enable|restore|activate|verify|set active)\b/.test(label)) {
            return 'success';
        }

        if (/\b(add|create|edit|update|view|manage|search|find|filter|apply|open|select|download|upload|login|sign in|primary)\b/.test(label)) {
            return 'primary';
        }

        if (/\b(danger|destructive)\b/.test(classes)) return 'danger';
        if (/\b(btn-warning|text-warning|caution|warning)\b/.test(classes)) return 'warning';
        if (/\b(secondary|neutral|text-button)\b/.test(classes)) return 'secondary';
        if (/\b(success|approve|complete)\b/.test(classes)) return 'success';
        if (/\b(primary)\b/.test(classes)) return 'primary';

        const explicitAction = control.getAttribute('data-ui-action');
        if (explicitAction === 'danger' || explicitAction === 'primary') return explicitAction;
        if (explicitAction === 'success' || explicitAction === 'warning') return explicitAction;
        if (explicitAction === 'secondary' || explicitAction === 'reset') return 'secondary';
        if (control.type === 'submit') return 'success';
        return 'secondary';
    }

    function styleControl(control) {
        if (!(control instanceof Element) || !control.matches(actionControls)) return;
        if (control.hidden) {
            control.removeAttribute('data-bheams-action');
            control.removeAttribute('data-bheams-icon-only');
            return;
        }
        if (control.closest('.fc, .leaflet-control, .map-mode-controls, [role="tablist"]')) return;
        if (control.closest('.sidebar') && !control.closest('.logout-form, .citizen-sidebar-logout')) return;
        if (control.closest('.nav-menu') && !control.matches('.btn-login')) return;

        control.setAttribute('data-bheams-action', getAction(control));
        const visibleLabel = (control.innerText || control.value || control.textContent).trim();
        if (!visibleLabel && (control.getAttribute('aria-label') || control.title)) {
            control.setAttribute('data-bheams-icon-only', 'true');
        } else {
            control.removeAttribute('data-bheams-icon-only');
        }
    }

    function scan(root) {
        if (root instanceof Element) styleControl(root);
        root.querySelectorAll(actionControls).forEach(styleControl);
    }

    scan(document);

    new MutationObserver(records => {
        records.forEach(record => {
            if (record.type === 'childList') {
                if (record.target instanceof Element) styleControl(record.target);
                record.addedNodes.forEach(node => {
                    if (node instanceof Element) scan(node);
                });
            } else if (record.type === 'characterData' && record.target.parentElement) {
                styleControl(record.target.parentElement);
            } else if (record.target instanceof Element) {
                styleControl(record.target);
            }
        });
    }).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['aria-label', 'class', 'hidden', 'title', 'value', 'data-ui-action'],
        characterData: true,
        childList: true,
        subtree: true
    });
})();
