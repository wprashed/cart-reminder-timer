<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Reset timer on cart change
add_action('woocommerce_cart_item_removed', 'wcrt_reset_timer');
add_action('woocommerce_cart_item_restored', 'wcrt_reset_timer');
add_action('woocommerce_cart_updated', 'wcrt_reset_timer');

function wcrt_reset_timer(){
    if(WC()->cart && !WC()->cart->is_empty()){
        WC()->session->set('wcrt_start', time());
        WC()->session->set('wcrt_expired', 0);
        WC()->session->set('wcrt_variant', rand(0,1)?'A':'B');
    } else {
        WC()->session->__unset('wcrt_start');
        WC()->session->__unset('wcrt_expired');
        WC()->session->__unset('wcrt_variant');
    }
}

// Apply coupon automatically
add_action('woocommerce_before_calculate_totals', function($cart){
    if(!WC()->session || !get_option('wcrt_coupon') ) return;
    if(!WC()->cart || WC()->cart->is_empty()) return;

    $code = 'CRT-TIMER';
    if(!WC()->cart->has_discount($code)){
        WC()->cart->apply_coupon($code);
        WC()->cart->calculate_totals();
    }
});

// Refresh block cart
add_action('wp_footer', function(){
    ?>
    <script>
    jQuery(document).on('updated_cart_totals wc_fragments_loaded added_to_cart removed_from_cart', function(){
        if(window.wcBlocksCartEditor && typeof wcBlocksCartEditor.refreshCart==='function'){
            wcBlocksCartEditor.refreshCart();
        }
        jQuery('body').trigger('wc_fragment_refresh');
        jQuery('body').trigger('update_checkout');
    });
    </script>
    <?php
});