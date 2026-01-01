<?php
if(!defined('ABSPATH')) exit;

class WCRT_Coupon {

    public static function init(){
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'apply_coupon']);
        add_action('woocommerce_cart_updated', [__CLASS__, 'maybe_reset_timer']);
    }

    public static function create_or_update_coupon(){
        $code = 'CRT-TIMER';
        $coupon = new WC_Coupon($code);

        $amount = floatval(get_option('wcrt_coupon_amount',10));
        $type = get_option('wcrt_coupon_type','percent');

        if(!$coupon->get_id()){
            $coupon_id = wp_insert_post([
                'post_title'=>$code,
                'post_type'=>'shop_coupon',
                'post_status'=>'publish',
            ]);
            $coupon = new WC_Coupon($coupon_id);
        }

        $coupon->set_discount_type($type);
        $coupon->set_amount($amount);
        $coupon->set_individual_use(true);
        $coupon->set_usage_limit(intval(get_option('wcrt_max_usage',1)));
        $coupon->save();
    }

    public static function apply_coupon($cart){
        if(!get_option('wcrt_coupon')) return;
        if(!$cart || $cart->is_empty()) return;

        self::create_or_update_coupon();
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