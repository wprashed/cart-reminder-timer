<?php
/**
 * Plugin Name: Cart Reminder Timer for WooCommerce
 * Description: Interactive countdown timer with auto-apply coupons, email reminders, A/B testing and cart abandonment tracking.
 * Version: 6.0
 * Author: Rashed Hossain
 * Author URI: https://rashed.im
 * Text Domain: cart-reminder-timer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 1.0.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
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
define( 'crt_VERSION', '6.0' );
define( 'crt_PLUGIN_FILE', __FILE__ );
define( 'crt_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'crt_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'crt_TEXT_DOMAIN', 'cart-reminder-timer' );

/**
 * Load plugin text domain for translations.
 *
 * @return void
 */
function crt_load_plugin_textdomain() {
	load_plugin_textdomain(
		crt_TEXT_DOMAIN,
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'crt_load_plugin_textdomain' );

/**
 * Check if WooCommerce is active.
 *
 * @return bool
 */
function crt_is_woocommerce_active() {
	return class_exists( 'WC_Cart' ) && function_exists( 'WC' );
}

/**
 * Initialize plugin on WooCommerce loaded.
 *
 * @return void
 */
function crt_init_plugin() {
	if ( ! crt_is_woocommerce_active() ) {
		add_action( 'admin_notices', 'crt_woocommerce_required_notice' );
		return;
	}

	require_once crt_PLUGIN_DIR . 'includes/class-crt-admin.php';
	require_once crt_PLUGIN_DIR . 'includes/class-crt-timer.php';
	require_once crt_PLUGIN_DIR . 'includes/class-crt-coupon.php';
	require_once crt_PLUGIN_DIR . 'includes/class-crt-email.php';
	require_once crt_PLUGIN_DIR . 'includes/class-crt-tracking.php';

	// Initialize all classes.
	crt_Admin::get_instance();
	crt_Timer::get_instance();
	crt_Coupon::get_instance();
	crt_Email::get_instance();
	crt_Tracking::get_instance();
}
add_action( 'woocommerce_loaded', 'crt_init_plugin' );

/**
 * Display notice if WooCommerce is not active.
 *
 * @return void
 */
function crt_woocommerce_required_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: WooCommerce plugin name */
					__( '<strong>Cart Reminder Timer</strong> requires %s to be installed and activated.', crt_TEXT_DOMAIN ),
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
function crt_enqueue_frontend_assets() {
	if ( ! crt_is_woocommerce_active() ) {
		return;
	}

	wp_enqueue_script(
		'CRT-timer',
		crt_PLUGIN_URL . 'assets/timer.js',
		array( 'jquery' ),
		crt_VERSION,
		true
	);

	wp_enqueue_style(
		'CRT-timer-css',
		crt_PLUGIN_URL . 'assets/timer.css',
		array(),
		crt_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'crt_enqueue_frontend_assets' );

/**
 * Create database tables and default coupon on plugin activation.
 *
 * @return void
 */
function crt_activate_plugin() {
	if ( ! crt_is_woocommerce_active() ) {
		wp_die( esc_html__( 'WooCommerce must be active to use Cart Reminder Timer.', crt_TEXT_DOMAIN ) );
	}

	require_once crt_PLUGIN_DIR . 'includes/class-crt-tracking.php';
	crt_Tracking::create_tables();

	if ( class_exists( 'WC_Coupon' ) ) {
		require_once crt_PLUGIN_DIR . 'includes/class-crt-coupon.php';
		crt_Coupon::create_or_get_coupon();
	}

	flush_rewrite_rules();
	wp_cache_flush();
}
register_activation_hook( crt_PLUGIN_FILE, 'crt_activate_plugin' );

/**
 * Cleanup on plugin deactivation.
 *
 * @return void
 */
function crt_deactivate_plugin() {
	wp_clear_scheduled_hook( 'crt_send_email_reminders' );
	flush_rewrite_rules();
}
register_deactivation_hook( crt_PLUGIN_FILE, 'crt_deactivate_plugin' );

/**
 * Get plugin option with default value.
 *
 * @param string $option Option name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function crt_get_option( $option, $default = false ) {
	return get_option( 'crt_' . $option, $default );
}

/**
 * Update plugin option.
 *
 * @param string $option Option name.
 * @param mixed  $value Option value.
 * @return bool
 */
function crt_update_option( $option, $value ) {
	return update_option( 'crt_' . $option, $value );
}
