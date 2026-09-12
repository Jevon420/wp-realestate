<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function () {
    add_theme_support('custom-logo', [
        'height' => 60,
        'width' => 220,
        'flex-height' => true,
        'flex-width' => true,
    ]);
});

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('fp_brand_colors', [
        'title' => 'Brand Colors',
        'priority' => 25,
        'description' => 'Change the two main colors used across the site (header, buttons, badges).',
    ]);

    $wp_customize->add_setting('fp_primary_color', [
        'default' => '#142942',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'fp_primary_color', [
        'label' => 'Primary Color (header, buttons)',
        'section' => 'fp_brand_colors',
    ]));

    $wp_customize->add_setting('fp_accent_color', [
        'default' => '#c9a15a',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'fp_accent_color', [
        'label' => 'Accent Color (highlights, "For Rent" badge)',
        'section' => 'fp_brand_colors',
    ]));

    $wp_customize->add_section('fp_contact_info', [
        'title' => 'Contact Info (footer & pages)',
        'priority' => 26,
    ]);

    $wp_customize->add_setting('fp_contact_phone', [
        'default' => '(868) 000-0000',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('fp_contact_phone', [
        'label' => 'Phone Number',
        'section' => 'fp_contact_info',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('fp_contact_email', [
        'default' => get_option('admin_email'),
        'sanitize_callback' => 'sanitize_email',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('fp_contact_email', [
        'label' => 'Contact Email',
        'section' => 'fp_contact_info',
        'type' => 'email',
    ]);

    $wp_customize->add_setting('fp_contact_address', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('fp_contact_address', [
        'label' => 'Office Address',
        'section' => 'fp_contact_info',
        'type' => 'text',
    ]);
});

/**
 * Darken a hex color by a percentage, used to derive the "dark" shade of
 * whatever color the client picks in the Customizer (used for footer bg etc).
 */
function fp_shade_color($hex, $percent)
{
    $hex = ltrim($hex, '#');

    if (strlen($hex) !== 6) {
        return '#' . $hex;
    }

    $r = max(0, min(255, hexdec(substr($hex, 0, 2)) * (1 - $percent)));
    $g = max(0, min(255, hexdec(substr($hex, 2, 2)) * (1 - $percent)));
    $b = max(0, min(255, hexdec(substr($hex, 4, 2)) * (1 - $percent)));

    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

add_action('wp_head', function () {
    $primary = get_theme_mod('fp_primary_color', '#142942');
    $accent = get_theme_mod('fp_accent_color', '#c9a15a');
    $primaryDark = fp_shade_color($primary, 0.25);
    $accentDark = fp_shade_color($accent, 0.15);
    ?>
    <style id="fp-customizer-colors">
        :root {
            --fp-navy: <?php echo esc_html($primary); ?>;
            --fp-navy-dark: <?php echo esc_html($primaryDark); ?>;
            --fp-gold: <?php echo esc_html($accent); ?>;
            --fp-gold-dark: <?php echo esc_html($accentDark); ?>;
        }
    </style>
    <?php
});
