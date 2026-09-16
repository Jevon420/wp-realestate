<?php
/**
 * Template Name: Contact Page
 */
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
        <div class="fp-container fp-contact-layout">
            <div class="fp-contact-content">
                <?php the_content(); ?>
                <p><?php echo esc_html(get_theme_mod('fp_contact_phone', '(868) 000-0000')); ?></p>
                <p><?php echo esc_html(get_theme_mod('fp_contact_email', get_option('admin_email'))); ?></p>
            </div>

            <div class="fp-contact-form-card">
                <?php if (isset($_GET['fp_contact']) && $_GET['fp_contact'] === 'success') : ?>
                    <p class="fp-form-success">Thanks for reaching out! We'll respond within one business day.</p>
                <?php elseif (isset($_GET['fp_contact']) && $_GET['fp_contact'] === 'error') : ?>
                    <p class="fp-form-error">Please check your details and try again.</p>
                <?php endif; ?>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="fp_contact_submit">
                    <input type="text" name="fp_website" class="fp-hp-field" tabindex="-1" autocomplete="off">
                    <?php wp_nonce_field('fp_contact_form', 'fp_contact_nonce'); ?>

                    <label for="fp-contact-name">Name</label>
                    <input type="text" id="fp-contact-name" name="fp_name" required>

                    <label for="fp-contact-email">Email</label>
                    <input type="email" id="fp-contact-email" name="fp_email" required>

                    <label for="fp-contact-phone">Phone</label>
                    <input type="text" id="fp-contact-phone" name="fp_phone">

                    <label for="fp-contact-subject">Subject</label>
                    <input type="text" id="fp-contact-subject" name="fp_subject">

                    <label for="fp-contact-message">Message</label>
                    <textarea id="fp-contact-message" name="fp_message" rows="5" required></textarea>

                    <button type="submit" class="fp-btn fp-btn--primary">Send Message</button>
                </form>
            </div>
        </div>
    </section>

<?php endwhile; ?>

<?php get_footer(); ?>
