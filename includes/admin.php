<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', function(){
    add_submenu_page('woocommerce','Cart Reminder Timer','Cart Reminder Timer','manage_options','wcrt-settings',function(){
        if(isset($_POST['wcrt_save'])){
            update_option('wcrt_duration', intval($_POST['wcrt_duration']));
            update_option('wcrt_coupon', isset($_POST['wcrt_coupon'])?1:0);
            update_option('wcrt_coupon_amount', intval($_POST['wcrt_coupon_amount']));
            update_option('wcrt_autoclear', isset($_POST['wcrt_autoclear'])?1:0);
            echo '<div class="updated"><p>Settings saved!</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>Cart Reminder Timer Settings</h1>
            <form method="post">
                <table class="form-table">
                    <tr><th>Timer Duration (minutes)</th>
                        <td><input type="number" name="wcrt_duration" value="<?php echo get_option('wcrt_duration',15); ?>"></td></tr>
                    <tr><th>Enable Coupon on Expiry</th>
                        <td><input type="checkbox" name="wcrt_coupon" <?php checked(get_option('wcrt_coupon'),1); ?>></td></tr>
                    <tr><th>Coupon Amount (%)</th>
                        <td><input type="number" name="wcrt_coupon_amount" value="<?php echo get_option('wcrt_coupon_amount',10); ?>"></td></tr>
                    <tr><th>Auto Clear Cart on Expiry</th>
                        <td><input type="checkbox" name="wcrt_autoclear" <?php checked(get_option('wcrt_autoclear'),1); ?>></td></tr>
                </table>
                <p><input type="submit" name="wcrt_save" class="button button-primary" value="Save Settings"></p>
            </form>
        </div>
        <?php
    });
});