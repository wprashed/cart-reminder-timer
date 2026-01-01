(function($){
    let mounted = false;
    let remaining = WCRT_DATA.remaining;

    function format(sec){ const m=Math.floor(sec/60); const s=sec%60; return `${m}:${s<10?'0':''}${s}`; }
    function message(){ const t=WCRT_DATA.loggedIn?'user':'guest'; return WCRT_DATA.messages[WCRT_DATA.variant][t]; }

    function mount(){
        if(mounted) return;

        // Classic placeholder
        let placeholder = document.querySelector('#wcrt-placeholder');
        if(placeholder){
            const box = document.createElement('div');
            box.className='wcrt-timer';
            box.innerHTML=`⏳ ${message()} <strong><span></span></strong>`;
            placeholder.replaceWith(box);
            countdown(box.querySelector('span'));
            mounted=true;
            return;
        }

        // Block cart
        let blockCart = document.querySelector('.wc-block-cart');
        if(blockCart && !blockCart.querySelector('.wcrt-timer')){
            const box = document.createElement('div');
            box.className='wcrt-timer';
            box.innerHTML=`⏳ ${message()} <strong><span></span></strong>`;
            blockCart.prepend(box);
            countdown(box.querySelector('span'));
            mounted=true;
            return;
        }

        // Block checkout
        let blockCheckout = document.querySelector('.wc-block-checkout');
        if(blockCheckout && !blockCheckout.querySelector('.wcrt-timer')){
            const box = document.createElement('div');
            box.className='wcrt-timer';
            box.innerHTML=`⏳ ${message()} <strong><span></span></strong>`;
            blockCheckout.prepend(box);
            countdown(box.querySelector('span'));
            mounted=true;
            return;
        }
    }

    function countdown(target){
        const tick=()=>{
            if(remaining<=0){ target.closest('.wcrt-timer').innerHTML='⚠️ Cart reservation expired.'; return; }
            target.textContent=format(remaining--); setTimeout(tick,1000);
        };
        tick();
    }

    $(document.body).on('updated_cart_totals wc_fragments_loaded', mount);
    if(window.wp && wp.data){ 
        wp.data.subscribe(()=>{
            const cart=wp.data.select('wc/store/cart')?.getCartData();
            if(cart && cart.items?.length) mount(); 
        }); 
    }
    document.addEventListener('DOMContentLoaded',mount);
})(jQuery);