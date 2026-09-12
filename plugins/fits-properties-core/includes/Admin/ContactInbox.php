<?php

namespace FitsPropertiesCore\Admin;

use FitsPropertiesCore\PostTypes\ContactMessage;

if (!defined('ABSPATH')) {
    exit;
}

class ContactInbox
{
    public function register()
    {
        add_filter('manage_' . ContactMessage::POST_TYPE . '_posts_columns', [$this, 'columns']);
        add_action('manage_' . ContactMessage::POST_TYPE . '_posts_custom_column', [$this, 'renderColumn'], 10, 2);
        add_filter('post_row_actions', [$this, 'removeQuickEdit'], 10, 2);
    }

    public function columns($columns)
    {
        return [
            'cb' => $columns['cb'],
            'title' => 'From',
            'fp_email' => 'Email',
            'fp_phone' => 'Phone',
            'fp_message' => 'Message',
            'date' => $columns['date'],
        ];
    }

    public function renderColumn($column, $postId)
    {
        switch ($column) {
            case 'fp_email':
                echo esc_html(get_post_meta($postId, 'fp_email', true));
                break;
            case 'fp_phone':
                echo esc_html(get_post_meta($postId, 'fp_phone', true) ?: '—');
                break;
            case 'fp_message':
                echo esc_html(wp_trim_words(get_post_meta($postId, 'fp_message', true), 15));
                break;
        }
    }

    public function removeQuickEdit($actions, $post)
    {
        if ($post->post_type === ContactMessage::POST_TYPE) {
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }
}
