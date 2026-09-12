<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<section class="fp-page-header">
    <div class="fp-container">
        <h1>Our Agents</h1>
    </div>
</section>

<section class="fp-section">
    <div class="fp-container">
        <?php if (have_posts()) : ?>
            <div class="fp-grid fp-grid--4">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/agent-card'); ?>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p>No agents found.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
