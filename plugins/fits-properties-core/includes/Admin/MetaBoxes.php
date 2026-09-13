<?php

namespace FitsPropertiesCore\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class MetaBoxes
{
    public function register()
    {
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_property', [$this, 'saveProperty']);
        add_action('save_post_agent', [$this, 'saveAgent']);
        add_action('save_post_testimonial', [$this, 'saveTestimonial']);
    }

    public function addMetaBoxes()
    {
        add_meta_box('fpc_property_description', 'Description', [$this, 'renderPropertyDescription'], 'property', 'normal', 'high');
        add_meta_box('fpc_property_listing', 'Listing & Pricing', [$this, 'renderPropertyListing'], 'property', 'normal', 'high');
        add_meta_box('fpc_property_specs', 'Specifications', [$this, 'renderPropertySpecs'], 'property', 'normal', 'default');
        add_meta_box('fpc_property_address', 'Address & Map Location', [$this, 'renderPropertyAddress'], 'property', 'normal', 'default');
        add_meta_box('fpc_property_gallery', 'Photos', [$this, 'renderGallery'], 'property', 'side', 'high');
        add_meta_box('fpc_agent_details', 'Agent Details', [$this, 'renderAgent'], 'agent', 'normal', 'high');
        add_meta_box('fpc_agent_photo', 'Photo', [$this, 'renderAgentPhoto'], 'agent', 'side', 'high');
        add_meta_box('fpc_testimonial_details', 'Testimonial Details', [$this, 'renderTestimonial'], 'testimonial', 'normal', 'high');
    }

    private function nonceField($action)
    {
        wp_nonce_field($action, $action . '_nonce');
    }

    private function verifyNonce($action)
    {
        return isset($_POST[$action . '_nonce']) && wp_verify_nonce($_POST[$action . '_nonce'], $action);
    }

    private function meta($postId, $key, $default = '')
    {
        $value = get_post_meta($postId, $key, true);

        return $value === '' ? $default : $value;
    }

    private function intro($text)
    {
        echo '<p class="fpc-intro">' . esc_html($text) . '</p>';
    }

    private function textarea($id, $name, $value, $rows = 6, $placeholder = '')
    {
        printf(
            '<textarea id="%1$s" name="%2$s" rows="%3$d" class="large-text" placeholder="%4$s">%5$s</textarea>',
            esc_attr($id),
            esc_attr($name),
            (int) $rows,
            esc_attr($placeholder),
            esc_textarea($value)
        );
    }

    /** ---------- Property: Description ---------- */

    public function renderPropertyDescription($post)
    {
        $this->nonceField('fpc_save_property');
        $this->intro('This is the main write-up shown on the property\'s page. Plain text only — press Enter to start a new paragraph.');
        $this->textarea('fpc_description', 'fpc_description', $this->meta($post->ID, 'fpc_description'), 8, 'Describe the property: what makes it stand out, the neighborhood, recent updates...');
    }

    /** ---------- Property: Listing & Pricing ---------- */

    public function renderPropertyListing($post)
    {
        $this->intro('Choose who this listing belongs to, whether it\'s for sale or rent, and its price. Use the "Property Types", "Locations" and "Features & Amenities" boxes in the sidebar to categorize it.');

        $listingType = $this->meta($post->ID, 'fpc_listing_type', 'sale');
        $agentId = (int) $this->meta($post->ID, 'fpc_agent_id');
        $featured = (bool) $this->meta($post->ID, 'fpc_is_featured');
        $status = $this->meta($post->ID, 'fpc_status', 'active');

        $agents = get_posts(['post_type' => 'agent', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC']);
        ?>
        <table class="form-table fpc-form-table">
            <tr>
                <th><label for="fpc_agent_id">Listing Agent</label></th>
                <td>
                    <select name="fpc_agent_id" id="fpc_agent_id">
                        <option value="">— No agent assigned —</option>
                        <?php foreach ($agents as $agent) : ?>
                            <option value="<?php echo esc_attr($agent->ID); ?>" <?php selected($agentId, $agent->ID); ?>>
                                <?php echo esc_html($agent->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">The agent shown on this property's page. <a href="<?php echo esc_url(admin_url('post-new.php?post_type=agent')); ?>">Add a new agent</a> if they're not listed.</p>
                </td>
            </tr>
            <tr>
                <th>Listing Type</th>
                <td>
                    <fieldset class="fpc-toggle-group">
                        <label class="fpc-radio-card">
                            <input type="radio" name="fpc_listing_type" value="sale" <?php checked($listingType, 'sale'); ?>>
                            <span>For Sale</span>
                        </label>
                        <label class="fpc-radio-card">
                            <input type="radio" name="fpc_listing_type" value="rent" <?php checked($listingType, 'rent'); ?>>
                            <span>For Rent</span>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th><label for="fpc_status">Status</label></th>
                <td>
                    <select name="fpc_status" id="fpc_status">
                        <?php foreach (['active' => 'Active', 'pending' => 'Pending / Under Contract', 'sold' => 'Sold', 'rented' => 'Rented'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Sold/Rented/Pending listings stay on the site (good for showing your track record) but display a status ribbon instead of the usual "For Sale/Rent" badge.</p>
                </td>
            </tr>
            <tr class="fpc-when-sale">
                <th><label for="fpc_price">Sale Price ($)</label></th>
                <td><input type="number" step="1" min="0" name="fpc_price" id="fpc_price" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_price')); ?>" class="regular-text"></td>
            </tr>
            <tr class="fpc-when-rent">
                <th><label for="fpc_rental_price">Rental Price ($)</label></th>
                <td>
                    <input type="number" step="1" min="0" name="fpc_rental_price" id="fpc_rental_price" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_rental_price')); ?>" class="regular-text">
                    <select name="fpc_rental_frequency">
                        <?php foreach (['monthly' => 'per Month', 'weekly' => 'per Week', 'yearly' => 'per Year'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($this->meta($post->ID, 'fpc_rental_frequency', 'monthly'), $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="fpc-when-rent">
                <th><label for="fpc_lease_term">Minimum Lease Term</label></th>
                <td>
                    <input type="number" min="0" name="fpc_lease_term" id="fpc_lease_term" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_lease_term')); ?>" class="small-text"> months
                </td>
            </tr>
            <tr>
                <th>Featured Listing</th>
                <td>
                    <label class="fpc-switch">
                        <input type="checkbox" name="fpc_is_featured" value="1" <?php checked($featured); ?>>
                        <span class="fpc-switch__track"><span class="fpc-switch__thumb"></span></span>
                        Show in the "Featured Properties" section on the homepage
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    /** ---------- Property: Specifications ---------- */

    public function renderPropertySpecs($post)
    {
        $furnished = (bool) $this->meta($post->ID, 'fpc_furnished');
        $utilities = (bool) $this->meta($post->ID, 'fpc_utilities_included');
        ?>
        <table class="form-table fpc-form-table">
            <tr>
                <th><label for="fpc_bedrooms">Bedrooms</label></th>
                <td><input type="number" min="0" name="fpc_bedrooms" id="fpc_bedrooms" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_bedrooms')); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_bathrooms">Bathrooms</label></th>
                <td><input type="number" min="0" step="0.5" name="fpc_bathrooms" id="fpc_bathrooms" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_bathrooms')); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_garage">Garage Spaces</label></th>
                <td><input type="number" min="0" name="fpc_garage" id="fpc_garage" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_garage')); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_area">Floor Area (sq ft)</label></th>
                <td><input type="number" min="0" name="fpc_area" id="fpc_area" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_area')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_year_built">Year Built</label></th>
                <td><input type="number" min="1800" max="2100" name="fpc_year_built" id="fpc_year_built" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_year_built')); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th>Extras</th>
                <td>
                    <label class="fpc-checkbox-inline"><input type="checkbox" name="fpc_furnished" value="1" <?php checked($furnished); ?>> Furnished</label>
                    <label class="fpc-checkbox-inline"><input type="checkbox" name="fpc_utilities_included" value="1" <?php checked($utilities); ?>> Utilities Included</label>
                </td>
            </tr>
        </table>
        <?php
    }

    /** ---------- Property: Address ---------- */

    public function renderPropertyAddress($post)
    {
        ?>
        <table class="form-table fpc-form-table">
            <tr>
                <th><label for="fpc_address_line">Street Address</label></th>
                <td><input type="text" name="fpc_address_line" id="fpc_address_line" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_address_line')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_state">State / Region</label></th>
                <td><input type="text" name="fpc_state" id="fpc_state" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_state')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_postal_code">Postal Code</label></th>
                <td><input type="text" name="fpc_postal_code" id="fpc_postal_code" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_postal_code')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_country">Country</label></th>
                <td><input type="text" name="fpc_country" id="fpc_country" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_country')); ?>" class="regular-text"></td>
            </tr>
        </table>
        <details class="fpc-advanced">
            <summary>Advanced: Map Coordinates (optional)</summary>
            <table class="form-table fpc-form-table">
                <tr>
                    <th><label for="fpc_latitude">Latitude</label></th>
                    <td><input type="text" name="fpc_latitude" id="fpc_latitude" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_latitude')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="fpc_longitude">Longitude</label></th>
                    <td><input type="text" name="fpc_longitude" id="fpc_longitude" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_longitude')); ?>" class="regular-text"></td>
                </tr>
            </table>
        </details>
        <?php
    }

    public function renderGallery($post)
    {
        $ids = get_post_meta($post->ID, 'fpc_gallery', true);
        $ids = $ids ? array_filter(explode(',', $ids)) : [];
        $coverUrl = $this->meta($post->ID, 'fpc_featured_image_url');
        $galleryUrls = $this->meta($post->ID, 'fpc_gallery_urls');
        ?>
        <p class="fpc-intro fpc-intro--tight">You can upload photos below, or paste image links instead — whichever is easier. Uploaded photos and links can be used together.</p>

        <p class="fpc-field-label">Cover Photo</p>
        <p class="description">Used on listing cards. Uploading one below as the "Featured Image" takes priority; otherwise this link is used.</p>
        <input type="url" name="fpc_featured_image_url" value="<?php echo esc_attr($coverUrl); ?>" class="widefat" placeholder="https://example.com/photo.jpg">

        <p class="fpc-field-label" style="margin-top:16px;">Uploaded Photos</p>
        <div id="fpc-gallery-field">
            <input type="hidden" name="fpc_gallery" id="fpc_gallery_ids" value="<?php echo esc_attr(implode(',', $ids)); ?>">
            <div id="fpc-gallery-preview" class="fpc-gallery-preview">
                <?php foreach ($ids as $id) : ?>
                    <span class="fpc-gallery-thumb-wrap" data-id="<?php echo esc_attr($id); ?>">
                        <?php echo wp_get_attachment_image((int) $id, [80, 80], false, ['style' => 'object-fit:cover;border-radius:4px;']); ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button button-primary" id="fpc-gallery-add">Add Photos</button>
            <button type="button" class="button" id="fpc-gallery-clear">Clear All</button>
        </div>

        <p class="fpc-field-label" style="margin-top:16px;">Linked Photos</p>
        <p class="description">One image link per line.</p>
        <?php $this->textarea('fpc_gallery_urls', 'fpc_gallery_urls', $galleryUrls, 4, "https://example.com/photo1.jpg\nhttps://example.com/photo2.jpg"); ?>
        <?php
    }

    /** ---------- Agent ---------- */

    public function renderAgent($post)
    {
        $this->nonceField('fpc_save_agent');
        $this->intro('Fill in their bio and contact details below. Set their photo using the "Photo" box in the sidebar.');
        ?>
        <p class="fpc-field-label">Bio</p>
        <?php $this->textarea('fpc_bio', 'fpc_bio', $this->meta($post->ID, 'fpc_bio'), 6, "A short introduction: their experience, specialties, what clients say about working with them..."); ?>

        <table class="form-table fpc-form-table" style="margin-top:8px;">
            <tr>
                <th><label for="fpc_phone">Phone Number</label></th>
                <td><input type="text" name="fpc_phone" id="fpc_phone" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_phone')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_specialization">Specialization</label></th>
                <td>
                    <input type="text" name="fpc_specialization" id="fpc_specialization" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_specialization')); ?>" class="regular-text" placeholder="e.g. Luxury Homes, Commercial, First-Time Buyers">
                </td>
            </tr>
            <tr>
                <th><label for="fpc_years_of_experience">Years of Experience</label></th>
                <td><input type="number" min="0" name="fpc_years_of_experience" id="fpc_years_of_experience" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_years_of_experience')); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="fpc_license_number">License Number</label></th>
                <td><input type="text" name="fpc_license_number" id="fpc_license_number" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_license_number')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Social Links</th>
                <td class="fpc-social-grid">
                    <p><label for="fpc_facebook_url">Facebook</label><input type="url" name="fpc_facebook_url" id="fpc_facebook_url" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_facebook_url')); ?>" class="regular-text" placeholder="https://facebook.com/..."></p>
                    <p><label for="fpc_twitter_url">Twitter / X</label><input type="url" name="fpc_twitter_url" id="fpc_twitter_url" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_twitter_url')); ?>" class="regular-text" placeholder="https://x.com/..."></p>
                    <p><label for="fpc_instagram_url">Instagram</label><input type="url" name="fpc_instagram_url" id="fpc_instagram_url" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_instagram_url')); ?>" class="regular-text" placeholder="https://instagram.com/..."></p>
                    <p><label for="fpc_linkedin_url">LinkedIn</label><input type="url" name="fpc_linkedin_url" id="fpc_linkedin_url" value="<?php echo esc_attr($this->meta($post->ID, 'fpc_linkedin_url')); ?>" class="regular-text" placeholder="https://linkedin.com/in/..."></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function renderAgentPhoto($post)
    {
        $photoUrl = $this->meta($post->ID, 'fpc_photo_url');
        ?>
        <p class="description">Upload a photo below as the "Featured Image", or paste a link instead.</p>
        <p class="fpc-field-label">Photo Link</p>
        <input type="url" name="fpc_photo_url" value="<?php echo esc_attr($photoUrl); ?>" class="widefat" placeholder="https://example.com/headshot.jpg">
        <?php
    }

    /** ---------- Testimonial ---------- */

    public function renderTestimonial($post)
    {
        $this->nonceField('fpc_save_testimonial');
        $this->intro('Use the Title field above for the reviewer\'s name.');

        $rating = $this->meta($post->ID, 'fpc_rating', '5');
        $relatedId = $this->meta($post->ID, 'fpc_related_post');

        $related = get_posts(['post_type' => ['property', 'agent'], 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC']);
        ?>
        <p class="fpc-field-label">Testimonial Text</p>
        <?php $this->textarea('fpc_testimonial_text', 'fpc_testimonial_text', $this->meta($post->ID, 'fpc_testimonial_text'), 4, 'What did they say about working with you?'); ?>

        <table class="form-table fpc-form-table" style="margin-top:8px;">
            <tr>
                <th>Rating</th>
                <td>
                    <fieldset class="fpc-star-picker" id="fpc-star-picker">
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <label class="<?php echo $i <= (int) $rating ? 'is-filled' : ''; ?>">
                                <input type="radio" name="fpc_rating" value="<?php echo $i; ?>" <?php checked($rating, (string) $i); ?>>
                                <span>&#9733;</span>
                            </label>
                        <?php endfor; ?>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th><label for="fpc_related_post">Related Property or Agent</label></th>
                <td>
                    <select name="fpc_related_post" id="fpc_related_post">
                        <option value="">None</option>
                        <?php foreach ($related as $item) : ?>
                            <option value="<?php echo esc_attr($item->ID); ?>" <?php selected((string) $relatedId, (string) $item->ID); ?>>
                                [<?php echo esc_html(ucfirst($item->post_type)); ?>] <?php echo esc_html($item->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Optional — link this testimonial to the property or agent it's about.</p>
                </td>
            </tr>
        </table>
        <?php
    }

    /** ---------- Save handlers ---------- */

    public function saveProperty($postId)
    {
        if (!$this->verifyNonce('fpc_save_property') || !current_user_can('edit_post', $postId)) {
            return;
        }

        if (isset($_POST['fpc_description'])) {
            update_post_meta($postId, 'fpc_description', sanitize_textarea_field(wp_unslash($_POST['fpc_description'])));
        }

        $text = [
            'fpc_price', 'fpc_rental_price', 'fpc_lease_term', 'fpc_bedrooms', 'fpc_bathrooms',
            'fpc_garage', 'fpc_area', 'fpc_year_built', 'fpc_address_line', 'fpc_state',
            'fpc_postal_code', 'fpc_country', 'fpc_latitude', 'fpc_longitude',
        ];
        foreach ($text as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($postId, $field, sanitize_text_field(wp_unslash($_POST[$field])));
            }
        }

        if (isset($_POST['fpc_featured_image_url'])) {
            update_post_meta($postId, 'fpc_featured_image_url', esc_url_raw(wp_unslash($_POST['fpc_featured_image_url'])));
        }

        if (isset($_POST['fpc_gallery_urls'])) {
            $urls = preg_split('/\r\n|\r|\n/', wp_unslash($_POST['fpc_gallery_urls']));
            $urls = array_filter(array_map('esc_url_raw', array_map('trim', $urls)));
            update_post_meta($postId, 'fpc_gallery_urls', implode("\n", $urls));
        }

        if (isset($_POST['fpc_agent_id'])) {
            update_post_meta($postId, 'fpc_agent_id', (int) $_POST['fpc_agent_id']);
        }

        foreach (['fpc_listing_type', 'fpc_rental_frequency', 'fpc_status'] as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($postId, $field, sanitize_key($_POST[$field]));
            }
        }

        foreach (['fpc_furnished', 'fpc_utilities_included', 'fpc_is_featured'] as $field) {
            update_post_meta($postId, $field, isset($_POST[$field]) ? 1 : 0);
        }

        if (isset($_POST['fpc_gallery'])) {
            $ids = array_filter(array_map('intval', explode(',', $_POST['fpc_gallery'])));
            update_post_meta($postId, 'fpc_gallery', implode(',', $ids));
        }
    }

    public function saveAgent($postId)
    {
        if (!$this->verifyNonce('fpc_save_agent') || !current_user_can('edit_post', $postId)) {
            return;
        }

        if (isset($_POST['fpc_bio'])) {
            update_post_meta($postId, 'fpc_bio', sanitize_textarea_field(wp_unslash($_POST['fpc_bio'])));
        }

        if (isset($_POST['fpc_photo_url'])) {
            update_post_meta($postId, 'fpc_photo_url', esc_url_raw(wp_unslash($_POST['fpc_photo_url'])));
        }

        $text = [
            'fpc_phone', 'fpc_specialization', 'fpc_years_of_experience', 'fpc_license_number',
            'fpc_facebook_url', 'fpc_twitter_url', 'fpc_instagram_url', 'fpc_linkedin_url',
        ];
        foreach ($text as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($postId, $field, sanitize_text_field(wp_unslash($_POST[$field])));
            }
        }
    }

    public function saveTestimonial($postId)
    {
        if (!$this->verifyNonce('fpc_save_testimonial') || !current_user_can('edit_post', $postId)) {
            return;
        }

        if (isset($_POST['fpc_testimonial_text'])) {
            update_post_meta($postId, 'fpc_testimonial_text', sanitize_textarea_field(wp_unslash($_POST['fpc_testimonial_text'])));
        }

        if (isset($_POST['fpc_rating'])) {
            update_post_meta($postId, 'fpc_rating', (int) $_POST['fpc_rating']);
        }

        if (isset($_POST['fpc_related_post'])) {
            update_post_meta($postId, 'fpc_related_post', (int) $_POST['fpc_related_post']);
        }
    }
}
