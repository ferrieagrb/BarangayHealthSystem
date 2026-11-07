(function () {
    'use strict';

    var loader = document.getElementById('global-loader');
    if (!loader || window.__globalLoaderReady) return;
    window.__globalLoaderReady = true;

    var textEl = loader.querySelector('.loader-status-text');
    var DEFAULT_TEXT = 'Processing, please wait...';
    var MAX_MANUAL_MS = 20000;

    var manual = false;
    var booting = loader.classList.contains('gl-boot');
    var pending = 0;
    var suppressUntil = 0;
    var manualTimer = null;

    var supportsPopover = typeof loader.showPopover === 'function';

    function attach() {
        if (document.body && loader.parentNode !== document.body) {
            document.body.appendChild(loader);
        }
        if (supportsPopover) loader.setAttribute('popover', 'manual');
    }

    function popoverOpen() {
        try { return loader.matches(':popover-open'); } catch (e) { return false; }
    }

    // Re-showing the popover moves it to the top of the top layer, above any open <dialog>.
    function raise() {
        if (!supportsPopover) return;
        try {
            if (popoverOpen()) loader.hidePopover();
            loader.showPopover();
        } catch (e) {}
    }

    function render() {
        var visible = manual || pending > 0 || booting;
        if (visible) {
            loader.classList.add('active');
            raise();
        } else {
            loader.classList.remove('active');
            if (supportsPopover && popoverOpen()) {
                try { loader.hidePopover(); } catch (e) {}
            }
        }
    }

    function setText(message) {
        if (textEl) textEl.textContent = message || DEFAULT_TEXT;
    }

    window.showLoader = function (message) {
        booting = false;
        loader.classList.remove('gl-boot');
        manual = true;
        setText(message);
        window.clearTimeout(manualTimer);
        manualTimer = window.setTimeout(window.hideLoader, MAX_MANUAL_MS);
        render();
    };

    window.hideLoader = function () {
        manual = false;
        booting = false;
        loader.classList.remove('gl-boot');
        window.clearTimeout(manualTimer);
        manualTimer = null;
        render();
    };

    // Use before navigations that will not replace the page (downloads, mailto, etc).
    window.loaderSuppress = function (ms) {
        suppressUntil = Date.now() + (ms || 1500);
    };

    function suppressed() {
        return Date.now() < suppressUntil;
    }

    function skipLink(link, e) {
        if (!link || !link.href) return true;
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return true;
        if (link.hasAttribute('download') || link.dataset.noLoader !== undefined) return true;
        var target = link.getAttribute('target');
        if (target && target !== '_self') return true;
        if (!/^https?:$/.test(link.protocol)) return true;
        var url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return true;
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash !== '') return true;
        return false;
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('a');
        if (!link) return;
        if (skipLink(link, e)) {
            window.loaderSuppress(1500);
            return;
        }
        // Handlers that call preventDefault later in the same click still win.
        window.setTimeout(function () {
            if (!e.defaultPrevented) window.showLoader();
        }, 0);
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.dataset.noLoader !== undefined) {
            window.loaderSuppress(1500);
            return;
        }
        if (form.getAttribute('target') === '_blank') return;
        window.setTimeout(function () {
            if (!e.defaultPrevented) window.showLoader();
        }, 0);
    });

    // Covers form.submit() and window.location changes, which do not fire click/submit events.
    window.addEventListener('beforeunload', function () {
        if (suppressed()) return;
        window.showLoader();
    });

    window.addEventListener('pageshow', function () { window.hideLoader(); });
    window.addEventListener('load', function () { window.hideLoader(); });
    window.setTimeout(function () { if (booting) window.hideLoader(); }, 6000);

    var nativeFetch = window.fetch;
    if (typeof nativeFetch === 'function') {
        window.fetch = function (input, init) {
            var options = init || {};
            var method = String(options.method || (input && input.method) || 'GET').toUpperCase();
            var show = options.noLoader ? false : (method !== 'GET' && method !== 'HEAD') || options.loader === true;
            if (!show) return nativeFetch.apply(this, arguments);

            pending += 1;
            render();
            var done = function () {
                pending = Math.max(0, pending - 1);
                render();
            };
            var clean = Object.assign({}, options);
            delete clean.loader;
            delete clean.noLoader;
            var promise = nativeFetch.call(this, input, clean);
            promise.then(done, done);
            return promise;
        };
    }

    if (document.body) attach();
    else document.addEventListener('DOMContentLoaded', attach);

    if (booting) render();
})();
