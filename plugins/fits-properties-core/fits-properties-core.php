<?php
/**
 * Plugin Name: Fits Properties Core
 * Description: Custom post types, taxonomies, and meta fields for the Fits Properties real estate site.
 * Version: 1.0.8
 * Author: Fits Properties
 * Text Domain: fits-properties-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FPC_PLUGIN_FILE', __FILE__);
define('FPC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FPC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FPC_VERSION', '1.0.0');

require_once FPC_PLUGIN_DIR . 'includes/Autoloader.php';
\FitsPropertiesCore\Autoloader::register();

register_activation_hook(__FILE__, ['FitsPropertiesCore\\Activator', 'activate']);
register_deactivation_hook(__FILE__, ['FitsPropertiesCore\\Deactivator', 'deactivate']);

\FitsPropertiesCore\Plugin::instance();
