<?php
if(!defined('ABSPATH')) exit;

// Reset timer on cart changes
add_action('woocommerce_cart_item_removed','wcrt_reset_timer');
add_action('woocommerce_cart_item_restored','wcrt_reset_timer');
add_action('woocommerce_cart_updated','wcrt_reset_timer');

function wcrt_reset_timer(){
    if(WC()->cart && !WC()->cart->is_empty() && WC()->cart->get_cart_contents_total()>=get_option('wcrt_min_cart',0)){
        WC()->session->set('wcrt_start',time());
        WC()->session->set('wcrt_expired',0);
        WC()->session->set('wcrt_variant', rand(0,1)?'A':'B');
    } else {
        WC()->session->__unset('wcrt_start');
        WC()->session->__unset('wcrt_expired');
        WC()->session->__unset('wcrt_variant');
    }
}

// Auto-apply coupon
add_action('woocommerce_before_calculate_totals',function($cart){
    if(!get_option('wcrt_coupon')) return;
    if(!WC()->cart || WC()->cart->is_empty()) return;

    $code='CRT-TIMER';
    if(!WC()->cart->has_discount($code)){
        WC()->cart->apply_coupon($code);
        WC()->cart->calculate_totals();
    }
});

// Refresh block cart on front-end
add_action('wp_footer',function(){
    ?>
    <script>
    jQuery(document).on('updated_cart_totals wc_fragments_loaded added_to_cart removed_from_cart',function(){
        if(window.wcBlocksCartEditor && typeof wcBlocksCartEditor.refreshCart==='function'){
            wcBlocksCartEditor.refreshCart();
        }
        jQuery('body').trigger('wc_fragment_refresh');
        jQuery('body').trigger('update_checkout');
    });
    </script>
    <?php
});

add_action('woocommerce_before_calculate_totals', function($cart){
    if(!get_option('wcrt_coupon')) return;
    if(!WC()->cart || WC()->cart->is_empty()) return;

    $code = 'CRT-TIMER';

    // Check if coupon exists
    $coupon_post = get_page_by_title($code, OBJECT, 'shop_coupon');
    if(!$coupon_post) return;

    // Update coupon amount based on admin setting
    $type = get_option('wcrt_coupon_type','percent'); // percent or fixed
    $amount = get_option('wcrt_coupon_amount',10);

    update_post_meta($coupon_post->ID, 'discount_type', $type);
    update_post_meta($coupon_post->ID, 'coupon_amount', $amount);

    // Apply coupon if not applied yet
    if(!WC()->cart->has_discount($code)){
        WC()->cart->apply_coupon($code);
        WC()->cart->calculate_totals();
    }
});