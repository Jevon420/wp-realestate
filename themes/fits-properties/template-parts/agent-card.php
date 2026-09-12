<?php
if (!defined('ABSPATH')) {
    exit;
}

$agentId = get_the_ID();
$phone = get_post_meta($agentId, 'fpc_phone', true);
$specialization = get_post_meta($agentId, 'fpc_specialization', true);
?>
<article class="fp-agent-card">
    <a class="fp-agent-card__media" href="<?php the_permalink(); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium'); ?>
        <?php else : ?>
            <div class="fp-agent-card__media-placeholder"></div>
        <?php endif; ?>
    </a>
    <div class="fp-agent-card__body">
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <?php if ($specialization) : ?><p class="fp-agent-card__spec"><?php echo esc_html($specialization); ?></p><?php endif; ?>
        <?php if ($phone) : ?><p class="fp-agent-card__phone"><?php echo esc_html($phone); ?></p><?php endif; ?>
    </div>
</article>
