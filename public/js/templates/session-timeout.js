document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('session-timeout-modal');
    const sessionLimit = Number(modal.dataset.sessionLifetime) * 60 * 1000;
    let timeoutHandle = setTimeout(triggerTimeout, sessionLimit);

    function resetTimer() {
        clearTimeout(timeoutHandle);
        timeoutHandle = setTimeout(triggerTimeout, sessionLimit);
    }

    function triggerTimeout() {
        modal.style.display = 'flex';
    }

    window.addEventListener('mousemove', resetTimer);
    window.addEventListener('keypress', resetTimer);
    window.addEventListener('click', resetTimer);
    window.addEventListener('scroll', resetTimer);
});
