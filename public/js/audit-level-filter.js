document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-audit-level-filter]').forEach(function (form) {
        form.querySelector('select').addEventListener('change', function () {
            const activeTab = document.querySelector('[data-audit-tabs] .tab.active');
            if (activeTab) {
                let input = form.querySelector('input[name="tab"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tab';
                    form.appendChild(input);
                }
                input.value = activeTab.dataset.tab;
            }
            form.requestSubmit();
        });
    });
});
