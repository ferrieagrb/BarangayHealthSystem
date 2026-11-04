(function () {
    var EYE = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    var EYE_OFF = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.9 17.9A10.9 10.9 0 0 1 12 19c-6.4 0-10-7-10-7a18.5 18.5 0 0 1 5.1-5.9M9.9 4.2A10.4 10.4 0 0 1 12 4c6.4 0 10 7 10 7a18.6 18.6 0 0 1-2.2 3.2M1 1l22 22"></path><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"></path></svg>';

    var style = document.createElement('style');
    style.textContent =
        '.pw-toggle-wrap{position:relative;display:block;width:100%}' +
        '.pw-toggle-wrap>input{width:100%;box-sizing:border-box;padding-right:44px!important}' +
        '.pw-toggle-btn{all:unset!important;box-sizing:border-box!important;position:absolute!important;top:50%!important;right:10px!important;transform:translateY(-50%)!important;display:flex!important;align-items:center;justify-content:center;width:28px!important;height:28px!important;border:0!important;border-radius:5px!important;background:transparent!important;box-shadow:none!important;color:#2563eb!important;cursor:pointer;z-index:3}' +
        '.pw-toggle-btn:hover,.pw-toggle-btn:active,.pw-toggle-btn:focus{background:transparent!important;border:0!important;box-shadow:none!important;outline:0!important}' +
        '.pw-toggle-btn:hover{color:#1d4ed8!important}' +
        '.pw-toggle-btn:focus-visible{outline:2px solid #2563eb!important;outline-offset:1px}' +
        '.pw-toggle-btn svg{width:19px;height:19px}';
    document.head.appendChild(style);

    // Inline !important beats any page stylesheet, so the button never regains a border.
    function lockStyle(button) {
        var props = {
            'all': 'unset', 'box-sizing': 'border-box', 'position': 'absolute', 'top': '50%', 'right': '10px',
            'transform': 'translateY(-50%)', 'display': 'flex', 'align-items': 'center', 'justify-content': 'center',
            'width': '28px', 'height': '28px', 'padding': '0', 'margin': '0', 'border': '0', 'border-radius': '5px',
            'background': 'transparent', 'box-shadow': 'none', 'outline': '0', 'color': '#2563eb', 'cursor': 'pointer', 'z-index': '3'
        };
        Object.keys(props).forEach(function (key) { button.style.setProperty(key, props[key], 'important'); });
    }

    function enhance(input) {
        if (input.dataset.pwToggle || input.hasAttribute('data-no-toggle')) {
            return;
        }
        var parent = input.parentElement;
        if (!parent || parent.querySelector('.toggle-password, .pw-toggle-btn') || parent.closest('.password-input-wrap')) {
            return;
        }
        input.dataset.pwToggle = '1';

        var wrap = document.createElement('span');
        wrap.className = 'pw-toggle-wrap';
        parent.insertBefore(wrap, input);
        // Keep absolutely positioned decorations (e.g. a lock icon) above the new wrapper.
        Array.prototype.forEach.call(parent.children, function (sibling) {
            if (sibling !== wrap && window.getComputedStyle(sibling).position === 'absolute') {
                sibling.style.zIndex = '2';
                sibling.style.pointerEvents = 'none';
            }
        });
        // Move the input's outer margin to the wrapper so the icon centres on the input itself.
        var margin = window.getComputedStyle(input).margin;
        wrap.style.setProperty('margin', margin, 'important');
        input.style.setProperty('margin', '0', 'important');
        wrap.appendChild(input);

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'pw-toggle-btn password-visibility-toggle';
        button.setAttribute('aria-label', 'Show password');
        button.setAttribute('aria-pressed', 'false');
        button.innerHTML = EYE_OFF;
        wrap.appendChild(button);
        lockStyle(button);

        button.addEventListener('click', function () {
            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.innerHTML = reveal ? EYE : EYE_OFF;
            button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            button.setAttribute('aria-pressed', reveal ? 'true' : 'false');
        });
    }

    function scan(root) {
        (root || document).querySelectorAll('input[type="password"]').forEach(enhance);
    }

    function init() {
        scan();
        new MutationObserver(function () { scan(); })
            .observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
