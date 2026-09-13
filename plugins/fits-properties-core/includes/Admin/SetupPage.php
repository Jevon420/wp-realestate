<?php

namespace FitsPropertiesCore\Admin;

use FitsPropertiesCore\Content\DemoContent;
use FitsPropertiesCore\Content\LegalContent;
use FitsPropertiesCore\Frontend\CookieConsent;
use FitsPropertiesCore\Frontend\Analytics;
use FitsPropertiesCore\Email\SmtpMailer;

if (!defined('ABSPATH')) {
    exit;
}

class SetupPage
{
    const NONCE_ACTION = 'fpc_install_demo_content';
    const COOKIE_NONCE_ACTION = 'fpc_save_cookie_settings';
    const LEGAL_NONCE_ACTION = 'fpc_refresh_legal_content';
    const ANALYTICS_NONCE_ACTION = 'fpc_save_analytics_settings';
    const SMTP_NONCE_ACTION = 'fpc_save_smtp_settings';
    const SMTP_TEST_NONCE_ACTION = 'fpc_send_test_email';

    public function register()
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_init', [$this, 'handleSubmit']);
        add_action('admin_init', [$this, 'handleCookieSettings']);
        add_action('admin_init', [$this, 'handleLegalContent']);
        add_action('admin_init', [$this, 'handleAnalyticsSettings']);
        add_action('admin_init', [$this, 'handleSmtpSettings']);
        add_action('admin_init', [$this, 'handleTestEmail']);
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

    public function handleLegalContent()
    {
        if (!isset($_POST['fpc_action']) || $_POST['fpc_action'] !== 'refresh_legal_content') {
            return;
        }

        if (!current_user_can('manage_options') || !isset($_POST['fpc_legal_nonce']) || !wp_verify_nonce($_POST['fpc_legal_nonce'], self::LEGAL_NONCE_ACTION)) {
            return;
        }

        $summary = LegalContent::apply();

        set_transient('fpc_legal_content_result', $summary, 60);

        wp_safe_redirect(admin_url('admin.php?page=fits-properties-setup&fpc_legal_done=1'));
        exit;
    }

    public function handleAnalyticsSettings()
    {
        if (!isset($_POST['fpc_action']) || $_POST['fpc_action'] !== 'save_analytics_settings') {
            return;
        }

        if (!current_user_can('manage_options') || !isset($_POST['fpc_analytics_nonce']) || !wp_verify_nonce($_POST['fpc_analytics_nonce'], self::ANALYTICS_NONCE_ACTION)) {
            return;
        }

        $measurementId = isset($_POST['fpc_ga4_measurement_id']) ? sanitize_text_field(wp_unslash($_POST['fpc_ga4_measurement_id'])) : '';
        update_option(Analytics::OPTION_MEASUREMENT_ID, $measurementId);

        wp_safe_redirect(admin_url('admin.php?page=fits-properties-setup&fpc_analytics_saved=1'));
        exit;
    }

    public function handleSmtpSettings()
    {
        if (!isset($_POST['fpc_action']) || $_POST['fpc_action'] !== 'save_smtp_settings') {
            return;
        }

        if (!current_user_can('manage_options') || !isset($_POST['fpc_smtp_nonce']) || !wp_verify_nonce($_POST['fpc_smtp_nonce'], self::SMTP_NONCE_ACTION)) {
            return;
        }

        update_option(SmtpMailer::OPTION_ENABLED, isset($_POST['fpc_smtp_enabled']) ? '1' : '0');
        update_option(SmtpMailer::OPTION_HOST, sanitize_text_field(wp_unslash($_POST['fpc_smtp_host'] ?? '')));
        update_option(SmtpMailer::OPTION_PORT, (int) ($_POST['fpc_smtp_port'] ?? 587));
        update_option(SmtpMailer::OPTION_ENCRYPTION, sanitize_key($_POST['fpc_smtp_encryption'] ?? 'tls'));
        update_option(SmtpMailer::OPTION_USERNAME, sanitize_text_field(wp_unslash($_POST['fpc_smtp_username'] ?? '')));
        update_option(SmtpMailer::OPTION_FROM_EMAIL, sanitize_email(wp_unslash($_POST['fpc_smtp_from_email'] ?? '')));
        update_option(SmtpMailer::OPTION_FROM_NAME, sanitize_text_field(wp_unslash($_POST['fpc_smtp_from_name'] ?? '')));

        // Only overwrite the stored password if a new one was actually
        // typed — the field is always rendered blank, so a blank submit
        // just means "leave it as it was."
        if (!empty($_POST['fpc_smtp_password'])) {
            update_option(SmtpMailer::OPTION_PASSWORD, wp_unslash($_POST['fpc_smtp_password']));
        }

        wp_safe_redirect(admin_url('admin.php?page=fits-properties-setup&fpc_smtp_saved=1'));
        exit;
    }

    public function handleTestEmail()
    {
        if (!isset($_POST['fpc_action']) || $_POST['fpc_action'] !== 'send_test_email') {
            return;
        }

        if (!current_user_can('manage_options') || !isset($_POST['fpc_smtp_test_nonce']) || !wp_verify_nonce($_POST['fpc_smtp_test_nonce'], self::SMTP_TEST_NONCE_ACTION)) {
            return;
        }

        $to = get_option('admin_email');
        $sent = wp_mail($to, 'Fits Properties test email', "If you're reading this, outgoing email is working correctly.\n\nSent " . current_time('mysql') . '.');

        set_transient('fpc_test_email_result', $sent ? 'success' : 'failure', 60);

        wp_safe_redirect(admin_url('admin.php?page=fits-properties-setup&fpc_test_email=1'));
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

            <?php if (isset($_GET['fpc_legal_done'])) : ?>
                <?php $legalResult = get_transient('fpc_legal_content_result'); delete_transient('fpc_legal_content_result'); ?>
                <div class="notice notice-success">
                    <p>
                        <strong>Legal content updated.</strong>
                        <?php if ($legalResult) : ?>
                            <?php echo (int) $legalResult['updated']; ?> page(s) updated,
                            <?php echo (int) $legalResult['skipped']; ?> left as-is (already edited or not found).
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['fpc_analytics_saved'])) : ?>
                <div class="notice notice-success"><p><strong>Analytics settings saved.</strong></p></div>
            <?php endif; ?>

            <?php if (isset($_GET['fpc_smtp_saved'])) : ?>
                <div class="notice notice-success"><p><strong>Email delivery settings saved.</strong></p></div>
            <?php endif; ?>

            <?php if (isset($_GET['fpc_test_email'])) : ?>
                <?php $testResult = get_transient('fpc_test_email_result'); delete_transient('fpc_test_email_result'); ?>
                <?php if ($testResult === 'success') : ?>
                    <div class="notice notice-success"><p><strong>Test email sent</strong> to <?php echo esc_html(get_option('admin_email')); ?>. Check your inbox (and spam folder).</p></div>
                <?php else : ?>
                    <div class="notice notice-error"><p><strong>Test email failed to send.</strong> Double-check your SMTP settings below, or check your host's error log for details.</p></div>
                <?php endif; ?>
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

                <div class="fpc-setup-card fpc-setup-card--wide">
                    <h2>6. Legal Pages</h2>
                    <p>
                        Your Privacy Policy, Terms of Use, and Cookie Policy pages currently have placeholder text.
                        This button replaces them with a fuller starting template — <strong>not legal advice</strong>,
                        and it should be reviewed (with the bracketed details filled in) before the site goes live.
                        It only touches a page if you haven't already edited it yourself.
                    </p>
                    <form method="post">
                        <?php wp_nonce_field(self::LEGAL_NONCE_ACTION, 'fpc_legal_nonce'); ?>
                        <input type="hidden" name="fpc_action" value="refresh_legal_content">
                        <button type="submit" class="button button-primary">Insert Starter Legal Content</button>
                    </form>
                </div>

                <div class="fpc-setup-card fpc-setup-card--wide">
                    <h2>7. Analytics</h2>
                    <p>Add your Google Analytics (GA4) Measurement ID to start tracking visits. It only loads after a visitor accepts cookies in the cookie banner above — never unconditionally.</p>
                    <form method="post">
                        <?php wp_nonce_field(self::ANALYTICS_NONCE_ACTION, 'fpc_analytics_nonce'); ?>
                        <input type="hidden" name="fpc_action" value="save_analytics_settings">
                        <p>
                            <label for="fpc_ga4_measurement_id"><strong>GA4 Measurement ID</strong></label><br>
                            <input type="text" name="fpc_ga4_measurement_id" id="fpc_ga4_measurement_id" class="regular-text" placeholder="G-XXXXXXXXXX" value="<?php echo esc_attr(Analytics::measurementId()); ?>">
                        </p>
                        <button type="submit" class="button button-primary">Save Analytics Settings</button>
                    </form>
                </div>

                <div class="fpc-setup-card fpc-setup-card--wide">
                    <h2>8. Email Delivery (SMTP)</h2>
                    <p>
                        By default, WordPress sends email (like contact form notifications) using your server's built-in mail
                        function, which many hosts block or send straight to spam. Turn this on and fill in real SMTP
                        credentials — from your hosting provider's email account, or a service like Brevo/SendGrid — for
                        reliable delivery once the site is live.
                    </p>
                    <form method="post">
                        <?php wp_nonce_field(self::SMTP_NONCE_ACTION, 'fpc_smtp_nonce'); ?>
                        <input type="hidden" name="fpc_action" value="save_smtp_settings">

                        <label class="fpc-switch" style="margin-bottom: 12px;">
                            <input type="checkbox" name="fpc_smtp_enabled" value="1" <?php checked(SmtpMailer::isEnabled()); ?>>
                            <span class="fpc-switch__track"><span class="fpc-switch__thumb"></span></span>
                            Send email via SMTP instead of the server default
                        </label>

                        <table class="form-table fpc-form-table">
                            <tr>
                                <th><label for="fpc_smtp_host">SMTP Host</label></th>
                                <td><input type="text" name="fpc_smtp_host" id="fpc_smtp_host" class="regular-text" placeholder="smtp.yourhost.com" value="<?php echo esc_attr(get_option(SmtpMailer::OPTION_HOST, '')); ?>"></td>
                            </tr>
                            <tr>
                                <th><label for="fpc_smtp_port">Port</label></th>
                                <td>
                                    <input type="number" name="fpc_smtp_port" id="fpc_smtp_port" class="small-text" value="<?php echo esc_attr(get_option(SmtpMailer::OPTION_PORT, 587)); ?>">
                                    <select name="fpc_smtp_encryption">
                                        <?php foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $value => $label) : ?>
                                            <option value="<?php echo esc_attr($value); ?>" <?php selected(get_option(SmtpMailer::OPTION_ENCRYPTION, 'tls'), $value); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="fpc_smtp_username">Username</label></th>
                                <td><input type="text" name="fpc_smtp_username" id="fpc_smtp_username" class="regular-text" value="<?php echo esc_attr(get_option(SmtpMailer::OPTION_USERNAME, '')); ?>"></td>
                            </tr>
                            <tr>
                                <th><label for="fpc_smtp_password">Password</label></th>
                                <td>
                                    <input type="password" name="fpc_smtp_password" id="fpc_smtp_password" class="regular-text" placeholder="<?php echo get_option(SmtpMailer::OPTION_PASSWORD, '') ? 'Saved — leave blank to keep it' : ''; ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="fpc_smtp_from_email">"From" Email</label></th>
                                <td><input type="email" name="fpc_smtp_from_email" id="fpc_smtp_from_email" class="regular-text" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>" value="<?php echo esc_attr(get_option(SmtpMailer::OPTION_FROM_EMAIL, '')); ?>"></td>
                            </tr>
                            <tr>
                                <th><label for="fpc_smtp_from_name">"From" Name</label></th>
                                <td><input type="text" name="fpc_smtp_from_name" id="fpc_smtp_from_name" class="regular-text" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" value="<?php echo esc_attr(get_option(SmtpMailer::OPTION_FROM_NAME, '')); ?>"></td>
                            </tr>
                        </table>

                        <button type="submit" class="button button-primary">Save Email Settings</button>
                    </form>

                    <form method="post" style="margin-top: 12px;">
                        <?php wp_nonce_field(self::SMTP_TEST_NONCE_ACTION, 'fpc_smtp_test_nonce'); ?>
                        <input type="hidden" name="fpc_action" value="send_test_email">
                        <button type="submit" class="button button-secondary">Send Test Email to <?php echo esc_html(get_option('admin_email')); ?></button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
