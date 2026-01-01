<?php
/**
 * Plugin Name: WooCommerce Cart Reminder Timer
 * Description: Advanced countdown timer with animations, auto-coupon, email reminders, A/B testing, and abandonment tracking.
 * Version: 5.0
 * Author: Rashed Hossain
 * Text Domain: wcrt
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('WCRT_VERSION', '5.0');
define('WCRT_PATH', plugin_dir_path(__FILE__));
define('WCRT_URL', plugins_url('', __FILE__));

// Include classes
require_once WCRT_PATH . 'includes/class-wcrt-admin.php';
require_once WCRT_PATH . 'includes/class-wcrt-timer.php';
require_once WCRT_PATH . 'includes/class-wcrt-coupon.php';
require_once WCRT_PATH . 'includes/class-wcrt-email.php';
require_once WCRT_PATH . 'includes/class-wcrt-tracking.php';

// Initialize plugin
add_action('plugins_loaded', function(){
    WCRT_Admin::init();
    WCRT_Timer::init();
    WCRT_Coupon::init();
    WCRT_Email::init();
    WCRT_Tracking::init();
});

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_script('wcrt-timer', WCRT_URL . '/assets/timer.js', ['jquery'], WCRT_VERSION, true);
    wp_enqueue_style('wcrt-timer-css', WCRT_URL . '/assets/timer.css', [], WCRT_VERSION);
    wp_enqueue_script('wcrt-sounds', WCRT_URL . '/assets/sounds.js', [], WCRT_VERSION, true);
});

// Create default coupon and database tables on activation
register_activation_hook(__FILE__, function(){
    if (class_exists('WC_Coupon')) {
        require_once WCRT_PATH . 'includes/class-wcrt-coupon.php';
        WCRT_Coupon::create_or_get_coupon();
    }
    WCRT_Tracking::create_tables();
});

// Cleanup on deactivation
register_deactivation_hook(__FILE__, function(){
    wp_clear_scheduled_hook('wcrt_send_email_reminders');
});
