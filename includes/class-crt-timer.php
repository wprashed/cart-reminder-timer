<?php
/**
 * CRT_Timer class - Handle countdown timer display and logic.
 *
 * @package Cart_Reminder_Timer_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Cart Reminder Timer class.
 */
class CRT_Timer {

	/**
	 * Instance of the class.
	 *
	 * @var CRT_Timer|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of class.
	 *
	 * @return CRT_Timer
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
		add_action( 'wp_ajax_crt_expire_discount', array( $this, 'expire_discount' ) );
		add_action( 'wp_ajax_nopriv_crt_expire_discount', array( $this, 'expire_discount' ) );
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

		wp_enqueue_script( 'crt-timer', CRT_PLUGIN_URL . 'assets/timer.js', array( 'jquery' ), CRT_VERSION, true );

		$timer_data = $this->get_timer_data();
		wp_localize_script( 'crt-timer', 'CRT_DATA', $timer_data );
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
		$min_cart = (float) crt_get_option( 'min_cart', 0 );
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
		$start_time = WC()->session->get( 'crt_start' );
		if ( ! $start_time ) {
			$start_time = time();
			WC()->session->set( 'crt_start', $start_time );
		}

		$duration = (int) crt_get_option( 'duration', 15 ) * 60;
		$elapsed = time() - $start_time;
		$remaining = max( 0, $duration - $elapsed );

		// Get or set A/B variant.
		$variant = WC()->session->get( 'crt_variant' );
		if ( ! $variant ) {
			$variant = rand( 0, 1 ) ? 'A' : 'B';
			WC()->session->set( 'crt_variant', $variant );
		}

		// Get discount info
		$discount_info = CRT_Discount::get_discount_info();

		// Get messages for this variant.
		$messages = array(
			'A' => array(
				'user'  => crt_get_option(
					'message_user',
					sprintf( __( 'Hurry! You have %s off - complete your purchase before time expires!', CRT_TEXT_DOMAIN ), $discount_info['message'] )
				),
				'guest' => crt_get_option(
					'message_guest',
					sprintf( __( 'Limited time offer! Get %s off - checkout now!', CRT_TEXT_DOMAIN ), $discount_info['message'] )
				),
			),
			'B' => array(
				'user'  => crt_get_option(
					'message_user_b',
					sprintf( __( 'Don\'t miss out! %s available - buy before timer expires!', CRT_TEXT_DOMAIN ), $discount_info['message'] )
				),
				'guest' => crt_get_option(
					'message_guest_b',
					sprintf( __( 'Act now! %s discount waiting for you!', CRT_TEXT_DOMAIN ), $discount_info['message'] )
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
			'position'         => sanitize_text_field( crt_get_option( 'position', 'top' ) ),
			'show_on'          => sanitize_text_field( crt_get_option( 'show_on', 'both' ) ),
			'color_scheme'     => sanitize_text_field( crt_get_option( 'color_scheme', 'danger' ) ),
			'dismissable'      => (int) crt_get_option( 'dismissable', 0 ),
			'show_progress'    => (int) crt_get_option( 'show_progress', 1 ),
			'enable_sound'     => (int) crt_get_option( 'enable_sound', 0 ),
			'ajax_url'         => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'nonce'            => wp_create_nonce( 'crt_timer_nonce' ),
			'expiredMessage'   => esc_html__( 'Timer expired! Your special discount has been removed.', CRT_TEXT_DOMAIN ),
		);
	}

	/**
	 * Expire discount via AJAX.
	 *
	 * @return void
	 */
	public function expire_discount() {
		check_ajax_referer( 'crt_timer_nonce', 'nonce' );

		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			wp_send_json_error( array( 'message' => __( 'Session not available', CRT_TEXT_DOMAIN ) ) );
		}

		WC()->session->set( 'crt_timer_expired', true );

		wp_send_json_success( array( 'message' => __( 'Timer expired', CRT_TEXT_DOMAIN ) ) );
	}
}