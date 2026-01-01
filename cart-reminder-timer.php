<?php
/**
 * Plugin Name: WooCommerce Cart Reminder Timer
 * Description: Countdown timer, auto-coupon, A/B testing, Classic + Block cart compatible, fully optimized.
 * Version: 2.0
 * Author: Rashed Hossain
 */

if(!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__).'includes/admin.php';
require_once plugin_dir_path(__FILE__).'includes/coupon-handler.php';
require_once plugin_dir_path(__FILE__).'includes/timer-handler.php';

// Enqueue scripts & styles
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_script('wcrt-timer', plugins_url('assets/timer.js',__FILE__), ['jquery'], '2.0', true);
    wp_enqueue_style('wcrt-timer-css', plugins_url('assets/timer.css',__FILE__));
});