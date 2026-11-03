(function () {
    var style = document.createElement('style');
    style.textContent =
        '.ecard-preview-overlay{position:fixed;top:0;left:0;right:0;bottom:0;width:100vw;height:100vh;height:100dvh;box-sizing:border-box;z-index:10000;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(15,23,42,.82);cursor:zoom-out;animation:ecardPreviewIn .16s ease-out}' +
        '.ecard-preview-stage{position:relative;max-width:calc(100vw - 32px);max-height:calc(100vh - 32px);cursor:default;box-shadow:0 24px 70px rgba(0,0,0,.5);border-radius:14px;overflow:hidden}' +
        '.ecard-preview-stage .bheams-id-card{margin:0!important;box-shadow:none!important;cursor:default!important}' +
        '.ecard-preview-close{position:absolute;top:14px;right:18px;width:40px;height:40px;border:0;border-radius:50%;background:rgba(255,255,255,.95);color:#0f172a;font-size:26px;line-height:1;cursor:pointer;z-index:2}' +
        '.bheams-id-card[data-ecard-preview]{cursor:zoom-in;transition:transform .15s ease,box-shadow .15s ease}' +
        '.bheams-id-card[data-ecard-preview]:hover{box-shadow:0 12px 30px rgba(15,23,42,.22)!important}' +
        '.bheams-id-card[data-ecard-preview]:focus-visible{outline:3px solid #2563eb;outline-offset:3px}' +
        '@keyframes ecardPreviewIn{from{opacity:0}to{opacity:1}}' +
        '@media print{.ecard-preview-overlay{display:none!important}}';
    document.head.appendChild(style);

    var overlay = null;
    var lastFocus = null;

    function close() {
        if (!overlay) { return; }
        overlay.remove();
        overlay = null;
        document.removeEventListener('keydown', onKey);
        if (lastFocus) { lastFocus.focus(); }
    }

    function onKey(event) {
        if (event.key === 'Escape') { close(); }
    }

    function open(card) {
        if (overlay) { return; }
        lastFocus = card;

        var clone = card.cloneNode(true);
        clone.removeAttribute('data-ecard-preview');
        clone.removeAttribute('tabindex');
        clone.removeAttribute('role');
        clone.removeAttribute('aria-label');
        clone.querySelectorAll('[id]').forEach(function (node) { node.removeAttribute('id'); });

        var host = document.body;
        overlay = document.createElement('div');
        overlay.className = 'ecard-preview-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-label', 'Health card preview');

        var stage = document.createElement('div');
        stage.className = 'ecard-preview-stage';
        if (card.closest('.ecard-id-card-scroll')) { stage.classList.add('ecard-id-card-scroll'); }

        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'ecard-preview-close';
        closeButton.setAttribute('aria-label', 'Close preview');
        closeButton.innerHTML = '&times;';

        clone.style.zoom = '1';
        stage.appendChild(clone);
        stage.appendChild(closeButton);
        var scope = document.createElement('div');
        scope.className = 'bhw-ecard-page';
        scope.style.cssText = 'padding:0;margin:0;max-width:none;width:auto;overflow:visible;background:none';
        scope.appendChild(stage);
        overlay.appendChild(scope);
        host.appendChild(overlay);

        // Fit the card to the viewport, scaling up as far as it comfortably goes.
        var width = clone.offsetWidth || 1;
        var height = clone.offsetHeight || 1;
        var scale = Math.min((window.innerWidth - 32) / width, (window.innerHeight - 32) / height, 4);
        if (Math.abs(scale - 1) > 0.02) { clone.style.zoom = String(scale); }

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay || event.target === closeButton) { close(); }
        });
        document.addEventListener('keydown', onKey);
        closeButton.focus();
    }

    function init() {
        document.querySelectorAll('.bheams-id-card').forEach(function (card) {
            if (card.closest('.ecard-preview-overlay')) { return; }
            card.setAttribute('data-ecard-preview', '1');
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');
            card.setAttribute('aria-label', 'Open health card in full view');
            card.addEventListener('click', function () { open(card); });
            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    open(card);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
