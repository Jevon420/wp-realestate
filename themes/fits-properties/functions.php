<?php

if (!defined('ABSPATH')) {
    exit;
}

define('FP_THEME_DIR', get_template_directory());
define('FP_THEME_URI', get_template_directory_uri());
define('FP_THEME_VERSION', wp_get_theme()->get('Version'));

require_once FP_THEME_DIR . '/inc/setup.php';
require_once FP_THEME_DIR . '/inc/assets.php';
require_once FP_THEME_DIR . '/inc/helpers.php';
require_once FP_THEME_DIR . '/inc/queries.php';
require_once FP_THEME_DIR . '/inc/archive-filters.php';
require_once FP_THEME_DIR . '/inc/ajax-load-more.php';
require_once FP_THEME_DIR . '/inc/customizer.php';
require_once FP_THEME_DIR . '/inc/seo.php';
require_once FP_THEME_DIR . '/inc/updates.php';
