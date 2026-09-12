<?php

namespace FitsPropertiesCore\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

class Taxonomies
{
    public function register()
    {
        add_action('init', [$this, 'registerPropertyType']);
        add_action('init', [$this, 'registerLocation']);
        add_action('init', [$this, 'registerPropertyFeature']);
    }

    public function registerPropertyType()
    {
        register_taxonomy('property_type', ['property'], [
            'labels' => [
                'name' => 'Property Types',
                'singular_name' => 'Property Type',
                'add_new_item' => 'Add New Property Type',
                'search_items' => 'Search Property Types',
            ],
            'hierarchical' => true,
            'public' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'property-type', 'with_front' => false],
            'show_in_rest' => true,
        ]);
    }

    /**
     * Zones and cities share one hierarchical taxonomy: a Zone is a
     * top-level term, its Cities are child terms — mirrors the
     * Laravel Zone hasMany City relationship in one taxonomy.
     */
    public function registerLocation()
    {
        register_taxonomy('location', ['property'], [
            'labels' => [
                'name' => 'Locations (Zones & Cities)',
                'singular_name' => 'Location',
                'add_new_item' => 'Add New Zone/City',
                'search_items' => 'Search Locations',
                'parent_item' => 'Parent Zone',
                'parent_item_colon' => 'Parent Zone:',
            ],
            'hierarchical' => true,
            'public' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'location', 'with_front' => false],
            'show_in_rest' => true,
        ]);
    }

    public function registerPropertyFeature()
    {
        register_taxonomy('property_feature', ['property'], [
            'labels' => [
                'name' => 'Features & Amenities',
                'singular_name' => 'Feature',
                'add_new_item' => 'Add New Feature',
                'search_items' => 'Search Features',
            ],
            'hierarchical' => false,
            'public' => true,
            'show_admin_column' => false,
            'rewrite' => ['slug' => 'feature', 'with_front' => false],
            'show_in_rest' => true,
        ]);
    }
}
