<?php
/**
 * Uninstall Dealicious - Cart Reminder Timer for WooCommerce.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Prefixed option names
$dealcare_crt_option_names = array(
	'dealcare_crt_duration',
	'dealcare_crt_show_on',
	'dealcare_crt_position',
	'dealcare_crt_color_scheme',
	'dealcare_crt_min_cart',
	'dealcare_crt_show_progress',
	'dealcare_crt_dismissable',
	'dealcare_crt_discount_type',
	'dealcare_crt_discount_amount',
	'dealcare_crt_message_user',
	'dealcare_crt_message_guest',
	'dealcare_crt_message_user_b',
	'dealcare_crt_message_guest_b',
	'dealcare_crt_enable_sound',
	'dealcare_crt_enable_email',
);

// Delete options
foreach ( $dealcare_crt_option_names as $dealcare_crt_option_name ) {
	delete_option( $dealcare_crt_option_name );
	delete_site_option( $dealcare_crt_option_name );
}

// Clear cron
wp_clear_scheduled_hook( 'dealcare_crt_send_email_reminders' );

// Drop table safely
$dealcare_crt_table_name = $wpdb->prefix . 'dealcare_crt_abandoned_carts';
$dealcare_crt_table_name = esc_sql( $dealcare_crt_table_name );

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( 'DROP TABLE IF EXISTS `' . $dealcare_crt_table_name . '`' );
