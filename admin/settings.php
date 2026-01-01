<?php
add_action('admin_menu',function(){
    add_options_page('Cart Reminder Timer','Cart Reminder Timer','manage_options','woo-cart-reminder-timer','wcrt_settings_page');
});

add_action('admin_init',function(){
    register_setting('wcrt_settings','wcrt_duration');
    register_setting('wcrt_settings','wcrt_autoclear');
    register_setting('wcrt_settings','wcrt_coupon');
    register_setting('wcrt_settings','wcrt_coupon_amount');
    register_setting('wcrt_settings','wcrt_msg_a_guest');
    register_setting('wcrt_settings','wcrt_msg_a_user');
    register_setting('wcrt_settings','wcrt_msg_b_guest');
    register_setting('wcrt_settings','wcrt_msg_b_user');
});

function wcrt_settings_page(){ ?>
    <div class="wrap">
        <h1>Cart Reminder Timer Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('wcrt_settings'); do_settings_sections('wcrt_settings'); ?>
            <table class="form-table">
                <tr><th>Timer Duration (minutes)</th>
                    <td><input type="number" min="1" name="wcrt_duration" value="<?php echo esc_attr(get_option('wcrt_duration',15)); ?>" /></td></tr>
                <tr><th>Auto-clear Cart</th>
                    <td><input type="checkbox" name="wcrt_autoclear" value="1" <?php checked(get_option('wcrt_autoclear'),1); ?> /></td></tr>
                <tr><th>Auto-create Coupon on Expiry</th>
                    <td><input type="checkbox" name="wcrt_coupon" value="1" <?php checked(get_option('wcrt_coupon'),1); ?> /></td></tr>
                <tr><th>Coupon Amount (%)</th>
                    <td><input type="number" name="wcrt_coupon_amount" min="1" max="100" value="<?php echo esc_attr(get_option('wcrt_coupon_amount',10)); ?>" /></td></tr>
                <tr><th>Variant A Guest Message</th>
                    <td><input type="text" name="wcrt_msg_a_guest" value="<?php echo esc_attr(get_option('wcrt_msg_a_guest','Prices are reserved for')); ?>" /></td></tr>
                <tr><th>Variant A User Message</th>
                    <td><input type="text" name="wcrt_msg_a_user" value="<?php echo esc_attr(get_option('wcrt_msg_a_user','Your exclusive price is reserved for')); ?>" /></td></tr>
                <tr><th>Variant B Guest Message</th>
                    <td><input type="text" name="wcrt_msg_b_guest" value="<?php echo esc_attr(get_option('wcrt_msg_b_guest','Hurry! Cart expires in')); ?>" /></td></tr>
                <tr><th>Variant B User Message</th>
                    <td><input type="text" name="wcrt_msg_b_user" value="<?php echo esc_attr(get_option('wcrt_msg_b_user','Complete checkout within')); ?>" /></td></tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php }
