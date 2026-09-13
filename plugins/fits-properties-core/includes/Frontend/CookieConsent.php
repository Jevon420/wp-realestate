<?php

namespace FitsPropertiesCore\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A self-contained cookie consent banner. Lives in the plugin (not the
 * theme) so it keeps working even if the site's theme is ever changed.
 * Currently the site doesn't set any non-essential cookies itself, but
 * this gives you a real consent record to check before loading anything
 * that would (Google Analytics, ad pixels, etc.) — see cookie-consent.js.
 */
class CookieConsent
{
    const OPTION_ENABLED = 'fpc_cookie_notice_enabled';
    const OPTION_MESSAGE = 'fpc_cookie_notice_message';

    public function register()
    {
        add_action('wp_footer', [$this, 'renderBanner']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public static function isEnabled()
    {
        return get_option(self::OPTION_ENABLED, '1') === '1';
    }

    public static function defaultMessage()
    {
        return 'We use cookies to keep this site working properly and, with your consent, to understand how it\'s used. See our Cookie Policy for details.';
    }

    public function enqueueAssets()
    {
        if (!self::isEnabled()) {
            return;
        }

        wp_enqueue_style('fpc-cookie-consent', FPC_PLUGIN_URL . 'assets/css/cookie-consent.css', [], FPC_VERSION);
        wp_enqueue_script('fpc-cookie-consent', FPC_PLUGIN_URL . 'assets/js/cookie-consent.js', [], FPC_VERSION, true);
    }

    public function renderBanner()
    {
        if (!self::isEnabled()) {
            return;
        }

        $message = get_option(self::OPTION_MESSAGE, self::defaultMessage());
        $policyPage = get_page_by_path('cookie-policy');
        ?>
        <div id="fpc-cookie-consent" class="fpc-cookie-consent" role="region" aria-label="Cookie notice" hidden>
            <p class="fpc-cookie-consent__text">
                <?php echo esc_html($message); ?>
                <?php if ($policyPage) : ?>
                    <a href="<?php echo esc_url(get_permalink($policyPage)); ?>">Cookie Policy</a>
                <?php endif; ?>
            </p>
            <div class="fpc-cookie-consent__actions">
                <button type="button" class="fpc-cookie-consent__btn fpc-cookie-consent__btn--secondary" data-fpc-consent="necessary">Necessary Only</button>
                <button type="button" class="fpc-cookie-consent__btn fpc-cookie-consent__btn--primary" data-fpc-consent="all">Accept All</button>
            </div>
        </div>
        <?php
    }
}
