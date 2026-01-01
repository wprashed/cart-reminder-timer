document.addEventListener('DOMContentLoaded', function () {
    const timerBox = document.getElementById('woo-cart-timer');
    if (!timerBox) return;

    let remaining = parseInt(timerBox.dataset.remaining, 10);
    const output = document.getElementById('woo-cart-timer-countdown');

    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    }

    function tick() {
        if (remaining <= 0) {
            timerBox.innerHTML = '⚠️ Your price reservation has expired.';
            return;
        }

        output.textContent = formatTime(remaining);
        remaining--;
        setTimeout(tick, 1000);
    }

    tick();
});