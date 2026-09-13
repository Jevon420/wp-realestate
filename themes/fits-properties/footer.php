</main>

<footer class="fp-footer">
    <div class="fp-container fp-footer__inner">
        <div class="fp-footer__col">
            <p class="fp-logo fp-logo--light"><?php bloginfo('name'); ?></p>
            <p class="fp-footer__desc"><?php bloginfo('description'); ?></p>
        </div>

        <div class="fp-footer__col">
            <h4>Explore</h4>
            <?php
            wp_nav_menu([
                'theme_location' => 'footer',
                'container' => false,
                'menu_class' => 'fp-footer__list',
                'fallback_cb' => function () {
                    echo '<ul class="fp-footer__list">';
                    echo '<li><a href="' . esc_url(get_post_type_archive_link('property')) . '">Properties</a></li>';
                    echo '<li><a href="' . esc_url(get_post_type_archive_link('agent')) . '">Agents</a></li>';
                    $about = get_page_by_path('about');
                    if ($about) {
                        echo '<li><a href="' . esc_url(get_permalink($about)) . '">About</a></li>';
                    }
                    $contact = get_page_by_path('contact');
                    if ($contact) {
                        echo '<li><a href="' . esc_url(get_permalink($contact)) . '">Contact</a></li>';
                    }
                    echo '</ul>';
                },
            ]);
            ?>
        </div>

        <div class="fp-footer__col">
            <h4>Contact</h4>
            <p><?php echo esc_html(get_theme_mod('fp_contact_phone', '(868) 000-0000')); ?></p>
            <p><?php echo esc_html(get_theme_mod('fp_contact_email', get_option('admin_email'))); ?></p>
        </div>
    </div>

    <div class="fp-container fp-footer__bottom">
        <p>&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>

        <?php
        $legalPages = [
            'privacy-policy' => 'Privacy Policy',
            'terms-of-use' => 'Terms of Use',
            'cookie-policy' => 'Cookie Policy',
        ];
        $legalLinks = [];
        foreach ($legalPages as $slug => $label) {
            $page = get_page_by_path($slug);
            if ($page) {
                $legalLinks[] = '<a href="' . esc_url(get_permalink($page)) . '">' . esc_html($label) . '</a>';
            }
        }
        ?>
        <?php if (!empty($legalLinks)) : ?>
            <p class="fp-footer__legal"><?php echo implode(' <span aria-hidden="true">&middot;</span> ', $legalLinks); ?></p>
        <?php endif; ?>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
