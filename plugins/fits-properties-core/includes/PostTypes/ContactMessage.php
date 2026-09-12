<?php

namespace FitsPropertiesCore\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

class ContactMessage
{
    const POST_TYPE = 'contact_message';

    public function register()
    {
        add_action('init', [$this, 'registerPostType']);
    }

    public function registerPostType()
    {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name' => 'Contact Messages',
                'singular_name' => 'Contact Message',
                'all_items' => 'Contact Messages',
                'search_items' => 'Search Messages',
                'not_found' => 'No messages found',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-email-alt',
            'menu_position' => 25,
            'capability_type' => 'page',
            'supports' => ['title'],
            'map_meta_cap' => true,
        ]);
    }
}
