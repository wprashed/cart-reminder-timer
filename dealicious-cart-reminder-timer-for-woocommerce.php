<?php
/**
 * Plugin Name: Dealicious - Cart Reminder Timer for WooCommerce
 * Description: Add urgency-driven cart and checkout countdown timers for WooCommerce with automatic discounts and reminder emails.
 * Version: 1.0.0
 * Author: Rashed Hossain
 * Author URI: https://rashed.im
 * Text Domain: dealicious-cart-reminder-timer-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 6.0
 * WC tested up to: 10.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Dealicious_Cart_Reminder_Timer
 * @author Rashed Hossain
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Define plugin constants.
 */
define( 'DEALCARE_CRT_VERSION', '1.0.0' );
define( 'DEALCARE_CRT_PLUGIN_FILE', __FILE__ );
define( 'DEALCARE_CRT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DEALCARE_CRT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if WooCommerce is active.
 *
 * @return bool
 */
function dealcare_crt_is_woocommerce_active() {
	return class_exists( 'WC_Cart' ) && function_exists( 'WC' );
}

/**
 * Initialize plugin on WooCommerce loaded.
 *
 * @return void
 */
function dealcare_crt_init_plugin() {
	if ( ! dealcare_crt_is_woocommerce_active() ) {
		add_action( 'admin_notices', 'dealcare_crt_woocommerce_required_notice' );
		return;
	}

	require_once DEALCARE_CRT_PLUGIN_DIR . 'includes/class-crt-admin.php';
	require_once DEALCARE_CRT_PLUGIN_DIR . 'includes/class-crt-timer.php';
	require_once DEALCARE_CRT_PLUGIN_DIR . 'includes/class-crt-discount.php';
	require_once DEALCARE_CRT_PLUGIN_DIR . 'includes/class-crt-email.php';
	require_once DEALCARE_CRT_PLUGIN_DIR . 'includes/class-crt-tracking.php';

	// Initialize all classes only after includes are loaded.
	DEALCARE_CRT_Admin::get_instance();
	DEALCARE_CRT_Timer::get_instance();
	DEALCARE_CRT_Discount::get_instance();
	DEALCARE_CRT_Email::get_instance();
	DEALCARE_CRT_Tracking::get_instance();
}
add_action( 'plugins_loaded', 'dealcare_crt_init_plugin', 15 );

/**
 * Display notice if WooCommerce is not active.
 *
 * @return void
 */
function dealcare_crt_woocommerce_required_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: WooCommerce plugin name */
					__( '<strong>Dealicious - Cart Reminder Timer for WooCommerce</strong> requires %s to be installed and activated.', 'dealicious-cart-reminder-timer-for-woocommerce' ),
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
function dealcare_crt_enqueue_frontend_assets() {
	if ( ! dealcare_crt_is_woocommerce_active() ) {
		return;
	}

	wp_enqueue_script(
		'dealcare-crt-timer',
		DEALCARE_CRT_PLUGIN_URL . 'assets/timer.js',
		array( 'jquery' ),
		DEALCARE_CRT_VERSION,
		true
	);

	wp_enqueue_style(
		'dealcare-crt-timer-css',
		DEALCARE_CRT_PLUGIN_URL . 'assets/timer.css',
		array(),
		DEALCARE_CRT_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'dealcare_crt_enqueue_frontend_assets' );

/**
 * Create database tables on plugin activation.
 *
 * @return void
 */
function dealcare_crt_activate_plugin() {
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	require_once DEALCARE_CRT_PLUGIN_DIR . 'includes/class-crt-tracking.php';

	if ( dealcare_crt_is_woocommerce_active() ) {
		DEALCARE_CRT_Tracking::create_tables();
	}

	flush_rewrite_rules();
	wp_cache_flush();
}
register_activation_hook( DEALCARE_CRT_PLUGIN_FILE, 'dealcare_crt_activate_plugin' );

/**
 * Cleanup on plugin deactivation.
 *
 * @return void
 */
function dealcare_crt_deactivate_plugin() {
	wp_clear_scheduled_hook( 'dealcare_crt_send_email_reminders' );
	flush_rewrite_rules();
}
register_deactivation_hook( DEALCARE_CRT_PLUGIN_FILE, 'dealcare_crt_deactivate_plugin' );

/**
 * Get plugin option with default value.
 *
 * @param string $option Option name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function dealcare_crt_get_option( $option, $default = false ) {
	return get_option( 'dealcare_crt_' . $option, $default );
}

/**
 * Update plugin option.
 *
 * @param string $option Option name.
 * @param mixed  $value Option value.
 * @return bool
 */
function dealcare_crt_update_option( $option, $value ) {
	return update_option( 'dealcare_crt_' . $option, $value );
}
