<?php
/**
 * CRT_Email class - Handle email reminders for abandoned carts.
 *
 * @package Cart_Reminder_Timer_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Email reminder class.
 */
class CRT_Email {

	/**
	 * Instance of the class.
	 *
	 * @var CRT_Email|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of class.
	 *
	 * @return CRT_Email
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
		add_action( 'wp_loaded', array( $this, 'schedule_reminders' ) );
	}

	/**
	 * Schedule email reminders cron job.
	 *
	 * @return void
	 */
	public function schedule_reminders() {
		if ( ! wp_next_scheduled( 'crt_send_email_reminders' ) ) {
			wp_schedule_event( time(), 'hourly', 'crt_send_email_reminders' );
		}
		add_action( 'crt_send_email_reminders', array( $this, 'send_abandoned_reminders' ) );
	}

	/**
	 * Send email reminders to users with abandoned carts.
	 *
	 * @return void
	 */
	public function send_abandoned_reminders() {
		if ( ! crt_get_option( 'enable_email' ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'crt_abandoned_carts';

		// Get carts that will expire in 5 minutes and haven't been reminded.
		$duration = (int) crt_get_option( 'duration', 15 );
		$remind_before = $duration - 5;

		$carts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE reminded = 0 AND created_at < DATE_SUB(NOW(), INTERVAL %d MINUTE)",
				$remind_before
			)
		);

		if ( ! $carts ) {
			return;
		}

		foreach ( $carts as $cart ) {
			$user = get_user_by( 'ID', $cart->user_id );
			if ( ! $user ) {
				continue;
			}

			$this->send_reminder_email( $user, $cart );

			// Mark as reminded.
			$wpdb->update(
				$table,
				array( 'reminded' => 1 ),
				array( 'id' => $cart->id ),
				array( '%d' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Send reminder email to user.
	 *
	 * @param WP_User $user User object.
	 * @param object  $cart Cart data from database.
	 * @return void
	 */
	private function send_reminder_email( $user, $cart ) {
		$user_name = ! empty( $user->first_name ) ? $user->first_name : $user->display_name;
		$cart_url = wc_get_cart_url();

		$subject = sprintf(
			/* translators: %s: Site name */
			__( 'Your cart is about to expire! - %s', CRT_TEXT_DOMAIN ),
			get_bloginfo( 'name' )
		);

		$message = sprintf(
			'<h2>%s</h2>
			<p>%s,</p>
			<p>%s</p>
			<p><a href="%s" style="background-color: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">%s</a></p>
			<p>%s</p>',
			esc_html__( 'Complete Your Purchase', CRT_TEXT_DOMAIN ),
			esc_html( $user_name ),
			esc_html__( 'Your reserved items are about to expire. Click the button below to complete your purchase and receive your discount!', CRT_TEXT_DOMAIN ),
			esc_url_raw( $cart_url ),
			esc_html__( 'Go to Cart', CRT_TEXT_DOMAIN ),
			sprintf(
				/* translators: %s: Cart value */
				esc_html__( 'Cart Value: %s', CRT_TEXT_DOMAIN ),
				wp_kses_post( wc_price( $cart->cart_value ) )
			)
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $user->user_email, $subject, $message, $headers );
	}
}
