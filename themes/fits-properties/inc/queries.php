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
        // Properties are tagged with a city (child term), not its zone —
        // pad_counts rolls child-term counts up into the parent so a
        // zone's count reflects all its cities' properties too.
        'pad_counts' => true,
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

/**
 * Properties sharing a Property Type or Location with the given one,
 * topped up with the latest properties if there aren't enough matches.
 */
function fp_get_related_properties($postId, $count = 3)
{
    $excludeIds = [$postId];
    $found = [];

    $typeTerms = wp_get_post_terms($postId, 'property_type', ['fields' => 'ids']);
    $locationTerms = wp_get_post_terms($postId, 'location', ['fields' => 'ids']);

    if (!empty($typeTerms) || !empty($locationTerms)) {
        $taxQuery = ['relation' => 'OR'];

        if (!empty($typeTerms)) {
            $taxQuery[] = ['taxonomy' => 'property_type', 'field' => 'term_id', 'terms' => $typeTerms];
        }
        if (!empty($locationTerms)) {
            $taxQuery[] = ['taxonomy' => 'location', 'field' => 'term_id', 'terms' => $locationTerms];
        }

        $matches = get_posts([
            'post_type' => 'property',
            'posts_per_page' => $count,
            'post__not_in' => $excludeIds,
            'tax_query' => $taxQuery,
            'fields' => 'ids',
        ]);

        $found = array_merge($found, $matches);
        $excludeIds = array_merge($excludeIds, $matches);
    }

    if (count($found) < $count) {
        $topUp = get_posts([
            'post_type' => 'property',
            'posts_per_page' => $count - count($found),
            'post__not_in' => $excludeIds,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
        ]);

        $found = array_merge($found, $topUp);
    }

    if (empty($found)) {
        // WP_Query with an empty post__in would ignore it and return
        // everything, so return an empty result explicitly instead.
        return new WP_Query(['post__in' => [0]]);
    }

    return new WP_Query([
        'post_type' => 'property',
        'post__in' => $found,
        'orderby' => 'post__in',
        'posts_per_page' => $count,
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
