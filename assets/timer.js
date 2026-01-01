document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.woo-cart-timer').forEach(timer => {
        let remaining = parseInt(timer.dataset.remaining, 10);
        const output = timer.querySelector('.woo-cart-timer-countdown');

        const format = s => {
            const m = Math.floor(s / 60);
            const r = s % 60;
            return `${m}:${r < 10 ? '0' : ''}${r}`;
        };

        const tick = () => {
            if (remaining <= 0) {
                timer.innerHTML = '⚠️ Cart reservation expired.';
                return;
            }
            output.textContent = format(remaining--);
            setTimeout(tick, 1000);
        };

        tick();
    });
});