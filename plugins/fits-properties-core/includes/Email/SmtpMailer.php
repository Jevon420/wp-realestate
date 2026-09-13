<?php

namespace FitsPropertiesCore\Email;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sends outgoing mail (contact form notifications, WP's own emails) via a
 * real SMTP account instead of the server's default mail() function,
 * which is frequently blocked or lands in spam on shared hosting. Off by
 * default — enable and fill in credentials from the Fits Properties
 * setup page once real SMTP credentials are available (e.g. after moving
 * to production hosting).
 */
class SmtpMailer
{
    const OPTION_ENABLED = 'fpc_smtp_enabled';
    const OPTION_HOST = 'fpc_smtp_host';
    const OPTION_PORT = 'fpc_smtp_port';
    const OPTION_ENCRYPTION = 'fpc_smtp_encryption';
    const OPTION_USERNAME = 'fpc_smtp_username';
    const OPTION_PASSWORD = 'fpc_smtp_password';
    const OPTION_FROM_EMAIL = 'fpc_smtp_from_email';
    const OPTION_FROM_NAME = 'fpc_smtp_from_name';

    public function register()
    {
        add_action('phpmailer_init', [$this, 'configure']);
        add_filter('wp_mail_from', [$this, 'filterFromEmail']);
        add_filter('wp_mail_from_name', [$this, 'filterFromName']);
    }

    public static function isEnabled()
    {
        return get_option(self::OPTION_ENABLED, '0') === '1';
    }

    public function configure($phpmailer)
    {
        if (!self::isEnabled()) {
            return;
        }

        $host = get_option(self::OPTION_HOST, '');

        if (!$host) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = $host;
        $phpmailer->Port = (int) get_option(self::OPTION_PORT, 587);

        $encryption = get_option(self::OPTION_ENCRYPTION, 'tls');
        if ($encryption && $encryption !== 'none') {
            $phpmailer->SMTPSecure = $encryption;
        }

        $username = get_option(self::OPTION_USERNAME, '');
        if ($username) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $username;
            $phpmailer->Password = get_option(self::OPTION_PASSWORD, '');
        }
    }

    public function filterFromEmail($email)
    {
        $fromEmail = get_option(self::OPTION_FROM_EMAIL, '');

        return $fromEmail ?: $email;
    }

    public function filterFromName($name)
    {
        $fromName = get_option(self::OPTION_FROM_NAME, '');

        return $fromName ?: $name;
    }
}
