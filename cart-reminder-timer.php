<?php
/**
 * Plugin Name: Cart Reminder Timer for WooCommerce
 * Description: Interactive countdown timer with auto-apply coupons, email reminders, A/B testing and cart abandonment tracking.
 * Version: 6.0
 * Author: Rashed Hossain
 * Author URI: https://rashedhossain.dev
 * Text Domain: cart-reminder-timer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Cart_Reminder_Timer
 * @author Rashed Hossain
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Define plugin constants.
 */
define( 'CRT_VERSION', '6.0' );
define( 'CRT_PLUGIN_FILE', __FILE__ );
define( 'CRT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CRT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CRT_TEXT_DOMAIN', 'cart-reminder-timer' );

/**
 * Load plugin text domain for translations.
 *
 * @return void
 */
function CRT_load_plugin_textdomain() {
	load_plugin_textdomain(
		CRT_TEXT_DOMAIN,
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'CRT_load_plugin_textdomain' );

/**
 * Check if WooCommerce is active.
 *
 * @return bool
 */
function CRT_is_woocommerce_active() {
	return class_exists( 'WC_Cart' ) && function_exists( 'WC' );
}

/**
 * Initialize plugin on WooCommerce loaded.
 *
 * @return void
 */
function CRT_init_plugin() {
	if ( ! CRT_is_woocommerce_active() ) {
		add_action( 'admin_notices', 'CRT_woocommerce_required_notice' );
		return;
	}

	require_once CRT_PLUGIN_DIR . 'includes/class-crt-admin.php';
	require_once CRT_PLUGIN_DIR . 'includes/class-crt-timer.php';
	require_once CRT_PLUGIN_DIR . 'includes/class-crt-coupon.php';
	require_once CRT_PLUGIN_DIR . 'includes/class-crt-email.php';
	require_once CRT_PLUGIN_DIR . 'includes/class-crt-tracking.php';

	// Initialize all classes.
	CRT_Admin::get_instance();
	CRT_Timer::get_instance();
	CRT_Coupon::get_instance();
	CRT_Email::get_instance();
	CRT_Tracking::get_instance();
}
add_action( 'woocommerce_loaded', 'CRT_init_plugin' );

/**
 * Display notice if WooCommerce is not active.
 *
 * @return void
 */
function CRT_woocommerce_required_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: WooCommerce plugin name */
					__( '<strong>Cart Reminder Timer</strong> requires %s to be installed and activated.', CRT_TEXT_DOMAIN ),
					'<strong>WooCommerce</strong>'
				)
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Enqueue frontend scripts and styles.
 *
 * @return void
 */
function CRT_enqueue_frontend_assets() {
	if ( ! CRT_is_woocommerce_active() ) {
		return;
	}

	wp_enqueue_script(
		'CRT-timer',
		CRT_PLUGIN_URL . 'assets/timer.js',
		array( 'jquery' ),
		CRT_VERSION,
		true
	);

	wp_enqueue_style(
		'CRT-timer-css',
		CRT_PLUGIN_URL . 'assets/timer.css',
		array(),
		CRT_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'CRT_enqueue_frontend_assets' );

/**
 * Create database tables and default coupon on plugin activation.
 *
 * @return void
 */
function CRT_activate_plugin() {
	if ( ! CRT_is_woocommerce_active() ) {
		wp_die( esc_html__( 'WooCommerce must be active to use Cart Reminder Timer.', CRT_TEXT_DOMAIN ) );
	}

	require_once CRT_PLUGIN_DIR . 'includes/class-crt-tracking.php';
	CRT_Tracking::create_tables();

	if ( class_exists( 'WC_Coupon' ) ) {
		require_once CRT_PLUGIN_DIR . 'includes/class-crt-coupon.php';
		CRT_Coupon::create_or_get_coupon();
	}

	flush_rewrite_rules();
	wp_cache_flush();
}
register_activation_hook( CRT_PLUGIN_FILE, 'CRT_activate_plugin' );

/**
 * Cleanup on plugin deactivation.
 *
 * @return void
 */
function CRT_deactivate_plugin() {
	wp_clear_scheduled_hook( 'CRT_send_email_reminders' );
	flush_rewrite_rules();
}
register_deactivation_hook( CRT_PLUGIN_FILE, 'CRT_deactivate_plugin' );

/**
 * Get plugin option with default value.
 *
 * @param string $option Option name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function CRT_get_option( $option, $default = false ) {
	return get_option( 'CRT_' . $option, $default );
}

/**
 * Update plugin option.
 *
 * @param string $option Option name.
 * @param mixed  $value Option value.
 * @return bool
 */
function CRT_update_option( $option, $value ) {
	return update_option( 'CRT_' . $option, $value );
}
