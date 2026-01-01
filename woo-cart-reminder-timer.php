<?php
/**
 * Plugin Name: Woo Cart Reminder Timer (Classic + Blocks)
 * Description: Countdown urgency timer for WooCommerce cart & checkout (Classic and Blocks).
 * Version: 3.0.0
 * Author: Rashed Hossain
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Woo_Cart_Reminder_Timer {

    const DEFAULT_MINUTES = 15;

    public function __construct() {
        add_action( 'woocommerce_add_to_cart', [ $this, 'start_timer' ] );
        add_action( 'woocommerce_cart_is_empty', [ $this, 'clear_timer' ] );
        add_action( 'wp', [ $this, 'maybe_clear_cart' ] );

        /* Classic cart placement */
        add_action( 'woocommerce_before_cart', [ $this, 'render_placeholder' ] );
        add_action( 'woocommerce_before_checkout_form', [ $this, 'render_placeholder' ] );

        /* Universal fallback (Blocks) */
        add_action( 'wp_footer', [ $this, 'render_placeholder' ], 5 );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /* -----------------------------
     * TIMER LOGIC
     * ---------------------------- */

    public function start_timer() {
        if ( ! WC()->session->get( 'wcrt_start' ) ) {
            WC()->session->set( 'wcrt_start', time() );
            WC()->session->set( 'wcrt_variant', rand( 0, 1 ) ? 'A' : 'B' );
        }
    }

    public function clear_timer() {
        WC()->session->__unset( 'wcrt_start' );
        WC()->session->__unset( 'wcrt_variant' );
    }

    private function remaining() {
        $start = WC()->session->get( 'wcrt_start' );
        if ( ! $start ) return 0;
        return max( 0, self::DEFAULT_MINUTES * 60 - ( time() - $start ) );
    }

    public function maybe_clear_cart() {
        if ( $this->remaining() > 0 ) return;
        if ( WC()->cart && ! WC()->cart->is_empty() ) {
            WC()->cart->empty_cart();
            $this->clear_timer();
        }
    }

    /* -----------------------------
     * PLACEHOLDER
     * ---------------------------- */

    public function render_placeholder() {
        if ( ! WC()->cart || WC()->cart->is_empty() ) return;
        if ( did_action( 'wcrt_placeholder_rendered' ) ) return;

        do_action( 'wcrt_placeholder_rendered' );

        echo '<div id="wcrt-placeholder" data-wcrt="1"></div>';
    }

    /* -----------------------------
     * ASSETS
     * ---------------------------- */

    public function enqueue_assets() {
        if ( ! WC()->cart || WC()->cart->is_empty() ) return;

        $remaining = $this->remaining();
        if ( $remaining <= 0 ) return;

        wp_enqueue_script(
            'wcrt-timer',
            plugin_dir_url( __FILE__ ) . 'assets/timer.js',
            [ 'jquery', 'wp-data' ],
            '3.0.0',
            true
        );

        wp_enqueue_style(
            'wcrt-style',
            plugin_dir_url( __FILE__ ) . 'assets/timer.css',
            [],
            '3.0.0'
        );

        wp_localize_script( 'wcrt-timer', 'WCRT_DATA', [
            'remaining' => $remaining,
            'variant'   => WC()->session->get( 'wcrt_variant', 'A' ),
            'loggedIn'  => is_user_logged_in(),
            'messages'  => [
                'A' => [
                    'guest' => 'Prices are reserved for',
                    'user'  => 'Your exclusive price is reserved for'
                ],
                'B' => [
                    'guest' => 'Hurry! Cart expires in',
                    'user'  => 'Complete checkout within'
                ]
            ]
        ] );
    }
}

new Woo_Cart_Reminder_Timer();