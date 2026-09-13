<?php

namespace FitsPropertiesCore\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Google Analytics (GA4), loaded only after the visitor has accepted
 * cookies via CookieConsent (checks the same fpc_cookie_consent cookie
 * and listens for the same consent event) — never loads unconditionally.
 */
class Analytics
{
    const OPTION_MEASUREMENT_ID = 'fpc_ga4_measurement_id';

    public function register()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public static function measurementId()
    {
        return trim(get_option(self::OPTION_MEASUREMENT_ID, ''));
    }

    public function enqueue()
    {
        $measurementId = self::measurementId();

        if (!$measurementId) {
            return;
        }

        wp_enqueue_script('fpc-analytics', FPC_PLUGIN_URL . 'assets/js/analytics.js', [], FPC_VERSION, true);
        wp_localize_script('fpc-analytics', 'fpAnalytics', [
            'measurementId' => $measurementId,
        ]);
    }
}
