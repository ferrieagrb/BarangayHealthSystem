    (() => {
        const countMode = document.querySelector('input[name="count_mode_only"]');
        const overridden = ['include_summary', 'include_graphics']
            .map(name => document.querySelector(`input[name="${name}"]`))
            .filter(Boolean);
        if (!countMode) return;
        const sync = () => overridden.forEach(input => {
            if (countMode.checked) input.checked = false;
            input.disabled = countMode.checked;
        });
        countMode.addEventListener('change', sync);
        sync();
    })();
