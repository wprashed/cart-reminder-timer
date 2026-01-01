<?php
/**
 * Plugin Name: Woo Cart Reminder Timer
 * Description: Countdown urgency timer for WooCommerce cart & checkout (Classic + Blocks). Admin panel for messages & duration, expiry → coupon, A/B tracking.
 * Version: 3.0.0
 * Author: Rashed Hossain
 */

if(!defined('ABSPATH')) exit;

// Load admin settings and coupon handler
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/coupon-handler.php';

class Woo_Cart_Reminder_Timer {

    const DEFAULT_MINUTES = 15;

    public function __construct() {
        add_action('woocommerce_add_to_cart', [$this,'start_timer']);
        add_action('woocommerce_cart_is_empty', [$this,'clear_timer']);
        add_action('wp', [$this,'maybe_clear_cart']);

        // Classic Cart / Checkout placeholders
        add_action('woocommerce_before_cart', [$this,'render_placeholder']);
        add_action('woocommerce_before_checkout_form', [$this,'render_placeholder']);

        // Fallback for Blocks
        add_action('wp_footer', [$this,'render_placeholder'], 5);

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
        WC()->session->__unset('wcrt_start');
        WC()->session->__unset('wcrt_variant');
        WC()->session->__unset('wcrt_expired');
    }

    private function remaining(){
        $start = WC()->session->get('wcrt_start');
        if(!$start) return 0;
        $duration = get_option('wcrt_duration',self::DEFAULT_MINUTES)*60;
        return max(0, $duration - (time() - $start));
    }

    public function maybe_clear_cart(){
        if($this->remaining()>0) return;
        if(WC()->cart && !WC()->cart->is_empty()){
            if(get_option('wcrt_autoclear',0)){
                WC()->cart->empty_cart();
                $this->clear_timer();
            }
        }
    }

    /* ---------------- PLACEHOLDER ---------------- */

    public function render_placeholder(){
        if(!WC()->cart || WC()->cart->is_empty()) return;
        if(did_action('wcrt_placeholder_rendered')) return;
        do_action('wcrt_placeholder_rendered');
        echo '<div id="wcrt-placeholder" data-wcrt="1"></div>';
    }

    /* ---------------- ASSETS ---------------- */

    public function enqueue_assets(){
        if(!WC()->cart || WC()->cart->is_empty()) return;

        $remaining = $this->remaining();
        if($remaining<=0) return;

        wp_enqueue_script(
            'wcrt-timer',
            plugin_dir_url(__FILE__).'assets/timer.js',
            ['jquery','wp-data'],
            '3.0.0',
            true
        );

        wp_enqueue_style(
            'wcrt-style',
            plugin_dir_url(__FILE__).'assets/timer.css',
            [],
            '3.0.0'
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