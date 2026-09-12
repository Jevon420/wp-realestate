<?php

namespace FitsPropertiesCore\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class AdminAssets
{
    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue($hook)
    {
        global $post_type;

        $ourPostTypes = ['property', 'agent', 'testimonial'];
        $isEditScreen = in_array($hook, ['post.php', 'post-new.php'], true) && in_array($post_type, $ourPostTypes, true);
        $isSetupPage = $hook === 'toplevel_page_fits-properties-setup';

        if (!$isEditScreen && !$isSetupPage) {
            return;
        }

        wp_enqueue_style('fpc-admin', FPC_PLUGIN_URL . 'assets/css/admin.css', [], FPC_VERSION);

        if ($isSetupPage) {
            return;
        }

        if ($post_type === 'property') {
            wp_enqueue_media();
        }

        wp_enqueue_script(
            'fpc-admin-meta',
            FPC_PLUGIN_URL . 'assets/js/admin-meta.js',
            ['jquery'],
            FPC_VERSION,
            true
        );
    }
}
