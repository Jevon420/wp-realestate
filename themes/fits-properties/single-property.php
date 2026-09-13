<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $propertyId = get_the_ID();
    $galleryItems = fp_gallery_items($propertyId);
    $mainImageHtml = fp_featured_image_html($propertyId, 'fpc_featured_image_url', 'large', ['id' => 'fp-gallery-main']);
    $specs = fp_property_specs($propertyId);
    $listingType = get_post_meta($propertyId, 'fpc_listing_type', true);
    $features = get_the_terms($propertyId, 'property_feature');
    $agent = fp_agent_for_property($propertyId);
    $address = get_post_meta($propertyId, 'fpc_address_line', true);
    $state = get_post_meta($propertyId, 'fpc_state', true);
    $postalCode = get_post_meta($propertyId, 'fpc_postal_code', true);
    $country = get_post_meta($propertyId, 'fpc_country', true);
    ?>

    <section class="fp-property-gallery">
        <div class="fp-container">
            <div class="fp-property-gallery__main">
                <?php if ($mainImageHtml) : ?>
                    <?php echo $mainImageHtml; ?>
                <?php elseif (!empty($galleryItems)) : ?>
                    <?php echo fp_gallery_item_thumb_html($galleryItems[0], [1200, 800]); ?>
                <?php else : ?>
                    <div class="fp-card__media-placeholder" style="height:420px;"></div>
                <?php endif; ?>
            </div>
            <?php if (!empty($galleryItems)) : ?>
                <div class="fp-property-gallery__thumbs">
                    <?php foreach ($galleryItems as $item) : ?>
                        <?php $full = fp_gallery_item_full_url($item); ?>
                        <?php if (!$full) { continue; } ?>
                        <button type="button" class="fp-gallery-thumb" data-full="<?php echo esc_url($full); ?>">
                            <?php echo fp_gallery_item_thumb_html($item, [70, 56]); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="fp-section">
        <div class="fp-container fp-property-layout">
            <div class="fp-property-main">
                <div class="fp-property-header">
                    <span class="fp-badge fp-badge--<?php echo esc_attr($listingType ?: 'sale'); ?>">
                        <?php echo $listingType === 'rent' ? 'For Rent' : 'For Sale'; ?>
                    </span>
                    <h1><?php the_title(); ?></h1>
                    <p class="fp-property-location"><?php echo esc_html(fp_property_location_label($propertyId)); ?></p>
                    <p class="fp-property-price"><?php echo esc_html(fp_property_price_label($propertyId)); ?></p>
                </div>

                <ul class="fp-property-specs">
                    <?php if ($specs['bedrooms'] !== '') : ?><li><strong><?php echo esc_html($specs['bedrooms']); ?></strong> Bedrooms</li><?php endif; ?>
                    <?php if ($specs['bathrooms'] !== '') : ?><li><strong><?php echo esc_html($specs['bathrooms']); ?></strong> Bathrooms</li><?php endif; ?>
                    <?php if ($specs['area'] !== '') : ?><li><strong><?php echo esc_html(number_format((float) $specs['area'])); ?></strong> sqft</li><?php endif; ?>
                    <?php if ($specs['garage'] !== '') : ?><li><strong><?php echo esc_html($specs['garage']); ?></strong> Garage</li><?php endif; ?>
                </ul>

                <div class="fp-property-description fp-animate">
                    <h2>Description</h2>
                    <?php echo fp_rich_text($propertyId, 'fpc_description'); ?>
                </div>

                <?php if (!empty($features) && !is_wp_error($features)) : ?>
                    <div class="fp-property-features fp-animate">
                        <h2>Features &amp; Amenities</h2>
                        <ul class="fp-tag-list">
                            <?php foreach ($features as $feature) : ?>
                                <li><?php echo esc_html($feature->name); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($address) : ?>
                    <div class="fp-property-address fp-animate">
                        <h2>Location</h2>
                        <p>
                            <?php echo esc_html($address); ?><?php if ($state) { echo ', ' . esc_html($state); } ?>
                            <?php if ($postalCode) { echo ' ' . esc_html($postalCode); } ?>
                            <?php if ($country) { echo '<br>' . esc_html($country); } ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="fp-property-sidebar">
                <?php if ($agent) : ?>
                    <div class="fp-agent-mini fp-animate">
                        <?php echo fp_featured_image_html($agent->ID, 'fpc_photo_url', 'thumbnail'); ?>
                        <h3><a href="<?php echo esc_url(get_permalink($agent)); ?>"><?php echo esc_html($agent->post_title); ?></a></h3>
                        <?php $phone = get_post_meta($agent->ID, 'fpc_phone', true); ?>
                        <?php if ($phone) : ?><p><?php echo esc_html($phone); ?></p><?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="fp-request-form fp-animate">
                    <h3>Request a Viewing</h3>
                    <?php if (isset($_GET['fp_contact']) && $_GET['fp_contact'] === 'success') : ?>
                        <p class="fp-form-success">Thanks! We'll be in touch shortly.</p>
                    <?php elseif (isset($_GET['fp_contact']) && $_GET['fp_contact'] === 'error') : ?>
                        <p class="fp-form-error">Please fill in all required fields.</p>
                    <?php endif; ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="fp_contact_submit">
                        <input type="hidden" name="fp_subject" value="Viewing Request: <?php echo esc_attr(get_the_title()); ?>">
                        <input type="text" name="fp_website" class="fp-hp-field" tabindex="-1" autocomplete="off">
                        <?php wp_nonce_field('fp_contact_form', 'fp_contact_nonce'); ?>

                        <label for="fp-req-name">Name</label>
                        <input type="text" id="fp-req-name" name="fp_name" required>

                        <label for="fp-req-email">Email</label>
                        <input type="email" id="fp-req-email" name="fp_email" required>

                        <label for="fp-req-phone">Phone</label>
                        <input type="text" id="fp-req-phone" name="fp_phone">

                        <label for="fp-req-message">Message</label>
                        <textarea id="fp-req-message" name="fp_message" rows="4" required>I'm interested in this property and would like to arrange a viewing.</textarea>

                        <button type="submit" class="fp-btn fp-btn--primary">Send Request</button>
                    </form>
                </div>
            </aside>
        </div>
    </section>

<?php endwhile; ?>

<?php get_footer(); ?>
