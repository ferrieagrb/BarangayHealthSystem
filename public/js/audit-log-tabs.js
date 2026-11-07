document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-audit-tabs]').forEach(function (page) {
        if (page.dataset.auditTabsInitialized) return;
        page.dataset.auditTabsInitialized = 'true';
        const initialTab = page.dataset.initialTab;
        const navigation = page.querySelector('.tabs');
        const buttons = Array.from(navigation.querySelectorAll('.tab'));
        const mobile = window.matchMedia('(max-width: 768px)');

        function showActiveTab(button, animate) {
            if (!mobile.matches) return;
            const navBounds = navigation.getBoundingClientRect();
            const tabBounds = button.getBoundingClientRect();
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            navigation.scrollTo({
                left: navigation.scrollLeft + tabBounds.left - navBounds.left - (navigation.clientWidth - button.offsetWidth) / 2,
                behavior: animate && !reducedMotion ? 'smooth' : 'instant'
            });
        }

        function activate(button, animate = true) {
            buttons.forEach(tab => {
                const selected = tab === button;
                tab.classList.toggle('active', selected);
                tab.setAttribute('aria-selected', String(selected));
                tab.tabIndex = selected ? 0 : -1;
                document.getElementById(tab.dataset.tab).classList.toggle('active', selected);
            });
            showActiveTab(button, animate);
        }

        buttons.forEach(function (button, index) {
            button.type = 'button';
            button.id = `audit-tab-${button.dataset.tab}`;
            button.setAttribute('role', 'tab');
            button.setAttribute('aria-controls', button.dataset.tab);
            const panel = document.getElementById(button.dataset.tab);
            panel.setAttribute('role', 'tabpanel');
            panel.setAttribute('aria-labelledby', button.id);
            button.addEventListener('click', () => activate(button));
            button.addEventListener('keydown', event => {
                let next;
                if (event.key === 'ArrowRight') next = (index + 1) % buttons.length;
                else if (event.key === 'ArrowLeft') next = (index - 1 + buttons.length) % buttons.length;
                else if (event.key === 'Home') next = 0;
                else if (event.key === 'End') next = buttons.length - 1;
                else return;
                event.preventDefault();
                buttons[next].focus({ preventScroll: true });
                activate(buttons[next]);
            });
        });
        activate(buttons.find(button => button.dataset.tab === initialTab) || buttons[0], false);
        mobile.addEventListener('change', () => showActiveTab(navigation.querySelector('.active'), false));
    });
});
