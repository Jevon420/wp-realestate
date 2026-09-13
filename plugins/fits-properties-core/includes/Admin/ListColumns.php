<?php

namespace FitsPropertiesCore\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class ListColumns
{
    public function register()
    {
        add_filter('manage_property_posts_columns', [$this, 'propertyColumns']);
        add_action('manage_property_posts_custom_column', [$this, 'renderPropertyColumn'], 10, 2);
    }

    public function propertyColumns($columns)
    {
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ($key === 'title') {
                $new['fpc_price'] = 'Price';
                $new['fpc_listing_type'] = 'Listing Type';
                $new['fpc_status'] = 'Status';
                $new['fpc_agent'] = 'Agent';
                $new['fpc_featured'] = 'Featured';
            }
        }

        return $new;
    }

    public function renderPropertyColumn($column, $postId)
    {
        switch ($column) {
            case 'fpc_price':
                $price = get_post_meta($postId, 'fpc_price', true);
                echo $price !== '' ? '$' . esc_html(number_format((float) $price, 2)) : '&mdash;';
                break;
            case 'fpc_listing_type':
                $type = get_post_meta($postId, 'fpc_listing_type', true);
                echo esc_html($type ? ucfirst($type) : '—');
                break;
            case 'fpc_status':
                $status = get_post_meta($postId, 'fpc_status', true) ?: 'active';
                $labels = ['active' => 'Active', 'pending' => 'Pending', 'sold' => 'Sold', 'rented' => 'Rented'];
                echo esc_html($labels[$status] ?? ucfirst($status));
                break;
            case 'fpc_agent':
                $agentId = (int) get_post_meta($postId, 'fpc_agent_id', true);
                if ($agentId && get_post($agentId)) {
                    echo '<a href="' . esc_url(get_edit_post_link($agentId)) . '">' . esc_html(get_the_title($agentId)) . '</a>';
                } else {
                    echo '&mdash;';
                }
                break;
            case 'fpc_featured':
                echo get_post_meta($postId, 'fpc_is_featured', true) ? '&#9733; Yes' : '&mdash;';
                break;
        }
    }
}
