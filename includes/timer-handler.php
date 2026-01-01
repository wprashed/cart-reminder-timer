<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_footer', function(){
    if(!WC()->session || WC()->cart->is_empty()) return;

    $start = WC()->session->get('wcrt_start') ?: time();
    WC()->session->set('wcrt_start', $start);

    $duration = get_option('wcrt_duration',15)*60;
    $remaining = max(0, $duration - (time() - $start));
    $variant = WC()->session->get('wcrt_variant') ?: 'A';
    WC()->session->set('wcrt_variant', $variant);

    ?>
    <script>
    window.WCRT_DATA = {
        remaining: <?php echo $remaining; ?>,
        variant: '<?php echo $variant; ?>',
        messages: {A:{user:'Hurry up! Your items are reserved.', guest:'Hurry! Items reserved.'}, B:{user:'Prices guaranteed for a short time.', guest:'Prices guaranteed for a short time.'}},
        loggedIn: <?php echo is_user_logged_in()?1:0; ?>
    };
    </script>
    <?php
});