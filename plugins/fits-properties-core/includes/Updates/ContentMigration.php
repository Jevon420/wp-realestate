<?php

namespace FitsPropertiesCore\Updates;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The block editor was removed from property/agent/testimonial in favor of
 * plain-text fields (fpc_description/fpc_bio/fpc_testimonial_text), since a
 * non-technical user found the block editor too complex. Anything already
 * written into post_content before that change is copied over once so
 * existing content doesn't appear to vanish from the site.
 */
class ContentMigration
{
    const OPTION_KEY = 'fpc_migrated_content_fields_v1';

    public function register()
    {
        add_action('admin_init', [$this, 'maybeRun']);
    }

    public function maybeRun()
    {
        if (get_option(self::OPTION_KEY)) {
            return;
        }

        $this->copy('property', 'fpc_description');
        $this->copy('agent', 'fpc_bio');
        $this->copy('testimonial', 'fpc_testimonial_text');

        update_option(self::OPTION_KEY, 1);
    }

    private function copy($postType, $metaKey)
    {
        $posts = get_posts([
            'post_type' => $postType,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
        ]);

        foreach ($posts as $postId) {
            $existing = get_post_meta($postId, $metaKey, true);

            if ($existing !== '') {
                continue;
            }

            $content = get_post_field('post_content', $postId);

            if ($content !== '') {
                update_post_meta($postId, $metaKey, wp_strip_all_tags($content));
            }
        }
    }
}
