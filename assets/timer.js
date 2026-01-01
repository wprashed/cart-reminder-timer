const { subscribe, select } = wp.data;

function formatTime(seconds) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
}

function getMessage() {
    const type = WooCartTimer.isLoggedIn ? 'user' : 'guest';
    return WooCartTimer.messages[WooCartTimer.variant][type];
}

function mountTimer() {
    const targets = document.querySelectorAll(
        '.wc-block-cart-items, .wc-block-checkout, .wc-block-mini-cart'
    );

    if (!targets.length) return;

    targets.forEach(container => {
        if (container.querySelector('.woo-cart-timer')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'woo-cart-timer';
        wrapper.innerHTML = `
            ⏳ ${getMessage()}
            <strong><span class="woo-cart-timer-countdown"></span></strong>
        `;

        container.prepend(wrapper);
        startCountdown(wrapper.querySelector('.woo-cart-timer-countdown'));
    });
}

function startCountdown(output) {
    let remaining = WooCartTimer.remaining;

    const tick = () => {
        if (remaining <= 0) {
            output.closest('.woo-cart-timer').innerHTML =
                '⚠️ Cart reservation expired.';
            return;
        }
        output.textContent = formatTime(remaining--);
        setTimeout(tick, 1000);
    };

    tick();
}

subscribe(() => {
    const cart = select('wc/store/cart').getCartData();
    if (!cart || !cart.items?.length) return;
    mountTimer();
});