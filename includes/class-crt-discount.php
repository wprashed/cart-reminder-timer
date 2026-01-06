<?php
/**
 * CRT_Discount class - Handle product-specific time-limited discounts.
 *
 * @package Cart_Reminder_Timer_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Direct discount management class (replaces coupon system).
 */
class CRT_Discount {

	/**
	 * Instance of the class.
	 *
	 * @var CRT_Discount|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of class.
	 *
	 * @return CRT_Discount
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
		add_filter( 'woocommerce_product_get_price', array( $this, 'apply_discount_to_price' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'apply_discount_to_price' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'apply_discount_to_subtotal' ), 10, 3 );
		add_filter( 'woocommerce_before_calculate_totals', array( $this, 'apply_discount_to_cart_items' ) );
		add_action( 'wp_ajax_crt_expire_discount', array( $this, 'expire_discount' ) );
		add_action( 'wp_ajax_nopriv_crt_expire_discount', array( $this, 'expire_discount' ) );
	}

	/**
	 * Check if discount is still valid (time hasn't expired).
	 *
	 * @return bool
	 */
	private function is_discount_valid() {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return false;
		}

		// If timer has expired, discount is not valid
		if ( WC()->session->get( 'crt_timer_expired' ) ) {
			return false;
		}

		$start_time = WC()->session->get( 'crt_start' );
		if ( ! $start_time ) {
			return false;
		}

		$duration = (int) crt_get_option( 'duration', 15 ) * 60;
		$elapsed = time() - $start_time;

		return $elapsed < $duration;
	}

	/**
	 * Apply discount to product price.
	 *
	 * Apply discount directly to product prices instead of using coupons
	 *
	 * @param float      $price The product price.
	 * @param WC_Product $product The product object.
	 * @return float
	 */
	public function apply_discount_to_price( $price, $product ) {
		if ( ! $this->is_discount_valid() ) {
			return $price;
		}

		// Don't apply on checkout to avoid double discounting
		if ( is_checkout() ) {
			return $price;
		}

		return $this->calculate_discounted_price( $price );
	}

	/**
	 * Apply discount to cart items.
	 *
	 * @param WC_Cart $cart The cart object.
	 * @return void
	 */
	public function apply_discount_to_cart_items( $cart ) {
		if ( did_action( 'woocommerce_before_calculate_totals' ) > 1 ) {
			return;
		}

		if ( ! $this->is_discount_valid() ) {
			return;
		}

		if ( ! $cart || $cart->is_empty() ) {
			return;
		}

		foreach ( $cart->get_cart_contents() as $item ) {
			$original_price = (float) $item['data']->get_price();
			$discounted_price = $this->calculate_discounted_price( $original_price );
			$item['data']->set_price( $discounted_price );
		}
	}

	/**
	 * Apply discount to cart subtotal display.
	 *
	 * @param string $subtotal The subtotal HTML.
	 * @param object $cart_item The cart item.
	 * @param string $cart_item_key The cart item key.
	 * @return string
	 */
	public function apply_discount_to_subtotal( $subtotal, $cart_item, $cart_item_key ) {
		if ( ! $this->is_discount_valid() ) {
			return $subtotal;
		}

		return $subtotal;
	}

	/**
	 * Calculate discounted price based on settings.
	 *
	 * @param float $price The original price.
	 * @return float
	 */
	private function calculate_discounted_price( $price ) {
		if ( $price <= 0 ) {
			return $price;
		}

		$discount_type = sanitize_text_field( crt_get_option( 'discount_type', 'percent' ) );
		$discount_amount = (float) crt_get_option( 'discount_amount', 10 );

		if ( 'percent' === $discount_type ) {
			return $price * ( 1 - ( $discount_amount / 100 ) );
		} else {
			return max( 0, $price - $discount_amount );
		}
	}

	/**
	 * Expire the discount via AJAX.
	 *
	 * @return void
	 */
	public function expire_discount() {
		check_ajax_referer( 'crt_timer_nonce', 'nonce' );

		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			wp_send_json_error( array( 'message' => __( 'Session not available', CRT_TEXT_DOMAIN ) ) );
		}

		WC()->session->set( 'crt_timer_expired', true );
		WC()->session->set( 'crt_start', null );

		// Trigger cart update to refresh totals
		WC()->cart->calculate_totals();

		wp_send_json_success( array( 'message' => __( 'Discount expired', CRT_TEXT_DOMAIN ) ) );
	}

	/**
	 * Get discount information for display.
	 *
	 * @return array
	 */
	public static function get_discount_info() {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return array(
				'amount'  => 0,
				'type'    => 'percent',
				'message' => '',
			);
		}

		$instance = self::get_instance();
		if ( ! $instance->is_discount_valid() ) {
			return array(
				'amount'  => 0,
				'type'    => 'percent',
				'message' => __( 'Discount has expired', CRT_TEXT_DOMAIN ),
			);
		}

		$discount_type = sanitize_text_field( crt_get_option( 'discount_type', 'percent' ) );
		$discount_amount = (float) crt_get_option( 'discount_amount', 10 );

		return array(
			'amount'  => $discount_amount,
			'type'    => $discount_type,
			'message' => sprintf(
				'%s%s %s',
				$discount_amount,
				'percent' === $discount_type ? '%' : get_woocommerce_currency_symbol(),
				__( 'off until timer expires', CRT_TEXT_DOMAIN )
			),
		);
	}
}