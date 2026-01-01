<?php
/**
 * Plugin Name: Woo Cart Reminder Timer
 * Description: Adds an urgency-based countdown timer to cart and mini-cart.
 * Version: 1.1.0
 * Author: Rashed Hossain
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Woo_Cart_Reminder_Timer {

    public function __construct() {
        add_action( 'woocommerce_add_to_cart', [ $this, 'set_cart_timer' ] );
        add_action( 'woocommerce_cart_is_empty', [ $this, 'clear_cart_timer' ] );

        add_action( 'woocommerce_before_cart', [ $this, 'render_timer' ] );
        add_action( 'woocommerce_widget_shopping_cart_before_buttons', [ $this, 'render_timer' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_menu', [ $this, 'settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );

        add_action( 'wp', [ $this, 'maybe_clear_cart' ] );
    }

    /* -----------------------------
     * SETTINGS
     * ---------------------------- */

    public function settings_page() {
        add_options_page(
            'Cart Reminder Timer',
            'Cart Reminder Timer',
            'manage_options',
            'cart-reminder-timer',
            [ $this, 'settings_html' ]
        );
    }

    public function register_settings() {
        register_setting( 'woo_cart_timer', 'woo_cart_timer_duration' );
        register_setting( 'woo_cart_timer', 'woo_cart_timer_autoclear' );
    }

    public function settings_html() {
        ?>
        <div class="wrap">
            <h1>Cart Reminder Timer</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'woo_cart_timer' ); ?>
                <table class="form-table">
                    <tr>
                        <th>Timer Duration (minutes)</th>
                        <td>
                            <input type="number" min="1"
                                   name="woo_cart_timer_duration"
                                   value="<?php echo esc_attr( get_option( 'woo_cart_timer_duration', 15 ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th>Auto-clear Cart After Expiry</th>
                        <td>
                            <input type="checkbox"
                                   name="woo_cart_timer_autoclear"
                                   value="1"
                                   <?php checked( get_option( 'woo_cart_timer_autoclear' ), 1 ); ?>>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /* -----------------------------
     * TIMER LOGIC
     * ---------------------------- */

    public function set_cart_timer() {
        if ( ! WC()->session->get( 'woo_cart_timer_start' ) ) {
            WC()->session->set( 'woo_cart_timer_start', time() );

            // A/B message assignment
            WC()->session->set(
                'woo_cart_timer_variant',
                rand( 0, 1 ) ? 'A' : 'B'
            );
        }
    }

    public function clear_cart_timer() {
        WC()->session->__unset( 'woo_cart_timer_start' );
        WC()->session->__unset( 'woo_cart_timer_variant' );
    }

    public function remaining_time() {
        $start = WC()->session->get( 'woo_cart_timer_start' );
        if ( ! $start ) return 0;

        $duration = (int) get_option( 'woo_cart_timer_duration', 15 ) * 60;
        return max( 0, $duration - ( time() - $start ) );
    }

    public function maybe_clear_cart() {
        if ( ! get_option( 'woo_cart_timer_autoclear' ) ) return;
        if ( $this->remaining_time() > 0 ) return;

        if ( ! WC()->cart->is_empty() ) {
            WC()->cart->empty_cart();
            $this->clear_cart_timer();
        }
    }

    /* -----------------------------
     * UI RENDER
     * ---------------------------- */

    public function render_timer() {
        if ( WC()->cart->is_empty() ) return;

        $remaining = $this->remaining_time();
        if ( $remaining <= 0 ) return;

        $variant = WC()->session->get( 'woo_cart_timer_variant', 'A' );
        $is_logged_in = is_user_logged_in();

        $messages = [
            'A' => $is_logged_in
                ? 'Your exclusive price is reserved for'
                : 'Prices are reserved for',
            'B' => $is_logged_in
                ? 'Complete checkout before this deal expires in'
                : 'Hurry! Cart prices expire in'
        ];
        ?>
        <div class="woo-cart-timer"
             data-remaining="<?php echo esc_attr( $remaining ); ?>">
            ⏳ <?php echo esc_html( $messages[ $variant ] ); ?>
            <strong><span class="woo-cart-timer-countdown"></span></strong>
        </div>
        <?php
    }

    public function enqueue_assets() {
        if ( ! is_cart() && ! is_checkout() ) return;

        wp_enqueue_script(
            'woo-cart-timer',
            plugin_dir_url( __FILE__ ) . 'assets/timer.js',
            [],
            '1.1',
            true
        );

        wp_enqueue_style(
            'woo-cart-timer',
            plugin_dir_url( __FILE__ ) . 'assets/timer.css',
            [],
            '1.1'
        );
    }
}

new Woo_Cart_Reminder_Timer();