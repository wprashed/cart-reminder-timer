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
		add_action( 'wp_footer', array( $this, 'inject_timer_data' ) );
		add_action( 'wp_ajax_crt_remove_expired_coupon', array( $this, 'remove_expired_coupon' ) );
		add_action( 'wp_ajax_nopriv_crt_remove_expired_coupon', array( $this, 'remove_expired_coupon' ) );
	}

	/**
	 * Inject timer data into page for JavaScript.
	 *
	 * @return void
	 */
	public function inject_timer_data() {
		if ( ! $this->should_show_timer() ) {
			return;
		}

		$timer_data = $this->get_timer_data();

		?>
		<script type="text/javascript">
			window.CRT_DATA = <?php echo wp_json_encode( $timer_data ); ?>;
		</script>
		<?php
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
		$remaining = max( 0, $duration - ( time() - $start_time ) );

		// Get or set A/B variant.
		$variant = WC()->session->get( 'crt_variant' );
		if ( ! $variant ) {
			$variant = rand( 0, 1 ) ? 'A' : 'B';
			WC()->session->set( 'crt_variant', $variant );
		}

		// Get messages for this variant.
		$messages = array(
			'A' => array(
				'user'  => crt_get_option(
					'message_user',
					__( 'Hurry! Your items are reserved for a limited time.', CRT_TEXT_DOMAIN )
				),
				'guest' => crt_get_option(
					'message_guest',
					__( 'Limited time offer! Complete checkout now to get your discount.', CRT_TEXT_DOMAIN )
				),
			),
			'B' => array(
				'user'  => crt_get_option(
					'message_user_b',
					__( 'Don\'t miss out! Cart expires soon - checkout now!', CRT_TEXT_DOMAIN )
				),
				'guest' => crt_get_option(
					'message_guest_b',
					__( 'Act now! Complete your purchase before the timer ends.', CRT_TEXT_DOMAIN )
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
			'loggedIn'         => is_user_logged_in() ? 1 : 0,
			'position'         => sanitize_text_field( crt_get_option( 'position', 'top' ) ),
			'show_on'          => sanitize_text_field( crt_get_option( 'show_on', 'both' ) ),
			'color_scheme'     => sanitize_text_field( crt_get_option( 'color_scheme', 'danger' ) ),
			'dismissable'      => (int) crt_get_option( 'dismissable', 0 ),
			'show_progress'    => (int) crt_get_option( 'show_progress', 1 ),
			'enable_sound'     => (int) crt_get_option( 'enable_sound', 0 ),
			'ajax_url'         => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'nonce'            => wp_create_nonce( 'crt_remove_coupon' ),
			'expiredMessage'   => esc_html__( 'Cart timer expired. Your discount has been removed.', CRT_TEXT_DOMAIN ),
		);
	}

	/**
	 * Remove expired coupon via AJAX.
	 *
	 * @return void
	 */
	public function remove_expired_coupon() {
		check_ajax_referer( 'crt_remove_coupon', 'nonce' );

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Cart not available', CRT_TEXT_DOMAIN ) ) );
		}

		$coupon_code = 'CRT-TIMER';
		if ( WC()->cart->has_discount( $coupon_code ) ) {
			WC()->cart->remove_coupon( $coupon_code );
			WC()->session->set( 'crt_coupon_applied', false );
			WC()->session->set( 'crt_timer_expired', true );
		}

		wp_send_json_success( array( 'message' => __( 'Coupon removed', CRT_TEXT_DOMAIN ) ) );
	}
}