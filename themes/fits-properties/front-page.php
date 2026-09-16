<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$featured = fp_get_featured_properties(6);
$propertyTypes = fp_get_property_types(6);
$locations = fp_get_top_level_locations();
$agents = fp_get_agents(4);
$testimonials = fp_get_testimonials(3);
$heroImage = get_theme_mod('fp_hero_image', '');

$propertyCount = wp_count_posts('property')->publish;
$agentCount = wp_count_posts('agent')->publish;
$zoneCount = (!is_wp_error($locations)) ? count($locations) : 0;
$yearsExperience = get_theme_mod('fp_years_experience', '10');
?>

<section class="fp-hero <?php echo $heroImage ? 'fp-hero--photo' : ''; ?>" <?php echo $heroImage ? 'style="background-image:url(' . esc_url($heroImage) . ')"' : ''; ?>>
    <div class="fp-container fp-hero__inner fp-animate">
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

<?php if ($propertyCount || $agentCount) : ?>
    <section class="fp-stats fp-animate">
        <div class="fp-container fp-stats__grid">
            <div class="fp-stat">
                <span class="fp-stat__number"><?php echo esc_html($propertyCount); ?>+</span>
                <span class="fp-stat__label">Properties Listed</span>
            </div>
            <div class="fp-stat">
                <span class="fp-stat__number"><?php echo esc_html($agentCount); ?></span>
                <span class="fp-stat__label">Expert Agents</span>
            </div>
            <?php if ($zoneCount) : ?>
                <div class="fp-stat">
                    <span class="fp-stat__number"><?php echo esc_html($zoneCount); ?></span>
                    <span class="fp-stat__label">Areas Covered</span>
                </div>
            <?php endif; ?>
            <?php if ($yearsExperience) : ?>
                <div class="fp-stat">
                    <span class="fp-stat__number"><?php echo esc_html($yearsExperience); ?>+</span>
                    <span class="fp-stat__label">Years of Experience</span>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($featured->have_posts()) : ?>
    <section class="fp-section">
        <div class="fp-container">
            <div class="fp-section__head fp-animate">
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
            <div class="fp-section__head fp-animate">
                <div>
                    <span class="fp-eyebrow">Explore</span>
                    <h2>Browse by Property Type</h2>
                </div>
            </div>
            <div class="fp-grid fp-grid--4">
                <?php foreach ($propertyTypes as $type) : ?>
                    <a class="fp-type-card fp-animate" href="<?php echo esc_url(get_term_link($type)); ?>">
                        <span><?php echo esc_html($type->name); ?></span>
                        <small><?php echo esc_html($type->count); ?> Listings</small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($locations) && !is_wp_error($locations)) : ?>
    <section class="fp-section">
        <div class="fp-container">
            <div class="fp-section__head fp-animate">
                <div>
                    <span class="fp-eyebrow">Discover</span>
                    <h2>Browse by Location</h2>
                </div>
            </div>
            <div class="fp-grid fp-grid--4">
                <?php foreach ($locations as $location) : ?>
                    <a class="fp-type-card fp-animate" href="<?php echo esc_url(get_term_link($location)); ?>">
                        <span><?php echo esc_html($location->name); ?></span>
                        <small><?php echo esc_html($location->count); ?> Listings</small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($agents->have_posts()) : ?>
    <section class="fp-section fp-section--muted">
        <div class="fp-container">
            <div class="fp-section__head fp-animate">
                <div>
                    <span class="fp-eyebrow">Our Team</span>
                    <h2>Meet Our Agents</h2>
                </div>
                <a class="fp-link" href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>">View All &rarr;</a>
            </div>
            <div class="fp-grid fp-grid--4">
                <?php while ($agents->have_posts()) : $agents->the_post(); ?>
                    <?php get_template_part('template-parts/agent-card'); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($testimonials->have_posts()) : ?>
    <section class="fp-section">
        <div class="fp-container">
            <div class="fp-section__head fp-animate">
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
    <div class="fp-container fp-cta__inner fp-animate">
        <h2>Ready to find your next home?</h2>
        <p>Talk to one of our agents today.</p>
        <a class="fp-btn fp-btn--light" href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a>
    </div>
</section>

<?php get_footer(); ?>
