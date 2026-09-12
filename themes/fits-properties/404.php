<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<section class="fp-section fp-404">
    <div class="fp-container fp-content fp-content--narrow">
        <h1>Page Not Found</h1>
        <p>The page you're looking for doesn't exist. Try browsing our properties instead.</p>
        <a class="fp-btn fp-btn--primary" href="<?php echo esc_url(get_post_type_archive_link('property')); ?>">View Properties</a>
    </div>
</section>

<?php get_footer(); ?>
