<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
$header = fp_page_header_attrs(get_theme_mod('fp_agents_header_image', ''));
?>

<section class="<?php echo esc_attr($header['class']); ?>"<?php echo $header['style']; ?>>
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
