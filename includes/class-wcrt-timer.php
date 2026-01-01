<?php
if(!defined('ABSPATH')) exit;

add_action('wp_footer',function(){
    if(!WC()->session || !WC()->cart || WC()->cart->is_empty()) return;

    $start = WC()->session->get('wcrt_start') ?: time();
    WC()->session->set('wcrt_start',$start);

    $duration = get_option('wcrt_duration',15)*60;
    $remaining = max(0,$duration-(time()-$start));
    $variant = WC()->session->get('wcrt_variant') ?: 'A';
    WC()->session->set('wcrt_variant',$variant);

    $messages = [
        'A'=>['user'=>get_option('wcrt_message_user','Hurry up! Your items are reserved.'),
              'guest'=>get_option('wcrt_message_guest','Hurry! Items reserved.')],
        'B'=>['user'=>get_option('wcrt_message_user','Prices guaranteed for a short time.'),
              'guest'=>get_option('wcrt_message_guest','Prices guaranteed for a short time.')]
    ];
    ?>
    <script>
    window.WCRT_DATA = {
        remaining: <?php echo $remaining; ?>,
        variant: '<?php echo $variant; ?>',
        messages: <?php echo json_encode($messages); ?>,
        loggedIn: <?php echo is_user_logged_in()?1:0; ?>,
        position: '<?php echo get_option('wcrt_position','top'); ?>',
        show_on: '<?php echo get_option('wcrt_show_on','both'); ?>'
    };
    </script>
    <?php
});