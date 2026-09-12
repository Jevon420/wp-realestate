<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('pre_get_posts', function (WP_Query $query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    $isPropertyContext = $query->is_post_type_archive('property') || $query->is_tax('location') || $query->is_tax('property_type');

    if (!$isPropertyContext) {
        return;
    }

    $query->set('post_type', 'property');
    $query->set('posts_per_page', 12);

    $metaQuery = [];

    if (!empty($_GET['fp_listing_type'])) {
        $metaQuery[] = [
            'key' => 'fpc_listing_type',
            'value' => sanitize_key($_GET['fp_listing_type']),
        ];
    }

    if (!empty($_GET['fp_min_price']) || !empty($_GET['fp_max_price'])) {
        $min = !empty($_GET['fp_min_price']) ? (float) $_GET['fp_min_price'] : 0;
        $max = !empty($_GET['fp_max_price']) ? (float) $_GET['fp_max_price'] : 999999999;

        $metaQuery[] = [
            'key' => 'fpc_price',
            'value' => [$min, $max],
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN',
        ];
    }

    if (!empty($metaQuery)) {
        $query->set('meta_query', $metaQuery);
    }

    $taxQuery = [];

    if (!empty($_GET['fp_location']) && !$query->is_tax('location')) {
        $taxQuery[] = [
            'taxonomy' => 'location',
            'field' => 'slug',
            'terms' => sanitize_title($_GET['fp_location']),
        ];
    }

    if (!empty($_GET['fp_type']) && !$query->is_tax('property_type')) {
        $taxQuery[] = [
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => sanitize_title($_GET['fp_type']),
        ];
    }

    if (!empty($taxQuery)) {
        $query->set('tax_query', $taxQuery);
    }
});
