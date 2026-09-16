<?php if (!defined('ABSPATH')) { exit; } ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.classList.add('fp-js');</script>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="fp-header">
    <div class="fp-container fp-header__inner">
        <?php if (has_custom_logo()) : ?>
            <div class="fp-logo fp-logo--image"><?php the_custom_logo(); ?></div>
        <?php else : ?>
            <a class="fp-logo" href="<?php echo esc_url(home_url('/')); ?>">
                <?php bloginfo('name'); ?>
            </a>
        <?php endif; ?>

        <nav class="fp-nav" aria-label="Primary">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'fp-nav__list',
                'fallback_cb' => function () {
                    echo '<ul class="fp-nav__list">';
                    echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
                    echo '<li><a href="' . esc_url(get_post_type_archive_link('property')) . '">Properties</a></li>';
                    echo '<li><a href="' . esc_url(get_post_type_archive_link('agent')) . '">Agents</a></li>';
                    $contact = get_page_by_path('contact');
                    if ($contact) {
                        echo '<li><a href="' . esc_url(get_permalink($contact)) . '">Contact</a></li>';
                    }
                    echo '</ul>';
                },
            ]);
            ?>
        </nav>

        <a class="fp-btn fp-btn--primary fp-header__cta" href="<?php echo esc_url(get_post_type_archive_link('property')); ?>">
            View Listings
        </a>

        <button class="fp-nav-toggle" type="button" aria-expanded="false" aria-controls="fp-mobile-nav">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div id="fp-mobile-nav" class="fp-mobile-nav">
        <?php
        wp_nav_menu([
            'theme_location' => 'primary',
            'container' => false,
            'menu_class' => 'fp-mobile-nav__list',
            'fallback_cb' => function () {
                echo '<ul class="fp-mobile-nav__list">';
                echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
                echo '<li><a href="' . esc_url(get_post_type_archive_link('property')) . '">Properties</a></li>';
                echo '<li><a href="' . esc_url(get_post_type_archive_link('agent')) . '">Agents</a></li>';
                $contact = get_page_by_path('contact');
                if ($contact) {
                    echo '<li><a href="' . esc_url(get_permalink($contact)) . '">Contact</a></li>';
                }
                echo '</ul>';
            },
        ]);
        ?>
    </div>
</header>

<main class="fp-main">
