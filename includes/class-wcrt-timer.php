<?php
if(!defined('ABSPATH')) exit;

class WCRT_Timer {

    public static function init(){
        add_action('wp_footer', [__CLASS__, 'inject_timer_data'], 100);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_timer_assets']);
    }

    public static function enqueue_timer_assets(){
        wp_enqueue_script('wcrt-timer', plugins_url('assets/timer.js', dirname(__FILE__) . '/../cart-reminder-timer.php'), ['jquery'], '4.1', true);
        wp_enqueue_style('wcrt-timer-css', plugins_url('assets/timer.css', dirname(__FILE__) . '/../cart-reminder-timer.php'), [], '4.1');
        
        wp_add_inline_script('wcrt-timer', 'window.WCRT_DATA = window.WCRT_DATA || {};', 'before');
    }

    public static function inject_timer_data(){
        if(!is_cart() && !is_checkout()) return;
        if(!WC()->cart || WC()->cart->is_empty()) return;

        $start = WC()->session->get('wcrt_start') ?: time();
        WC()->session->set('wcrt_start', $start);

        $duration = get_option('wcrt_duration',15) * 60;
        $remaining = max(0, $duration - (time()-$start));
        $variant = WC()->session->get('wcrt_variant') ?: 'A';
        WC()->session->set('wcrt_variant', $variant);
        
        $show_timer = WC()->cart->get_subtotal() >= floatval(get_option('wcrt_min_cart', 0));

        $messages = [
            'A'=>[
                'user'=>get_option('wcrt_message_user','Hurry up! Your items are reserved.'),
                'guest'=>get_option('wcrt_message_guest','Hurry! Items reserved.')
            ],
            'B'=>[
                'user'=>get_option('wcrt_message_user','Prices guaranteed for a short time.'),
                'guest'=>get_option('wcrt_message_guest','Prices guaranteed for a short time.')
            ]
        ];
        
        if(!$show_timer) return;
        
        ?>
        <script>
        window.WCRT_DATA = {
            remaining: <?php echo intval($remaining); ?>,
            variant: '<?php echo esc_js($variant); ?>',
            messages: <?php echo wp_json_encode($messages); ?>,
            loggedIn: <?php echo is_user_logged_in()?'true':'false'; ?>,
            position: '<?php echo esc_js(get_option('wcrt_position','top')); ?>',
            show_on: '<?php echo esc_js(get_option('wcrt_show_on','both')); ?>'
        };
        </script>
        <?php
    }
}

WCRT_Timer::init();