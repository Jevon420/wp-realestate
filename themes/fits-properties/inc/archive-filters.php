<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds WP_Query args from a request array (works with $_GET on the page
 * load, or $_POST forwarded from Load More — same shape either way) so
 * the property archive/taxonomy pages and the AJAX "Load More" endpoint
 * always filter identically.
 *
 * $applyLocationFilter/$applyTypeFilter are set to false by pre_get_posts
 * when the main query is already scoped to that taxonomy by the URL
 * itself (e.g. /location/northgate/), to avoid a redundant/conflicting
 * tax_query clause; the AJAX handler always leaves them true since it has
 * no such implicit scoping and must be told explicitly via fp_location/
 * fp_type (or fp_taxonomy/fp_term for a plain term-archive "Load More").
 */
function fp_property_query_args($request, $paged = 1, $applyLocationFilter = true, $applyTypeFilter = true)
{
    $args = [
        'post_type' => 'property',
        'posts_per_page' => 12,
        'paged' => $paged,
    ];

    if (!empty($request['s'])) {
        $args['s'] = sanitize_text_field($request['s']);
    }

    $listingType = !empty($request['fp_listing_type']) ? sanitize_key($request['fp_listing_type']) : '';

    $metaQuery = ['relation' => 'AND'];

    if ($listingType) {
        $metaQuery[] = [
            'key' => 'fpc_listing_type',
            'value' => $listingType,
        ];
    }

    if (!empty($request['fp_min_price']) || !empty($request['fp_max_price'])) {
        $min = !empty($request['fp_min_price']) ? (float) $request['fp_min_price'] : 0;
        $max = !empty($request['fp_max_price']) ? (float) $request['fp_max_price'] : 999999999999;

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

    if (!empty($request['fp_min_beds'])) {
        $metaQuery[] = [
            'key' => 'fpc_bedrooms',
            'value' => (int) $request['fp_min_beds'],
            'type' => 'NUMERIC',
            'compare' => '>=',
        ];
    }

    if (!empty($request['fp_min_baths'])) {
        $metaQuery[] = [
            'key' => 'fpc_bathrooms',
            'value' => (float) $request['fp_min_baths'],
            'type' => 'NUMERIC',
            'compare' => '>=',
        ];
    }

    if (!empty($request['fp_furnished'])) {
        $metaQuery[] = [
            'key' => 'fpc_furnished',
            'value' => 1,
        ];
    }

    if (count($metaQuery) > 1) {
        $args['meta_query'] = $metaQuery;
    }

    $taxQuery = ['relation' => 'AND'];

    if (!empty($request['fp_city']) && $applyLocationFilter) {
        $taxQuery[] = [
            'taxonomy' => 'location',
            'field' => 'slug',
            'terms' => sanitize_title($request['fp_city']),
        ];
    } elseif (!empty($request['fp_location']) && $applyLocationFilter) {
        // Zones are parent terms; WordPress automatically includes their
        // child cities when matching a hierarchical taxonomy by term.
        $taxQuery[] = [
            'taxonomy' => 'location',
            'field' => 'slug',
            'terms' => sanitize_title($request['fp_location']),
        ];
    }

    if (!empty($request['fp_type']) && $applyTypeFilter) {
        $taxQuery[] = [
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => sanitize_title($request['fp_type']),
        ];
    }

    if (!empty($request['fp_features']) && is_array($request['fp_features'])) {
        // One clause per feature (relation AND) so a property must have
        // every checked feature, not just any one of them.
        foreach ($request['fp_features'] as $featureSlug) {
            $taxQuery[] = [
                'taxonomy' => 'property_feature',
                'field' => 'slug',
                'terms' => sanitize_title($featureSlug),
            ];
        }
    }

    // A plain term-archive "Load More" request (no filter form on that
    // page) tells us the term directly instead of via fp_location/fp_type.
    if (!empty($request['fp_taxonomy']) && !empty($request['fp_term'])) {
        $taxQuery[] = [
            'taxonomy' => sanitize_key($request['fp_taxonomy']),
            'field' => 'slug',
            'terms' => sanitize_title($request['fp_term']),
        ];
    }

    if (count($taxQuery) > 1) {
        $args['tax_query'] = $taxQuery;
    }

    $sort = !empty($request['fp_sort']) ? sanitize_key($request['fp_sort']) : 'newest';

    if ($sort === 'price_low' || $sort === 'price_high') {
        $args['fp_sort_by_price'] = $sort === 'price_high' ? 'desc' : 'asc';
    } elseif ($sort === 'beds_high') {
        $args['meta_key'] = 'fpc_bedrooms';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
    }

    return $args;
}

add_action('pre_get_posts', function (WP_Query $query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    $isPropertyContext = $query->is_post_type_archive('property') || $query->is_tax('location') || $query->is_tax('property_type');

    if (!$isPropertyContext) {
        return;
    }

    $args = fp_property_query_args($_GET, 1, !$query->is_tax('location'), !$query->is_tax('property_type'));
    unset($args['paged']); // the main query already reads the page number from the URL

    foreach ($args as $key => $value) {
        $query->set($key, $value);
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
