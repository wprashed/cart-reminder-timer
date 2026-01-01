<?php
/**
 * Plugin Name: Woo Cart Reminder Timer
 * Description: Countdown urgency timer for WooCommerce cart & checkout (Classic + Blocks). Admin panel, expiry → coupon, A/B tracking.
 * Version: 4.0.0
 * Author: Rashed Hossain
 */

if(!defined('ABSPATH')) exit;

// Load admin settings and coupon handler
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/coupon-handler.php';

register_activation_hook(__FILE__, function(){
    $code = 'CRT-TIMER';
    if(!post_exists($code,'','shop_coupon')){
        $post_id = wp_insert_post([
            'post_title'=>$code,
            'post_content'=>'Cart Reminder Timer coupon',
            'post_status'=>'publish',
            'post_type'=>'shop_coupon'
        ]);
        update_post_meta($post_id,'discount_type','percent');
        update_post_meta($post_id,'coupon_amount', get_option('wcrt_coupon_amount',10));
        update_post_meta($post_id,'individual_use','yes');
        update_post_meta($post_id,'usage_limit',1);
        update_post_meta($post_id,'apply_before_tax','yes');
    }
});

class Woo_Cart_Reminder_Timer {

    const DEFAULT_MINUTES = 15;

    public function __construct() {
        add_action('woocommerce_add_to_cart', [$this,'start_timer']);
        add_action('woocommerce_cart_is_empty', [$this,'clear_timer']);
        add_action('wp', [$this,'maybe_clear_cart']);

        // Classic Cart / Checkout placeholders
        add_action('woocommerce_before_cart', [$this,'render_placeholder']);
        add_action('woocommerce_before_checkout_form', [$this,'render_placeholder']);

        // Assets
        add_action('wp_enqueue_scripts', [$this,'enqueue_assets']);

        // Track A/B variant per order
        add_action('woocommerce_checkout_create_order', [$this,'track_variant_order'],10,2);
    }

    /* ---------------- TIMER LOGIC ---------------- */

    public function start_timer(){
        if(!WC()->session->get('wcrt_start')){
            WC()->session->set('wcrt_start', time());
            WC()->session->set('wcrt_variant', rand(0,1)?'A':'B');
        }
    }

    public function clear_timer(){
        if( WC()->session ){
            WC()->session->__unset('wcrt_start');
            WC()->session->__unset('wcrt_variant');
            WC()->session->__unset('wcrt_expired');
        }
    }

    private function remaining(){
        if( !WC()->cart || !WC()->session ) return 0; // safe check
        $start = WC()->session->get('wcrt_start');
        if(!$start) return 0;
        $duration = get_option('wcrt_duration',self::DEFAULT_MINUTES)*60;
        return max(0, $duration - (time() - $start));
    }

    public function maybe_clear_cart(){
        // Only front-end and cart exists
        if( is_admin() || !WC()->cart || WC()->cart->is_empty() ) return;

        $remaining = $this->remaining();
        if($remaining > 0) return;

        if(get_option('wcrt_autoclear',0)){
            WC()->cart->empty_cart();
            $this->clear_timer();
        }
    }

    /* ---------------- PLACEHOLDER ---------------- */

    public function render_placeholder(){
        if(!WC()->cart || WC()->cart->is_empty()) return;
        echo '<div id="wcrt-placeholder"></div>';
    }

    /* ---------------- ASSETS ---------------- */

    public function enqueue_assets(){
        if( is_admin() || !WC()->cart || WC()->cart->is_empty() ) return;

        $remaining = $this->remaining();
        if($remaining <= 0) return;

        wp_enqueue_script(
            'wcrt-timer',
            plugin_dir_url(__FILE__).'assets/timer.js',
            ['jquery','wp-data'],
            '4.0.0',
            true
        );

        wp_enqueue_style(
            'wcrt-style',
            plugin_dir_url(__FILE__).'assets/timer.css',
            [],
            '4.0.0'
        );

        wp_localize_script('wcrt-timer','WCRT_DATA',[
            'remaining' => $remaining,
            'variant'   => WC()->session->get('wcrt_variant','A'),
            'loggedIn'  => is_user_logged_in(),
            'messages'  => [
                'A'=>[
                    'guest'=>get_option('wcrt_msg_a_guest','Prices are reserved for'),
                    'user'=>get_option('wcrt_msg_a_user','Your exclusive price is reserved for')
                ],
                'B'=>[
                    'guest'=>get_option('wcrt_msg_b_guest','Hurry! Cart expires in'),
                    'user'=>get_option('wcrt_msg_b_user','Complete checkout within')
                ]
            ]
        ]);
    }

    /* ---------------- TRACK VARIANT ---------------- */

    public function track_variant_order($order,$data){
        $variant = WC()->session->get('wcrt_variant','A');
        $order->update_meta_data('_crt_variant',$variant);
    }
}

new Woo_Cart_Reminder_Timer();