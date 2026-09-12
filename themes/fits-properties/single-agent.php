<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $agentId = get_the_ID();
    $phone = get_post_meta($agentId, 'fpc_phone', true);
    $specialization = get_post_meta($agentId, 'fpc_specialization', true);
    $years = get_post_meta($agentId, 'fpc_years_of_experience', true);
    $license = get_post_meta($agentId, 'fpc_license_number', true);
    $socials = [
        'Facebook' => get_post_meta($agentId, 'fpc_facebook_url', true),
        'Twitter/X' => get_post_meta($agentId, 'fpc_twitter_url', true),
        'Instagram' => get_post_meta($agentId, 'fpc_instagram_url', true),
        'LinkedIn' => get_post_meta($agentId, 'fpc_linkedin_url', true),
    ];

    $listings = new WP_Query([
        'post_type' => 'property',
        'meta_key' => 'fpc_agent_id',
        'meta_value' => $agentId,
        'posts_per_page' => 6,
    ]);
    ?>

    <?php $imageHtml = fp_featured_image_html($agentId, 'fpc_photo_url', 'medium'); ?>
    <section class="fp-page-header fp-page-header--agent">
        <div class="fp-container fp-agent-header">
            <?php if ($imageHtml) : ?>
                <div class="fp-agent-header__photo"><?php echo $imageHtml; ?></div>
            <?php endif; ?>
            <div>
                <h1><?php the_title(); ?></h1>
                <?php if ($specialization) : ?><p class="fp-agent-header__spec"><?php echo esc_html($specialization); ?></p><?php endif; ?>
                <ul class="fp-agent-header__meta">
                    <?php if ($phone) : ?><li>Phone: <?php echo esc_html($phone); ?></li><?php endif; ?>
                    <?php if ($years !== '') : ?><li><?php echo esc_html($years); ?> years experience</li><?php endif; ?>
                    <?php if ($license) : ?><li>License #<?php echo esc_html($license); ?></li><?php endif; ?>
                </ul>
                <div class="fp-agent-header__socials">
                    <?php foreach ($socials as $label => $url) : ?>
                        <?php if ($url) : ?>
                            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html($label); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="fp-section">
        <div class="fp-container">
            <div class="fp-property-description">
                <?php echo fp_rich_text($agentId, 'fpc_bio'); ?>
            </div>
        </div>
    </section>

    <?php if ($listings->have_posts()) : ?>
        <section class="fp-section fp-section--muted">
            <div class="fp-container">
                <div class="fp-section__head">
                    <h2>Listings by <?php the_title(); ?></h2>
                </div>
                <div class="fp-grid fp-grid--3">
                    <?php while ($listings->have_posts()) : $listings->the_post(); ?>
                        <?php get_template_part('template-parts/property-card'); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
