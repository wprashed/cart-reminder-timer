<?php
if(!defined('ABSPATH')) exit;

class WCRT_Coupon {

    public static function init(){
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'apply_or_update_coupon']);
        add_action('woocommerce_cart_updated', [__CLASS__, 'maybe_reset_timer']);
        add_action('woocommerce_cart_item_removed', [__CLASS__, 'maybe_reset_timer']);
        add_action('woocommerce_cart_item_restored', [__CLASS__, 'maybe_reset_timer']);
    }

    public static function create_or_get_coupon(){
        if(!class_exists('WC_Coupon')) return null;

        $code = 'CRT-TIMER';

        $query = new WP_Query([
            'post_type' => 'shop_coupon',
            'title' => $code,
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);
        $coupon_post = $query->have_posts() ? $query->posts[0] : null;
        wp_reset_postdata();

        if(!$coupon_post){
            $coupon_id = wp_insert_post([
                'post_title' => $code,
                'post_type' => 'shop_coupon',
                'post_status' => 'publish',
            ]);
            $coupon_post = get_post($coupon_id);
        }

        return new WC_Coupon($coupon_post->ID);
    }

    public static function apply_or_update_coupon($cart){
        if(!get_option('wcrt_coupon')) return;
        if(!$cart || $cart->is_empty()) return;

        $coupon = self::create_or_get_coupon();
        if(!$coupon) return;

        $type = get_option('wcrt_coupon_type', 'percent');
        $amount = floatval(get_option('wcrt_coupon_amount', 10));
        $usage_limit = intval(get_option('wcrt_max_usage', 1));

        $coupon->set_discount_type($type);
        $coupon->set_amount($amount);
        $coupon->set_individual_use(true);
        $coupon->set_usage_limit($usage_limit);
        $coupon->save();

        $code = $coupon->get_code();

        if(!WC()->session->get('wcrt_coupon_applied')){
            if($cart->has_discount($code)){
                $cart->remove_coupon($code);
            }
            $cart->apply_coupon($code);
            WC()->session->set('wcrt_coupon_applied', true);
        }
    }

    public static function maybe_reset_timer(){
        if(!WC()->cart) return;

        if(WC()->cart->is_empty()){
            WC()->session->__unset('wcrt_start');
            WC()->session->__unset('wcrt_variant');
            WC()->session->__unset('wcrt_coupon_applied');
        } else {
            WC()->session->set('wcrt_start', time());
            WC()->session->set('wcrt_variant', rand(0, 1) ? 'A' : 'B');
            WC()->session->__unset('wcrt_coupon_applied');
        }
    }
}

WCRT_Coupon::init();
