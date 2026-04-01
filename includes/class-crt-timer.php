<?php
/**
 * DEALCARE_CRT_Timer class - Handle countdown timer display and logic.
 *
 * @package Dealicious_Cart_Reminder_Timer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Dealicious cart reminder timer class.
 */
class DEALCARE_CRT_Timer {

	/**
	 * Instance of the class.
	 *
	 * @var DEALCARE_CRT_Timer|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of class.
	 *
	 * @return DEALCARE_CRT_Timer
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_timer_script' ) );
		add_action( 'wp_ajax_dealcare_crt_expire_discount', array( $this, 'expire_discount' ) );
		add_action( 'wp_ajax_nopriv_dealcare_crt_expire_discount', array( $this, 'expire_discount' ) );
	}

	/**
	 * Enqueue timer script with inline data.
	 *
	 * @return void
	 */
	public function enqueue_timer_script() {
		if ( ! $this->should_show_timer() ) {
			return;
		}

		wp_enqueue_script( 'dealcare-crt-timer', DEALCARE_CRT_PLUGIN_URL . 'assets/timer.js', array( 'jquery' ), DEALCARE_CRT_VERSION, true );

		$timer_data = $this->get_timer_data();
		wp_localize_script( 'dealcare-crt-timer', 'dealcareCrtData', $timer_data );
	}

	/**
	 * Check if timer should be displayed.
	 *
	 * @return bool
	 */
	private function should_show_timer() {
		// Don't show if not on cart/checkout or WooCommerce inactive.
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return false;
		}

		// Don't show on empty cart.
		if ( WC()->cart->is_empty() ) {
			return false;
		}

		// Check minimum cart amount.
		$min_cart = (float) dealcare_crt_get_option( 'min_cart', 0 );
		if ( $min_cart > 0 && WC()->cart->get_subtotal() < $min_cart ) {
			return false;
		}

		return true;
	}

	/**
	 * Get timer data array.
	 *
	 * @return array
	 */
	private function get_timer_data() {
		$start_time = WC()->session->get( 'dealcare_crt_start' );
		if ( ! $start_time ) {
			$start_time = time();
			WC()->session->set( 'dealcare_crt_start', $start_time );
		}

		$duration = (int) dealcare_crt_get_option( 'duration', 15 ) * 60;
		$elapsed = time() - $start_time;
		$remaining = max( 0, $duration - $elapsed );

		// Get or set A/B variant.
		$variant = WC()->session->get( 'dealcare_crt_variant' );
		if ( ! $variant ) {
			$variant = wp_rand( 0, 1 ) ? 'A' : 'B';
			WC()->session->set( 'dealcare_crt_variant', $variant );
		}

		// Get discount info
		$discount_info = DEALCARE_CRT_Discount::get_discount_info();

		// Get messages for this variant.
		$messages = array(
			'A' => array(
				'user'  => dealcare_crt_get_option(
					'message_user',
					/* translators: %s: Discount message such as "10% off until timer expires". */
					sprintf( __( 'Hurry! You have %s off - complete your purchase before time expires!', 'dealicious-cart-reminder-timer-for-woocommerce' ), $discount_info['message'] )
				),
				'guest' => dealcare_crt_get_option(
					'message_guest',
					/* translators: %s: Discount message such as "10% off until timer expires". */
					sprintf( __( 'Limited time offer! Get %s off - checkout now!', 'dealicious-cart-reminder-timer-for-woocommerce' ), $discount_info['message'] )
				),
			),
			'B' => array(
				'user'  => dealcare_crt_get_option(
					'message_user_b',
					/* translators: %s: Discount message such as "10% off until timer expires". */
					sprintf( __( 'Don\'t miss out! %s available - buy before timer expires!', 'dealicious-cart-reminder-timer-for-woocommerce' ), $discount_info['message'] )
				),
				'guest' => dealcare_crt_get_option(
					'message_guest_b',
					/* translators: %s: Discount message such as "10% off until timer expires". */
					sprintf( __( 'Act now! %s discount waiting for you!', 'dealicious-cart-reminder-timer-for-woocommerce' ), $discount_info['message'] )
				),
			),
		);

		return array(
			'remaining'        => intval( $remaining ),
			'duration'         => intval( $duration ),
			'variant'          => sanitize_text_field( $variant ),
			'messages'         => array_map(
				function( $group ) {
					return array_map( 'sanitize_text_field', $group );
				},
				$messages
			),
			'discountInfo'     => array(
				'amount'  => floatval( $discount_info['amount'] ),
				'type'    => sanitize_text_field( $discount_info['type'] ),
				'message' => sanitize_text_field( $discount_info['message'] ),
			),
			'loggedIn'         => is_user_logged_in() ? 1 : 0,
			'position'         => sanitize_text_field( dealcare_crt_get_option( 'position', 'top' ) ),
			'show_on'          => sanitize_text_field( dealcare_crt_get_option( 'show_on', 'both' ) ),
			'color_scheme'     => sanitize_text_field( dealcare_crt_get_option( 'color_scheme', 'danger' ) ),
			'dismissable'      => (int) dealcare_crt_get_option( 'dismissable', 0 ),
			'show_progress'    => (int) dealcare_crt_get_option( 'show_progress', 1 ),
			'enable_sound'     => (int) dealcare_crt_get_option( 'enable_sound', 0 ),
			'ajax_url'         => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'nonce'            => wp_create_nonce( 'dealcare_crt_timer_nonce' ),
			'expiredMessage'   => esc_html__( 'Timer expired! Your special discount has been removed.', 'dealicious-cart-reminder-timer-for-woocommerce' ),
		);
	}

	/**
	 * Expire discount via AJAX.
	 *
	 * @return void
	 */
	public function expire_discount() {
		check_ajax_referer( 'dealcare_crt_timer_nonce', 'nonce' );

		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			wp_send_json_error( array( 'message' => __( 'Session not available', 'dealicious-cart-reminder-timer-for-woocommerce' ) ) );
		}

		WC()->session->set( 'dealcare_crt_timer_expired', true );

		wp_send_json_success( array( 'message' => __( 'Timer expired', 'dealicious-cart-reminder-timer-for-woocommerce' ) ) );
	}
}
