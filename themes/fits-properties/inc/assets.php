<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'fp-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_style('fp-main', FP_THEME_URI . '/assets/css/main.css', ['fp-fonts'], FP_THEME_VERSION);
    wp_enqueue_script('fp-main', FP_THEME_URI . '/assets/js/main.js', [], FP_THEME_VERSION, true);

    if (is_singular('property')) {
        wp_enqueue_script('fp-gallery', FP_THEME_URI . '/assets/js/gallery.js', [], FP_THEME_VERSION, true);
    }
});
