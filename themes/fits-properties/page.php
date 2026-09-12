<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    ?>
    <section class="fp-page-header">
        <div class="fp-container">
            <h1><?php the_title(); ?></h1>
        </div>
    </section>

    <section class="fp-section">
        <div class="fp-container fp-content">
            <?php the_content(); ?>
        </div>
    </section>
<?php endwhile; ?>

<?php get_footer(); ?>
