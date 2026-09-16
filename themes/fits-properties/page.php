<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $headerImage = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '';
    $header = fp_page_header_attrs($headerImage);
    ?>
    <section class="<?php echo esc_attr($header['class']); ?>"<?php echo $header['style']; ?>>
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
