<?php
/**
 * crt_Tracking class - Track abandoned carts and conversions.
 *
 * @package Cart_Reminder_Timer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Cart tracking class.
 */
class crt_Tracking {

	/**
	 * Instance of the class.
	 *
	 * @var crt_Tracking|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of class.
	 *
	 * @return crt_Tracking
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
		add_action( 'wp_footer', array( $this, 'track_cart' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'track_conversion' ) );
	}

	/**
	 * Create database tables for tracking.
	 *
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'crt_abandoned_carts';

		$sql = $wpdb->prepare(
			"CREATE TABLE IF NOT EXISTS {$table_name} (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				user_id bigint(20),
				cart_value decimal(10,2),
				variant varchar(1),
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				reminded tinyint(1) DEFAULT 0,
				converted tinyint(1) DEFAULT 0,
				PRIMARY KEY (id),
				KEY user_id (user_id),
				KEY created_at (created_at)
			) {$charset_collate};"
		);

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Track abandoned cart.
	 *
	 * @return void
	 */
	public function track_cart() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
			return;
		}

		// Skip if already tracked in this session.
		if ( WC()->session->get( 'crt_tracked' ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'crt_abandoned_carts';

		$wpdb->insert(
			$table,
			array(
				'user_id'    => get_current_user_id(),
				'cart_value' => WC()->cart->get_subtotal(),
				'variant'    => WC()->session->get( 'crt_variant' ) ?: 'A',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%f', '%s', '%s' )
		);

		WC()->session->set( 'crt_tracked', 1 );
	}

	/**
	 * Track order conversion.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function track_conversion( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order || ! $order->get_user_id() ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'crt_abandoned_carts';

		$wpdb->update(
			$table,
			array( 'converted' => 1 ),
			array( 'user_id' => $order->get_user_id() ),
			array( '%d' ),
			array( '%d' )
		);
	}
}
