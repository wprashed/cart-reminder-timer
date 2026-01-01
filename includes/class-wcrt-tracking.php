<?php
if(!defined('ABSPATH')) exit;

class WCRT_Tracking {

    public static function init(){
        add_action('wp_footer', [__CLASS__, 'track_cart']);
        add_action('woocommerce_thankyou', [__CLASS__, 'track_conversion']);
    }

    public static function create_tables(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcrt_abandoned_carts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20),
            cart_value decimal(10,2),
            variant varchar(1),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            reminded tinyint(1) DEFAULT 0,
            converted tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function track_cart(){
        if(!is_user_logged_in() || !WC()->cart || WC()->cart->is_empty()) return;

        global $wpdb;
        $table = $wpdb->prefix . 'wcrt_abandoned_carts';

        // Check if already tracked in this session
        if(WC()->session->get('wcrt_tracked')) return;

        $wpdb->insert($table, [
            'user_id' => get_current_user_id(),
            'cart_value' => WC()->cart->get_subtotal(),
            'variant' => WC()->session->get('wcrt_variant') ?: 'A',
            'created_at' => current_time('mysql')
        ]);

        WC()->session->set('wcrt_tracked', 1);
    }

    public static function track_conversion($order_id){
        global $wpdb;
        $table = $wpdb->prefix . 'wcrt_abandoned_carts';
        $order = wc_get_order($order_id);

        if(!$order || !$order->get_user_id()) return;

        $wpdb->update($table, ['converted' => 1], ['user_id' => $order->get_user_id()], ['%d'], ['%d']);
    }
}

WCRT_Tracking::init();
