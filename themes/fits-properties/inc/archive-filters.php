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

    $listingType = !empty($_GET['fp_listing_type']) ? sanitize_key($_GET['fp_listing_type']) : '';

    $metaQuery = ['relation' => 'AND'];

    if ($listingType) {
        $metaQuery[] = [
            'key' => 'fpc_listing_type',
            'value' => $listingType,
        ];
    }

    if (!empty($_GET['fp_min_price']) || !empty($_GET['fp_max_price'])) {
        $min = !empty($_GET['fp_min_price']) ? (float) $_GET['fp_min_price'] : 0;
        $max = !empty($_GET['fp_max_price']) ? (float) $_GET['fp_max_price'] : 999999999999;

        $priceKeys = $listingType === 'rent' ? ['fpc_rental_price'] : ($listingType === 'sale' ? ['fpc_price'] : ['fpc_price', 'fpc_rental_price']);
        $priceClause = count($priceKeys) > 1 ? ['relation' => 'OR'] : [];

        foreach ($priceKeys as $key) {
            $priceClause[] = [
                'key' => $key,
                'value' => [$min, $max],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        }

        $metaQuery[] = $priceClause;
    }

    if (!empty($_GET['fp_min_beds'])) {
        $metaQuery[] = [
            'key' => 'fpc_bedrooms',
            'value' => (int) $_GET['fp_min_beds'],
            'type' => 'NUMERIC',
            'compare' => '>=',
        ];
    }

    if (!empty($_GET['fp_min_baths'])) {
        $metaQuery[] = [
            'key' => 'fpc_bathrooms',
            'value' => (float) $_GET['fp_min_baths'],
            'type' => 'NUMERIC',
            'compare' => '>=',
        ];
    }

    if (!empty($_GET['fp_furnished'])) {
        $metaQuery[] = [
            'key' => 'fpc_furnished',
            'value' => 1,
        ];
    }

    if (count($metaQuery) > 1) {
        $query->set('meta_query', $metaQuery);
    }

    $taxQuery = ['relation' => 'AND'];

    if (!empty($_GET['fp_city']) && !$query->is_tax('location')) {
        $taxQuery[] = [
            'taxonomy' => 'location',
            'field' => 'slug',
            'terms' => sanitize_title($_GET['fp_city']),
        ];
    } elseif (!empty($_GET['fp_location']) && !$query->is_tax('location')) {
        // Zones are parent terms; WordPress automatically includes their
        // child cities when matching a hierarchical taxonomy by term.
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

    if (!empty($_GET['fp_features']) && is_array($_GET['fp_features'])) {
        // One clause per feature (relation AND) so a property must have
        // every checked feature, not just any one of them.
        foreach ($_GET['fp_features'] as $featureSlug) {
            $taxQuery[] = [
                'taxonomy' => 'property_feature',
                'field' => 'slug',
                'terms' => sanitize_title($featureSlug),
            ];
        }
    }

    if (count($taxQuery) > 1) {
        $query->set('tax_query', $taxQuery);
    }

    $sort = !empty($_GET['fp_sort']) ? sanitize_key($_GET['fp_sort']) : 'newest';

    if ($sort === 'price_low' || $sort === 'price_high') {
        $query->set('fp_sort_by_price', $sort === 'price_high' ? 'desc' : 'asc');
    } elseif ($sort === 'beds_high') {
        $query->set('meta_key', 'fpc_bedrooms');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'DESC');
    }
});

/**
 * "Price" isn't a single column — sale listings store it in fpc_price,
 * rentals in fpc_rental_price. This sorts by whichever one a property
 * actually has, via a manual join, only when fp_sort_by_price is set on
 * the query (so it never affects unrelated queries elsewhere on the site).
 */
add_filter('posts_clauses', function ($clauses, $query) {
    $direction = $query->get('fp_sort_by_price');

    if (!$direction || is_admin()) {
        return $clauses;
    }

    global $wpdb;
    $direction = $direction === 'desc' ? 'DESC' : 'ASC';

    $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS fp_price_sale ON fp_price_sale.post_id = {$wpdb->posts}.ID AND fp_price_sale.meta_key = 'fpc_price'";
    $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS fp_price_rent ON fp_price_rent.post_id = {$wpdb->posts}.ID AND fp_price_rent.meta_key = 'fpc_rental_price'";
    $clauses['orderby'] = "COALESCE(NULLIF(fp_price_sale.meta_value, ''), NULLIF(fp_price_rent.meta_value, ''))+0 {$direction}";

    return $clauses;
}, 10, 2);
