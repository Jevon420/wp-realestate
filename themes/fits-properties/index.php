<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<section class="fp-page-header">
    <div class="fp-container">
        <h1><?php echo is_home() ? 'Blog' : get_the_archive_title(); ?></h1>
    </div>
</section>

<section class="fp-section">
    <div class="fp-container fp-content fp-content--narrow">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article class="fp-post-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="fp-post-card__media"><?php the_post_thumbnail('medium_large'); ?></a>
                    <?php endif; ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p class="fp-post-card__meta"><?php echo esc_html(get_the_date()); ?></p>
                    <div><?php the_excerpt(); ?></div>
                </article>
            <?php endwhile; ?>

            <div class="fp-pagination">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <p>Nothing found.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
