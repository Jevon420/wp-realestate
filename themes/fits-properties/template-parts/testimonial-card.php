<?php
if (!defined('ABSPATH')) {
    exit;
}

$testimonialId = get_the_ID();
$rating = get_post_meta($testimonialId, 'fpc_rating', true);
$text = get_post_meta($testimonialId, 'fpc_testimonial_text', true);
?>
<blockquote class="fp-testimonial fp-animate">
    <p class="fp-testimonial__stars"><?php echo fp_stars($rating); ?></p>
    <p class="fp-testimonial__content"><?php echo esc_html($text); ?></p>
    <cite class="fp-testimonial__author"><?php the_title(); ?></cite>
</blockquote>
