<?php
if (!defined('ABSPATH')) {
    exit;
}

$rating = get_post_meta(get_the_ID(), 'fpc_rating', true);
?>
<blockquote class="fp-testimonial">
    <p class="fp-testimonial__stars"><?php echo fp_stars($rating); ?></p>
    <p class="fp-testimonial__content"><?php the_content(); ?></p>
    <cite class="fp-testimonial__author"><?php the_title(); ?></cite>
</blockquote>
