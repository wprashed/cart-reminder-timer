<?php
if (!defined('ABSPATH')) exit;

class WCRT_Admin {

    public static function init(){
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    public static function menu(){
        add_submenu_page(
            'woocommerce',
            'Cart Reminder Timer',
            'Cart Reminder Timer',
            'manage_options',
            'wcrt-settings',
            [__CLASS__, 'settings_page']
        );
    }

    public static function enqueue_admin_assets(){
        wp_enqueue_style('wcrt-admin', WCRT_URL . '/assets/admin.css');
    }

    public static function settings_page(){
        if(!current_user_can('manage_options')) return;

        if(isset($_POST['wcrt_save']) && wp_verify_nonce($_POST['wcrt_nonce'], 'wcrt_save_settings')){
            update_option('wcrt_duration', intval($_POST['wcrt_duration']));
            update_option('wcrt_show_on', sanitize_text_field($_POST['wcrt_show_on']));
            update_option('wcrt_coupon', isset($_POST['wcrt_coupon'])?1:0);
            update_option('wcrt_coupon_type', sanitize_text_field($_POST['wcrt_coupon_type']));
            update_option('wcrt_coupon_amount', floatval($_POST['wcrt_coupon_amount']));
            update_option('wcrt_autoclear', isset($_POST['wcrt_autoclear'])?1:0);
            update_option('wcrt_message_user', sanitize_text_field($_POST['wcrt_message_user']));
            update_option('wcrt_message_guest', sanitize_text_field($_POST['wcrt_message_guest']));
            update_option('wcrt_ab_testing', isset($_POST['wcrt_ab_testing'])?1:0);
            update_option('wcrt_max_usage', intval($_POST['wcrt_max_usage']));
            update_option('wcrt_min_cart', floatval($_POST['wcrt_min_cart']));
            update_option('wcrt_position', sanitize_text_field($_POST['wcrt_position']));
            update_option('wcrt_color_scheme', sanitize_text_field($_POST['wcrt_color_scheme']));
            update_option('wcrt_enable_sound', isset($_POST['wcrt_enable_sound'])?1:0);
            update_option('wcrt_enable_email', isset($_POST['wcrt_enable_email'])?1:0);
            update_option('wcrt_dismissable', isset($_POST['wcrt_dismissable'])?1:0);
            update_option('wcrt_show_progress', isset($_POST['wcrt_show_progress'])?1:0);
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }

        $duration = get_option('wcrt_duration', 15);
        $show_on = get_option('wcrt_show_on', 'both');
        $color_scheme = get_option('wcrt_color_scheme', 'danger');
        ?>
        <div class="wrap wcrt-admin-wrap">
            <h1>🚀 Cart Reminder Timer Settings</h1>
            
            <form method="post" class="wcrt-form">
                <?php wp_nonce_field('wcrt_save_settings', 'wcrt_nonce'); ?>
                
                <div class="wcrt-tabs">
                    <div class="wcrt-tab-buttons">
                        <button type="button" class="wcrt-tab-btn active" data-tab="general">General</button>
                        <button type="button" class="wcrt-tab-btn" data-tab="coupon">Coupon</button>
                        <button type="button" class="wcrt-tab-btn" data-tab="notifications">Notifications</button>
                        <button type="button" class="wcrt-tab-btn" data-tab="advanced">Advanced</button>
                    </div>

                    <div class="wcrt-tab-content active" id="general">
                        <table class="form-table wcrt-form-table">
                            <tr>
                                <th><label for="wcrt_duration">Timer Duration (minutes)</label></th>
                                <td><input type="number" id="wcrt_duration" name="wcrt_duration" value="<?php echo $duration; ?>" min="1" max="60"></td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_show_on">Show Timer On</label></th>
                                <td>
                                    <select id="wcrt_show_on" name="wcrt_show_on">
                                        <option value="cart" <?php selected($show_on, 'cart'); ?>>Cart Only</option>
                                        <option value="checkout" <?php selected($show_on, 'checkout'); ?>>Checkout Only</option>
                                        <option value="both" <?php selected($show_on, 'both'); ?>>Both Cart & Checkout</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_position">Timer Position</label></th>
                                <td>
                                    <select id="wcrt_position" name="wcrt_position">
                                        <option value="top" <?php selected(get_option('wcrt_position'), 'top'); ?>>Top</option>
                                        <option value="bottom" <?php selected(get_option('wcrt_position'), 'bottom'); ?>>Bottom</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_color_scheme">Color Scheme</label></th>
                                <td>
                                    <select id="wcrt_color_scheme" name="wcrt_color_scheme">
                                        <option value="danger" <?php selected($color_scheme, 'danger'); ?>>🔴 Red (Danger)</option>
                                        <option value="warning" <?php selected($color_scheme, 'warning'); ?>>🟡 Yellow (Warning)</option>
                                        <option value="info" <?php selected($color_scheme, 'info'); ?>>🔵 Blue (Info)</option>
                                        <option value="success" <?php selected($color_scheme, 'success'); ?>>🟢 Green (Success)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_show_progress">Show Progress Bar</label></th>
                                <td><input type="checkbox" id="wcrt_show_progress" name="wcrt_show_progress" <?php checked(get_option('wcrt_show_progress'), 1); ?>> Enable animated progress bar</td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_dismissable">Allow Dismiss</label></th>
                                <td><input type="checkbox" id="wcrt_dismissable" name="wcrt_dismissable" <?php checked(get_option('wcrt_dismissable'), 1); ?>> Users can dismiss timer (can reopen)</td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_min_cart">Minimum Cart Amount to Show Timer</label></th>
                                <td><input type="number" step="0.01" id="wcrt_min_cart" name="wcrt_min_cart" value="<?php echo get_option('wcrt_min_cart', 0); ?>"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="wcrt-tab-content" id="coupon">
                        <table class="form-table wcrt-form-table">
                            <tr>
                                <th><label for="wcrt_coupon">Enable Coupon on Timer Expiry</label></th>
                                <td><input type="checkbox" id="wcrt_coupon" name="wcrt_coupon" <?php checked(get_option('wcrt_coupon'), 1); ?>> Auto-apply discount when time runs out</td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_coupon_type">Coupon Type</label></th>
                                <td>
                                    <select id="wcrt_coupon_type" name="wcrt_coupon_type">
                                        <option value="percent" <?php selected(get_option('wcrt_coupon_type'), 'percent'); ?>>Percentage (%)</option>
                                        <option value="fixed" <?php selected(get_option('wcrt_coupon_type'), 'fixed'); ?>>Fixed Amount ($)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_coupon_amount">Coupon Amount</label></th>
                                <td><input type="number" step="0.01" id="wcrt_coupon_amount" name="wcrt_coupon_amount" value="<?php echo get_option('wcrt_coupon_amount', 10); ?>"></td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_max_usage">Max Usage Per User</label></th>
                                <td><input type="number" id="wcrt_max_usage" name="wcrt_max_usage" value="<?php echo get_option('wcrt_max_usage', 1); ?>" min="1"></td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_autoclear">Auto Clear Cart on Expiry</label></th>
                                <td><input type="checkbox" id="wcrt_autoclear" name="wcrt_autoclear" <?php checked(get_option('wcrt_autoclear'), 1); ?>> Clear all items when timer expires</td>
                            </tr>
                        </table>
                    </div>

                    <div class="wcrt-tab-content" id="notifications">
                        <table class="form-table wcrt-form-table">
                            <tr>
                                <th><label for="wcrt_message_user">Message for Logged-in Users</label></th>
                                <td><input type="text" id="wcrt_message_user" name="wcrt_message_user" value="<?php echo esc_attr(get_option('wcrt_message_user', 'Hurry up! Your items are reserved.')); ?>" class="large-text"></td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_message_guest">Message for Guests</label></th>
                                <td><input type="text" id="wcrt_message_guest" name="wcrt_message_guest" value="<?php echo esc_attr(get_option('wcrt_message_guest', 'Hurry! Items reserved for a limited time.')); ?>" class="large-text"></td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_enable_sound">Enable Sound Alerts</label></th>
                                <td><input type="checkbox" id="wcrt_enable_sound" name="wcrt_enable_sound" <?php checked(get_option('wcrt_enable_sound'), 1); ?>> Play sound when timer reaches 1 minute</td>
                            </tr>
                            <tr>
                                <th><label for="wcrt_enable_email">Enable Email Reminders</label></th>
                                <td><input type="checkbox" id="wcrt_enable_email" name="wcrt_enable_email" <?php checked(get_option('wcrt_enable_email'), 1); ?>> Send email reminder before timer expires</td>
                            </tr>
                        </table>
                    </div>

                    <div class="wcrt-tab-content" id="advanced">
                        <table class="form-table wcrt-form-table">
                            <tr>
                                <th><label for="wcrt_ab_testing">Enable A/B Testing</label></th>
                                <td><input type="checkbox" id="wcrt_ab_testing" name="wcrt_ab_testing" <?php checked(get_option('wcrt_ab_testing'), 1); ?>> Test different messages and track performance</td>
                            </tr>
                            <tr>
                                <th colspan="2">
                                    <div class="wcrt-info-box">
                                        <strong>ℹ️ Tracking:</strong> This plugin tracks cart abandonment and conversion metrics. View analytics in WooCommerce Reports.
                                    </div>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>

                <p><input type="submit" name="wcrt_save" class="button button-primary button-large" value="💾 Save Settings"></p>
            </form>
        </div>

        <script>
        document.querySelectorAll('.wcrt-tab-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                const tab = this.dataset.tab;
                document.querySelectorAll('.wcrt-tab-content').forEach(c => c.classList.remove('active'));
                document.querySelectorAll('.wcrt-tab-btn').forEach(b => b.classList.remove('active'));
                document.getElementById(tab).classList.add('active');
                this.classList.add('active');
            });
        });
        </script>
        <?php
    }
}
