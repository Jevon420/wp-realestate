<?php

namespace FitsPropertiesCore\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds an optional "Header Image" field to Property Type and Location
 * terms, so each archive page (e.g. /property-type/villa/) can have its
 * own page-header background photo instead of the plain default.
 */
class TermImage
{
    const META_KEY = 'fpc_term_image';
    const TAXONOMIES = ['property_type', 'location'];

    public function register()
    {
        foreach (self::TAXONOMIES as $taxonomy) {
            add_action($taxonomy . '_add_form_fields', [$this, 'renderAddField']);
            add_action($taxonomy . '_edit_form_fields', [$this, 'renderEditField']);
            add_action('created_' . $taxonomy, [$this, 'save']);
            add_action('edited_' . $taxonomy, [$this, 'save']);
        }

        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue($hook)
    {
        if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
            return;
        }

        $screen = get_current_screen();

        if (!$screen || !in_array($screen->taxonomy, self::TAXONOMIES, true)) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('fpc-term-image', FPC_PLUGIN_URL . 'assets/js/term-image.js', ['jquery'], FPC_VERSION, true);
    }

    public function renderAddField($taxonomy)
    {
        $label = $taxonomy === 'location' ? 'zone/city' : 'property type';
        ?>
        <div class="form-field">
            <label for="fpc_term_image_id">Header Image</label>
            <div id="fpc-term-image-preview" style="margin-bottom:8px;"></div>
            <input type="hidden" name="fpc_term_image_id" id="fpc_term_image_id" value="">
            <button type="button" class="button" id="fpc-term-image-select">Select Image</button>
            <button type="button" class="button" id="fpc-term-image-remove" style="display:none;">Remove</button>
            <p class="description">Optional. Used as the background photo on this <?php echo esc_html($label); ?> archive page.</p>
        </div>
        <?php
    }

    public function renderEditField($term)
    {
        $imageId = (int) get_term_meta($term->term_id, self::META_KEY, true);
        $imageUrl = $imageId ? wp_get_attachment_image_url($imageId, 'medium') : '';
        ?>
        <tr class="form-field">
            <th scope="row"><label for="fpc_term_image_id">Header Image</label></th>
            <td>
                <div id="fpc-term-image-preview" style="margin-bottom:8px;">
                    <?php if ($imageUrl) : ?>
                        <img src="<?php echo esc_url($imageUrl); ?>" style="max-width:200px;height:auto;display:block;border-radius:4px;">
                    <?php endif; ?>
                </div>
                <input type="hidden" name="fpc_term_image_id" id="fpc_term_image_id" value="<?php echo esc_attr($imageId); ?>">
                <button type="button" class="button" id="fpc-term-image-select">Select Image</button>
                <button type="button" class="button" id="fpc-term-image-remove" <?php echo $imageId ? '' : 'style="display:none;"'; ?>>Remove</button>
                <p class="description">Optional. Used as the background photo on this archive page instead of the plain default.</p>
            </td>
        </tr>
        <?php
    }

    public function save($termId)
    {
        if (!current_user_can('manage_categories') || !isset($_POST['fpc_term_image_id'])) {
            return;
        }

        $imageId = (int) $_POST['fpc_term_image_id'];

        if ($imageId) {
            update_term_meta($termId, self::META_KEY, $imageId);
        } else {
            delete_term_meta($termId, self::META_KEY);
        }
    }
}
