<?php
/**
 * Plugin Name: WooCommerce Cart Reminder Timer
 * Description: Countdown timer, auto-coupon, A/B testing, Classic + Block cart compatible, fully optimized.
 * Version: 3.0
 * Author: Rashed Hossain
 * Text Domain: wcrt
 */

if (!defined('ABSPATH')) exit;

// Include classes
require_once plugin_dir_path(__FILE__).'includes/class-wcrt-admin.php';
require_once plugin_dir_path(__FILE__).'includes/class-wcrt-timer.php';
require_once plugin_dir_path(__FILE__).'includes/class-wcrt-coupon.php';

// Create default coupon on plugin activation
register_activation_hook(__FILE__, function(){
    if (!class_exists('WC_Coupon')) return;
    WCRT_Coupon::create_or_update_coupon();
});

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_script('wcrt-timer', plugins_url('assets/timer.js',__FILE__), ['jquery'], '3.0', true);
    wp_enqueue_style('wcrt-timer-css', plugins_url('assets/timer.css',__FILE__));
});