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
	'crt_duration',
	'crt_show_on',
	'crt_position',
	'crt_color_scheme',
	'crt_min_cart',
	'crt_show_progress',
	'crt_dismissable',
	'crt_discount_type',
	'crt_discount_amount',
	'crt_message_user',
	'crt_message_guest',
	'crt_message_user_b',
	'crt_message_guest_b',
	'crt_enable_sound',
	'crt_enable_email',
);

foreach ( $option_names as $option_name ) {
	delete_option( $option_name );
	delete_site_option( $option_name );
}

wp_clear_scheduled_hook( 'crt_send_email_reminders' );

$table_name = $wpdb->prefix . 'crt_abandoned_carts';
$table_name_sql = esc_sql( $table_name );
$wpdb->query( "DROP TABLE IF EXISTS `{$table_name_sql}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared
