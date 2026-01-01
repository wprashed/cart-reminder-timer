(function($){
let mounted=false;
let remaining=WCRT_DATA.remaining;

function format(sec){const m=Math.floor(sec/60); const s=sec%60; return m+':'+(s<10?'0':'')+s;}

function mount(){
    if(mounted) return;

    let container=null;
    if(WCRT_DATA.show_on==='cart'||WCRT_DATA.show_on==='both'){
        container=document.querySelector('.woocommerce-cart-form')||document.querySelector('.wc-block-cart');
    }
    if(WCRT_DATA.show_on==='checkout'||WCRT_DATA.show_on==='both'){
        container=container||document.querySelector('.wc-block-checkout');
    }
    if(!container) return;
    if(container.querySelector('.wcrt-timer')) return;

    const div=document.createElement('div');
    div.className='wcrt-timer';
    div.innerHTML=`⏳ ${WCRT_DATA.messages[WCRT_DATA.variant][WCRT_DATA.loggedIn?'user':'guest']} <strong><span></span></strong>`;
    if(WCRT_DATA.position==='top') container.prepend(div);
    else container.appendChild(div);

    countdown(div.querySelector('span'));
    mounted=true;
}

function countdown(target){
    const tick=()=>{
        if(remaining<=0){ target.closest('.wcrt-timer').innerHTML='⚠️ Cart timer expired.'; return; }
        target.textContent=format(remaining--);
        setTimeout(tick,1000);
    };
    tick();
}

$(document.body).on('updated_cart_totals wc_fragments_loaded added_to_cart removed_from_cart',function(){
    mounted=false; mount();
});
$(document).ready(mount);
})(jQuery);