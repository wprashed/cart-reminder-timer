<?php
/**
 * crt_Coupon class - Handle coupon creation and application.
 *
 * @package Cart_Reminder_Timer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Coupon management class.
 */
class crt_Coupon {

	/**
	 * Instance of the class.
	 *
	 * @var crt_Coupon|null
	 */
	private static $instance = null;

	/**
	 * Coupon code.
	 *
	 * @var string
	 */
	const COUPON_CODE = 'CRT-TIMER';

	/**
	 * Get single instance of class.
	 *
	 * @return crt_Coupon
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
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_or_update_coupon' ) );
		add_action( 'woocommerce_cart_updated', array( $this, 'maybe_reset_timer' ) );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'maybe_reset_timer' ) );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'maybe_reset_timer' ) );
	}

	/**
	 * Create or get timer coupon.
	 *
	 * @return WC_Coupon|null
	 */
	public static function create_or_get_coupon() {
		if ( ! class_exists( 'WC_Coupon' ) ) {
			return null;
		}

		// Try to get existing coupon.
		$coupon_id = wc_get_coupon_id_by_code( self::COUPON_CODE );

		if ( $coupon_id ) {
			return new WC_Coupon( $coupon_id );
		}

		// Create new coupon if doesn't exist.
		$coupon_post = wp_insert_post(
			array(
				'post_title'  => self::COUPON_CODE,
				'post_type'   => 'shop_coupon',
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $coupon_post ) ) {
			return null;
		}

		return new WC_Coupon( $coupon_post );
	}

	/**
	 * Apply or update coupon on cart totals.
	 *
	 * @param WC_Cart $cart WooCommerce cart object.
	 * @return void
	 */
	public function apply_or_update_coupon( $cart ) {
		if ( did_action( 'woocommerce_before_calculate_totals' ) > 1 ) {
			return;
		}

		if ( ! crt_get_option( 'coupon' ) ) {
			return;
		}

		if ( ! $cart || $cart->is_empty() ) {
			return;
		}

		$coupon_cache_key = 'crt_coupon_obj_' . crt_VERSION;
		$coupon = wp_cache_get( $coupon_cache_key );

		if ( false === $coupon ) {
			$coupon = self::create_or_get_coupon();
			wp_cache_set( $coupon_cache_key, $coupon, '', HOUR_IN_SECONDS );
		}

		if ( ! $coupon ) {
			return;
		}

		$discount_type = sanitize_text_field( crt_get_option( 'coupon_type', 'percent' ) );
		$discount_amount = (float) crt_get_option( 'coupon_amount', 10 );
		$usage_limit = (int) crt_get_option( 'max_usage', 1 );

		if ( $coupon->get_discount_type() !== $discount_type ||
			 abs( (float) $coupon->get_amount() - $discount_amount ) > 0.01 ||
			 (int) $coupon->get_usage_limit_per_user() !== $usage_limit
		) {
			$coupon->set_discount_type( $discount_type );
			$coupon->set_amount( $discount_amount );
			$coupon->set_individual_use( true );
			$coupon->set_usage_limit_per_user( $usage_limit );
			$coupon->save();
		}

		$code = $coupon->get_code();

		// Apply coupon if not already applied.
		if ( ! WC()->session->get( 'crt_coupon_applied' ) ) {
			if ( $cart->has_discount( $code ) ) {
				$cart->remove_coupon( $code );
			}
			$cart->apply_coupon( $code );
			WC()->session->set( 'crt_coupon_applied', true );
		}
	}

	/**
	 * Reset timer when cart is modified.
	 *
	 * @return void
	 */
	public function maybe_reset_timer() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		if ( WC()->cart->is_empty() ) {
			WC()->session->__unset( 'crt_start' );
			WC()->session->__unset( 'crt_variant' );
			WC()->session->__unset( 'crt_coupon_applied' );
		} else {
			WC()->session->set( 'crt_start', time() );
			WC()->session->set( 'crt_variant', rand( 0, 1 ) ? 'A' : 'B' );
			WC()->session->__unset( 'crt_coupon_applied' );
		}
	}
}