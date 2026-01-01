<?php
/**
 * CRT_Admin class - Handle admin settings and configuration.
 *
 * @package Cart_Reminder_Timer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Admin settings class.
 */
class CRT_Admin {

	/**
	 * Instance of the class.
	 *
	 * @var CRT_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of class.
	 *
	 * @return CRT_Admin
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
			__( 'Cart Reminder Timer', CRT_TEXT_DOMAIN ),
			__( 'Cart Reminder Timer', CRT_TEXT_DOMAIN ),
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
			CRT_PLUGIN_URL . 'assets/admin.css',
			array(),
			CRT_VERSION
		);

		wp_enqueue_script(
			'CRT-admin',
			CRT_PLUGIN_URL . 'assets/admin.js',
			array( 'jquery' ),
			CRT_VERSION,
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
			wp_die( esc_html__( 'Access denied.', CRT_TEXT_DOMAIN ) );
		}

		// Handle form submission.
		if ( isset( $_POST['CRT_nonce'] ) ) {
			$this->save_settings();
		}

		?>
		<div class="CRT-admin-container">
			<div class="CRT-admin-header">
				<div class="CRT-header-content">
					<h1><?php esc_html_e( 'Cart Reminder Timer Settings', CRT_TEXT_DOMAIN ); ?></h1>
					<p><?php esc_html_e( 'Configure your countdown timer and boost conversions', CRT_TEXT_DOMAIN ); ?></p>
				</div>
			</div>

			<div class="CRT-admin-body">
				<form method="post" class="CRT-settings-form">
					<?php wp_nonce_field( 'CRT_save_settings', 'CRT_nonce' ); ?>

					<div class="CRT-tabs-nav">
						<button type="button" class="CRT-tab-nav-item active" data-tab="general">
							<span class="dashicons dashicons-admin-generic"></span>
							<?php esc_html_e( 'General', CRT_TEXT_DOMAIN ); ?>
						</button>
						<button type="button" class="CRT-tab-nav-item" data-tab="coupon">
							<span class="dashicons dashicons-tag"></span>
							<?php esc_html_e( 'Coupon', CRT_TEXT_DOMAIN ); ?>
						</button>
						<button type="button" class="CRT-tab-nav-item" data-tab="messages">
							<span class="dashicons dashicons-format-chat"></span>
							<?php esc_html_e( 'Messages', CRT_TEXT_DOMAIN ); ?>
						</button>
						<button type="button" class="CRT-tab-nav-item" data-tab="advanced">
							<span class="dashicons dashicons-admin-tools"></span>
							<?php esc_html_e( 'Advanced', CRT_TEXT_DOMAIN ); ?>
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
							<?php esc_html_e( 'Save Settings', CRT_TEXT_DOMAIN ); ?>
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
		$duration = (int) CRT_get_option( 'duration', 15 );
		$show_on = CRT_get_option( 'show_on', 'both' );
		$position = CRT_get_option( 'position', 'top' );
		$color_scheme = CRT_get_option( 'color_scheme', 'danger' );
		$show_progress = (int) CRT_get_option( 'show_progress', 1 );
		$dismissable = (int) CRT_get_option( 'dismissable', 0 );
		$min_cart = (float) CRT_get_option( 'min_cart', 0 );

		?>
		<div class="CRT-tab-pane active" id="CRT-tab-general">
			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Timer Configuration', CRT_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label for="CRT_duration">
						<?php esc_html_e( 'Timer Duration', CRT_TEXT_DOMAIN ); ?>
						<span class="CRT-required">*</span>
					</label>
					<div class="CRT-input-group">
						<input type="number" id="CRT_duration" name="CRT_duration" value="<?php echo esc_attr( $duration ); ?>" min="1" max="60" class="CRT-input-number" />
						<span class="CRT-input-addon"><?php esc_html_e( 'minutes', CRT_TEXT_DOMAIN ); ?></span>
					</div>
					<p class="CRT-help-text">
						<?php esc_html_e( 'How long items are reserved (1-60 minutes). Default: 15', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label for="CRT_show_on">
						<?php esc_html_e( 'Show Timer On', CRT_TEXT_DOMAIN ); ?>
						<span class="CRT-required">*</span>
					</label>
					<select id="CRT_show_on" name="CRT_show_on" class="CRT-select">
						<option value="cart" <?php selected( $show_on, 'cart' ); ?>>
							<?php esc_html_e( 'Cart Page Only', CRT_TEXT_DOMAIN ); ?>
						</option>
						<option value="checkout" <?php selected( $show_on, 'checkout' ); ?>>
							<?php esc_html_e( 'Checkout Page Only', CRT_TEXT_DOMAIN ); ?>
						</option>
						<option value="both" <?php selected( $show_on, 'both' ); ?>>
							<?php esc_html_e( 'Both Cart & Checkout', CRT_TEXT_DOMAIN ); ?>
						</option>
					</select>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Choose where the timer appears to customers', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label for="CRT_min_cart">
						<?php esc_html_e( 'Minimum Cart Amount', CRT_TEXT_DOMAIN ); ?>
					</label>
					<div class="CRT-input-group">
						<input type="number" id="CRT_min_cart" name="CRT_min_cart" value="<?php echo esc_attr( $min_cart ); ?>" step="0.01" min="0" class="CRT-input-text" />
						<span class="CRT-input-addon"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
					</div>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Only show timer when cart exceeds this amount. Leave 0 to always show.', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>

			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Appearance', CRT_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label for="CRT_position">
						<?php esc_html_e( 'Timer Position', CRT_TEXT_DOMAIN ); ?>
					</label>
					<select id="CRT_position" name="CRT_position" class="CRT-select">
						<option value="top" <?php selected( $position, 'top' ); ?>>
							<?php esc_html_e( 'Top', CRT_TEXT_DOMAIN ); ?>
						</option>
						<option value="bottom" <?php selected( $position, 'bottom' ); ?>>
							<?php esc_html_e( 'Bottom', CRT_TEXT_DOMAIN ); ?>
						</option>
					</select>
				</div>

				<div class="CRT-form-group">
					<label for="CRT_color_scheme">
						<?php esc_html_e( 'Color Scheme', CRT_TEXT_DOMAIN ); ?>
					</label>
					<div class="CRT-color-options">
						<?php
						$schemes = array(
							'danger'  => __( 'Red - Danger', CRT_TEXT_DOMAIN ),
							'warning' => __( 'Yellow - Warning', CRT_TEXT_DOMAIN ),
							'info'    => __( 'Blue - Info', CRT_TEXT_DOMAIN ),
							'success' => __( 'Green - Success', CRT_TEXT_DOMAIN ),
						);
						foreach ( $schemes as $value => $label ) {
							?>
							<label class="CRT-color-option">
								<input type="radio" name="CRT_color_scheme" value="<?php echo esc_attr( $value ); ?>" <?php checked( $color_scheme, $value ); ?> />
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
						<input type="checkbox" name="CRT_show_progress" value="1" <?php checked( $show_progress, 1 ); ?> />
						<span><?php esc_html_e( 'Show Progress Bar', CRT_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Display animated progress bar showing remaining time', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="CRT_dismissable" value="1" <?php checked( $dismissable, 1 ); ?> />
						<span><?php esc_html_e( 'Allow Dismiss', CRT_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Users can close timer and reopen it with a floating button', CRT_TEXT_DOMAIN ); ?>
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
		$coupon_enabled = (int) CRT_get_option( 'coupon', 0 );
		$coupon_type = CRT_get_option( 'coupon_type', 'percent' );
		$coupon_amount = (float) CRT_get_option( 'coupon_amount', 10 );
		$max_usage = (int) CRT_get_option( 'max_usage', 1 );
		$autoclear = (int) CRT_get_option( 'autoclear', 0 );

		?>
		<div class="CRT-tab-pane" id="CRT-tab-coupon">
			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Auto-Apply Coupon', CRT_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" id="CRT_coupon" name="CRT_coupon" value="1" <?php checked( $coupon_enabled, 1 ); ?> />
						<span><?php esc_html_e( 'Enable Auto-Apply Coupon on Timer Expiry', CRT_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Automatically apply a discount when timer expires', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label for="CRT_coupon_type">
						<?php esc_html_e( 'Discount Type', CRT_TEXT_DOMAIN ); ?>
					</label>
					<select id="CRT_coupon_type" name="CRT_coupon_type" class="CRT-select">
						<option value="percent" <?php selected( $coupon_type, 'percent' ); ?>>
							<?php esc_html_e( 'Percentage (%)', CRT_TEXT_DOMAIN ); ?>
						</option>
						<option value="fixed" <?php selected( $coupon_type, 'fixed' ); ?>>
							<?php esc_html_e( 'Fixed Amount', CRT_TEXT_DOMAIN ); ?> (<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>)
						</option>
					</select>
				</div>

				<div class="CRT-form-group">
					<label for="CRT_coupon_amount">
						<?php esc_html_e( 'Discount Amount', CRT_TEXT_DOMAIN ); ?>
					</label>
					<div class="CRT-input-group">
						<input type="number" id="CRT_coupon_amount" name="CRT_coupon_amount" value="<?php echo esc_attr( $coupon_amount ); ?>" step="0.01" min="0" class="CRT-input-text" />
						<span class="CRT-input-addon" id="CRT_coupon_unit"><?php echo esc_html( $coupon_type === 'percent' ? '%' : get_woocommerce_currency_symbol() ); ?></span>
					</div>
				</div>

				<div class="CRT-form-group">
					<label for="CRT_max_usage">
						<?php esc_html_e( 'Max Usage Per User', CRT_TEXT_DOMAIN ); ?>
					</label>
					<input type="number" id="CRT_max_usage" name="CRT_max_usage" value="<?php echo esc_attr( $max_usage ); ?>" min="1" class="CRT-input-number" />
					<p class="CRT-help-text">
						<?php esc_html_e( 'How many times each user can use the coupon', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="CRT_autoclear" value="1" <?php checked( $autoclear, 1 ); ?> />
						<span><?php esc_html_e( 'Auto Clear Cart on Expiry', CRT_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Automatically clear all items when timer expires (if coupon not applied)', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-info-box">
					<strong><?php esc_html_e( 'Note:', CRT_TEXT_DOMAIN ); ?></strong>
					<?php esc_html_e( 'Coupon code will be automatically created and managed by the plugin.', CRT_TEXT_DOMAIN ); ?>
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
		$msg_user = CRT_get_option( 'message_user', __( 'Hurry! Your items are reserved.', CRT_TEXT_DOMAIN ) );
		$msg_guest = CRT_get_option( 'message_guest', __( 'Limited time offer! Complete checkout now.', CRT_TEXT_DOMAIN ) );
		$enable_sound = (int) CRT_get_option( 'enable_sound', 0 );
		$enable_email = (int) CRT_get_option( 'enable_email', 0 );
		$ab_testing = (int) CRT_get_option( 'ab_testing', 0 );

		?>
		<div class="CRT-tab-pane" id="CRT-tab-messages">
			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Messages & Notifications', CRT_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label for="CRT_message_user">
						<?php esc_html_e( 'Message for Logged-In Users', CRT_TEXT_DOMAIN ); ?>
					</label>
					<input type="text" id="CRT_message_user" name="CRT_message_user" value="<?php echo esc_attr( $msg_user ); ?>" class="CRT-input-text CRT-input-large" maxlength="100" />
					<p class="CRT-help-text">
						<?php esc_html_e( 'Personalized message for logged-in customers', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label for="CRT_message_guest">
						<?php esc_html_e( 'Message for Guests', CRT_TEXT_DOMAIN ); ?>
					</label>
					<input type="text" id="CRT_message_guest" name="CRT_message_guest" value="<?php echo esc_attr( $msg_guest ); ?>" class="CRT-input-text CRT-input-large" maxlength="100" />
					<p class="CRT-help-text">
						<?php esc_html_e( 'Generic message for guest customers', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>

			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Audio & Email Alerts', CRT_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="CRT_enable_sound" value="1" <?php checked( $enable_sound, 1 ); ?> />
						<span><?php esc_html_e( 'Enable Sound Alert', CRT_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Play notification sound when 1 minute remains', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="CRT_enable_email" value="1" <?php checked( $enable_email, 1 ); ?> />
						<span><?php esc_html_e( 'Enable Email Reminders', CRT_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Send email reminder before cart expires (logged-in users only)', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>

			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'A/B Testing', CRT_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-form-group">
					<label class="CRT-checkbox-label">
						<input type="checkbox" name="CRT_ab_testing" value="1" <?php checked( $ab_testing, 1 ); ?> />
						<span><?php esc_html_e( 'Enable A/B Testing', CRT_TEXT_DOMAIN ); ?></span>
					</label>
					<p class="CRT-help-text">
						<?php esc_html_e( 'Test different messages and track which converts better', CRT_TEXT_DOMAIN ); ?>
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
				<h2><?php esc_html_e( 'Analytics & Tracking', CRT_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-info-box CRT-info-primary">
					<strong><?php esc_html_e( 'Cart Abandonment Tracking', CRT_TEXT_DOMAIN ); ?></strong>
					<p>
						<?php esc_html_e( 'This plugin tracks abandoned carts and their conversion status. View detailed analytics in WooCommerce > Reports > Orders.', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>

				<div class="CRT-info-box CRT-info-success">
					<strong><?php esc_html_e( 'Getting Started', CRT_TEXT_DOMAIN ); ?></strong>
					<ol>
						<li><?php esc_html_e( 'Configure timer duration in General tab', CRT_TEXT_DOMAIN ); ?></li>
						<li><?php esc_html_e( 'Customize messages in Messages tab', CRT_TEXT_DOMAIN ); ?></li>
						<li><?php esc_html_e( 'Enable coupon and set discount in Coupon tab', CRT_TEXT_DOMAIN ); ?></li>
						<li><?php esc_html_e( 'Save and test on your cart page', CRT_TEXT_DOMAIN ); ?></li>
					</ol>
				</div>
			</div>

			<div class="CRT-settings-section">
				<h2><?php esc_html_e( 'Help & Documentation', CRT_TEXT_DOMAIN ); ?></h2>

				<div class="CRT-documentation-links">
					<a href="https://rashedhossain.dev/docs/cart-reminder" target="_blank" rel="noopener noreferrer" class="CRT-doc-link">
						<?php esc_html_e( 'View Full Documentation', CRT_TEXT_DOMAIN ); ?>
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
		check_admin_referer( 'CRT_save_settings', 'CRT_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', CRT_TEXT_DOMAIN ) );
		}

		// Save all settings with proper sanitization.
		CRT_update_option( 'duration', isset( $_POST['CRT_duration'] ) ? (int) $_POST['CRT_duration'] : 15 );
		CRT_update_option( 'show_on', isset( $_POST['CRT_show_on'] ) ? sanitize_text_field( wp_unslash( $_POST['CRT_show_on'] ) ) : 'both' );
		CRT_update_option( 'position', isset( $_POST['CRT_position'] ) ? sanitize_text_field( wp_unslash( $_POST['CRT_position'] ) ) : 'top' );
		CRT_update_option( 'color_scheme', isset( $_POST['CRT_color_scheme'] ) ? sanitize_text_field( wp_unslash( $_POST['CRT_color_scheme'] ) ) : 'danger' );
		CRT_update_option( 'min_cart', isset( $_POST['CRT_min_cart'] ) ? (float) $_POST['CRT_min_cart'] : 0 );
		CRT_update_option( 'show_progress', isset( $_POST['CRT_show_progress'] ) ? 1 : 0 );
		CRT_update_option( 'dismissable', isset( $_POST['CRT_dismissable'] ) ? 1 : 0 );
		CRT_update_option( 'coupon', isset( $_POST['CRT_coupon'] ) ? 1 : 0 );
		CRT_update_option( 'coupon_type', isset( $_POST['CRT_coupon_type'] ) ? sanitize_text_field( wp_unslash( $_POST['CRT_coupon_type'] ) ) : 'percent' );
		CRT_update_option( 'coupon_amount', isset( $_POST['CRT_coupon_amount'] ) ? (float) $_POST['CRT_coupon_amount'] : 10 );
		CRT_update_option( 'max_usage', isset( $_POST['CRT_max_usage'] ) ? (int) $_POST['CRT_max_usage'] : 1 );
		CRT_update_option( 'autoclear', isset( $_POST['CRT_autoclear'] ) ? 1 : 0 );
		CRT_update_option( 'message_user', isset( $_POST['CRT_message_user'] ) ? sanitize_text_field( wp_unslash( $_POST['CRT_message_user'] ) ) : __( 'Hurry! Your items are reserved.', CRT_TEXT_DOMAIN ) );
		CRT_update_option( 'message_guest', isset( $_POST['CRT_message_guest'] ) ? sanitize_text_field( wp_unslash( $_POST['CRT_message_guest'] ) ) : __( 'Limited time offer! Complete checkout now.', CRT_TEXT_DOMAIN ) );
		CRT_update_option( 'enable_sound', isset( $_POST['CRT_enable_sound'] ) ? 1 : 0 );
		CRT_update_option( 'enable_email', isset( $_POST['CRT_enable_email'] ) ? 1 : 0 );
		CRT_update_option( 'ab_testing', isset( $_POST['CRT_ab_testing'] ) ? 1 : 0 );

		// Clear cache.
		wp_cache_flush();

		// Show success message.
		add_action(
			'admin_notices',
			function() {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php esc_html_e( 'Settings saved successfully!', CRT_TEXT_DOMAIN ); ?>
					</p>
				</div>
				<?php
			}
		);
	}
}