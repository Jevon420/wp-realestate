<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_fp_load_more_properties', 'fp_handle_load_more_properties');
add_action('wp_ajax_nopriv_fp_load_more_properties', 'fp_handle_load_more_properties');

function fp_handle_load_more_properties()
{
    check_ajax_referer('fp_load_more', 'nonce');

    $page = isset($_POST['fp_page']) ? max(1, (int) $_POST['fp_page']) : 1;
    $request = wp_unslash($_POST);

    $query = new WP_Query(fp_property_query_args($request, $page));

    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/property-card');
        }
    }

    wp_reset_postdata();
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'hasMore' => $page < $query->max_num_pages,
    ]);
}
