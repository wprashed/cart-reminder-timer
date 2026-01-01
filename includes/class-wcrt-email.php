<?php
if(!defined('ABSPATH')) exit;

class WCRT_Email {

    public static function init(){
        add_action('wp_loaded', [__CLASS__, 'schedule_reminders']);
    }

    public static function schedule_reminders(){
        if(!wp_next_scheduled('wcrt_send_email_reminders')){
            wp_schedule_event(time(), 'hourly', 'wcrt_send_email_reminders');
        }
        add_action('wcrt_send_email_reminders', [__CLASS__, 'send_abandoned_reminders']);
    }

    public static function send_abandoned_reminders(){
        if(!get_option('wcrt_enable_email')) return;

        global $wpdb;
        $table = $wpdb->prefix . 'wcrt_abandoned_carts';

        // Send emails for carts that will expire in 5 minutes
        $carts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE reminded = 0 AND created_at < DATE_SUB(NOW(), INTERVAL %d MINUTE)",
            intval(get_option('wcrt_duration', 15)) - 5
        ));

        foreach($carts as $cart){
            $user = get_user_by('ID', $cart->user_id);
            if(!$user) continue;

            $subject = 'Your cart is about to expire!';
            $message = sprintf(
                'Hi %s,<br><br>Your cart items are reserved for only a few more minutes. <a href="%s">Complete your purchase now</a> before your items are released!<br><br>Best regards',
                $user->first_name ?: $user->display_name,
                wc_get_cart_url()
            );

            wp_mail($user->user_email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);

            $wpdb->update($table, ['reminded' => 1], ['id' => $cart->id]);
        }
    }
}

WCRT_Email::init();
