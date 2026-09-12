<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$term = get_queried_object();
?>

<section class="fp-page-header">
    <div class="fp-container">
        <h1><?php echo esc_html($term->name); ?> Properties</h1>
        <?php if ($term->description) : ?><p><?php echo esc_html($term->description); ?></p><?php endif; ?>
    </div>
</section>

<section class="fp-section">
    <div class="fp-container">
        <?php if (have_posts()) : ?>
            <div class="fp-grid fp-grid--3">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/property-card'); ?>
                <?php endwhile; ?>
            </div>

            <div class="fp-pagination">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <p>No properties found for this type yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
