document.addEventListener('DOMContentLoaded', function () {
    const mobile = window.matchMedia('(max-width: 768px)');
    function centerControl(navigation, button, animate) {
        if (!mobile.matches || !button) return;
        const navBounds = navigation.getBoundingClientRect();
        const tabBounds = button.getBoundingClientRect();
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        navigation.scrollTo({
            left: navigation.scrollLeft + tabBounds.left - navBounds.left - (navigation.clientWidth - button.offsetWidth) / 2,
            behavior: animate && !reducedMotion ? 'smooth' : 'instant'
        });
    }

    document.querySelectorAll('[data-archive-tabs]').forEach(function (navigation) {
        if (navigation.dataset.archiveTabsInitialized) return;
        navigation.dataset.archiveTabsInitialized = 'true';
        const showCurrent = () => centerControl(navigation, navigation.querySelector('[aria-current="page"]'), false);
        showCurrent();
        mobile.addEventListener('change', showCurrent);
        navigation.addEventListener('focusin', event => centerControl(navigation, event.target.closest('a'), true));
    });

    document.querySelectorAll('[data-audit-tabs]').forEach(function (page) {
        if (page.dataset.auditTabsInitialized) return;
        page.dataset.auditTabsInitialized = 'true';
        const initialTab = page.dataset.initialTab;
        const navigation = page.querySelector('.tabs');
        const buttons = Array.from(navigation.querySelectorAll('.tab'));

        function showActiveTab(button, animate) {
            centerControl(navigation, button, animate);
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
