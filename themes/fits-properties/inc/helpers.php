<?php

if (!defined('ABSPATH')) {
    exit;
}

function fp_format_price($amount)
{
    if ($amount === '' || $amount === null) {
        return null;
    }

    return '$' . number_format((float) $amount, 0);
}

function fp_property_price_label($postId)
{
    $listingType = get_post_meta($postId, 'fpc_listing_type', true);

    if ($listingType === 'rent') {
        $rentalPrice = get_post_meta($postId, 'fpc_rental_price', true);
        $frequency = get_post_meta($postId, 'fpc_rental_frequency', true) ?: 'monthly';
        $frequencyLabels = ['monthly' => 'month', 'weekly' => 'week', 'yearly' => 'year'];
        $price = fp_format_price($rentalPrice);

        return $price ? $price . ' / ' . ($frequencyLabels[$frequency] ?? $frequency) : 'Price on request';
    }

    $price = fp_format_price(get_post_meta($postId, 'fpc_price', true));

    return $price ?: 'Price on request';
}

function fp_property_gallery_ids($postId)
{
    $ids = get_post_meta($postId, 'fpc_gallery', true);

    return $ids ? array_filter(array_map('intval', explode(',', $ids))) : [];
}

function fp_property_locations($postId)
{
    return get_the_terms($postId, 'location');
}

function fp_property_location_label($postId)
{
    $terms = fp_property_locations($postId);

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    // Prefer the most specific (child/city) term.
    usort($terms, function ($a, $b) {
        return $b->parent <=> $a->parent;
    });

    $city = $terms[0];
    $zone = $city->parent ? get_term($city->parent, 'location') : null;

    if ($zone && !is_wp_error($zone)) {
        return $city->name . ', ' . $zone->name;
    }

    return $city->name;
}

function fp_stars($rating, $max = 5)
{
    $rating = (int) $rating;
    $out = '';

    for ($i = 1; $i <= $max; $i++) {
        $out .= $i <= $rating ? '&#9733;' : '&#9734;';
    }

    return $out;
}

function fp_agent_for_property($postId)
{
    $agentId = (int) get_post_meta($postId, 'fpc_agent_id', true);

    if (!$agentId) {
        return null;
    }

    $agent = get_post($agentId);

    return ($agent && $agent->post_type === 'agent' && $agent->post_status === 'publish') ? $agent : null;
}

function fp_property_specs($postId)
{
    return [
        'bedrooms' => get_post_meta($postId, 'fpc_bedrooms', true),
        'bathrooms' => get_post_meta($postId, 'fpc_bathrooms', true),
        'area' => get_post_meta($postId, 'fpc_area', true),
        'garage' => get_post_meta($postId, 'fpc_garage', true),
    ];
}
