<?php
/**
 * Uninstall Dealicious - Cart Reminder Timer for WooCommerce.
 *
 * @package Dealicious_Cart_Reminder_Timer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$option_names = array(
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

foreach ( $option_names as $option_name ) {
	delete_option( $option_name );
	delete_site_option( $option_name );
}

wp_clear_scheduled_hook( 'dealcare_crt_send_email_reminders' );

$table_name = $wpdb->prefix . 'dealcare_crt_abandoned_carts';
$table_name_sql = esc_sql( $table_name );
$wpdb->query( "DROP TABLE IF EXISTS `{$table_name_sql}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared
