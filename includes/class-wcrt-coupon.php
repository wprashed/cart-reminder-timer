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
        
        $coupon_id = get_option('wcrt_coupon_id');
        
        if($coupon_id && get_post_type($coupon_id) === 'shop_coupon'){
            $coupon = new WC_Coupon($coupon_id);
            if($coupon->get_code() === $code){
                return $coupon;
            }
        }
        
        // Search by coupon code directly using WC_Data_Store
        $data_store = WC_Data_Store::load('coupon');
        $coupon_id = $data_store->get_coupon_id_by_code($code);
        
        if($coupon_id){
            update_option('wcrt_coupon_id', $coupon_id);
            return new WC_Coupon($coupon_id);
        }

        // Create new coupon if not found
        $coupon_id = wp_insert_post([
            'post_title' => $code,
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
        ]);
        
        if(is_wp_error($coupon_id)) return null;
        
        update_option('wcrt_coupon_id', $coupon_id);
        return new WC_Coupon($coupon_id);
    }

    public static function apply_or_update_coupon($cart){
        if(!get_option('wcrt_coupon')) return;
        if(!$cart || $cart->is_empty()) return;
        
        if($cart->get_subtotal() < floatval(get_option('wcrt_min_cart', 0))) return;

        $coupon = self::create_or_get_coupon();
        if(!$coupon) return;

        $type = get_option('wcrt_coupon_type','percent');
        $amount = floatval(get_option('wcrt_coupon_amount',10));
        $usage_limit = intval(get_option('wcrt_max_usage',1));

        $coupon->set_discount_type($type);
        $coupon->set_amount($amount);
        $coupon->set_individual_use(true);
        $coupon->set_usage_limit($usage_limit);
        $coupon->set_usage_limit_per_user($usage_limit);
        $coupon->save();

        $code = $coupon->get_code();

        $session_key = 'wcrt_coupon_applied_' . md5($code);
        if(!WC()->session->get($session_key)){
            if(!$cart->has_discount($code)){
                $cart->apply_coupon($code);
            }
            WC()->session->set($session_key, true);
        }
    }

    public static function maybe_reset_timer(){
        if(!WC()->cart) return;

        if(WC()->cart->is_empty()){
            WC()->session->__unset('wcrt_start');
            WC()->session->__unset('wcrt_variant');
            $session_key = 'wcrt_coupon_applied_' . md5('CRT-TIMER');
            WC()->session->__unset($session_key);
        } else {
            WC()->session->set('wcrt_start', time());
            if(!WC()->session->get('wcrt_variant')){
                WC()->session->set('wcrt_variant', rand(0,1)?'A':'B');
            }
        }
    }
}

WCRT_Coupon::init();