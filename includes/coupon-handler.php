<?php
add_action('wp', function(){
    if(!WC()->cart || WC()->cart->is_empty()) return;

    $start = WC()->session->get('wcrt_start');
    if(!$start) return;

    $duration = get_option('wcrt_duration',15)*60;
    $remaining = $duration - (time() - $start);
    if($remaining>0) return;

    if(WC()->session->get('wcrt_expired')) return;
    WC()->session->set('wcrt_expired',1);

    // Auto coupon
    if(get_option('wcrt_coupon')){
        $code = 'CRT-'.strtoupper(wp_generate_password(6,false,false));
        $amount = get_option('wcrt_coupon_amount',10);

        if(!post_exists($code,'','shop_coupon')){
            $post = [
                'post_title'=>$code,
                'post_content'=>'Cart Reminder Timer coupon',
                'post_status'=>'publish',
                'post_author'=>1,
                'post_type'=>'shop_coupon'
            ];
            $post_id = wp_insert_post($post);
            update_post_meta($post_id,'discount_type','percent');
            update_post_meta($post_id,'coupon_amount',$amount);
            update_post_meta($post_id,'individual_use','yes');
            update_post_meta($post_id,'usage_limit',1);
            update_post_meta($post_id,'apply_before_tax','yes');
        }
        WC()->cart->add_discount($code);
    } elseif(get_option('wcrt_autoclear',0)){
        WC()->cart->empty_cart();
    }
});