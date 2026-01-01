<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', function(){
    add_submenu_page('woocommerce','Cart Reminder Timer','Cart Reminder Timer','manage_options','wcrt-settings',function(){
        if(isset($_POST['wcrt_save'])){
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
            echo '<div class="updated"><p>Settings saved!</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>Cart Reminder Timer Settings</h1>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th>Timer Duration (minutes)</th>
                        <td><input type="number" name="wcrt_duration" value="<?php echo get_option('wcrt_duration',15); ?>"></td>
                    </tr>
                    <tr>
                        <th>Show Timer On</th>
                        <td>
                            <select name="wcrt_show_on">
                                <option value="cart" <?php selected(get_option('wcrt_show_on'),'cart'); ?>>Cart</option>
                                <option value="checkout" <?php selected(get_option('wcrt_show_on'),'checkout'); ?>>Checkout</option>
                                <option value="both" <?php selected(get_option('wcrt_show_on'),'both'); ?>>Both</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Enable Coupon on Expiry</th>
                        <td><input type="checkbox" name="wcrt_coupon" <?php checked(get_option('wcrt_coupon'),1); ?>></td>
                    </tr>
                    <tr>
                        <th>Coupon Type</th>
                        <td>
                            <select name="wcrt_coupon_type">
                                <option value="percent" <?php selected(get_option('wcrt_coupon_type'),'percent'); ?>>Percentage</option>
                                <option value="fixed" <?php selected(get_option('wcrt_coupon_type'),'fixed'); ?>>Fixed</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Coupon Amount</th>
                        <td><input type="number" name="wcrt_coupon_amount" value="<?php echo get_option('wcrt_coupon_amount',10); ?>"></td>
                    </tr>
                    <tr>
                        <th>Auto Clear Cart on Expiry</th>
                        <td><input type="checkbox" name="wcrt_autoclear" <?php checked(get_option('wcrt_autoclear'),1); ?>></td>
                    </tr>
                    <tr>
                        <th>Message for Logged-in Users</th>
                        <td><input type="text" name="wcrt_message_user" value="<?php echo esc_attr(get_option('wcrt_message_user','Hurry up! Your items are reserved.')); ?>"></td>
                    </tr>
                    <tr>
                        <th>Message for Guests</th>
                        <td><input type="text" name="wcrt_message_guest" value="<?php echo esc_attr(get_option('wcrt_message_guest','Hurry! Items reserved.')); ?>"></td>
                    </tr>
                    <tr>
                        <th>Enable A/B Testing</th>
                        <td><input type="checkbox" name="wcrt_ab_testing" <?php checked(get_option('wcrt_ab_testing'),1); ?>></td>
                    </tr>
                    <tr>
                        <th>Max Usage Per User</th>
                        <td><input type="number" name="wcrt_max_usage" value="<?php echo get_option('wcrt_max_usage',1); ?>"></td>
                    </tr>
                    <tr>
                        <th>Minimum Cart Amount to Start Timer</th>
                        <td><input type="number" step="0.01" name="wcrt_min_cart" value="<?php echo get_option('wcrt_min_cart',0); ?>"></td>
                    </tr>
                    <tr>
                        <th>Timer Position</th>
                        <td>
                            <select name="wcrt_position">
                                <option value="top" <?php selected(get_option('wcrt_position'),'top'); ?>>Top</option>
                                <option value="bottom" <?php selected(get_option('wcrt_position'),'bottom'); ?>>Bottom</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p><input type="submit" name="wcrt_save" class="button button-primary" value="Save Settings"></p>
            </form>
        </div>
        <?php
    });
});