<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Woo Cart Reminder Timer Coupon Handler
 * Applies pre-created coupon on expiry
 * Works for Classic + Block carts
 */

add_action('wp', function(){

    // Only run on front-end
    if( is_admin() || !WC()->cart || WC()->cart->is_empty() ) return;

    // Get cart timer start
    $start = WC()->session->get('wcrt_start');
    if( !$start ) return;

    // Calculate remaining time
    $duration = get_option('wcrt_duration',15) * 60;
    $remaining = $duration - ( time() - $start );
    if( $remaining > 0 ) return;

    // Already expired? Stop
    if( WC()->session->get('wcrt_expired') ) return;
    WC()->session->set('wcrt_expired', 1);

    // If auto-coupon enabled
    if( get_option('wcrt_coupon') ){

        $code = 'CRT-TIMER'; // pre-created coupon code

        // Ensure coupon exists (safety)
        if( !post_exists($code,'','shop_coupon') ){
            $post_id = wp_insert_post([
                'post_title'   => $code,
                'post_content' => 'Cart Reminder Timer coupon',
                'post_status'  => 'publish',
                'post_type'    => 'shop_coupon'
            ]);
            update_post_meta($post_id,'discount_type','percent');
            update_post_meta($post_id,'coupon_amount', get_option('wcrt_coupon_amount',10) );
            update_post_meta($post_id,'individual_use','yes');
            update_post_meta($post_id,'usage_limit',1);
            update_post_meta($post_id,'apply_before_tax','yes');
        }

        // Apply coupon if not already applied
        if( !WC()->cart->has_discount( $code ) ){
            WC()->cart->apply_coupon( $code );
        }

        // Refresh block cart / checkout so discount shows immediately
        add_action('wp_footer', function(){
            ?>
            <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function(){
                jQuery('body').trigger('update_checkout');
                jQuery('body').trigger('wc_fragment_refresh');
            });
            </script>
            <?php
        });

    } elseif( get_option('wcrt_autoclear',0) ){
        // Optional auto-clear cart if coupon not used
        WC()->cart->empty_cart();
    }

});