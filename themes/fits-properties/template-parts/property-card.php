<?php
/**
 * Expects $post to be set up via the loop (get_the_ID(), etc.).
 */
if (!defined('ABSPATH')) {
    exit;
}

$propertyId = get_the_ID();
$specs = fp_property_specs($propertyId);
$listingType = get_post_meta($propertyId, 'fpc_listing_type', true);
$imageHtml = fp_featured_image_html($propertyId, 'fpc_featured_image_url', 'medium_large');
?>
<article class="fp-card">
    <a class="fp-card__media" href="<?php the_permalink(); ?>">
        <?php if ($imageHtml) : ?>
            <?php echo $imageHtml; ?>
        <?php else : ?>
            <div class="fp-card__media-placeholder"></div>
        <?php endif; ?>
        <span class="fp-badge fp-badge--<?php echo esc_attr($listingType ?: 'sale'); ?>">
            <?php echo $listingType === 'rent' ? 'For Rent' : 'For Sale'; ?>
        </span>
    </a>

    <div class="fp-card__body">
        <p class="fp-card__price"><?php echo esc_html(fp_property_price_label($propertyId)); ?></p>
        <h3 class="fp-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="fp-card__location"><?php echo esc_html(fp_property_location_label($propertyId)); ?></p>

        <ul class="fp-card__specs">
            <?php if ($specs['bedrooms'] !== '') : ?><li><?php echo esc_html($specs['bedrooms']); ?> Beds</li><?php endif; ?>
            <?php if ($specs['bathrooms'] !== '') : ?><li><?php echo esc_html($specs['bathrooms']); ?> Baths</li><?php endif; ?>
            <?php if ($specs['area'] !== '') : ?><li><?php echo esc_html(number_format((float) $specs['area'])); ?> sqft</li><?php endif; ?>
        </ul>
    </div>
</article>
