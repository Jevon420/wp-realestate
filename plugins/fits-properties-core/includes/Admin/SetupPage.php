<?php

namespace FitsPropertiesCore\Admin;

use FitsPropertiesCore\Content\DemoContent;
use FitsPropertiesCore\Frontend\CookieConsent;

if (!defined('ABSPATH')) {
    exit;
}

class SetupPage
{
    const NONCE_ACTION = 'fpc_install_demo_content';
    const COOKIE_NONCE_ACTION = 'fpc_save_cookie_settings';

    public function register()
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_init', [$this, 'handleSubmit']);
        add_action('admin_init', [$this, 'handleCookieSettings']);
    }

    public function addMenu()
    {
        add_menu_page(
            'Fits Properties',
            'Fits Properties',
            'manage_options',
            'fits-properties-setup',
            [$this, 'render'],
            'dashicons-admin-home',
            3
        );
    }

    public function handleSubmit()
    {
        if (!isset($_POST['fpc_action']) || $_POST['fpc_action'] !== 'install_demo_content') {
            return;
        }

        if (!current_user_can('manage_options') || !isset($_POST['fpc_nonce']) || !wp_verify_nonce($_POST['fpc_nonce'], self::NONCE_ACTION)) {
            return;
        }

        $summary = DemoContent::seed();

        set_transient('fpc_demo_content_result', $summary, 60);

        wp_safe_redirect(admin_url('admin.php?page=fits-properties-setup&fpc_seeded=1'));
        exit;
    }

    public function handleCookieSettings()
    {
        if (!isset($_POST['fpc_action']) || $_POST['fpc_action'] !== 'save_cookie_settings') {
            return;
        }

        if (!current_user_can('manage_options') || !isset($_POST['fpc_cookie_nonce']) || !wp_verify_nonce($_POST['fpc_cookie_nonce'], self::COOKIE_NONCE_ACTION)) {
            return;
        }

        update_option(CookieConsent::OPTION_ENABLED, isset($_POST['fpc_cookie_enabled']) ? '1' : '0');

        $message = isset($_POST['fpc_cookie_message']) ? sanitize_textarea_field(wp_unslash($_POST['fpc_cookie_message'])) : '';
        update_option(CookieConsent::OPTION_MESSAGE, $message !== '' ? $message : CookieConsent::defaultMessage());

        wp_safe_redirect(admin_url('admin.php?page=fits-properties-setup&fpc_cookie_saved=1'));
        exit;
    }

    public function render()
    {
        $result = get_transient('fpc_demo_content_result');
        delete_transient('fpc_demo_content_result');
        ?>
        <div class="wrap fpc-setup-page">
            <h1>Fits Properties — Site Setup</h1>
            <p class="description">Use this page to get your site ready to hand off, or to add more sample data while you're building it out.</p>

            <?php if ($result) : ?>
                <div class="notice notice-success">
                    <p>
                        <strong>Demo content installed.</strong>
                        Added <?php echo (int) $result['properties']; ?> properties,
                        <?php echo (int) $result['agents']; ?> agents,
                        <?php echo (int) $result['testimonials']; ?> testimonials,
                        <?php echo (int) $result['pages']; ?> pages, and
                        <?php echo (int) $result['terms']; ?> categories/locations
                        (<?php echo (int) $result['skipped']; ?> already existed and were left as-is).
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['fpc_cookie_saved'])) : ?>
                <div class="notice notice-success"><p><strong>Cookie notice settings saved.</strong></p></div>
            <?php endif; ?>

            <div class="fpc-setup-cards">
                <div class="fpc-setup-card">
                    <h2>1. Sample Data</h2>
                    <p>Adds demo properties, agents, testimonials, and starter pages (About, Contact, Privacy, Terms) with linked stock photos — so the site looks complete right away. Safe to click more than once; it won't duplicate anything that already exists by name.</p>
                    <form method="post">
                        <?php wp_nonce_field(self::NONCE_ACTION, 'fpc_nonce'); ?>
                        <input type="hidden" name="fpc_action" value="install_demo_content">
                        <button type="submit" class="button button-primary button-hero">Install Demo Content</button>
                    </form>
                </div>

                <div class="fpc-setup-card">
                    <h2>2. Look &amp; Feel</h2>
                    <p>Change the site's colors, upload a logo, and set the phone number/email shown in the footer and contact page.</p>
                    <a class="button button-secondary" href="<?php echo esc_url(admin_url('customize.php')); ?>">Open Customizer</a>
                </div>

                <div class="fpc-setup-card">
                    <h2>3. Navigation Menu</h2>
                    <p>The demo content sets up a starter menu automatically. Edit it any time to add or reorder links.</p>
                    <a class="button button-secondary" href="<?php echo esc_url(admin_url('nav-menus.php')); ?>">Edit Menus</a>
                </div>

                <div class="fpc-setup-card">
                    <h2>4. Manage Content</h2>
                    <p>Once you're ready, replace the sample listings with real ones.</p>
                    <a class="button button-secondary" href="<?php echo esc_url(admin_url('edit.php?post_type=property')); ?>">Manage Properties</a>
                    <a class="button button-secondary" href="<?php echo esc_url(admin_url('edit.php?post_type=agent')); ?>">Manage Agents</a>
                    <a class="button button-secondary" href="<?php echo esc_url(admin_url('edit.php?post_type=testimonial')); ?>">Manage Testimonials</a>
                </div>

                <div class="fpc-setup-card fpc-setup-card--wide">
                    <h2>5. Cookie Notice</h2>
                    <p>
                        A cookie consent banner shown to first-time visitors, linking to your Cookie Policy page. Keeps working
                        even if you change themes later, since it's built into this plugin rather than the theme.
                        <?php $cookiePage = get_page_by_path('cookie-policy'); ?>
                        <?php if ($cookiePage) : ?>
                            <a href="<?php echo esc_url(get_edit_post_link($cookiePage)); ?>">Edit the Cookie Policy page</a>.
                        <?php endif; ?>
                    </p>
                    <form method="post">
                        <?php wp_nonce_field(self::COOKIE_NONCE_ACTION, 'fpc_cookie_nonce'); ?>
                        <input type="hidden" name="fpc_action" value="save_cookie_settings">

                        <label class="fpc-switch" style="margin-bottom: 12px;">
                            <input type="checkbox" name="fpc_cookie_enabled" value="1" <?php checked(CookieConsent::isEnabled()); ?>>
                            <span class="fpc-switch__track"><span class="fpc-switch__thumb"></span></span>
                            Show the cookie notice banner
                        </label>

                        <p>
                            <label for="fpc_cookie_message"><strong>Banner Message</strong></label><br>
                            <textarea name="fpc_cookie_message" id="fpc_cookie_message" rows="3" class="large-text"><?php echo esc_textarea(get_option(CookieConsent::OPTION_MESSAGE, CookieConsent::defaultMessage())); ?></textarea>
                        </p>

                        <button type="submit" class="button button-primary">Save Cookie Settings</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
