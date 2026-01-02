<?php
/**
 * WCRT_Coupon class - Handle coupon creation and application.
 *
 * @package WooCommerce_Cart_Reminder_Timer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Coupon management class.
 */
class WCRT_Coupon {

	/**
	 * Instance of the class.
	 *
	 * @var WCRT_Coupon|null
	 */
	private static $instance = null;

	/**
	 * Coupon code.
	 *
	 * @var string
	 */
	const COUPON_CODE = 'WCRT-TIMER';

	/**
	 * Get single instance of class.
	 *
	 * @return WCRT_Coupon
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
		add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'hide_coupon_code' ), 10, 2 );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'hide_coupon_code_meta' ), 10, 2 );
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

		if ( ! wcrt_get_option( 'coupon' ) ) {
			return;
		}

		if ( ! $cart || $cart->is_empty() ) {
			return;
		}

		if ( WC()->session->get( 'wcrt_timer_expired' ) ) {
			return;
		}

		$coupon_cache_key = 'wcrt_coupon_obj_' . WCRT_VERSION;
		$coupon = wp_cache_get( $coupon_cache_key );

		if ( false === $coupon ) {
			$coupon = self::create_or_get_coupon();
			wp_cache_set( $coupon_cache_key, $coupon, '', HOUR_IN_SECONDS );
		}

		if ( ! $coupon ) {
			return;
		}

		$discount_type = sanitize_text_field( wcrt_get_option( 'coupon_type', 'percent' ) );
		$discount_amount = (float) wcrt_get_option( 'coupon_amount', 10 );
		$usage_limit = (int) wcrt_get_option( 'max_usage', 1 );

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
		if ( ! WC()->session->get( 'wcrt_coupon_applied' ) ) {
			if ( $cart->has_discount( $code ) ) {
				$cart->remove_coupon( $code );
			}
			$cart->apply_coupon( $code );
			WC()->session->set( 'wcrt_coupon_applied', true );
		}
	}

	/**
	 * Hide the actual coupon code and show only discount amount.
	 *
	 * @param string $label The coupon label.
	 * @param object $coupon The coupon object.
	 * @return string
	 */
	public function hide_coupon_code( $label, $coupon ) {
		if ( $coupon && self::COUPON_CODE === $coupon->get_code() ) {
			$discount_type = $coupon->get_discount_type();
			$amount = $coupon->get_amount();

			if ( 'percent' === $discount_type ) {
				return sprintf(
					/* translators: %s: discount percentage */
					esc_html__( 'Discount: %s%%', WCRT_TEXT_DOMAIN ),
					$amount
				);
			} else {
				return sprintf(
					/* translators: %s: discount amount */
					esc_html__( 'Discount: %s', WCRT_TEXT_DOMAIN ),
					wc_price( $amount )
				);
			}
		}

		return $label;
	}

	/**
	 * Hide coupon code from order meta data.
	 *
	 * @param array  $formatted_meta Formatted meta data.
	 * @param object $item The order item object.
	 * @return array
	 */
	public function hide_coupon_code_meta( $formatted_meta, $item ) {
		return array_filter(
			$formatted_meta,
			function( $meta ) {
				return 'coupon_code' !== $meta->key || self::COUPON_CODE !== $meta->value;
			}
		);
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
			WC()->session->__unset( 'wcrt_start' );
			WC()->session->__unset( 'wcrt_variant' );
			WC()->session->__unset( 'wcrt_coupon_applied' );
			WC()->session->__unset( 'wcrt_timer_expired' );
		} else {
			WC()->session->set( 'wcrt_start', time() );
			WC()->session->set( 'wcrt_variant', rand( 0, 1 ) ? 'A' : 'B' );
			WC()->session->__unset( 'wcrt_coupon_applied' );
			WC()->session->__unset( 'wcrt_timer_expired' );
		}
	}
}