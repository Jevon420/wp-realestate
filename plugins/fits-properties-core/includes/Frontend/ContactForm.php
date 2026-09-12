<?php

namespace FitsPropertiesCore\Frontend;

use FitsPropertiesCore\PostTypes\ContactMessage;

if (!defined('ABSPATH')) {
    exit;
}

class ContactForm
{
    const NONCE_ACTION = 'fp_contact_form';
    const ACTION = 'fp_contact_submit';

    public function register()
    {
        add_action('admin_post_' . self::ACTION, [$this, 'handle']);
        add_action('admin_post_nopriv_' . self::ACTION, [$this, 'handle']);
    }

    public function handle()
    {
        $redirectTo = wp_get_referer() ?: home_url('/');

        if (!isset($_POST['fp_contact_nonce']) || !wp_verify_nonce($_POST['fp_contact_nonce'], self::NONCE_ACTION)) {
            wp_safe_redirect(add_query_arg('fp_contact', 'error', $redirectTo));
            exit;
        }

        // Honeypot: real visitors never fill this hidden field.
        if (!empty($_POST['fp_website'])) {
            wp_safe_redirect(add_query_arg('fp_contact', 'success', $redirectTo));
            exit;
        }

        $name = isset($_POST['fp_name']) ? sanitize_text_field(wp_unslash($_POST['fp_name'])) : '';
        $email = isset($_POST['fp_email']) ? sanitize_email(wp_unslash($_POST['fp_email'])) : '';
        $phone = isset($_POST['fp_phone']) ? sanitize_text_field(wp_unslash($_POST['fp_phone'])) : '';
        $subject = isset($_POST['fp_subject']) ? sanitize_text_field(wp_unslash($_POST['fp_subject'])) : 'General Inquiry';
        $message = isset($_POST['fp_message']) ? sanitize_textarea_field(wp_unslash($_POST['fp_message'])) : '';

        if ($name === '' || $message === '' || !is_email($email)) {
            wp_safe_redirect(add_query_arg('fp_contact', 'error', $redirectTo));
            exit;
        }

        $postId = wp_insert_post([
            'post_type' => ContactMessage::POST_TYPE,
            'post_title' => $name,
            'post_status' => 'publish',
        ]);

        if (!is_wp_error($postId) && $postId) {
            update_post_meta($postId, 'fp_email', $email);
            update_post_meta($postId, 'fp_phone', $phone);
            update_post_meta($postId, 'fp_subject', $subject);
            update_post_meta($postId, 'fp_message', $message);

            $this->notifyAdmin($name, $email, $phone, $subject, $message);
        }

        wp_safe_redirect(add_query_arg('fp_contact', 'success', $redirectTo));
        exit;
    }

    private function notifyAdmin($name, $email, $phone, $subject, $message)
    {
        $to = get_option('admin_email');
        $siteName = get_bloginfo('name');

        $emailSubject = sprintf('[%s] New Contact Message: %s', $siteName, $subject);
        $body = "You have a new message from the {$siteName} contact form.\n\n"
            . "Name: {$name}\n"
            . "Email: {$email}\n"
            . "Phone: " . ($phone ?: '—') . "\n"
            . "Subject: {$subject}\n\n"
            . "Message:\n{$message}\n";

        $headers = ['Reply-To: ' . $email];

        wp_mail($to, $emailSubject, $body, $headers);
    }
}
