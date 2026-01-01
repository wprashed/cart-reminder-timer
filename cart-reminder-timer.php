<?php
/**
 * Plugin Name: WooCommerce Cart Reminder Timer
 * Description: Adds countdown timer to cart/checkout, auto-applies coupon on expiry, works for Classic & Block carts, with A/B testing.
 * Version: 1.0
 * Author: Rashed Hossain
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Include files
require_once plugin_dir_path(__FILE__).'includes/admin.php';
require_once plugin_dir_path(__FILE__).'includes/coupon-handler.php';
require_once plugin_dir_path(__FILE__).'includes/timer-handler.php';

// Enqueue scripts & styles
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_script('wcrt-timer', plugins_url('assets/timer.js',__FILE__), ['jquery'], '1.0', true);
    wp_enqueue_style('wcrt-timer-css', plugins_url('assets/timer.css',__FILE__));
});

// Plugin activation: create default coupon
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
        update_post_meta($post_id,'coupon_amount',10);
        update_post_meta($post_id,'individual_use','yes');
        update_post_meta($post_id,'usage_limit',1);
        update_post_meta($post_id,'apply_before_tax','yes');
    }
});