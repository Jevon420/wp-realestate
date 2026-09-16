<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$term = get_queried_object();
$headerImageId = get_term_meta($term->term_id, 'fpc_term_image', true);
$headerImage = $headerImageId ? wp_get_attachment_image_url($headerImageId, 'large') : '';
$header = fp_page_header_attrs($headerImage);
?>

<section class="<?php echo esc_attr($header['class']); ?>"<?php echo $header['style']; ?>>
    <div class="fp-container">
        <h1><?php echo esc_html($term->name); ?> Properties</h1>
        <?php if ($term->description) : ?><p><?php echo esc_html($term->description); ?></p><?php endif; ?>
    </div>
</section>

<section class="fp-section">
    <div class="fp-container">
        <?php if (have_posts()) : ?>
            <div class="fp-grid fp-grid--3" id="fp-property-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/property-card'); ?>
                <?php endwhile; ?>
            </div>

            <?php if ($wp_query->max_num_pages > 1) : ?>
                <div class="fp-load-more">
                    <button type="button" id="fp-load-more-btn" class="fp-btn fp-btn--primary" data-target="fp-property-grid" data-max-pages="<?php echo (int) $wp_query->max_num_pages; ?>" data-taxonomy="property_type" data-term="<?php echo esc_attr($term->slug); ?>">
                        Load More
                    </button>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <p>No properties found for this type yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
