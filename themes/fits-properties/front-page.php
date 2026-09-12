<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$featured = fp_get_featured_properties(6);
$propertyTypes = fp_get_property_types(6);
$testimonials = fp_get_testimonials(3);
?>

<section class="fp-hero">
    <div class="fp-container fp-hero__inner">
        <span class="fp-eyebrow">Real Estate</span>
        <h1>Find Your Next Property with <?php bloginfo('name'); ?></h1>
        <p>Browse homes, apartments and commercial spaces for sale and rent.</p>

        <form class="fp-search" action="<?php echo esc_url(get_post_type_archive_link('property')); ?>" method="get">
            <input type="text" name="s" placeholder="Search by title, city or zone&hellip;">
            <select name="fp_listing_type">
                <option value="">Any Type</option>
                <option value="sale">For Sale</option>
                <option value="rent">For Rent</option>
            </select>
            <button type="submit" class="fp-btn fp-btn--primary">Search</button>
        </form>
    </div>
</section>

<?php if ($featured->have_posts()) : ?>
    <section class="fp-section">
        <div class="fp-container">
            <div class="fp-section__head">
                <div>
                    <span class="fp-eyebrow">Curated Listings</span>
                    <h2>Featured Properties</h2>
                </div>
                <a class="fp-link" href="<?php echo esc_url(get_post_type_archive_link('property')); ?>">View All &rarr;</a>
            </div>
            <div class="fp-grid fp-grid--3">
                <?php while ($featured->have_posts()) : $featured->the_post(); ?>
                    <?php get_template_part('template-parts/property-card'); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($propertyTypes) && !is_wp_error($propertyTypes)) : ?>
    <section class="fp-section fp-section--muted">
        <div class="fp-container">
            <div class="fp-section__head">
                <div>
                    <span class="fp-eyebrow">Explore</span>
                    <h2>Browse by Property Type</h2>
                </div>
            </div>
            <div class="fp-grid fp-grid--4">
                <?php foreach ($propertyTypes as $type) : ?>
                    <a class="fp-type-card" href="<?php echo esc_url(get_term_link($type)); ?>">
                        <span><?php echo esc_html($type->name); ?></span>
                        <small><?php echo esc_html($type->count); ?> Listings</small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($testimonials->have_posts()) : ?>
    <section class="fp-section">
        <div class="fp-container">
            <div class="fp-section__head">
                <div>
                    <span class="fp-eyebrow">Testimonials</span>
                    <h2>What Our Clients Say</h2>
                </div>
            </div>
            <div class="fp-grid fp-grid--3">
                <?php while ($testimonials->have_posts()) : $testimonials->the_post(); ?>
                    <?php get_template_part('template-parts/testimonial-card'); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="fp-cta">
    <div class="fp-container fp-cta__inner">
        <h2>Ready to find your next home?</h2>
        <p>Talk to one of our agents today.</p>
        <a class="fp-btn fp-btn--light" href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a>
    </div>
</section>

<?php get_footer(); ?>
