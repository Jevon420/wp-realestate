<?php

namespace FitsPropertiesCore\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

class PostTypes
{
    public function register()
    {
        add_action('init', [$this, 'registerProperty']);
        add_action('init', [$this, 'registerAgent']);
        add_action('init', [$this, 'registerTestimonial']);
    }

    public function registerProperty()
    {
        register_post_type('property', [
            'labels' => [
                'name' => 'Properties',
                'singular_name' => 'Property',
                'add_new_item' => 'Add New Property',
                'edit_item' => 'Edit Property',
                'all_items' => 'All Properties',
                'search_items' => 'Search Properties',
                'not_found' => 'No properties found',
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'properties', 'with_front' => false],
            'menu_icon' => 'dashicons-admin-multisite',
            'menu_position' => 5,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author'],
            'show_in_rest' => true,
        ]);
    }

    public function registerAgent()
    {
        register_post_type('agent', [
            'labels' => [
                'name' => 'Agents',
                'singular_name' => 'Agent',
                'add_new_item' => 'Add New Agent',
                'edit_item' => 'Edit Agent',
                'all_items' => 'All Agents',
                'search_items' => 'Search Agents',
                'not_found' => 'No agents found',
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'agents', 'with_front' => false],
            'menu_icon' => 'dashicons-groups',
            'menu_position' => 6,
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
        ]);
    }

    public function registerTestimonial()
    {
        register_post_type('testimonial', [
            'labels' => [
                'name' => 'Testimonials',
                'singular_name' => 'Testimonial',
                'add_new_item' => 'Add New Testimonial',
                'edit_item' => 'Edit Testimonial',
                'all_items' => 'All Testimonials',
                'search_items' => 'Search Testimonials',
                'not_found' => 'No testimonials found',
            ],
            'public' => true,
            'has_archive' => false,
            'rewrite' => ['slug' => 'testimonials', 'with_front' => false],
            'menu_icon' => 'dashicons-star-filled',
            'menu_position' => 7,
            'supports' => ['title', 'editor', 'author'],
            'show_in_rest' => true,
        ]);
    }
}
