<?php
if(!defined('ABSPATH')) exit;

class WCRT_Timer {

    public static function init(){
        add_action('wp_footer', [__CLASS__, 'inject_timer_data']);
    }

    public static function inject_timer_data(){
        if(!WC()->cart || WC()->cart->is_empty()) return;

        $min_cart = floatval(get_option('wcrt_min_cart', 0));
        if($min_cart > 0 && WC()->cart->get_subtotal() < $min_cart) return;

        $start = WC()->session->get('wcrt_start') ?: time();
        WC()->session->set('wcrt_start', $start);

        $duration = get_option('wcrt_duration', 15) * 60;
        $remaining = max(0, $duration - (time() - $start));
        $variant = WC()->session->get('wcrt_variant') ?: (rand(0, 1) ? 'A' : 'B');
        WC()->session->set('wcrt_variant', $variant);

        $messages = [
            'A' => [
                'user' => get_option('wcrt_message_user', 'Hurry up! Your items are reserved.'),
                'guest' => get_option('wcrt_message_guest', 'Hurry! Items reserved for a limited time.')
            ],
            'B' => [
                'user' => get_option('wcrt_message_user', 'Hurry up! Your items are reserved.'),
                'guest' => get_option('wcrt_message_guest', 'Hurry! Items reserved for a limited time.')
            ]
        ];
        
        $data = [
            'remaining' => $remaining,
            'variant' => $variant,
            'messages' => $messages,
            'loggedIn' => is_user_logged_in() ? 1 : 0,
            'position' => get_option('wcrt_position', 'top'),
            'show_on' => get_option('wcrt_show_on', 'both'),
            'color_scheme' => get_option('wcrt_color_scheme', 'danger'),
            'dismissable' => get_option('wcrt_dismissable', 0),
            'show_progress' => get_option('wcrt_show_progress', 1),
            'enable_sound' => get_option('wcrt_enable_sound', 0),
            'duration' => $duration
        ];
        ?>
        <script>
        window.WCRT_DATA = <?php echo wp_json_encode($data); ?>;
        </script>
        <?php
    }
}

WCRT_Timer::init();
