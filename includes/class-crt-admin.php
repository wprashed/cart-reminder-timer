<?php
/**
 * crt_Admin class - Handle admin settings and configuration.
 *
 * @package Cart_Reminder_Timer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Admin settings class.
 */
class crt_Admin {

	/**
	 * Instance of the class.
	 *
	 * @var crt_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of class.
	 *
	 * @return crt_Admin
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
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Cart Reminder Timer', crt_TEXT_DOMAIN ),
			__( 'Cart Reminder Timer', crt_TEXT_DOMAIN ),
			'manage_options',
			'CRT-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$current_screen = get_current_screen();
		if ( ! $current_screen || 'woocommerce_page_CRT-settings' !== $current_screen->id ) {
			return;
		}

		wp_enqueue_style(
			'CRT-admin',
			crt_PLUGIN_URL . 'assets/admin.css',
			array(),
			crt_VERSION
		);

		wp_enqueue_script(
			'CRT-admin',
			crt_PLUGIN_URL . 'assets/admin.js',
			array( 'jquery' ),
			crt_VERSION,
			true
		);

		wp_localize_script(
			'CRT-admin',
			'CRTAdminData',
			array(
				'currencySymbol' => esc_html( get_woocommerce_currency_symbol() ),
			)
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', crt_TEXT_DOMAIN ) );
		}

		// Handle form submission.
		if ( isset( $_POST['crt_nonce'] ) ) {
			$this->save_settings();
		}

		?>
		<div class="CRT-admin-container">
			<div class="CRT-admin-header">
				<div class="CRT-header-content">
					<h1><?php esc_html_e( 'Cart Reminder Timer Settings', crt_TEXT_DOMAIN ); ?></h1>
					<p><?php esc_html_e( 'Configure your countdown timer and boost conversions', crt_TEXT_DOMAIN ); ?></p>
				</div>
			</div>

			<div class="CRT-admin-body">
				<form method="post" class="CRT-settings-form">
					<?php wp_nonce_field( 'crt_save_settings', 'crt_nonce' ); ?>

					<div class="CRT-tabs-nav">
						<button type="button" class="CRT-tab-nav-item active" data-tab="general">
							<span class="dashicons dashicons-admin-generic"></span>
							<?php esc_html_e( 'General', crt_TEXT_DOMAIN ); ?>
						</button>
						<button type="button" class="CRT-tab-nav-item" data-tab="coupon">
							<span class="dashicons dashicons-tag"></span>
							<?php esc_html_e( 'Coupon', crt_TEXT_DOMAIN ); ?>
						</button>
						<button type="button" class="CRT-tab-nav-item" data-tab="messages">
							<span class="dashicons dashicons-format-chat"></span>
							<?php esc_html_e( 'Messages', crt_TEXT_DOMAIN ); ?>
						</button>
						<button type="button" class="CRT-tab-nav-item" data-tab="advanced">
							<span class="dashicons dashicons-admin-tools"></span>
							<?php esc_html_e( 'Advanced', crt_TEXT_DOMAIN ); ?>
						</button>
					</div>

					<div class="CRT-tabs-content">
						<?php
						$this->render_general_tab();
						$this->render_coupon_tab();
						$this->render_messages_tab();
						$this->render_advanced_tab();
						?>
					</div>

					<div class="CRT-form-actions">
						<button type="submit" class="button button-primary button-large">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Save Settings', crt_TEXT_DOMAIN ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render general tab.
	 *
	 * @return void
	 */
	private function render_general_tab() {
		$duration = (int) crt_get_option( 'duration', 15 );
		$show_on = crt_get_option( 'show_on', 'both' );
		$position = crt_get_option( 'position', 'top' );
		$color_scheme = crt_get_option( 'color_scheme', 'danger' );
		$show_progress = (int) crt_get_option( 'show_progress', 1 );
		$dismissable = (int) crt_get_option( 'dismissable', 0 );
		$min_cart = (float) crt_get_option( 'min_cart', 0 );

		?>
		<div class="CRT-tab-pane active" id="CRT-tab-general">
			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Timer Configuration', crt_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label for="crt_duration">
						<?php esc_html_e( 'Timer Duration', crt_TEXT_DOMAIN ); ?>
						<span class="CRT-required">*</span>
					</label>
					<div class="CRT-input-group">
						<input type="number" id="crt_duration" name="crt_duration" value="<?php echo esc_attr( $duration ); ?>" min="1" max="60" class="CRT-input-number" />
						<span class="CRT-input-addon"><?php esc_html_e( 'minutes', crt_TEXT_DOMAIN ); ?></span>
					</div>
					<p class="CRT-help-text">
						<?php esc_html_e( 'How long items are reserved (1-60 minutes). Default: 15', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label for="crt_show_on">
						<?php esc_html_e( 'Show Timer On', crt_TEXT_DOMAIN ); ?>
						<span class="CRT-required">*</span>
					</label>
					<select id="crt_show_on" name="crt_show_on" class="CRT-select">
						<option value="cart" <?php selected( $show_on, 'cart' ); ?>>
							<?php esc_html_e( 'Cart Page Only', crt_TEXT_DOMAIN ); ?>
						</option>
						<option value="checkout" <?php selected( $show_on, 'checkout' ); ?>>
							<?php esc_html_e( 'Checkout Page Only', crt_TEXT_DOMAIN ); ?>
						</option>
						<option value="both" <?php selected( $show_on, 'both' ); ?>>
							<?php esc_html_e( 'Both Cart & Checkout', crt_TEXT_DOMAIN ); ?>
						</option>
					</select>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Choose where the timer appears to customers', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label for="crt_min_cart">
						<?php esc_html_e( 'Minimum Cart Amount', crt_TEXT_DOMAIN ); ?>
					</label>
					<div class="CRT-input-group">
						<input type="number" id="crt_min_cart" name="crt_min_cart" value="<?php echo esc_attr( $min_cart ); ?>" step="0.01" min="0" class="CRT-input-text" />
						<span class="CRT-input-addon"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
					</div>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Only show timer when cart exceeds this amount. Leave 0 to always show.', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>

			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Appearance', crt_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label for="crt_position">
						<?php esc_html_e( 'Timer Position', crt_TEXT_DOMAIN ); ?>
					</label>
					<select id="crt_position" name="crt_position" class="CRT-select">
						<option value="top" <?php selected( $position, 'top' ); ?>>
							<?php esc_html_e( 'Top', crt_TEXT_DOMAIN ); ?>
						</option>
						<option value="bottom" <?php selected( $position, 'bottom' ); ?>>
							<?php esc_html_e( 'Bottom', crt_TEXT_DOMAIN ); ?>
						</option>
					</select>
				</div>

				<div class="CRT-form-group">
					<label for="crt_color_scheme">
						<?php esc_html_e( 'Color Scheme', crt_TEXT_DOMAIN ); ?>
					</label>
					<div class="CRT-color-options">
						<?php
						$schemes = array(
							'danger'  => __( 'Red - Danger', crt_TEXT_DOMAIN ),
							'warning' => __( 'Yellow - Warning', crt_TEXT_DOMAIN ),
							'info'    => __( 'Blue - Info', crt_TEXT_DOMAIN ),
							'success' => __( 'Green - Success', crt_TEXT_DOMAIN ),
						);
						foreach ( $schemes as $value => $label ) {
							?>
							<label class="CRT-color-option">
								<input type="radio" name="crt_color_scheme" value="<?php echo esc_attr( $value ); ?>" <?php checked( $color_scheme, $value ); ?> />
								<span class="CRT-color-preview CRT-color-<?php echo esc_attr( $value ); ?>"></span>
								<span class="CRT-color-label"><?php echo esc_html( $label ); ?></span>
							</label>
							<?php
						}
						?>
					</div>
				</div>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="crt_show_progress" value="1" <?php checked( $show_progress, 1 ); ?> />
						<span><?php esc_html_e( 'Show Progress Bar', crt_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Display animated progress bar showing remaining time', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="crt_dismissable" value="1" <?php checked( $dismissable, 1 ); ?> />
						<span><?php esc_html_e( 'Allow Dismiss', crt_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Users can close timer and reopen it with a floating button', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render coupon tab.
	 *
	 * @return void
	 */
	private function render_coupon_tab() {
		$coupon_enabled = (int) crt_get_option( 'coupon', 0 );
		$coupon_type = crt_get_option( 'coupon_type', 'percent' );
		$coupon_amount = (float) crt_get_option( 'coupon_amount', 10 );
		$max_usage = (int) crt_get_option( 'max_usage', 1 );
		$autoclear = (int) crt_get_option( 'autoclear', 0 );

		?>
		<div class="CRT-tab-pane" id="CRT-tab-coupon">
			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Auto-Apply Coupon', crt_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" id="crt_coupon" name="crt_coupon" value="1" <?php checked( $coupon_enabled, 1 ); ?> />
						<span><?php esc_html_e( 'Enable Auto-Apply Coupon on Timer Expiry', crt_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Automatically apply a discount when timer expires', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label for="crt_coupon_type">
						<?php esc_html_e( 'Discount Type', crt_TEXT_DOMAIN ); ?>
					</label>
					<select id="crt_coupon_type" name="crt_coupon_type" class="CRT-select">
						<option value="percent" <?php selected( $coupon_type, 'percent' ); ?>>
							<?php esc_html_e( 'Percentage (%)', crt_TEXT_DOMAIN ); ?>
						</option>
						<option value="fixed" <?php selected( $coupon_type, 'fixed' ); ?>>
							<?php esc_html_e( 'Fixed Amount', crt_TEXT_DOMAIN ); ?> (<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>)
						</option>
					</select>
				</div>

				<div class="CRT-form-group">
					<label for="crt_coupon_amount">
						<?php esc_html_e( 'Discount Amount', crt_TEXT_DOMAIN ); ?>
					</label>
					<div class="CRT-input-group">
						<input type="number" id="crt_coupon_amount" name="crt_coupon_amount" value="<?php echo esc_attr( $coupon_amount ); ?>" step="0.01" min="0" class="CRT-input-text" />
						<span class="CRT-input-addon" id="crt_coupon_unit"><?php echo esc_html( $coupon_type === 'percent' ? '%' : get_woocommerce_currency_symbol() ); ?></span>
					</div>
				</div>

				<div class="CRT-form-group">
					<label for="crt_max_usage">
						<?php esc_html_e( 'Max Usage Per User', crt_TEXT_DOMAIN ); ?>
					</label>
					<input type="number" id="crt_max_usage" name="crt_max_usage" value="<?php echo esc_attr( $max_usage ); ?>" min="1" class="CRT-input-number" />
					<p class="CRT-help-text">
						<?php esc_html_e( 'How many times each user can use the coupon', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="crt_autoclear" value="1" <?php checked( $autoclear, 1 ); ?> />
						<span><?php esc_html_e( 'Auto Clear Cart on Expiry', crt_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Automatically clear all items when timer expires (if coupon not applied)', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-info-box">
					<strong><?php esc_html_e( 'Note:', crt_TEXT_DOMAIN ); ?></strong>
					<?php esc_html_e( 'Coupon code will be automatically created and managed by the plugin.', crt_TEXT_DOMAIN ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render messages tab.
	 *
	 * @return void
	 */
	private function render_messages_tab() {
		$msg_user = crt_get_option( 'message_user', __( 'Hurry! Your items are reserved.', crt_TEXT_DOMAIN ) );
		$msg_guest = crt_get_option( 'message_guest', __( 'Limited time offer! Complete checkout now.', crt_TEXT_DOMAIN ) );
		$enable_sound = (int) crt_get_option( 'enable_sound', 0 );
		$enable_email = (int) crt_get_option( 'enable_email', 0 );
		$ab_testing = (int) crt_get_option( 'ab_testing', 0 );

		?>
		<div class="CRT-tab-pane" id="CRT-tab-messages">
			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Messages & Notifications', crt_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label for="crt_message_user">
						<?php esc_html_e( 'Message for Logged-In Users', crt_TEXT_DOMAIN ); ?>
					</label>
					<input type="text" id="crt_message_user" name="crt_message_user" value="<?php echo esc_attr( $msg_user ); ?>" class="CRT-input-text CRT-input-large" maxlength="100" />
					<p class="CRT-help-text">
						<?php esc_html_e( 'Personalized message for logged-in customers', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label for="crt_message_guest">
						<?php esc_html_e( 'Message for Guests', crt_TEXT_DOMAIN ); ?>
					</label>
					<input type="text" id="crt_message_guest" name="crt_message_guest" value="<?php echo esc_attr( $msg_guest ); ?>" class="CRT-input-text CRT-input-large" maxlength="100" />
					<p class="CRT-help-text">
						<?php esc_html_e( 'Generic message for guest customers', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>

			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Audio & Email Alerts', crt_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="crt_enable_sound" value="1" <?php checked( $enable_sound, 1 ); ?> />
						<span><?php esc_html_e( 'Enable Sound Alert', crt_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Play notification sound when 1 minute remains', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="crt_enable_email" value="1" <?php checked( $enable_email, 1 ); ?> />
						<span><?php esc_html_e( 'Enable Email Reminders', crt_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Send email reminder before cart expires (logged-in users only)', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>

			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'A/B Testing', crt_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="crt_ab_testing" value="1" <?php checked( $ab_testing, 1 ); ?> />
						<span><?php esc_html_e( 'Enable A/B Testing', crt_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Test different messages and track which converts better', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render advanced tab.
	 *
	 * @return void
	 */
	private function render_advanced_tab() {
		?>
		<div class="CRT-tab-pane" id="CRT-tab-advanced">
			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Analytics & Tracking', crt_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-info-box CRT-info-primary">
					<strong><?php esc_html_e( 'Cart Abandonment Tracking', crt_TEXT_DOMAIN ); ?></strong>
					<p>
						<?php esc_html_e( 'This plugin tracks abandoned carts and their conversion status. View detailed analytics in WooCommerce > Reports > Orders.', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-info-box CRT-info-success">
					<strong><?php esc_html_e( 'Getting Started', crt_TEXT_DOMAIN ); ?></strong>
					<ol>
						<li><?php esc_html_e( 'Configure timer duration in General tab', crt_TEXT_DOMAIN ); ?></li>
						<li><?php esc_html_e( 'Customize messages in Messages tab', crt_TEXT_DOMAIN ); ?></li>
						<li><?php esc_html_e( 'Enable coupon and set discount in Coupon tab', crt_TEXT_DOMAIN ); ?></li>
						<li><?php esc_html_e( 'Save and test on your cart page', crt_TEXT_DOMAIN ); ?></li>
					</ol>
				</div>
			</div>

			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Help & Documentation', crt_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-documentation-links">
					<a href="https://rashedhossain.dev/docs/cart-reminder" target="_blank" rel="noopener noreferrer" class="CRT-doc-link">
						<?php esc_html_e( 'View Full Documentation', crt_TEXT_DOMAIN ); ?>
						<span class="dashicons dashicons-external"></span>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save settings from POST data.
	 *
	 * @return void
	 */
	private function save_settings() {
		check_admin_referer( 'crt_save_settings', 'crt_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', crt_TEXT_DOMAIN ) );
		}

		// Save all settings with proper sanitization.
		crt_update_option( 'duration', isset( $_POST['crt_duration'] ) ? (int) $_POST['crt_duration'] : 15 );
		crt_update_option( 'show_on', isset( $_POST['crt_show_on'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_show_on'] ) ) : 'both' );
		crt_update_option( 'position', isset( $_POST['crt_position'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_position'] ) ) : 'top' );
		crt_update_option( 'color_scheme', isset( $_POST['crt_color_scheme'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_color_scheme'] ) ) : 'danger' );
		crt_update_option( 'min_cart', isset( $_POST['crt_min_cart'] ) ? (float) $_POST['crt_min_cart'] : 0 );
		crt_update_option( 'show_progress', isset( $_POST['crt_show_progress'] ) ? 1 : 0 );
		crt_update_option( 'dismissable', isset( $_POST['crt_dismissable'] ) ? 1 : 0 );
		crt_update_option( 'coupon', isset( $_POST['crt_coupon'] ) ? 1 : 0 );
		crt_update_option( 'coupon_type', isset( $_POST['crt_coupon_type'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_coupon_type'] ) ) : 'percent' );
		crt_update_option( 'coupon_amount', isset( $_POST['crt_coupon_amount'] ) ? (float) $_POST['crt_coupon_amount'] : 10 );
		crt_update_option( 'max_usage', isset( $_POST['crt_max_usage'] ) ? (int) $_POST['crt_max_usage'] : 1 );
		crt_update_option( 'autoclear', isset( $_POST['crt_autoclear'] ) ? 1 : 0 );
		crt_update_option( 'message_user', isset( $_POST['crt_message_user'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_message_user'] ) ) : __( 'Hurry! Your items are reserved.', crt_TEXT_DOMAIN ) );
		crt_update_option( 'message_guest', isset( $_POST['crt_message_guest'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_message_guest'] ) ) : __( 'Limited time offer! Complete checkout now.', crt_TEXT_DOMAIN ) );
		crt_update_option( 'enable_sound', isset( $_POST['crt_enable_sound'] ) ? 1 : 0 );
		crt_update_option( 'enable_email', isset( $_POST['crt_enable_email'] ) ? 1 : 0 );
		crt_update_option( 'ab_testing', isset( $_POST['crt_ab_testing'] ) ? 1 : 0 );

		// Clear cache.
		wp_cache_flush();

		// Show success message.
		add_action(
			'admin_notices',
			function() {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php esc_html_e( 'Settings saved successfully!', crt_TEXT_DOMAIN ); ?>
					</p>
				</div>
				<?php
			}
		);
	}
}