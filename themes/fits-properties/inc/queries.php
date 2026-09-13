<?php

if (!defined('ABSPATH')) {
    exit;
}

function fp_get_featured_properties($count = 6)
{
    return new WP_Query([
        'post_type' => 'property',
        'posts_per_page' => $count,
        'meta_key' => 'fpc_is_featured',
        'meta_value' => 1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
}

function fp_get_latest_properties($count = 6, $excludeIds = [])
{
    return new WP_Query([
        'post_type' => 'property',
        'posts_per_page' => $count,
        'post__not_in' => $excludeIds,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
}

function fp_get_property_types($count = 6)
{
    return get_terms([
        'taxonomy' => 'property_type',
        'hide_empty' => false,
        'number' => $count,
    ]);
}

function fp_get_top_level_locations()
{
    return get_terms([
        'taxonomy' => 'location',
        'hide_empty' => false,
        'parent' => 0,
    ]);
}

/**
 * Cities are the child terms of the "location" taxonomy (a Zone is the
 * parent, its Cities are children) — this returns just the cities,
 * across all zones, each labeled with its parent zone's name.
 */
function fp_get_cities()
{
    $all = get_terms(['taxonomy' => 'location', 'hide_empty' => false]);

    if (is_wp_error($all)) {
        return [];
    }

    return array_values(array_filter($all, function ($term) {
        return $term->parent !== 0;
    }));
}

function fp_get_property_features()
{
    return get_terms([
        'taxonomy' => 'property_feature',
        'hide_empty' => false,
    ]);
}

function fp_get_agents($count = -1)
{
    return new WP_Query([
        'post_type' => 'agent',
        'posts_per_page' => $count,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
}

function fp_get_testimonials($count = 6)
{
    return new WP_Query([
        'post_type' => 'testimonial',
        'posts_per_page' => $count,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
}
