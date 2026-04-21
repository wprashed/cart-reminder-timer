<?php
/**
 * DEALCARE_CRT_Admin class - Handle admin settings and configuration.
 *
 * @package Dealicious_Cart_Reminder_Timer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access not allowed.' );
}

/**
 * Admin settings class.
 */
class DEALCARE_CRT_Admin {

	/**
	 * Instance of the class.
	 *
	 * @var DEALCARE_CRT_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of class.
	 *
	 * @return DEALCARE_CRT_Admin
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
			__( 'Dealicious - Cart Reminder Timer for WooCommerce', 'dealicious-cart-reminder-timer-for-woocommerce' ),
			__( 'Dealicious - Cart Reminder Timer for WooCommerce', 'dealicious-cart-reminder-timer-for-woocommerce' ),
			'manage_options',
			'dealcare-crt-settings',
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
		if ( ! $current_screen || 'woocommerce_page_dealcare-crt-settings' !== $current_screen->id ) {
			return;
		}

		wp_enqueue_style(
			'dealcare-crt-admin',
			DEALCARE_CRT_PLUGIN_URL . 'assets/admin.css',
			array(),
			DEALCARE_CRT_VERSION
		);

		wp_enqueue_script(
			'dealcare-crt-admin',
			DEALCARE_CRT_PLUGIN_URL . 'assets/admin.js',
			array( 'jquery' ),
			DEALCARE_CRT_VERSION,
			true
		);

		wp_localize_script(
			'dealcare-crt-admin',
			'dealcareCrtAdminData',
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
			wp_die( esc_html__( 'Access denied.', 'dealicious-cart-reminder-timer-for-woocommerce' ) );
		}

		if (
			isset( $_POST['dealcare_crt_nonce'] ) &&
			wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['dealcare_crt_nonce'] ) ),
				'dealcare_crt_save_settings'
			)
		) {
			$this->save_settings();
		}

		?>
		<div class="crt-admin-container">
			<div class="crt-admin-header">
				<div class="crt-header-content">
					<h1><?php esc_html_e( 'Dealicious - Cart Reminder Timer for WooCommerce Settings', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></h1>
					<p><?php esc_html_e( 'Configure your countdown timer and time-limited discounts', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></p>
				</div>
			</div>

			<div class="crt-admin-body">
				<form method="post" class="crt-settings-form">
					<?php wp_nonce_field( 'dealcare_crt_save_settings', 'dealcare_crt_nonce' ); ?>

					<div class="crt-tabs-nav">
						<button type="button" class="crt-tab-nav-item active" data-tab="general">
							<span class="dashicons dashicons-admin-generic"></span>
							<?php esc_html_e( 'General', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</button>
						<button type="button" class="crt-tab-nav-item" data-tab="discount">
							<span class="dashicons dashicons-tag"></span>
							<?php esc_html_e( 'Discount', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</button>
						<button type="button" class="crt-tab-nav-item" data-tab="messages">
							<span class="dashicons dashicons-format-chat"></span>
							<?php esc_html_e( 'Messages', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</button>
						<button type="button" class="crt-tab-nav-item" data-tab="advanced">
							<span class="dashicons dashicons-admin-tools"></span>
							<?php esc_html_e( 'Advanced', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</button>
					</div>

					<div class="crt-tabs-content">
						<?php
						$this->render_general_tab();
						$this->render_discount_tab();
						$this->render_messages_tab();
						$this->render_advanced_tab();
						?>
					</div>

					<div class="crt-form-actions">
						<button type="submit" class="button button-primary button-large">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Save Settings', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
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
		$duration = (int) dealcare_crt_get_option( 'duration', 15 );
		$show_on = dealcare_crt_get_option( 'show_on', 'both' );
		$position = dealcare_crt_get_option( 'position', 'top' );
		$color_scheme = dealcare_crt_get_option( 'color_scheme', 'danger' );
		$show_progress = (int) dealcare_crt_get_option( 'show_progress', 1 );
		$dismissable = (int) dealcare_crt_get_option( 'dismissable', 0 );
		$min_cart = (float) dealcare_crt_get_option( 'min_cart', 0 );

		?>
		<div class="crt-tab-pane active" id="crt-tab-general">
			<div class="crt-settings-section">
				<h2><?php esc_html_e( 'Timer Configuration', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></h2>

				<div class="crt-form-group">
					<label for="crt_duration">
						<?php esc_html_e( 'Timer Duration', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						<span class="crt-required">*</span>
					</label>
					<div class="crt-input-group">
						<input type="number" id="crt_duration" name="crt_duration" value="<?php echo esc_attr( $duration ); ?>" min="1" max="60" class="crt-input-number" />
						<span class="crt-input-addon"><?php esc_html_e( 'minutes', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></span>
					</div>
					<p class="crt-help-text">
						<?php esc_html_e( 'How long customers have to complete purchase (1-60 minutes). Default: 15', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>

				<div class="crt-form-group">
					<label for="crt_show_on">
						<?php esc_html_e( 'Show Timer On', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						<span class="crt-required">*</span>
					</label>
					<select id="crt_show_on" name="crt_show_on" class="crt-select">
						<option value="cart" <?php selected( $show_on, 'cart' ); ?>>
							<?php esc_html_e( 'Cart Page Only', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</option>
						<option value="checkout" <?php selected( $show_on, 'checkout' ); ?>>
							<?php esc_html_e( 'Checkout Page Only', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</option>
						<option value="both" <?php selected( $show_on, 'both' ); ?>>
							<?php esc_html_e( 'Both Cart & Checkout', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</option>
					</select>
					<p class="crt-help-text">
						<?php esc_html_e( 'Choose where the timer and discount appear to customers', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>

				<div class="crt-form-group">
					<label for="crt_min_cart">
						<?php esc_html_e( 'Minimum Cart Amount', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</label>
					<div class="crt-input-group">
						<span class="crt-input-addon"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
						<input type="number" id="crt_min_cart" name="crt_min_cart" value="<?php echo esc_attr( $min_cart ); ?>" step="0.01" min="0" class="crt-input-text" />
					</div>
					<p class="crt-help-text">
						<?php esc_html_e( 'Only show timer when cart exceeds this amount. Leave 0 to always show.', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>
			</div>

			<div class="crt-settings-section">
				<h2><?php esc_html_e( 'Appearance', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></h2>

				<div class="crt-form-group">
					<label for="crt_position">
						<?php esc_html_e( 'Timer Position', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</label>
					<select id="crt_position" name="crt_position" class="crt-select">
						<option value="top" <?php selected( $position, 'top' ); ?>>
							<?php esc_html_e( 'Top', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</option>
						<option value="bottom" <?php selected( $position, 'bottom' ); ?>>
							<?php esc_html_e( 'Bottom', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</option>
					</select>
				</div>

				<div class="crt-form-group">
					<label for="crt_color_scheme">
						<?php esc_html_e( 'Color Scheme', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</label>
					<div class="crt-color-options">
						<?php
						$schemes = array(
							'danger'  => __( 'Red - Danger', 'dealicious-cart-reminder-timer-for-woocommerce' ),
							'warning' => __( 'Yellow - Warning', 'dealicious-cart-reminder-timer-for-woocommerce' ),
							'info'    => __( 'Blue - Info', 'dealicious-cart-reminder-timer-for-woocommerce' ),
							'success' => __( 'Green - Success', 'dealicious-cart-reminder-timer-for-woocommerce' ),
						);
						foreach ( $schemes as $value => $label ) {
							?>
							<label class="crt-color-option">
								<input type="radio" name="crt_color_scheme" value="<?php echo esc_attr( $value ); ?>" <?php checked( $color_scheme, $value ); ?> />
								<span class="crt-color-preview crt-color-<?php echo esc_attr( $value ); ?>"></span>
								<span class="crt-color-label"><?php echo esc_html( $label ); ?></span>
							</label>
							<?php
						}
						?>
					</div>
				</div>

				<div class="crt-form-group">
					<label class="crt-checkbox-label">
						<input type="checkbox" name="crt_show_progress" value="1" <?php checked( $show_progress, 1 ); ?> />
						<span><?php esc_html_e( 'Show Progress Bar', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></span>
					</label>
					<p class="crt-help-text">
						<?php esc_html_e( 'Display animated progress bar showing remaining time', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>

				<div class="crt-form-group">
					<label class="crt-checkbox-label">
						<input type="checkbox" name="crt_dismissable" value="1" <?php checked( $dismissable, 1 ); ?> />
						<span><?php esc_html_e( 'Allow Dismiss', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></span>
					</label>
					<p class="crt-help-text">
						<?php esc_html_e( 'Users can close timer and reopen it with a floating button', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render discount tab.
	 *
	 * @return void
	 */
	private function render_discount_tab() {
		$discount_type = dealcare_crt_get_option( 'discount_type', 'percent' );
		$discount_amount = (float) dealcare_crt_get_option( 'discount_amount', 10 );

		?>
		<div class="crt-tab-pane" id="crt-tab-discount">
			<div class="crt-settings-section">
				<h2><?php esc_html_e( 'Time-Limited Discount', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></h2>

				<p class="crt-info-text">
					<?php esc_html_e( 'Configure the discount that customers receive when they complete their purchase before the timer expires.', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
				</p>

				<div class="crt-form-group">
					<label for="crt_discount_type">
						<?php esc_html_e( 'Discount Type', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						<span class="crt-required">*</span>
					</label>
					<select id="crt_discount_type" name="crt_discount_type" class="crt-select">
						<option value="percent" <?php selected( $discount_type, 'percent' ); ?>>
							<?php esc_html_e( 'Percentage (%)', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						</option>
						<option value="fixed" <?php selected( $discount_type, 'fixed' ); ?>>
							<?php esc_html_e( 'Fixed Amount', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?> (<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>)
						</option>
					</select>
					<p class="crt-help-text">
						<?php esc_html_e( 'Choose whether to discount by percentage or fixed amount', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>

				<div class="crt-form-group">
					<label for="crt_discount_amount">
						<?php esc_html_e( 'Discount Amount', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
						<span class="crt-required">*</span>
					</label>
					<div class="crt-input-group">
						<input type="number" id="crt_discount_amount" name="crt_discount_amount" value="<?php echo esc_attr( $discount_amount ); ?>" step="0.01" min="0" class="crt-input-text" />
						<span class="crt-input-addon" id="crt_discount_unit"><?php echo esc_html( $discount_type === 'percent' ? '%' : get_woocommerce_currency_symbol() ); ?></span>
					</div>
					<p class="crt-help-text">
						<?php esc_html_e( 'The discount amount applied to all products in the cart', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>

				<div class="crt-info-box crt-info-success">
					<strong><?php esc_html_e( 'How It Works:', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></strong>
					<ul style="margin: 10px 0; padding-left: 20px;">
						<li><?php esc_html_e( 'Timer starts when customer adds items to cart', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Discount is automatically applied to all products', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Discount expires when timer runs out', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Customer must checkout before time expires to get the discount', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></li>
					</ul>
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
		$discount_type = dealcare_crt_get_option( 'discount_type', 'percent' );
		$msg_user = dealcare_crt_get_option( 'message_user', __( 'Hurry! You have a special discount - complete your purchase before time expires!', 'dealicious-cart-reminder-timer-for-woocommerce' ) );
		$msg_guest = dealcare_crt_get_option( 'message_guest', __( 'Limited time offer! Get your discount - checkout now!', 'dealicious-cart-reminder-timer-for-woocommerce' ) );
		$enable_sound = (int) dealcare_crt_get_option( 'enable_sound', 0 );
		$enable_email = (int) dealcare_crt_get_option( 'enable_email', 0 );

		?>
		<div class="crt-tab-pane" id="crt-tab-messages">
			<div class="crt-settings-section">
				<h2><?php esc_html_e( 'Messages & Notifications', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></h2>

				<div class="crt-form-group">
					<label for="crt_message_user">
						<?php esc_html_e( 'Message for Logged-In Users', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</label>
					<input type="text" id="crt_message_user" name="crt_message_user" value="<?php echo esc_attr( $msg_user ); ?>" class="crt-input-text crt-input-large" maxlength="150" />
					<p class="crt-help-text">
						<?php esc_html_e( 'Personalized message for logged-in customers', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>

				<div class="crt-form-group">
					<label for="crt_message_guest">
						<?php esc_html_e( 'Message for Guests', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</label>
					<input type="text" id="crt_message_guest" name="crt_message_guest" value="<?php echo esc_attr( $msg_guest ); ?>" class="crt-input-text crt-input-large" maxlength="150" />
					<p class="crt-help-text">
						<?php esc_html_e( 'Generic message for guest customers', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>
			</div>

			<div class="crt-settings-section">
				<h2><?php esc_html_e( 'Audio & Email Alerts', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></h2>

				<div class="crt-form-group">
					<label class="crt-checkbox-label">
						<input type="checkbox" name="crt_enable_sound" value="1" <?php checked( $enable_sound, 1 ); ?> />
						<span><?php esc_html_e( 'Enable Sound Alert', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></span>
					</label>
					<p class="crt-help-text">
						<?php esc_html_e( 'Play notification sound when 1 minute remains', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>

				<div class="crt-form-group">
					<label class="crt-checkbox-label">
						<input type="checkbox" name="crt_enable_email" value="1" <?php checked( $enable_email, 1 ); ?> />
						<span><?php esc_html_e( 'Enable Email Reminders', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></span>
					</label>
					<p class="crt-help-text">
						<?php esc_html_e( 'Send email reminder before discount expires (logged-in users only)', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
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
		<div class="crt-tab-pane" id="crt-tab-advanced">
			<div class="crt-settings-section">
				<h2><?php esc_html_e( 'Advanced Settings', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></h2>

				<div class="crt-info-box crt-info-success">
					<strong><?php esc_html_e( 'Getting Started', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></strong>
					<ol>
						<li><?php esc_html_e( 'Set timer duration in General tab', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Configure discount amount in Discount tab', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Customize messages in Messages tab', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Save and test on your cart page', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></li>
					</ol>
				</div>
			</div>

			<div class="crt-settings-section">
				<h2><?php esc_html_e( 'Help & Documentation', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?></h2>

				<div class="crt-documentation-links">
					<a href="https://rashedhossain.dev/docs/cart-reminder" target="_blank" rel="noopener noreferrer" class="crt-doc-link">
						<?php esc_html_e( 'View Full Documentation', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
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
		check_admin_referer( 'dealcare_crt_save_settings', 'dealcare_crt_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'dealicious-cart-reminder-timer-for-woocommerce' ) );
		}

		dealcare_crt_update_option( 'duration', isset( $_POST['crt_duration'] ) ? (int) $_POST['crt_duration'] : 15 );
		dealcare_crt_update_option( 'show_on', isset( $_POST['crt_show_on'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_show_on'] ) ) : 'both' );
		dealcare_crt_update_option( 'position', isset( $_POST['crt_position'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_position'] ) ) : 'top' );
		dealcare_crt_update_option( 'color_scheme', isset( $_POST['crt_color_scheme'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_color_scheme'] ) ) : 'danger' );
		dealcare_crt_update_option( 'min_cart', isset( $_POST['crt_min_cart'] ) ? (float) $_POST['crt_min_cart'] : 0 );
		dealcare_crt_update_option( 'show_progress', isset( $_POST['crt_show_progress'] ) ? 1 : 0 );
		dealcare_crt_update_option( 'dismissable', isset( $_POST['crt_dismissable'] ) ? 1 : 0 );
		dealcare_crt_update_option( 'discount_type', isset( $_POST['crt_discount_type'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_discount_type'] ) ) : 'percent' );
		dealcare_crt_update_option( 'discount_amount', isset( $_POST['crt_discount_amount'] ) ? (float) $_POST['crt_discount_amount'] : 10 );
		dealcare_crt_update_option( 'message_user', isset( $_POST['crt_message_user'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_message_user'] ) ) : __( 'Hurry! You have a special discount - complete your purchase before time expires!', 'dealicious-cart-reminder-timer-for-woocommerce' ) );
		dealcare_crt_update_option( 'message_guest', isset( $_POST['crt_message_guest'] ) ? sanitize_text_field( wp_unslash( $_POST['crt_message_guest'] ) ) : __( 'Limited time offer! Get your discount - checkout now!', 'dealicious-cart-reminder-timer-for-woocommerce' ) );
		dealcare_crt_update_option( 'enable_sound', isset( $_POST['crt_enable_sound'] ) ? 1 : 0 );
		dealcare_crt_update_option( 'enable_email', isset( $_POST['crt_enable_email'] ) ? 1 : 0 );

		wp_cache_flush();

		add_action(
			'admin_notices',
			function() {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php esc_html_e( 'Settings saved successfully!', 'dealicious-cart-reminder-timer-for-woocommerce' ); ?>
					</p>
				</div>
				<?php
			}
		);
	}
}
