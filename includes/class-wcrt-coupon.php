<?php
if(!defined('ABSPATH')) exit;

class WCRT_Coupon {

    public static function init(){
        // Apply coupon dynamically
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'apply_coupon']);

        // Reset timer on cart changes
        add_action('woocommerce_cart_updated', [__CLASS__, 'maybe_reset_timer']);
        add_action('woocommerce_cart_item_removed', [__CLASS__, 'maybe_reset_timer']);
        add_action('woocommerce_cart_item_restored', [__CLASS__, 'maybe_reset_timer']);
    }

    // Always ensure coupon exists and is up to date
    public static function create_or_update_coupon(){
        if(!class_exists('WC_Coupon')) return;

        $code = 'CRT-TIMER';
        $coupon_post = get_page_by_title($code, OBJECT, 'shop_coupon');

        if(!$coupon_post){ // create coupon if not exists
            $coupon_id = wp_insert_post([
                'post_title' => $code,
                'post_type' => 'shop_coupon',
                'post_status' => 'publish',
            ]);
            $coupon_post = get_post($coupon_id);
        }

        $coupon = new WC_Coupon($coupon_post->ID);

        // Update coupon settings dynamically
        $amount = floatval(get_option('wcrt_coupon_amount', 10));
        $type = get_option('wcrt_coupon_type', 'percent');

        $coupon->set_discount_type($type);
        $coupon->set_amount($amount);
        $coupon->set_individual_use(true);
        $coupon->set_usage_limit(intval(get_option('wcrt_max_usage', 1)));
        $coupon->save();
    }

    public static function apply_coupon($cart){
        if(!get_option('wcrt_coupon')) return;
        if(!$cart || $cart->is_empty()) return;

        self::create_or_update_coupon(); // ensure coupon exists

        $code = 'CRT-TIMER';
        if(!$cart->has_discount($code)){
            $cart->apply_coupon($code);
            $cart->calculate_totals();
        }
    }

    public static function maybe_reset_timer(){
        if(!WC()->cart) return;
        if(WC()->cart->is_empty()){
            WC()->session->__unset('wcrt_start');
            WC()->session->__unset('wcrt_variant');
        } else {
            WC()->session->set('wcrt_start', time());
            WC()->session->set('wcrt_variant', rand(0,1)?'A':'B');
        }
    }
}

WCRT_Coupon::init();