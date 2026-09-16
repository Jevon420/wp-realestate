<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'fp-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_style('fp-main', FP_THEME_URI . '/assets/css/main.css', ['fp-fonts'], FP_THEME_VERSION);
    wp_enqueue_script('fp-main', FP_THEME_URI . '/assets/js/main.js', [], FP_THEME_VERSION, true);
    wp_enqueue_script('fp-animate', FP_THEME_URI . '/assets/js/animate.js', [], FP_THEME_VERSION, true);

    if (is_singular('property')) {
        wp_enqueue_script('fp-lightbox', FP_THEME_URI . '/assets/js/lightbox.js', [], FP_THEME_VERSION, true);

        $lat = get_post_meta(get_the_ID(), 'fpc_latitude', true);
        $lng = get_post_meta(get_the_ID(), 'fpc_longitude', true);

        if ($lat !== '' && $lng !== '') {
            wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
            wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
            wp_enqueue_script('fp-map', FP_THEME_URI . '/assets/js/map.js', ['leaflet'], FP_THEME_VERSION, true);
        }
    }

    $isPropertyListing = is_post_type_archive('property') || is_tax('location') || is_tax('property_type');

    if ($isPropertyListing) {
        wp_enqueue_script('fp-load-more', FP_THEME_URI . '/assets/js/load-more.js', ['fp-animate'], FP_THEME_VERSION, true);
        wp_localize_script('fp-load-more', 'fpLoadMore', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fp_load_more'),
        ]);
    }
});
