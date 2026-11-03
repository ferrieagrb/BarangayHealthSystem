(() => {
    const sidebar = document.getElementById('bhwSidebar');
    if (!sidebar) return;

    const mobile = window.matchMedia('(max-width: 768px)');
    const toggle = sidebar.querySelector('.sidebar-toggle');
    const dropdown = sidebar.querySelector('.nav-dropdown');
    const menuButton = dropdown?.querySelector('.nav-dropdown-toggle');
    const menu = dropdown?.querySelector('.nav-dropdown-menu');
    let placeholder = null;
    let closeTimer;

    function isCollapsedDesktop() {
        return !mobile.matches && sidebar.classList.contains('collapsed');
    }

    function updateSidebarToggle() {
        const expanded = mobile.matches
            ? sidebar.classList.contains('collapsed')
            : !sidebar.classList.contains('collapsed');
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', mobile.matches
            ? (expanded ? 'Close sidebar' : 'Open sidebar')
            : (expanded ? 'Collapse sidebar' : 'Expand sidebar'));
        toggle.querySelector('i').className = expanded ? 'bx bx-chevron-left' : 'bx bx-chevron-right';
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        updateSidebarToggle();
    });
    mobile.addEventListener('change', updateSidebarToggle);
    updateSidebarToggle();

    if (!dropdown || !menuButton || !menu) return;

    function setMenuExpanded(expanded) {
        dropdown.classList.toggle('is-open', expanded);
        menuButton.setAttribute('aria-expanded', String(expanded));
        menu.inert = !expanded;
    }

    function closeFlyout() {
        window.clearTimeout(closeTimer);
        if (!menu.classList.contains('sidebar-flyout')) return;

        menu.classList.remove('sidebar-flyout');
        menu.removeAttribute('style');
        if (placeholder?.parentNode) {
            placeholder.parentNode.insertBefore(menu, placeholder);
            placeholder.remove();
        }
        placeholder = null;
        setMenuExpanded(false);
    }

    function positionFlyout() {
        if (!menu.classList.contains('sidebar-flyout')) return;

        const toggleRect = menuButton.getBoundingClientRect();
        const menuHeight = Math.min(menu.scrollHeight, window.innerHeight - 16);
        const top = Math.max(8, Math.min(toggleRect.top, window.innerHeight - menuHeight - 8));
        const left = Math.max(8, Math.min(
            sidebar.getBoundingClientRect().right + 8,
            window.innerWidth - 232
        ));
        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
    }

    function openFlyout(focusFirst = false) {
        if (!isCollapsedDesktop()) return;
        window.clearTimeout(closeTimer);
        if (!placeholder) {
            placeholder = document.createComment('Reports submenu position');
            menu.before(placeholder);
            document.body.appendChild(menu);
        }

        menu.classList.add('sidebar-flyout');
        setMenuExpanded(true);
        menu.style.visibility = 'hidden';
        positionFlyout();
        menu.style.visibility = '';
        if (focusFirst) menu.querySelector('a')?.focus();
    }

    function closeMenu() {
        closeFlyout();
        setMenuExpanded(false);
    }

    toggle.addEventListener('click', closeMenu);

    function scheduleFlyoutClose() {
        window.clearTimeout(closeTimer);
        closeTimer = window.setTimeout(() => {
            if (menu.matches(':hover') || menuButton.matches(':hover')
                || menu.contains(document.activeElement) || document.activeElement === menuButton) return;
            closeFlyout();
        }, 180);
    }

    menuButton.addEventListener('click', () => {
        if (isCollapsedDesktop()) {
            openFlyout();
            return;
        }

        if (mobile.matches && !sidebar.classList.contains('collapsed')) {
            sidebar.classList.add('collapsed');
            updateSidebarToggle();
        }
        setMenuExpanded(!dropdown.classList.contains('is-open'));
    });

    menuButton.addEventListener('pointerenter', event => {
        if (event.pointerType === 'mouse') openFlyout();
    });
    menuButton.addEventListener('pointerleave', scheduleFlyoutClose);
    menuButton.addEventListener('keydown', event => {
        if (event.key === 'ArrowDown' || (event.key === 'Tab' && !event.shiftKey)) {
            if (isCollapsedDesktop()) {
                event.preventDefault();
                openFlyout(true);
            }
        } else if (event.key === 'Escape' && menu.classList.contains('sidebar-flyout')) {
            event.preventDefault();
            closeFlyout();
            menuButton.focus();
        }
    });

    menu.addEventListener('pointerenter', () => window.clearTimeout(closeTimer));
    menu.addEventListener('pointerleave', scheduleFlyoutClose);
    menu.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menu.classList.contains('sidebar-flyout')) {
            event.preventDefault();
            closeFlyout();
            menuButton.focus();
        }
    });
    menu.addEventListener('focusout', event => {
        if (menu.classList.contains('sidebar-flyout')
            && event.relatedTarget !== menuButton
            && !menu.contains(event.relatedTarget)) {
            closeFlyout();
        }
    });
    menu.addEventListener('click', event => {
        if (event.target.closest('a[href]')) closeMenu();
    });
    document.addEventListener('pointerdown', event => {
        if (menu.classList.contains('sidebar-flyout')
            && !menu.contains(event.target) && !menuButton.contains(event.target)) {
            closeFlyout();
        }
    });
    document.addEventListener('focusin', event => {
        if (menu.classList.contains('sidebar-flyout')
            && !menu.contains(event.target) && !menuButton.contains(event.target)) {
            closeFlyout();
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menu.classList.contains('sidebar-flyout')) {
            event.preventDefault();
            closeFlyout();
            menuButton.focus();
        }
    });

    mobile.addEventListener('change', () => {
        closeMenu();
    });
    window.addEventListener('resize', positionFlyout, { passive: true });
    window.addEventListener('scroll', positionFlyout, { passive: true, capture: true });
    window.addEventListener('pageshow', closeMenu);
})();
