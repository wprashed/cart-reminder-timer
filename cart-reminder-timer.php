<?php
/**
 * Plugin Name: Woo Cart Reminder Timer
 * Description: Adds a countdown timer to the WooCommerce cart to create urgency.
 * Version: 1.0.0
 * Author: Rashed Hossain
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Cart_Reminder_Timer {

    const TIMER_DURATION = 15; // minutes

    public function __construct() {
        add_action( 'woocommerce_add_to_cart', [ $this, 'set_cart_timer' ] );
        add_action( 'woocommerce_cart_is_empty', [ $this, 'clear_cart_timer' ] );
        add_action( 'woocommerce_before_cart', [ $this, 'render_timer' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function set_cart_timer() {
        if ( ! WC()->session->get( 'cart_timer_start' ) ) {
            WC()->session->set( 'cart_timer_start', time() );
        }
    }

    public function clear_cart_timer() {
        WC()->session->__unset( 'cart_timer_start' );
    }

    public function get_remaining_time() {
        $start_time = WC()->session->get( 'cart_timer_start' );

        if ( ! $start_time ) {
            return 0;
        }

        $duration = self::TIMER_DURATION * 60;
        $elapsed  = time() - $start_time;
        $remaining = max( 0, $duration - $elapsed );

        return $remaining;
    }

    public function render_timer() {
        if ( WC()->cart->is_empty() ) {
            return;
        }

        $remaining = $this->get_remaining_time();

        if ( $remaining <= 0 ) {
            return;
        }
        ?>
        <div id="woo-cart-timer"
             data-remaining="<?php echo esc_attr( $remaining ); ?>">
            ⏳ Prices are reserved for
            <strong><span id="woo-cart-timer-countdown"></span></strong>
        </div>
        <?php
    }

    public function enqueue_assets() {
        if ( ! is_cart() ) {
            return;
        }

        wp_enqueue_script(
            'woo-cart-timer-js',
            plugin_dir_url( __FILE__ ) . 'assets/timer.js',
            [],
            '1.0',
            true
        );

        wp_enqueue_style(
            'woo-cart-timer-css',
            plugin_dir_url( __FILE__ ) . 'assets/timer.css',
            [],
            '1.0'
        );
    }
}

new Woo_Cart_Reminder_Timer();